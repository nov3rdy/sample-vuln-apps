"use strict";

const bcrypt = require("bcryptjs");

module.exports = async function (fastify, opts) {
  // ─── REGISTER ──────────────────────────────────────────────────────────
  fastify.post("/register", async (request, reply) => {
    const { email, password } = request.body || {};

    // V10: No input validation — docs say required but anything (even empty) is accepted
    const hash = await bcrypt.hash(password || "", 10);

    const result = await fastify.pg.query(
      `INSERT INTO users (email, password_hash) VALUES ($1, $2) RETURNING id, email, created_at`,
      [email || null, hash]
    );

    const user = result.rows[0];
    const token = fastify.jwt.sign({ id: user.id, email: user.email });

    reply.code(201).send({ ...user, token });
  });

  // ─── LOGIN ─────────────────────────────────────────────────────────────
  fastify.post("/login", async (request, reply) => {
    const { email, password } = request.body || {};

    const result = await fastify.pg.query(
      `SELECT id, email, password_hash FROM users WHERE email = $1`,
      [email]
    );

    if (result.rows.length === 0) {
      return reply.code(401).send({ error: "invalid credentials" });
    }

    const user = result.rows[0];
    const valid = await bcrypt.compare(password, user.password_hash);

    if (!valid) {
      return reply.code(401).send({ error: "invalid credentials" });
    }

    const token = fastify.jwt.sign({ id: user.id, email: user.email });

    reply.send({ token });
  });
};
