"use strict";

module.exports = async function (fastify, opts) {
  // ─── CREATE ───────────────────────────────────────────────────────────
  fastify.post("/bookmarks", async (request, reply) => {
    const { title, url, description, owner_email } = request.body || {};

    // V10: No input validation — docs say max 255 chars but anything is accepted
    const result = await fastify.pg.query(
      `INSERT INTO bookmarks (title, url, description, owner_email) VALUES ($1, $2, $3, $4) RETURNING *`,
      [title || null, url || null, description || null, owner_email || null]
    );

    reply.code(201).send(result.rows[0]);
  });

  // ─── READ ALL ─────────────────────────────────────────────────────────
  fastify.get("/bookmarks", async (request, reply) => {
    const result = await fastify.pg.query(
      `SELECT * FROM bookmarks ORDER BY created_at DESC`
    );
    reply.send(result.rows);
  });

  // ─── SEARCH (V1: SQL Injection) ────────────────────────────────────────
  fastify.get("/bookmarks/search", async (request, reply) => {
    const { q } = request.query;

    // VULNERABLE: string concatenation instead of parameterized query
    const result = await fastify.pg.query(
      `SELECT * FROM bookmarks WHERE title ILIKE '%${q}%' OR url ILIKE '%${q}%' ORDER BY created_at DESC`
    );

    reply.send(result.rows);
  });

  // ─── READ ONE (V3: IDOR — no ownership check) ──────────────────────────
  fastify.get("/bookmarks/:id", async (request, reply) => {
    const { id } = request.params;

    const result = await fastify.pg.query(
      `SELECT * FROM bookmarks WHERE id = $1`,
      [id]
    );

    if (result.rows.length === 0) {
      return reply.code(404).send({ error: "Bookmark not found" });
    }

    reply.send(result.rows[0]);
  });

  // ─── UPDATE (V4: Mass Assignment) ──────────────────────────────────────
  fastify.put("/bookmarks/:id", async (request, reply) => {
    const { id } = request.params;
    const body = request.body || {};
    const { title, url, description } = body;

    const existing = await fastify.pg.query(
      `SELECT * FROM bookmarks WHERE id = $1`,
      [id]
    );

    if (existing.rows.length === 0) {
      return reply.code(404).send({ error: "Bookmark not found" });
    }

    const updates = {
      title: title !== undefined ? title : existing.rows[0].title,
      url: url !== undefined ? url : existing.rows[0].url,
      description: description !== undefined ? description : existing.rows[0].description,
      updated_at: "NOW()",
    };

    // VULNERABLE: extra fields from body go directly into UPDATE
    if (body.owner_email !== undefined) {
      updates.owner_email = body.owner_email;
    }
    if (body.created_at !== undefined) {
      updates.created_at = body.created_at;
    }

    const setClauses = [];
    const values = [];
    let paramIndex = 1;

    for (const [key, value] of Object.entries(updates)) {
      if (value === "NOW()") {
        setClauses.push(`${key} = NOW()`);
      } else {
        setClauses.push(`${key} = $${paramIndex}`);
        values.push(value);
        paramIndex++;
      }
    }
    values.push(id);

    const result = await fastify.pg.query(
      `UPDATE bookmarks SET ${setClauses.join(", ")} WHERE id = $${paramIndex} RETURNING *`,
      values
    );

    reply.send(result.rows[0]);
  });

  // ─── DELETE ───────────────────────────────────────────────────────────
  fastify.delete("/bookmarks/:id", async (request, reply) => {
    const { id } = request.params;

    const result = await fastify.pg.query(
      `DELETE FROM bookmarks WHERE id = $1 RETURNING id`,
      [id]
    );

    if (result.rows.length === 0) {
      return reply.code(404).send({ error: "Bookmark not found" });
    }

    reply.code(204).send();
  });
};
