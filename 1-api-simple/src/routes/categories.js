"use strict";

module.exports = async function (fastify, opts) {
  // ─── CREATE ───────────────────────────────────────────────────────────
  fastify.post("/categories", async (request, reply) => {
    const { name, color } = request.body || {};

    // V10: No input validation — docs say name is required and max 100 chars but anything is accepted
    const result = await fastify.pg.query(
      `INSERT INTO categories (name, color) VALUES ($1, $2) RETURNING *`,
      [name || null, color || null]
    );

    reply.code(201).send(result.rows[0]);
  });

  // ─── READ ALL ─────────────────────────────────────────────────────────
  fastify.get("/categories", async (request, reply) => {
    const result = await fastify.pg.query(
      `SELECT * FROM categories ORDER BY created_at DESC`
    );
    reply.send(result.rows);
  });

  // ─── READ ONE ─────────────────────────────────────────────────────────
  fastify.get("/categories/:id", async (request, reply) => {
    const { id } = request.params;

    const result = await fastify.pg.query(
      `SELECT * FROM categories WHERE id = $1`,
      [id]
    );

    if (result.rows.length === 0) {
      return reply.code(404).send({ error: "Category not found" });
    }

    reply.send(result.rows[0]);
  });

  // ─── UPDATE ───────────────────────────────────────────────────────────
  fastify.put("/categories/:id", async (request, reply) => {
    const { id } = request.params;
    const { name, color } = request.body;

    const existing = await fastify.pg.query(
      `SELECT * FROM categories WHERE id = $1`,
      [id]
    );

    if (existing.rows.length === 0) {
      return reply.code(404).send({ error: "Category not found" });
    }

    const result = await fastify.pg.query(
      `UPDATE categories
       SET name = $1, color = $2, updated_at = NOW()
       WHERE id = $3
       RETURNING *`,
      [
        name || existing.rows[0].name,
        color !== undefined ? color : existing.rows[0].color,
        id,
      ]
    );

    reply.send(result.rows[0]);
  });

  // ─── DELETE ───────────────────────────────────────────────────────────
  fastify.delete("/categories/:id", async (request, reply) => {
    const { id } = request.params;

    const result = await fastify.pg.query(
      `DELETE FROM categories WHERE id = $1 RETURNING id`,
      [id]
    );

    if (result.rows.length === 0) {
      return reply.code(404).send({ error: "Category not found" });
    }

    reply.code(204).send();
  });
};
