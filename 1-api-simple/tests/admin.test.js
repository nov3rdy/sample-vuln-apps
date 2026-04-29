"use strict";

const { buildTestApp } = require("./test-helpers");

const adminRoutes = require("../src/routes/admin");

async function buildApp(queries = {}) {
  const app = buildTestApp(queries);
  app.register(adminRoutes);
  await app.ready();
  return app;
}

describe("V6: Broken Function Level Authorization — GET /admin/stats", () => {
  test("returns database version and table stats", async () => {
    const app = await buildApp({
      "SELECT version()": {
        rows: [{ version: "PostgreSQL 16.2 on x86_64-pc-linux-musl" }],
      },
      "pg_stat_user_tables": {
        rows: [
          { table_name: "bookmarks", row_count: "5" },
          { table_name: "categories", row_count: "3" },
          { table_name: "users", row_count: "2" },
        ],
      },
    });

    const res = await app.inject({
      method: "GET",
      url: "/admin/stats",
    });

    expect(res.statusCode).toBe(200);
    const body = res.json();
    expect(body.database.version).toContain("PostgreSQL");
    expect(body.tables).toEqual({
      bookmarks: { row_count: 5 },
      categories: { row_count: 3 },
      users: { row_count: 2 },
    });
  });

  test("returns 200 without any Authorization header — no auth required", async () => {
    const app = await buildApp({
      "SELECT version()": {
        rows: [{ version: "PostgreSQL 16.2" }],
      },
      "pg_stat_user_tables": { rows: [] },
    });

    const res = await app.inject({
      method: "GET",
      url: "/admin/stats",
      headers: {}, // explicitly no auth header
    });

    expect(res.statusCode).toBe(200);
  });

  test("returns 200 even with invalid Authorization header", async () => {
    const app = await buildApp({
      "SELECT version()": {
        rows: [{ version: "PostgreSQL 16.2" }],
      },
      "pg_stat_user_tables": { rows: [] },
    });

    const res = await app.inject({
      method: "GET",
      url: "/admin/stats",
      headers: { authorization: "Bearer totally-invalid-token" },
    });

    // Auth header is completely ignored
    expect(res.statusCode).toBe(200);
  });

  test("exposes user table row count — information leakage", async () => {
    const app = await buildApp({
      "SELECT version()": {
        rows: [{ version: "PostgreSQL 16.2" }],
      },
      "pg_stat_user_tables": {
        rows: [{ table_name: "users", row_count: "42" }],
      },
    });

    const res = await app.inject({
      method: "GET",
      url: "/admin/stats",
    });

    expect(res.json().tables.users).toBeDefined();
    expect(res.json().tables.users.row_count).toBe(42);
  });

  test("returns empty tables object when no tables have stats", async () => {
    const app = await buildApp({
      "SELECT version()": {
        rows: [{ version: "PostgreSQL 16.2" }],
      },
      "pg_stat_user_tables": { rows: [] },
    });

    const res = await app.inject({
      method: "GET",
      url: "/admin/stats",
    });

    expect(res.statusCode).toBe(200);
    expect(res.json().tables).toEqual({});
  });
});
