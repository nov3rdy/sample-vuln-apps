"use strict";

const { buildTestApp } = require("./test-helpers");

const categoryRoutes = require("../src/routes/categories");

const SAMPLE = {
  id: 1,
  name: "Documentation",
  color: "#3B82F6",
  created_at: "2026-04-22T10:00:00.000Z",
  updated_at: "2026-04-22T10:00:00.000Z",
};

async function buildApp(queries = {}) {
  const app = buildTestApp(queries);
  app.register(categoryRoutes);
  await app.ready();
  return app;
}

describe("Categories CRUD", () => {
  // ─── CREATE ──────────────────────────────────────────────────────────
  describe("POST /categories", () => {
    test("creates a category and returns 201", async () => {
      const app = await buildApp({
        "INSERT INTO categories": { rows: [SAMPLE], rowCount: 1 },
      });

      const res = await app.inject({
        method: "POST",
        url: "/categories",
        payload: { name: "Documentation", color: "#3B82F6" },
      });

      expect(res.statusCode).toBe(201);
      expect(res.json()).toMatchObject({ id: 1, name: "Documentation" });
    });

    // V10: No input validation — docs say name is required
    test("accepts empty body — no validation (V10)", async () => {
      const app = await buildApp({
        "INSERT INTO categories": {
          rows: [{ ...SAMPLE, name: null, color: null }],
          rowCount: 1,
        },
      });

      const res = await app.inject({
        method: "POST",
        url: "/categories",
        payload: {},
      });

      expect(res.statusCode).toBe(201);
      expect(app.pg.query).toHaveBeenCalledWith(
        expect.any(String),
        expect.arrayContaining([null, null])
      );
    });

    // V10: No input validation — docs say max 100 chars
    test("accepts name exceeding 100 chars — no length validation (V10)", async () => {
      const longName = "X".repeat(200);
      const app = await buildApp({
        "INSERT INTO categories": {
          rows: [{ ...SAMPLE, name: longName }],
          rowCount: 1,
        },
      });

      const res = await app.inject({
        method: "POST",
        url: "/categories",
        payload: { name: longName },
      });

      expect(res.statusCode).toBe(201);
      expect(app.pg.query).toHaveBeenCalledWith(
        expect.any(String),
        expect.arrayContaining([longName])
      );
    });

    // V10: No input validation — docs say hex color format
    test("accepts invalid color format — no format validation (V10)", async () => {
      const app = await buildApp({
        "INSERT INTO categories": {
          rows: [{ ...SAMPLE, color: "not-a-hex-color" }],
          rowCount: 1,
        },
      });

      const res = await app.inject({
        method: "POST",
        url: "/categories",
        payload: { name: "Test", color: "not-a-hex-color" },
      });

      expect(res.statusCode).toBe(201);
      expect(app.pg.query).toHaveBeenCalledWith(
        expect.any(String),
        expect.arrayContaining(["not-a-hex-color"])
      );
    });

    test("stores null color when omitted", async () => {
      const app = await buildApp({
        "INSERT INTO categories": {
          rows: [{ ...SAMPLE, color: null }],
          rowCount: 1,
        },
      });

      const res = await app.inject({
        method: "POST",
        url: "/categories",
        payload: { name: "Test" },
      });

      expect(res.statusCode).toBe(201);
      expect(res.json().color).toBeNull();
    });
  });

  // ─── READ ALL ────────────────────────────────────────────────────────
  describe("GET /categories", () => {
    test("returns all categories", async () => {
      const app = await buildApp({
        "SELECT * FROM categories": {
          rows: [{ ...SAMPLE, id: 2, name: "Tutorials" }, SAMPLE],
        },
      });

      const res = await app.inject({ method: "GET", url: "/categories" });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toHaveLength(2);
    });

    test("returns empty array when none exist", async () => {
      const app = await buildApp({
        "SELECT * FROM categories": { rows: [] },
      });

      const res = await app.inject({ method: "GET", url: "/categories" });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toEqual([]);
    });
  });

  // ─── READ ONE ────────────────────────────────────────────────────────
  describe("GET /categories/:id", () => {
    test("returns a single category", async () => {
      const app = await buildApp({
        "WHERE id = $1": { rows: [SAMPLE] },
      });

      const res = await app.inject({ method: "GET", url: "/categories/1" });

      expect(res.statusCode).toBe(200);
      expect(res.json()).toMatchObject({ id: 1, name: "Documentation" });
    });

    test("returns 404 when not found", async () => {
      const app = await buildApp({
        "WHERE id = $1": { rows: [] },
      });

      const res = await app.inject({ method: "GET", url: "/categories/999" });

      expect(res.statusCode).toBe(404);
      expect(res.json().error).toBe("Category not found");
    });
  });

  // ─── UPDATE ──────────────────────────────────────────────────────────
  describe("PUT /categories/:id", () => {
    test("updates a category", async () => {
      const updated = { ...SAMPLE, name: "Docs & References" };
      const app = await buildApp({
        "SELECT * FROM categories WHERE id = $1": { rows: [SAMPLE] },
        "UPDATE categories": { rows: [updated], rowCount: 1 },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/categories/1",
        payload: { name: "Docs & References" },
      });

      expect(res.statusCode).toBe(200);
      expect(res.json().name).toBe("Docs & References");
    });

    test("returns 404 when not found", async () => {
      const app = await buildApp({
        "SELECT * FROM categories WHERE id = $1": { rows: [] },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/categories/999",
        payload: { name: "X" },
      });

      expect(res.statusCode).toBe(404);
    });

    test("allows clearing color with null", async () => {
      const updated = { ...SAMPLE, color: null };
      const app = await buildApp({
        "SELECT * FROM categories WHERE id = $1": { rows: [SAMPLE] },
        "UPDATE categories": { rows: [updated], rowCount: 1 },
      });

      const res = await app.inject({
        method: "PUT",
        url: "/categories/1",
        payload: { color: null },
      });

      expect(res.statusCode).toBe(200);
    });
  });

  // ─── DELETE ──────────────────────────────────────────────────────────
  describe("DELETE /categories/:id", () => {
    test("deletes and returns 204", async () => {
      const app = await buildApp({
        "DELETE FROM categories": { rows: [{ id: 1 }], rowCount: 1 },
      });

      const res = await app.inject({ method: "DELETE", url: "/categories/1" });

      expect(res.statusCode).toBe(204);
      expect(res.body).toBe("");
    });

    test("returns 404 when not found", async () => {
      const app = await buildApp({
        "DELETE FROM categories": { rows: [], rowCount: 0 },
      });

      const res = await app.inject({ method: "DELETE", url: "/categories/999" });

      expect(res.statusCode).toBe(404);
      expect(res.json().error).toBe("Category not found");
    });
  });
});
