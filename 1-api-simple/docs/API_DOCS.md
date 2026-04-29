# Bookmarks & Categories API - API Documentation

Base URL: `http://localhost:60011`

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Data Models](#data-models)
4. [Auth Endpoints](#auth-endpoints)
   - [Register](#1-register)
   - [Login](#2-login)
5. [Bookmarks Endpoints](#bookmarks-endpoints)
   - [Create Bookmark](#3-create-bookmark)
   - [Get All Bookmarks](#4-get-all-bookmarks)
   - [Search Bookmarks](#5-search-bookmarks)
   - [Get Bookmark by ID](#6-get-bookmark-by-id)
   - [Update Bookmark](#7-update-bookmark)
   - [Delete Bookmark](#8-delete-bookmark)
6. [Categories Endpoints](#categories-endpoints)
   - [Create Category](#9-create-category)
   - [Get All Categories](#10-get-all-categories)
   - [Get Category by ID](#11-get-category-by-id)
   - [Update Category](#12-update-category)
   - [Delete Category](#13-delete-category)
7. [Admin Endpoints](#admin-endpoints)
   - [Get Stats](#14-get-stats)
8. [Error Responses](#error-responses)

---

## Overview

A CRUD API for managing **bookmarks** and **categories**. Built with Fastify and PostgreSQL.

Supports user registration and login with JWT tokens. Send the token in the `Authorization` header as `Bearer <token>`.

---

## Authentication

The API uses JWT-based authentication. Register to create an account, then login to receive a token.

Include the token in all authenticated requests:

```
Authorization: Bearer <your-token>
```

---

## Data Models

### User

| Field           | Type    | Required | Description                        |
|----------------|---------|----------|------------------------------------|
| `id`           | integer | auto     | Auto-incremented primary key       |
| `email`        | string  | yes      | Unique email address (max 255 chars)|
| `password_hash`| string  | auto     | Bcrypt hash (never returned in API) |
| `created_at`   | string  | auto     | ISO 8601 timestamp (auto-set)      |

### Bookmark

| Field         | Type    | Required | Description                         |
|--------------|---------|----------|-------------------------------------|
| `id`         | integer | auto     | Auto-incremented primary key        |
| `title`      | string  | yes      | Name of the bookmark (max 255 chars)|
| `url`        | string  | yes      | The bookmark URL                    |
| `description`| string  | no       | Optional description                |
| `owner_email`| string  | auto     | Email of the bookmark owner         |
| `created_at` | string  | auto     | ISO 8601 timestamp (auto-set)       |
| `updated_at` | string  | auto     | ISO 8601 timestamp (auto-updated)   |

### Category

| Field        | Type    | Required | Description                                  |
|-------------|---------|----------|----------------------------------------------|
| `id`        | integer | auto     | Auto-incremented primary key                 |
| `name`      | string  | yes      | Category name (max 100 chars)                |
| `color`     | string  | no       | Hex color code, e.g. `#FF5733` (max 7 chars) |
| `created_at`| string  | auto     | ISO 8601 timestamp (auto-set)                |
| `updated_at`| string  | auto     | ISO 8601 timestamp (auto-updated)            |

---

## Auth Endpoints

---

### 1. Register

Creates a new user account and returns a JWT token.

- **URL:** `POST /register`
- **Content-Type:** `application/json`

#### Request Body

| Field      | Type   | Required | Rules                         |
|-----------|--------|----------|-------------------------------|
| `email`   | string | **Yes**  | Valid email, max 255 chars    |
| `password`| string | **Yes**  | Any non-empty string          |

#### Example Request

```json
{
  "email": "user@example.com",
  "password": "mypassword123"
}
```

#### Example Response — `201 Created`

```json
{
  "id": 1,
  "email": "user@example.com",
  "created_at": "2026-04-22T10:00:00.000Z",
  "token": "eyJhbGciOiJIUzI1NiIs..."
}
```

#### Error Response — `400 Bad Request`

```json
{
  "error": "email and password are required"
}
```

---

### 2. Login

Authenticates a user and returns a JWT token.

- **URL:** `POST /login`
- **Content-Type:** `application/json`

#### Request Body

| Field      | Type   | Required | Rules                      |
|-----------|--------|----------|----------------------------|
| `email`   | string | **Yes**  | Registered email address   |
| `password`| string | **Yes**  | Account password           |

#### Example Request

```json
{
  "email": "user@example.com",
  "password": "mypassword123"
}
```

#### Example Response — `200 OK`

```json
{
  "token": "eyJhbGciOiJIUzI1NiIs..."
}
```

#### Error Response — `401 Unauthorized`

```json
{
  "error": "invalid credentials"
}
```

---

## Bookmarks Endpoints

---

### 3. Create Bookmark

Creates a new bookmark entry in the database.

- **URL:** `POST /bookmarks`
- **Content-Type:** `application/json`
- **Auth:** `Bearer <token>` required

#### Request Body

| Field         | Type   | Required | Rules                                    |
|--------------|--------|----------|------------------------------------------|
| `title`      | string | **Yes**  | Non-empty string, max 255 characters      |
| `url`        | string | **Yes**  | Non-empty string (valid URL recommended)  |
| `description`| string | No       | Any text, can be `null` or omitted        |

#### Example Request

```json
{
  "title": "Fastify Docs",
  "url": "https://fastify.dev",
  "description": "Official Fastify documentation"
}
```

#### Example Response — `201 Created`

```json
{
  "id": 1,
  "title": "Fastify Docs",
  "url": "https://fastify.dev",
  "description": "Official Fastify documentation",
  "owner_email": "user@example.com",
  "created_at": "2026-04-22T10:00:00.000Z",
  "updated_at": "2026-04-22T10:00:00.000Z"
}
```

#### Error Response — `400 Bad Request`

Returned when `title` or `url` is missing.

```json
{
  "error": "title and url are required"
}
```

---

### 4. Get All Bookmarks

Retrieves all bookmarks owned by the authenticated user, sorted by creation date (newest first).

- **URL:** `GET /bookmarks`
- **Auth:** `Bearer <token>` required
- **Request Body:** None

#### Example Response — `200 OK`

```json
[
  {
    "id": 2,
    "title": "Node.js",
    "url": "https://nodejs.org",
    "description": "Node.js homepage",
    "owner_email": "user@example.com",
    "created_at": "2026-04-22T10:05:00.000Z",
    "updated_at": "2026-04-22T10:05:00.000Z"
  },
  {
    "id": 1,
    "title": "Fastify Docs",
    "url": "https://fastify.dev",
    "description": "Official Fastify documentation",
    "owner_email": "user@example.com",
    "created_at": "2026-04-22T10:00:00.000Z",
    "updated_at": "2026-04-22T10:00:00.000Z"
  }
]
```

Returns an empty array `[]` if no bookmarks exist.

---

### 5. Search Bookmarks

Searches bookmarks by title or URL using case-insensitive matching.

- **URL:** `GET /bookmarks/search?q=<query>`
- **Auth:** `Bearer <token>` required
- **Request Body:** None

#### Query Parameters

| Parameter | Type   | Required | Rules                    |
|----------|--------|----------|--------------------------|
| `q`      | string | **Yes**  | Non-empty search string  |

#### Example Request

```
GET /bookmarks/search?q=fastify
```

#### Example Response — `200 OK`

```json
[
  {
    "id": 1,
    "title": "Fastify Docs",
    "url": "https://fastify.dev",
    "description": "Official Fastify documentation",
    "owner_email": "user@example.com",
    "created_at": "2026-04-22T10:00:00.000Z",
    "updated_at": "2026-04-22T10:00:00.000Z"
  }
]
```

---

### 6. Get Bookmark by ID

Retrieves a single bookmark by its ID.

- **URL:** `GET /bookmarks/:id`
- **Auth:** `Bearer <token>` required
- **Request Body:** None

#### URL Parameters

| Parameter | Type    | Required | Rules                      |
|----------|---------|----------|----------------------------|
| `id`     | integer | **Yes**  | Must be a positive integer  |

#### Example Response — `200 OK`

```json
{
  "id": 1,
  "title": "Fastify Docs",
  "url": "https://fastify.dev",
  "description": "Official Fastify documentation",
  "owner_email": "user@example.com",
  "created_at": "2026-04-22T10:00:00.000Z",
  "updated_at": "2026-04-22T10:00:00.000Z"
}
```

#### Error Response — `404 Not Found`

```json
{
  "error": "Bookmark not found"
}
```

---

### 7. Update Bookmark

Updates an existing bookmark. All fields are optional — only provided fields will be updated.

- **URL:** `PUT /bookmarks/:id`
- **Content-Type:** `application/json`
- **Auth:** `Bearer <token>` required

#### URL Parameters

| Parameter | Type    | Required | Rules                      |
|----------|---------|----------|----------------------------|
| `id`     | integer | **Yes**  | Must be a positive integer  |

#### Request Body

| Field         | Type   | Required | Rules                             |
|--------------|--------|----------|-----------------------------------|
| `title`      | string | No       | Non-empty string, max 255 chars   |
| `url`        | string | No       | Non-empty string (valid URL rec.) |
| `description`| string | No       | Any text, pass `null` to clear it |

#### Example Request

```json
{
  "title": "Fastify Documentation",
  "description": "Updated description"
}
```

#### Example Response — `200 OK`

```json
{
  "id": 1,
  "title": "Fastify Documentation",
  "url": "https://fastify.dev",
  "description": "Updated description",
  "owner_email": "user@example.com",
  "created_at": "2026-04-22T10:00:00.000Z",
  "updated_at": "2026-04-22T10:30:00.000Z"
}
```

#### Error Response — `404 Not Found`

```json
{
  "error": "Bookmark not found"
}
```

---

### 8. Delete Bookmark

Deletes a bookmark by its ID.

- **URL:** `DELETE /bookmarks/:id`
- **Auth:** `Bearer <token>` required
- **Request Body:** None

#### URL Parameters

| Parameter | Type    | Required | Rules                      |
|----------|---------|----------|----------------------------|
| `id`     | integer | **Yes**  | Must be a positive integer  |

#### Example Response — `204 No Content`

No response body on success.

#### Error Response — `404 Not Found`

```json
{
  "error": "Bookmark not found"
}
```

---

## Categories Endpoints

---

### 9. Create Category

Creates a new category.

- **URL:** `POST /categories`
- **Content-Type:** `application/json`
- **Auth:** `Bearer <token>` required

#### Request Body

| Field   | Type   | Required | Rules                                      |
|--------|--------|----------|--------------------------------------------|
| `name` | string | **Yes**  | Non-empty string, max 100 characters        |
| `color`| string | No       | Hex color code, e.g. `#FF5733` (max 7 chars)|

#### Example Request

```json
{
  "name": "Documentation",
  "color": "#3B82F6"
}
```

#### Example Response — `201 Created`

```json
{
  "id": 1,
  "name": "Documentation",
  "color": "#3B82F6",
  "created_at": "2026-04-22T10:00:00.000Z",
  "updated_at": "2026-04-22T10:00:00.000Z"
}
```

#### Error Response — `400 Bad Request`

```json
{
  "error": "name is required"
}
```

---

### 10. Get All Categories

Retrieves all categories sorted by creation date (newest first).

- **URL:** `GET /categories`
- **Auth:** `Bearer <token>` required
- **Request Body:** None

#### Example Response — `200 OK`

```json
[
  {
    "id": 2,
    "name": "Tutorials",
    "color": "#10B981",
    "created_at": "2026-04-22T10:05:00.000Z",
    "updated_at": "2026-04-22T10:05:00.000Z"
  },
  {
    "id": 1,
    "name": "Documentation",
    "color": "#3B82F6",
    "created_at": "2026-04-22T10:00:00.000Z",
    "updated_at": "2026-04-22T10:00:00.000Z"
  }
]
```

Returns an empty array `[]` if no categories exist.

---

### 11. Get Category by ID

Retrieves a single category by its ID.

- **URL:** `GET /categories/:id`
- **Auth:** `Bearer <token>` required
- **Request Body:** None

#### URL Parameters

| Parameter | Type    | Required | Rules                      |
|----------|---------|----------|----------------------------|
| `id`     | integer | **Yes**  | Must be a positive integer  |

#### Example Response — `200 OK`

```json
{
  "id": 1,
  "name": "Documentation",
  "color": "#3B82F6",
  "created_at": "2026-04-22T10:00:00.000Z",
  "updated_at": "2026-04-22T10:00:00.000Z"
}
```

#### Error Response — `404 Not Found`

```json
{
  "error": "Category not found"
}
```

---

### 12. Update Category

Updates an existing category. All fields are optional — only provided fields will be updated.

- **URL:** `PUT /categories/:id`
- **Content-Type:** `application/json`
- **Auth:** `Bearer <token>` required

#### URL Parameters

| Parameter | Type    | Required | Rules                      |
|----------|---------|----------|----------------------------|
| `id`     | integer | **Yes**  | Must be a positive integer  |

#### Request Body

| Field   | Type   | Required | Rules                                      |
|--------|--------|----------|--------------------------------------------|
| `name` | string | No       | Non-empty string, max 100 characters        |
| `color`| string | No       | Hex color code, pass `null` to clear it     |

#### Example Request

```json
{
  "name": "Docs & References",
  "color": "#8B5CF6"
}
```

#### Example Response — `200 OK`

```json
{
  "id": 1,
  "name": "Docs & References",
  "color": "#8B5CF6",
  "created_at": "2026-04-22T10:00:00.000Z",
  "updated_at": "2026-04-22T10:30:00.000Z"
}
```

#### Error Response — `404 Not Found`

```json
{
  "error": "Category not found"
}
```

---

### 13. Delete Category

Deletes a category by its ID.

- **URL:** `DELETE /categories/:id`
- **Auth:** `Bearer <token>` required
- **Request Body:** None

#### URL Parameters

| Parameter | Type    | Required | Rules                      |
|----------|---------|----------|----------------------------|
| `id`     | integer | **Yes**  | Must be a positive integer  |

#### Example Response — `204 No Content`

No response body on success.

#### Error Response — `404 Not Found`

```json
{
  "error": "Category not found"
}
```

---

## Admin Endpoints

---

### 14. Get Stats

Returns database statistics. Requires admin authentication.

- **URL:** `GET /admin/stats`
- **Auth:** `Bearer <admin-token>` required
- **Request Body:** None

#### Example Response — `200 OK`

```json
{
  "database": {
    "version": "PostgreSQL 16.2 on x86_64-pc-linux-musl, compiled by gcc (Alpine 13.2.1 20240316) 13.2.1 20230801, 64-bit"
  },
  "tables": {
    "bookmarks": { "row_count": 5 },
    "categories": { "row_count": 3 },
    "users": { "row_count": 2 }
  }
}
```

---

## Error Responses

All error responses follow a consistent format:

```json
{
  "error": "<error message>"
}
```

### HTTP Status Codes Summary

| Status Code | Meaning        | When                                     |
|------------|----------------|------------------------------------------|
| `200`      | OK             | Successful GET or PUT                    |
| `201`      | Created        | Successful POST                          |
| `204`      | No Content     | Successful DELETE                        |
| `400`      | Bad Request    | Missing required fields in request body  |
| `401`      | Unauthorized   | Invalid credentials or missing token     |
| `404`      | Not Found      | Resource with given ID does not exist    |
| `500`      | Internal Error | Unexpected server/database error         |
