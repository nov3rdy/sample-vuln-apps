"use strict";

require("dotenv").config();

const Fastify = require("fastify");
const postgres = require("@fastify/postgres");
const cors = require("@fastify/cors");
const jwt = require("@fastify/jwt");

async function buildApp() {
  const app = Fastify({ logger: true });

  // V5: Verbose Error Messages — exposes DB errors, stack traces, table/column names
  app.setErrorHandler((error, request, reply) => {
    const response = {
      error: error.message,
      statusCode: error.statusCode || 500,
    };
    if (error.detail) response.detail = error.detail;
    if (error.table) response.table = error.table;
    if (error.column) response.column = error.column;
    if (error.schema) response.schema = error.schema;
    if (error.constraint) response.constraint = error.constraint;
    if (error.stack) response.stack = error.stack;
    reply.code(response.statusCode).send(response);
  });

  app.register(postgres, {
    host: process.env.PG_HOST || "localhost",
    port: process.env.PG_PORT || 5432,
    database: process.env.PG_DATABASE || "bookmarks_db",
    user: process.env.PG_USER || "postgres",
    password: process.env.PG_PASSWORD || "postgres",
  });

  // V7: Open CORS — wildcard origin with credentials enabled
  app.register(cors, {
    origin: "*",
    credentials: true,
  });

  // V2: JWT setup — token generation works but is never verified on routes
  app.register(jwt, {
    secret: process.env.JWT_SECRET || "changeme",
  });

  app.addHook("onReady", async () => {
    await app.pg.query(`
      CREATE TABLE IF NOT EXISTS bookmarks (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        url TEXT NOT NULL,
        description TEXT,
        owner_email VARCHAR(255),
        created_at TIMESTAMPTZ DEFAULT NOW(),
        updated_at TIMESTAMPTZ DEFAULT NOW()
      );
    `);
    await app.pg.query(`
      CREATE TABLE IF NOT EXISTS categories (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        color VARCHAR(7),
        created_at TIMESTAMPTZ DEFAULT NOW(),
        updated_at TIMESTAMPTZ DEFAULT NOW()
      );
    `);
    await app.pg.query(`
      CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at TIMESTAMPTZ DEFAULT NOW()
      );
    `);
  });

  app.register(require("./routes/bookmarks"));
  app.register(require("./routes/categories"));
  app.register(require("./routes/auth"));
  app.register(require("./routes/admin"));

  return app;
}

module.exports = buildApp;
