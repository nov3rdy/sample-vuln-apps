"use strict";

const { buildTestApp } = require("./test-helpers");

const bookmarkRoutes = require("../src/routes/bookmarks");

const SAMPLE = {
  id: 1,
  title: "Fastify Docs",
  url: "https://fastify.dev",
  description: "Official documentation",
  owner_email: "alice@example.com",
  created_at: "2026-04-22T10:00:00.000Z",
  updated_at: "2026-04-22T10:00:00.000Z",
};

async function buildApp(queries = {}) {
  const app = buildTestApp(queries);
  app.register(bookmarkRoutes);
  await app.ready();
  return app;
}

describe("Bookmarks CRUD", () => {
  // ─── CREATE ──────────────────────────────────────────────────────────
  describe("POST /bookmarks", () => {
    test("creates a bookmark and returns 201", async () => {
      const app = await buildApp({
        "INSERT INTO bookmarks": { rows: [SAMPLE], rowCount: 1 },
      });

      const res = await app.inject({
        method: "POST",
        url: "/bookmarks",
        payload: { title: "Fastify Docs", url: "https://fastify.dev", description: "test" },
      });

      expect(res.statusCode).toBe(201);
      expect(res.json()).toMatchObject({ id: 1, title: "Fastify Docs" });
    });

    test("accepts owner_email field (V3: IDOR)", async () => {
      const app = await buildApp({
        "INSERT INTO bookmarks": { rows: [SAMPLE], rowCount: 1 },
      });

      await app.inject({
        method: "POST",
        url: "/bookmarks",
        payload: { title: "T", url: "http://a.com", owner_email: "eve@evil.com" },
      });

      expect(app.pg.query).toHaveBeenCalledWith(
        expect.stringContaining("INSERT INTO bookmarks"),
        expect.arrayContaining(["eve@evil.com"])
      );
    });

    // V10: No input validation
    test("accepts empty title and url — no validation (V10)", async () => {
      const app = await buildApp({
        "INSERT INTO bookmarks": { rows: [{ ...SAMPLE, title: null, url: null }], rowCount: 1 },
      });

      const res = await app.inject({
        method: "POST",
        url: "/bookmarks",
        payload: {},
      });

      expect(res.statusCode).toBe(201);
      // Verify null was passed for both title and url
      expect(app.pg.query).toHaveBeenCalledWith(
        expect.any(String),
        expect.arrayContaining([null, null])
      );
    });

    test("accepts title exceeding 255 chars — no length validation (V10)", async () => {
      const longTitle = "A".repeat(500);
      const app = await buildApp({
        "INSERT INTO bookmarks": {
          rows: [{ ...SAMPLE, title: longTitle }],
          rowCount: 1,
        },
      });

      const res = await app.inject({
        method: "POST",
        url: "/bookmarks",
        payload: { title: longTitle, url: "http://a.com" },
      });

      expect(res.statusCode).toBe(201);
      expect(app.pg.query).toHaveBeenCalledWith(
        expect.any(String),
        expect.arrayContaining([longTitle])
      );
    });
  });

  // ─── READ ALL ────────────────────────────────────────────────────────
  describe("GET /bookmarks", () => {
    test("returns all bookmarks", async () => {
      const app = await buildApp({
        "SELECT * FROM bookmarks": {
          rows: [{ ...SAMPLE, id: 2 }, SAMPLE],
        },
      });

      const res = await app.inject({ method: "GET", url: "/bookmarks" });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toHaveLength(2);
    });

    test("returns empty array when none exist", async () => {
      const app = await buildApp({
        "SELECT * FROM bookmarks": { rows: [] },
      });

      const res = await app.inject({ method: "GET", url: "/bookmarks" });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toEqual([]);
    });

    // V3: IDOR — returns bookmarks from all owners
    test("returns bookmarks from all owners — no filtering (V3)", async () => {
      const allBookmarks = [
        { ...SAMPLE, owner_email: "alice@example.com" },
        { ...SAMPLE, id: 2, owner_email: "bob@example.com" },
      ];
      const app = await buildApp({
        "SELECT * FROM bookmarks": { rows: allBookmarks },
      });

      const res = await app.inject({ method: "GET", url: "/bookmarks" });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toHaveLength(2);
    });
  });

  // ─── SEARCH (V1: SQL Injection) ──────────────────────────────────────
  describe("GET /bookmarks/search", () => {
    test("returns matching results", async () => {
      const app = await buildApp({
        "WHERE title ILIKE": { rows: [SAMPLE] },
      });

      const res = await app.inject({
        method: "GET",
        url: "/bookmarks/search?q=fastify",
      });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toHaveLength(1);
    });

    test("returns empty array when no matches", async () => {
      const app = await buildApp({
        "WHERE title ILIKE": { rows: [] },
      });

      const res = await app.inject({
        method: "GET",
        url: "/bookmarks/search?q=nothing",
      });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toEqual([]);
    });

    test("search works without q parameter — no validation (V10)", async () => {
      const app = await buildApp({
        "WHERE title ILIKE": { rows: [] },
      });

      const res = await app.inject({
        method: "GET",
        url: "/bookmarks/search",
      });

      // Should still execute (undefined in query string)
      expect(res.statusCode).toBe(200);
    });

    test("raw user input appears directly in SQL — not parameterized (V1)", async () => {
      const app = await buildApp({
        "WHERE title ILIKE": { rows: [] },
      });

      const malicious = "' UNION SELECT id, email, password_hash, 1,1,1,1 FROM users --";

      await app.inject({
        method: "GET",
        url: `/bookmarks/search?q=${encodeURIComponent(malicious)}`,
      });

      const calledSql = app.pg.query.mock.calls[0][0];

      // VULNERABLE: The malicious input is embedded raw in SQL
      expect(calledSql).toContain("UNION SELECT");
      expect(calledSql).toContain("FROM users");
      // NOT using parameterized query ($1)
      expect(calledSql).not.toContain("$1");
    });

    test("SQL injection via LIKE clause terminator (V1)", async () => {
      const app = await buildApp({
        "WHERE title ILIKE": { rows: [] },
      });

      await app.inject({
        method: "GET",
        url: "/bookmarks/search?q=%25' OR 1=1 --",
      });

      const calledSql = app.pg.query.mock.calls[0][0];
      expect(calledSql).toContain("OR 1=1");
    });
  });

  // ─── READ ONE (V3: IDOR) ─────────────────────────────────────────────
  describe("GET /bookmarks/:id", () => {
    test("returns a single bookmark", async () => {
      const app = await buildApp({
        "WHERE id = $1": { rows: [SAMPLE] },
      });

      const res = await app.inject({ method: "GET", url: "/bookmarks/1" });

      expect(res.statusCode).toBe(200);
      expect(res.json().id).toBe(1);
    });

    test("returns 404 when not found", async () => {
      const app = await buildApp({
        "WHERE id = $1": { rows: [] },
      });

      const res = await app.inject({ method: "GET", url: "/bookmarks/999" });

      expect(res.statusCode).toBe(404);
    });

    // V3: IDOR — no ownership check, any user can read any bookmark
    test("returns bookmark regardless of owner — no ownership check (V3)", async () => {
      const bobBookmark = { ...SAMPLE, owner_email: "bob@example.com" };
      const app = await buildApp({
        "WHERE id = $1": { rows: [bobBookmark] },
      });

      // Requester is alice, but can read bob's bookmark
      const res = await app.inject({
        method: "GET",
        url: "/bookmarks/1",
        headers: { authorization: "Bearer <alice_token>" },
      });

      expect(res.statusCode).toBe(200);
      expect(res.json().owner_email).toBe("bob@example.com");
    });
  });

  // ─── UPDATE (V4: Mass Assignment) ────────────────────────────────────
  describe("PUT /bookmarks/:id", () => {
    test("updates a bookmark", async () => {
      const updated = { ...SAMPLE, title: "New Title" };
      const app = await buildApp({
        "SELECT * FROM bookmarks WHERE id = $1": { rows: [SAMPLE] },
        "UPDATE bookmarks": { rows: [updated], rowCount: 1 },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/bookmarks/1",
        payload: { title: "New Title" },
      });

      expect(res.statusCode).toBe(200);
      expect(res.json().title).toBe("New Title");
    });

    test("returns 404 when not found", async () => {
      const app = await buildApp({
        "SELECT * FROM bookmarks WHERE id = $1": { rows: [] },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/bookmarks/999",
        payload: { title: "X" },
      });

      expect(res.statusCode).toBe(404);
    });

    test("allows overwriting owner_email via body (V4: Mass Assignment)", async () => {
      const updated = { ...SAMPLE, owner_email: "eve@evil.com" };
      const app = await buildApp({
        "SELECT * FROM bookmarks WHERE id = $1": { rows: [SAMPLE] },
        "UPDATE bookmarks": { rows: [updated], rowCount: 1 },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/bookmarks/1",
        payload: { owner_email: "eve@evil.com" },
      });

      expect(res.statusCode).toBe(200);
      const calledSql = app.pg.query.mock.calls[1][0];
      const calledParams = app.pg.query.mock.calls[1][1];
      expect(calledSql).toContain("owner_email");
      expect(calledParams).toContain("eve@evil.com");
    });

    test("allows overwriting created_at via body (V4: Mass Assignment)", async () => {
      const fakeDate = "2000-01-01T00:00:00.000Z";
      const updated = { ...SAMPLE, created_at: fakeDate };
      const app = await buildApp({
        "SELECT * FROM bookmarks WHERE id = $1": { rows: [SAMPLE] },
        "UPDATE bookmarks": { rows: [updated], rowCount: 1 },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/bookmarks/1",
        payload: { created_at: fakeDate },
      });

      expect(res.statusCode).toBe(200);
      const calledParams = app.pg.query.mock.calls[1][1];
      expect(calledParams).toContain(fakeDate);
    });

    // V10: No input validation on update
    test("accepts empty string title on update (V10)", async () => {
      const updated = { ...SAMPLE, title: "" };
      const app = await buildApp({
        "SELECT * FROM bookmarks WHERE id = $1": { rows: [SAMPLE] },
        "UPDATE bookmarks": { rows: [updated], rowCount: 1 },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/bookmarks/1",
        payload: { title: "" },
      });

      expect(res.statusCode).toBe(200);
    });
  });

  // ─── DELETE ──────────────────────────────────────────────────────────
  describe("DELETE /bookmarks/:id", () => {
    test("deletes and returns 204", async () => {
      const app = await buildApp({
        "DELETE FROM bookmarks": { rows: [{ id: 1 }], rowCount: 1 },
      });

      const res = await app.inject({ method: "DELETE", url: "/bookmarks/1" });

      expect(res.statusCode).toBe(204);
      expect(res.body).toBe("");
    });

    test("returns 404 when not found", async () => {
      const app = await buildApp({
        "DELETE FROM bookmarks": { rows: [], rowCount: 0 },
      });

      const res = await app.inject({ method: "DELETE", url: "/bookmarks/999" });

      expect(res.statusCode).toBe(404);
    });

    // V3: IDOR — can delete any bookmark regardless of ownership
    test("deletes bookmark regardless of owner (V3: IDOR)", async () => {
      const app = await buildApp({
        "DELETE FROM bookmarks": { rows: [{ id: 1 }], rowCount: 1 },
      });

      // No authorization header, no ownership check
      const res = await app.inject({
        method: "DELETE",
        url: "/bookmarks/1",
      });

      expect(res.statusCode).toBe(204);
    });
  });
});
