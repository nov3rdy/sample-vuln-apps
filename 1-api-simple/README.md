# Simple Bookmarks API

A deliberately **vulnerable** CRUD API playground for learning API penetration testing. Built with Fastify, PostgreSQL, and Docker.

## Features

- **Bookmarks CRUD** — title, url, description, owner_email
- **Categories CRUD** — name, color (hex)
- **User Auth** — register/login with JWT tokens
- **Search** — bookmark search endpoint
- **Admin Stats** — database statistics
- **Dockerized** — single `docker compose up` to run everything
- **Postman collection** — import and start testing immediately
- **Jest tests** — 66 tests covering all endpoints (must pass before Docker build)

## Quick Start

```bash
make start
make seed
```

API runs at `http://localhost:60011`

## Endpoints

### Auth

| Method | Path         | Description       |
|--------|--------------|-------------------|
| `POST` | `/register`  | Create account     |
| `POST` | `/login`     | Get JWT token      |

### Bookmarks

| Method   | Path                    | Description          |
|----------|-------------------------|----------------------|
| `POST`   | `/bookmarks`            | Create a bookmark    |
| `GET`    | `/bookmarks`            | List all bookmarks   |
| `GET`    | `/bookmarks/search?q=`  | Search bookmarks     |
| `GET`    | `/bookmarks/:id`        | Get bookmark by ID   |
| `PUT`    | `/bookmarks/:id`        | Update a bookmark    |
| `DELETE` | `/bookmarks/:id`        | Delete a bookmark    |

### Categories

| Method   | Path               | Description          |
|----------|--------------------|----------------------|
| `POST`   | `/categories`      | Create a category    |
| `GET`    | `/categories`      | List all categories  |
| `GET`    | `/categories/:id`  | Get category by ID   |
| `PUT`    | `/categories/:id`  | Update a category    |
| `DELETE` | `/categories/:id`  | Delete a category    |

### Admin

| Method | Path            | Description         |
|--------|-----------------|---------------------|
| `GET`  | `/admin/stats`  | Database statistics |

## Project Structure

```
├── server.js                  # Entry point
├── seed.js                    # Database seeder
├── src/
│   ├── app.js                 # Fastify app + DB connection + plugins
│   ├── routes/
│   │   ├── auth.js            # Auth routes (register, login)
│   │   ├── bookmarks.js       # Bookmark routes + search
│   │   ├── categories.js      # Category routes
│   │   └── admin.js           # Admin stats route
│   └── db/
│       ├── init.sql           # SQL schema
│       └── seed.js            # Seed data script
├── tests/                     # Jest tests (66 tests)
├── docs/
│   ├── API_DOCS.md            # Full API documentation
│   └── Bookmarks_API.postman_collection.json
├── Dockerfile
├── docker-compose.yml
└── Makefile
```

## Vulnerabilities

This API intentionally contains the following security vulnerabilities:

| # | Vulnerability | OWASP | Affected Endpoints | Description |
|---|---|---|---|---|
| 1 | SQL Injection | API8:2023 | `GET /bookmarks/search?q=` | User input is concatenated directly into the SQL query using string interpolation (`${q}`) instead of parameterized queries. An attacker can inject SQL to read, modify, or delete data from any table. |
| 2 | Broken Authentication | API2:2023 | All endpoints | JWT tokens are generated on `/register` and `/login` but never verified. No route checks the `Authorization` header — all endpoints are fully accessible without a token. |
| 3 | Insecure Direct Object Reference | API1:2023 | `GET /bookmarks`, `GET /bookmarks/:id`, `PUT /bookmarks/:id`, `DELETE /bookmarks/:id` | Bookmarks have sequential integer IDs and an `owner_email` field, but no endpoint checks ownership. Any user can read, modify, or delete any bookmark by guessing IDs. |
| 4 | Mass Assignment | API3:2023 | `PUT /bookmarks/:id` | The update endpoint accepts arbitrary fields from the request body and writes them directly to the database. An attacker can inject `owner_email` or overwrite `created_at` via the request payload. |
| 5 | Security Misconfiguration — Verbose Errors | API8:2023 | All endpoints | The global error handler returns internal details on every 500 error: `error.detail`, `error.table`, `error.column`, `error.schema`, `error.constraint`, and full `error.stack`. |
| 6 | Broken Function Level Authorization | API5:2023 | `GET /admin/stats` | The admin endpoint exposes the PostgreSQL version and row counts for all tables (including users). No authentication or authorization check is performed. |
| 7 | CORS Misconfiguration | API8:2023 | All endpoints | CORS is configured with `origin: "*"` and `credentials: true`, allowing any website to make authenticated cross-origin requests. |
| 8 | Unrestricted Resource Consumption | API4:2023 | `POST /login` | No rate limiting is implemented on any endpoint. The login endpoint can be brute-forced with unlimited requests without lockout or throttling. |
| 9 | Weak JWT Secret | API2:2023 | `POST /register`, `POST /login` | The JWT signing secret is the hardcoded string `changeme` — a common word, under 16 characters, no special characters or numbers. Tokens can be forged by anyone who guesses the secret. |
| 10 | Improper Input Validation | API8:2023 | `POST /bookmarks`, `PUT /bookmarks/:id`, `POST /categories`, `POST /register` | No input validation is performed. The API docs claim fields are required with max lengths (255 chars for title, 100 for name, hex color format), but all constraints are unenforced — empty strings, oversized values, and invalid formats are all accepted. |
