"use strict";

const Fastify = require("fastify");
const bcrypt = require("bcryptjs");

const authRoutes = require("../src/routes/auth");

const SAMPLE_USER = {
  id: 1,
  email: "test@example.com",
  password_hash: "$2a$10$hashedpassword",
  created_at: "2026-04-22T10:00:00.000Z",
};

function buildAuthApp(queries = {}) {
  const app = Fastify({ logger: false });

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

  // Mock JWT — uses the same weak secret as the real app
  app.decorate("jwt", {
    sign: jest.fn((payload) => "fake.jwt.token"),
  });

  app.register(authRoutes);
  return app.ready().then(() => app);
}

describe("POST /register", () => {
  test("creates user and returns 201 with token", async () => {
    const app = await buildAuthApp({
      "INSERT INTO users": {
        rows: [{ id: 1, email: "test@example.com", created_at: SAMPLE_USER.created_at }],
        rowCount: 1,
      },
    });

    const res = await app.inject({
      method: "POST",
      url: "/register",
      payload: { email: "test@example.com", password: "password123" },
    });

    expect(res.statusCode).toBe(201);
    const body = res.json();
    expect(body).toMatchObject({ id: 1, email: "test@example.com", token: "fake.jwt.token" });
  });

  // V10: No input validation
  test("accepts empty email and password — no validation (V10)", async () => {
    const app = await buildAuthApp({
      "INSERT INTO users": {
        rows: [{ id: 2, email: null, created_at: SAMPLE_USER.created_at }],
        rowCount: 1,
      },
    });

    const res = await app.inject({
      method: "POST",
      url: "/register",
      payload: {},
    });

    expect(res.statusCode).toBe(201);
  });

  test("hashes password with bcrypt", async () => {
    const hashSpy = jest.spyOn(bcrypt, "hash").mockResolvedValue("$2a$10$fakehash");
    const app = await buildAuthApp({
      "INSERT INTO users": {
        rows: [{ id: 1, email: "a@b.com", created_at: SAMPLE_USER.created_at }],
        rowCount: 1,
      },
    });

    await app.inject({
      method: "POST",
      url: "/register",
      payload: { email: "a@b.com", password: "secret" },
    });

    expect(hashSpy).toHaveBeenCalledWith("secret", 10);
    hashSpy.mockRestore();
  });

  // V2: Token is generated but routes never verify it
  test("returns JWT token on registration (V2: never verified)", async () => {
    const app = await buildAuthApp({
      "INSERT INTO users": {
        rows: [{ id: 1, email: "a@b.com", created_at: SAMPLE_USER.created_at }],
        rowCount: 1,
      },
    });

    const res = await app.inject({
      method: "POST",
      url: "/register",
      payload: { email: "a@b.com", password: "secret" },
    });

    expect(app.jwt.sign).toHaveBeenCalledWith({ id: 1, email: "a@b.com" });
    expect(res.json().token).toBeDefined();
  });
});

describe("POST /login", () => {
  test("returns token on valid credentials", async () => {
    jest.spyOn(bcrypt, "compare").mockResolvedValue(true);

    const app = await buildAuthApp({
      "WHERE email = $1": { rows: [SAMPLE_USER] },
    });

    const res = await app.inject({
      method: "POST",
      url: "/login",
      payload: { email: "test@example.com", password: "password123" },
    });

    expect(res.statusCode).toBe(200);
    expect(res.json().token).toBe("fake.jwt.token");
    bcrypt.compare.mockRestore();
  });

  test("returns 401 when user not found", async () => {
    const app = await buildAuthApp({
      "WHERE email = $1": { rows: [] },
    });

    const res = await app.inject({
      method: "POST",
      url: "/login",
      payload: { email: "nobody@example.com", password: "test" },
    });

    expect(res.statusCode).toBe(401);
    expect(res.json().error).toBe("invalid credentials");
  });

  test("returns 401 when password is wrong", async () => {
    jest.spyOn(bcrypt, "compare").mockResolvedValue(false);

    const app = await buildAuthApp({
      "WHERE email = $1": { rows: [SAMPLE_USER] },
    });

    const res = await app.inject({
      method: "POST",
      url: "/login",
      payload: { email: "test@example.com", password: "wrong" },
    });

    expect(res.statusCode).toBe(401);
    expect(res.json().error).toBe("invalid credentials");
    bcrypt.compare.mockRestore();
  });

  // V10: No input validation — login with empty fields doesn't reject early
  test("does not reject empty email/password early (V10)", async () => {
    const app = await buildAuthApp({
      "WHERE email = $1": { rows: [] },
    });

    const res = await app.inject({
      method: "POST",
      url: "/login",
      payload: {},
    });

    // Should reach the DB query (which returns empty), not reject with 400
    expect(app.pg.query).toHaveBeenCalled();
    expect(res.statusCode).toBe(401); // invalid credentials, not 400 bad request
  });

  // V8: No rate limiting — same endpoint can be called rapidly
  test("can be called repeatedly without lockout (V8: No Rate Limiting)", async () => {
    jest.spyOn(bcrypt, "compare").mockResolvedValue(false);

    const app = await buildAuthApp({
      "WHERE email = $1": { rows: [SAMPLE_USER] },
    });

    // Simulate 10 rapid failed login attempts
    const attempts = [];
    for (let i = 0; i < 10; i++) {
      attempts.push(
        app.inject({
          method: "POST",
          url: "/login",
          payload: { email: "test@example.com", password: `wrong${i}` },
        })
      );
    }

    const results = await Promise.all(attempts);

    // All attempts should get 401 — no rate limiting or lockout
    for (const res of results) {
      expect(res.statusCode).toBe(401);
    }
    expect(app.pg.query).toHaveBeenCalledTimes(10);

    bcrypt.compare.mockRestore();
  });
});
