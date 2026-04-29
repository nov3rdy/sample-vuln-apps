# CompanyHub

A deliberately **vulnerable** internal company portal for learning web application penetration testing. Built with PHP 8, raw PDO, MySQL, and nginx.

## Features

- **Authentication** — register / login / logout / password reset (session cookies + "remember me")
- **Employee Directory** — list, search, view colleagues
- **Notes** — personal CRUD with public/private toggle
- **Messages** — internal DM inbox + send
- **Files** — upload and download shared documents
- **Team Links** — store URLs and fetch a server-side preview
- **Profile** — avatar upload, theme/preferences cookie
- **Import Contacts** — bulk-add employees from an XML file
- **Admin Panel** — user role management, site banner editor, database stats
- **Dockerized** — single `make start` brings up nginx + php-fpm + MySQL
- **PHPUnit tests** — ~60 tests run inside the container against the live HTTP stack

## Quick Start

```bash
make start
make install
make seed
```

Portal runs at `http://localhost:60012`.

Sample logins (printed by `make seed`):

| Email                       | Password    | Role   |
|-----------------------------|-------------|--------|
| `admin@companyhub.local`    | `admin123`  | admin  |
| `alice@companyhub.local`    | `password1` | user   |
| `bob@companyhub.local`      | `qwerty`    | user   |
| `carol@companyhub.local`    | `sunshine`  | user   |
| `dave@companyhub.local`     | `letmein`   | user   |

## Pages

### Auth

| Method | Path        | Description           |
|--------|-------------|-----------------------|
| `GET`  | `/login`    | Sign-in form          |
| `POST` | `/login`    | Submit credentials    |
| `GET`  | `/register` | Create-account form   |
| `POST` | `/register` | Submit registration   |
| `GET`  | `/logout`   | Clear session         |
| `GET`  | `/forgot`   | Request password reset|
| `POST` | `/forgot`   | Issue reset token     |
| `GET`  | `/reset`    | Reset form            |
| `POST` | `/reset`    | Submit new password   |

### Directory

| Method | Path                  | Description                |
|--------|-----------------------|----------------------------|
| `GET`  | `/directory`          | List all employees         |
| `GET`  | `/directory/search`   | Search employees by `?q=`  |
| `GET`  | `/directory/{id}`     | View one employee          |

### Notes

| Method | Path                  | Description           |
|--------|-----------------------|-----------------------|
| `GET`  | `/notes`              | List your + public notes |
| `GET`  | `/notes/new`          | New-note form         |
| `POST` | `/notes`              | Create a note         |
| `GET`  | `/notes/{id}`         | View a note           |
| `GET`  | `/notes/{id}/edit`    | Edit-note form        |
| `POST` | `/notes/{id}`         | Update a note         |
| `POST` | `/notes/{id}/delete`  | Delete a note         |

### Messages

| Method | Path                | Description          |
|--------|---------------------|----------------------|
| `GET`  | `/messages`         | Inbox                |
| `GET`  | `/messages/new`     | Compose form         |
| `POST` | `/messages`         | Send a DM            |
| `GET`  | `/messages/{id}`    | View a thread        |

### Files

| Method | Path                  | Description                          |
|--------|-----------------------|--------------------------------------|
| `GET`  | `/files`              | Listing + upload form                |
| `POST` | `/files`              | Upload a shared document             |
| `GET`  | `/files/download`     | Download by `?id=` or `?file=` path  |

### Team Links

| Method | Path                 | Description               |
|--------|----------------------|---------------------------|
| `GET`  | `/links`             | List + add-link form      |
| `POST` | `/links`             | Add a link                |
| `GET`  | `/links/preview`     | Server-side fetch `?url=` |

### Profile

| Method | Path                       | Description                    |
|--------|----------------------------|--------------------------------|
| `GET`  | `/profile`                 | Profile page                   |
| `POST` | `/profile/avatar`          | Upload avatar                  |
| `POST` | `/profile/preferences`     | Save theme/preferences cookie  |

### Import

| Method | Path        | Description                  |
|--------|-------------|------------------------------|
| `GET`  | `/import`   | Upload form                  |
| `POST` | `/import`   | Bulk-import contacts via XML |

### Admin

| Method | Path                          | Description                   |
|--------|-------------------------------|-------------------------------|
| `GET`  | `/admin`                      | Admin landing                 |
| `GET`  | `/admin/users`                | User management               |
| `POST` | `/admin/users/{id}/role`      | Change a user's role          |
| `GET`  | `/admin/banner`               | Edit the site banner HTML     |
| `POST` | `/admin/banner`               | Save the banner               |
| `GET`  | `/admin/stats`                | Database stats + version      |

### Diagnostics

| Method | Path          | Description     |
|--------|---------------|-----------------|
| `GET`  | `/debug.php`  | `phpinfo()`     |
| `GET`  | `/uploads/`   | Autoindex listing of uploads |

## Project Structure

```
├── public/                   # nginx web root
│   ├── index.php             # Front controller
│   ├── debug.php             # phpinfo()
│   ├── assets/{css,js}/      # CSS + DOM-XSS-vulnerable notification.js
│   └── uploads/              # Avatars + shared files (autoindex'd)
├── src/
│   ├── App.php               # Bootstrap + route table
│   ├── Db.php                # PDO wrapper (raw + prepared helpers)
│   ├── Router.php            # Tiny path → controller dispatcher
│   ├── Auth.php              # Sessions, remember-me, role/preferences cookies
│   ├── Controller.php        # Base controller (view/redirect/flash)
│   ├── Controllers/          # 10 feature controllers
│   └── Views/                # Page templates + partials
├── db/
│   ├── init.sql              # MySQL schema
│   └── seed.php              # CLI seeder
├── tests/                    # PHPUnit (controller tests + VulnerabilitiesTest)
├── docs/
│   ├── SRS.md                # Software Requirements Specification (CHUB-SRS-002 v2.1)
│   ├── IFA.md                # Interface Agreement / API contract  (CHUB-IFA-002 v2.1)
│   ├── User Matrix.md        # User Access Matrix                  (CHUB-UMX-002 v2.1)
│   ├── UAT Plan.md           # User Acceptance Testing Plan        (CHUB-UAT-002 v2.1)
│   └── Testing User.csv      # List of seeded test accounts
├── composer.json             # Pins outdated phpmailer
├── docker-compose.yml        # web (nginx) + app (php-fpm) + db (mysql)
├── Dockerfile                # php:8.2-fpm-alpine + extensions
├── nginx.conf                # autoindex on /uploads/, no security headers
└── Makefile
```

## Vulnerabilities

This portal intentionally contains the following security vulnerabilities:

| #  | Vulnerability | OWASP | Affected Pages | Description |
|----|---|---|---|---|
| 1  | SQL Injection | A03:2021 — Injection | `POST /login` | The login query interpolates `email` and the MD5 password directly into SQL: `SELECT … WHERE email='$email' AND password_md5='$hash'`. Passing `email=' OR 1=1-- ` bypasses authentication and logs in as the first matching row. |
| 2  | Stored XSS | A03:2021 — Injection | `GET /notes/{id}`, `GET /messages/{id}`, layout banner | Note bodies, message bodies and the site banner are rendered with `<?= $body ?>` — no `htmlspecialchars()`. Anyone viewing the resource executes attacker JavaScript in their session. |
| 3  | Reflected XSS | A03:2021 — Injection | `GET /directory/search?q=` | The search heading echoes the query string verbatim. `?q=<script>alert(1)</script>` reflects unescaped into the response. |
| 4  | DOM-based XSS | A03:2021 — Injection | layout `notification.js` | The notification banner script reads `location.hash`, decodes it, and assigns to `el.innerHTML`. Visiting `…#msg=<img src=x onerror=…>` runs the payload client-side. |
| 5  | Cross-Site Request Forgery | classic web | All state-changing `POST` endpoints | No CSRF tokens are emitted or verified. A malicious page can submit a form to any endpoint (delete note, change role, send DM) using the victim's cookies. |
| 6  | Insecure Direct Object References | A01:2021 — Broken Access Control | `GET /notes/{id}`, `GET /messages/{id}`, `GET /files/download?id=` | These endpoints look the row up by id with no `WHERE user_id = ?` or `recipient_id = ?` check, so any logged-in user can read or modify anyone else's resources. |
| 7  | Vertical Privilege Escalation | A01:2021 — Broken Access Control | All `/admin/*` routes | The admin guard reads `$_COOKIE['role']` instead of the database. Setting `Cookie: role=admin` on any logged-in session unlocks the admin panel without being an admin user. |
| 8  | Path Traversal | A01:2021 — Broken Access Control | `GET /files/download?file=` | The `file` parameter is concatenated under `public/uploads/` with no normalisation. `?file=../../etc/passwd` returns files from anywhere on the container filesystem. |
| 9  | Insecure File Upload | A04:2021 — Insecure Design | `POST /files`, `POST /profile/avatar` | Uploaded filenames and content are trusted as-is and saved under the webroot. `.php` files land at `/uploads/{name}.php` and are executed by nginx → php-fpm. |
| 10 | Server-Side Request Forgery | A10:2021 — SSRF | `GET /links/preview?url=` | The preview endpoint fetches whatever URL the user supplies. There is no scheme allowlist and no block on link-local / cloud-metadata / internal addresses. |
| 11 | Open Redirect | classic web | `POST /login` | The `next` query/form parameter is honoured verbatim after authentication, so `…/login?next=https://evil.example/` happily 302s the user off-site. |
| 12 | Security Misconfiguration | A05:2021 — Security Misconfig | global | `display_errors=On`, `phpinfo()` exposed at `/debug.php`, nginx `autoindex on` for `/uploads/`, and no CSP / HSTS / `X-Frame-Options` / `X-Content-Type-Options` response headers. |
| 13 | Cryptographic Failures | A02:2021 — Cryptographic Failures | `POST /login`, "remember me" cookie | Passwords are stored as raw `MD5` (no salt, fast hash). The remember-me cookie is `base64(user_id)` with no signature or MAC, so it can be forged for any user id. |
| 14 | Identification & Authentication Failures | A07:2021 — Identification & AuthN | `POST /login`, `POST /forgot` | No login rate-limit / lockout. Password-reset tokens are 6 lowercase-alphanumeric characters (~31 bits), brute-forceable. |
| 15 | Insecure Deserialization (PHP object injection) | A08:2021 — Software & Data Integrity | `preferences` cookie | `Auth::loadPreferencesCookie()` calls `unserialize()` on a base64-decoded client cookie on every request. Any reachable class with `__wakeup` / `__destruct` becomes a gadget. |
| 16 | Vulnerable & Outdated Components | A06:2021 — Vulnerable Components | `composer.json` | `phpmailer/phpmailer` is pinned at `6.0.6`, which is vulnerable to **CVE-2018-19296** (PHP object injection via deserialization in `Mail::mailSend()`). |
| 17 | XML External Entity (XXE) | A05:2021 — Security Misconfig | `POST /import` | Contact-import XML is parsed with `LIBXML_NOENT \| LIBXML_DTDLOAD`, allowing external entities (`<!ENTITY xxe SYSTEM "file:///etc/passwd">`) to be expanded and surfaced via the import summary. |
| 18 | Security Logging & Monitoring Failures | A09:2021 — Logging Failures | global | No login-failure log, no admin-action audit, no log table or log file. Privilege changes and unsuccessful logins leave no trace. |
| 19 | Clickjacking | classic web | All pages | Responses ship without `X-Frame-Options` or `Content-Security-Policy: frame-ancestors`, so any external site can iframe `/admin/*` and trick a victim into clicking through admin actions. |
