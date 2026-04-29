"use strict";

const fs = require("fs");
const path = require("path");

describe("V9: Weak JWT Secret", () => {
  test("app.js uses 'changeme' as hardcoded fallback secret", () => {
    const source = fs.readFileSync(path.join(__dirname, "..", "src", "app.js"), "utf8");
    expect(source).toContain('"changeme"');
  });

  test("the fallback secret 'changeme' is trivially weak", () => {
    const secret = "changeme";
    expect(secret.length).toBeLessThan(16);
    expect(secret).toMatch(/^[a-z]+$/); // lowercase letters only, no special chars
  });
});

describe("V5: Verbose Error Messages", () => {
  test("error handler exposes DB error detail, table, column, and stack trace", () => {
    const source = fs.readFileSync(path.join(__dirname, "..", "src", "app.js"), "utf8");

    expect(source).toContain("error.detail");
    expect(source).toContain("error.table");
    expect(source).toContain("error.column");
    expect(source).toContain("error.stack");
    expect(source).toContain("error.schema");
    expect(source).toContain("error.constraint");
  });
});

describe("V7: Open CORS", () => {
  test("CORS allows all origins with credentials enabled", () => {
    const source = fs.readFileSync(path.join(__dirname, "..", "src", "app.js"), "utf8");

    expect(source).toContain('origin: "*"');
    expect(source).toContain("credentials: true");
  });
});

describe("V2: Broken Authentication", () => {
  test("no route uses jwt.verify or auth middleware", () => {
    const bookmarks = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "bookmarks.js"), "utf8");
    const categories = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "categories.js"), "utf8");
    const admin = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "admin.js"), "utf8");

    // None of the route files should contain any auth check
    for (const src of [bookmarks, categories, admin]) {
      expect(src).not.toContain("jwt.verify");
      expect(src).not.toContain("preHandler");
      expect(src).not.toContain("onRequest");
      expect(src).not.toContain("authenticate");
    }
  });

  test("JWT sign exists in auth routes but no verification anywhere", () => {
    const auth = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "auth.js"), "utf8");
    const bookmarks = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "bookmarks.js"), "utf8");
    const categories = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "categories.js"), "utf8");
    const admin = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "admin.js"), "utf8");

    // Auth routes sign tokens
    expect(auth).toContain("jwt.sign");
    // But no route file verifies them
    for (const src of [bookmarks, categories, admin]) {
      expect(src).not.toContain("jwt");
    }
  });
});

describe("V3: IDOR", () => {
  test("bookmark routes never filter by owner_email", () => {
    const source = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "bookmarks.js"), "utf8");

    // GET all should not have WHERE owner_email
    const getAllMatch = source.match(/GET.*bookmarks.*[\s\S]*?SELECT \* FROM bookmarks ORDER BY/);
    if (getAllMatch) {
      expect(getAllMatch[0]).not.toContain("owner_email");
    }

    // DELETE should not check ownership
    expect(source).not.toContain("WHERE owner_email");
  });
});

describe("V8: No Rate Limiting", () => {
  test("no rate limit dependency is installed", () => {
    const pkg = JSON.parse(
      fs.readFileSync(path.join(__dirname, "..", "package.json"), "utf8")
    );

    const allDeps = { ...pkg.dependencies, ...pkg.devDependencies };
    expect(allDeps).not.toHaveProperty("@fastify/rate-limit");
  });

  test("login route has no rate limiting middleware", () => {
    const source = fs.readFileSync(path.join(__dirname, "..", "src", "routes", "auth.js"), "utf8");
    expect(source).not.toContain("rateLimit");
    expect(source).not.toContain("rate-limit");
    expect(source).not.toContain("rateLimiter");
  });
});
