"use strict";

const Fastify = require("fastify");

/**
 * Builds a Fastify app with a mocked pg.query for testing.
 * @param {object} queries - Map of sql pattern => result to control mock responses.
 *   Supports exact match or partial match (checks if SQL includes the key).
 *   If a key starts with "!", matches negatively (used for UPDATE/DELETE existence check).
 */
function buildTestApp(queries = {}) {
  const app = Fastify({ logger: false });

  // Mock fastify.pg
  app.decorate("pg", {
    query: jest.fn((sql, params) => {
      for (const [pattern, result] of Object.entries(queries)) {
        if (sql.includes(pattern)) {
          return Promise.resolve(result);
        }
      }
      return Promise.resolve({ rows: [], rowCount: 0 });
    }),
  });

  return app;
}

module.exports = { buildTestApp };
