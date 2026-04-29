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

describe("V1: SQL Injection — GET /bookmarks/search", () => {
  test("returns results matching the query", async () => {
    const app = await buildApp({
      "WHERE title ILIKE": { rows: [SAMPLE] },
    });

    const res = await app.inject({
      method: "GET",
      url: "/bookmarks/search?q=fastify",
    });

    expect(res.statusCode).toBe(200);
    expect(res.json()).toHaveLength(1);
    expect(res.json()[0].title).toBe("Fastify Docs");
  });

  test("returns empty array when nothing matches", async () => {
    const app = await buildApp({
      "WHERE title ILIKE": { rows: [] },
    });

    const res = await app.inject({
      method: "GET",
      url: "/bookmarks/search?q=nonexistent",
    });

    expect(res.statusCode).toBe(200);
    expect(res.json()).toEqual([]);
  });

  test("embeds raw user input directly into SQL — not parameterized", async () => {
    const app = await buildApp({
      "WHERE title ILIKE": { rows: [] },
    });

    await app.inject({
      method: "GET",
      url: "/bookmarks/search?q=test",
    });

    const calledSql = app.pg.query.mock.calls[0][0];

    // The SQL contains the literal string '%test%' embedded directly
    expect(calledSql).toContain("'%test%'");
    // NOT using parameterized $1 placeholder for the search term
    expect(calledSql).not.toMatch(/\$1/);
  });

  test("UNION injection payload reaches SQL query", async () => {
    const app = await buildApp({
      "WHERE title ILIKE": { rows: [] },
    });

    const payload = "' UNION SELECT id, email, password_hash, 1,1,1,1 FROM users --";

    await app.inject({
      method: "GET",
      url: `/bookmarks/search?q=${encodeURIComponent(payload)}`,
    });

    const calledSql = app.pg.query.mock.calls[0][0];

    expect(calledSql).toContain("UNION SELECT");
    expect(calledSql).toContain("password_hash");
    expect(calledSql).toContain("FROM users");
  });

  test("boolean-based injection payload reaches SQL query", async () => {
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

  test("comment-based injection reaches SQL query", async () => {
    const app = await buildApp({
      "WHERE title ILIKE": { rows: [] },
    });

    await app.inject({
      method: "GET",
      url: "/bookmarks/search?q=%25' --",
    });

    const calledSql = app.pg.query.mock.calls[0][0];
    // The -- comment is embedded in the SQL string
    expect(calledSql).toContain("' --");
    // The ORDER BY is still in the string, but DB would ignore it after --
    expect(calledSql).toContain("ORDER BY created_at DESC");
  });
});
