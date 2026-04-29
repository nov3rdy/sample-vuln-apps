"use strict";

const path = require("path");
require("dotenv").config({ path: path.join(__dirname, "..", "..", ".env") });

const { Client } = require("pg");
const bcrypt = require("bcryptjs");

const DB_URL = `postgresql://${process.env.PG_USER || "postgres"}:${process.env.PG_PASSWORD || "postgres"}@${process.env.PG_HOST || "localhost"}:${process.env.PG_PORT || 5432}/${process.env.PG_DATABASE || "bookmarks_db"}`;

const SEED_USERS = [
  { email: "alice@example.com", password: "alicepass123" },
  { email: "bob@example.com", password: "bobpass456" },
  { email: "carol@example.com", password: "carolpass789" },
  { email: "admin@bookmarks.io", password: "admin123" },
];

const SEED_BOOKMARKS = [
  { title: "Fastify Documentation", url: "https://fastify.dev", description: "Official Fastify framework documentation", owner_email: "alice@example.com" },
  { title: "Node.js Official Site", url: "https://nodejs.org", description: "Node.js runtime homepage", owner_email: "alice@example.com" },
  { title: "PostgreSQL Manual", url: "https://www.postgresql.org/docs/", description: "PostgreSQL documentation", owner_email: "bob@example.com" },
  { title: "OWASP API Security Top 10", url: "https://owasp.org/API-Security/", description: "OWASP API security guidelines", owner_email: "bob@example.com" },
  { title: "Docker Hub", url: "https://hub.docker.com", description: "Container image registry", owner_email: "carol@example.com" },
  { title: "JWT Introduction", url: "https://jwt.io/introduction", description: "JSON Web Tokens explained", owner_email: "carol@example.com" },
  { title: "Internal Dashboard", url: "http://192.168.1.50:8080/dashboard", description: "FLAG{idor_found_inside_dashboard}", owner_email: "admin@bookmarks.io" },
  { title: "Secret API Key Reference", url: "https://internal.bookmarks.io/docs/keys", description: "FLAG{sqli_reveals_hidden_data}", owner_email: "admin@bookmarks.io" },
];

const SEED_CATEGORIES = [
  { name: "Documentation", color: "#3B82F6" },
  { name: "Tutorials", color: "#10B981" },
  { name: "Security", color: "#EF4444" },
  { name: "DevOps", color: "#8B5CF6" },
  { name: "Internal", color: "#F59E0B" },
];

async function seed() {
  const client = new Client({ connectionString: DB_URL });

  try {
    await client.connect();
    console.log("Connected to database");

    // Drop and recreate tables to ensure schema is up to date
    await client.query("DROP TABLE IF EXISTS bookmarks, categories, users CASCADE");
    console.log("Dropped existing tables");

    await client.query(`
      CREATE TABLE bookmarks (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        url TEXT NOT NULL,
        description TEXT,
        owner_email VARCHAR(255),
        created_at TIMESTAMPTZ DEFAULT NOW(),
        updated_at TIMESTAMPTZ DEFAULT NOW()
      );
    `);
    await client.query(`
      CREATE TABLE categories (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        color VARCHAR(7),
        created_at TIMESTAMPTZ DEFAULT NOW(),
        updated_at TIMESTAMPTZ DEFAULT NOW()
      );
    `);
    await client.query(`
      CREATE TABLE users (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at TIMESTAMPTZ DEFAULT NOW()
      );
    `);
    console.log("Created tables");

    // Seed users
    for (const u of SEED_USERS) {
      const hash = await bcrypt.hash(u.password, 10);
      await client.query(
        `INSERT INTO users (email, password_hash) VALUES ($1, $2)`,
        [u.email, hash]
      );
    }
    console.log(`Seeded ${SEED_USERS.length} users`);

    // Seed categories
    for (const c of SEED_CATEGORIES) {
      await client.query(
        `INSERT INTO categories (name, color) VALUES ($1, $2)`,
        [c.name, c.color]
      );
    }
    console.log(`Seeded ${SEED_CATEGORIES.length} categories`);

    // Seed bookmarks
    for (const b of SEED_BOOKMARKS) {
      await client.query(
        `INSERT INTO bookmarks (title, url, description, owner_email) VALUES ($1, $2, $3, $4)`,
        [b.title, b.url, b.description, b.owner_email]
      );
    }
    console.log(`Seeded ${SEED_BOOKMARKS.length} bookmarks`);

    console.log("\nDone! Seed data ready.");
  } catch (err) {
    console.error("Seed failed:", err.message);
    process.exit(1);
  } finally {
    await client.end();
  }
}

seed();
