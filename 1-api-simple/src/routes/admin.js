"use strict";

module.exports = async function (fastify, opts) {
  // V6: Broken Function Level Authorization — no auth check on admin endpoint
  fastify.get("/admin/stats", async (request, reply) => {
    const versionResult = await fastify.pg.query(`SELECT version()`);
    const tablesResult = await fastify.pg.query(
      `SELECT relname AS table_name, n_live_tup AS row_count
       FROM pg_stat_user_tables
       ORDER BY relname`
    );

    const tables = {};
    for (const row of tablesResult.rows) {
      tables[row.table_name] = { row_count: Number(row.row_count) };
    }

    reply.send({
      database: { version: versionResult.rows[0].version },
      tables,
    });
  });
};
