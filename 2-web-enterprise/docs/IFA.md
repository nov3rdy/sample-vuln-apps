<!--
═══════════════════════════════════════════════════════════════════════════════
  CompanyHub  —  Interface Agreement
  Document classification: INTERNAL USE ONLY
═══════════════════════════════════════════════════════════════════════════════
-->

# CompanyHub — Interface Agreement

| | |
|---|---|
| **Document ID**       | CHUB-IFA-002 |
| **Document Title**    | CompanyHub Internal Portal — Interface Agreement |
| **Version**           | 2.1 |
| **Status**            | APPROVED |
| **Classification**    | Internal Use Only |
| **Issue Date**        | 2026-04-22 |
| **Effective Date**    | 2026-05-01 |
| **Next Review**       | 2026-10-22 |
| **Owner**             | Internal Tooling Squad — Platform Engineering |
| **Counterparty**      | Information Security — AppSec; QA — Test Engineering |
| **Supersedes**        | CHUB-IFA-001 (v1.4, 2025-11-30) |

### Revision History

| Rev. | Date       | Author          | Summary of Change                                                                |
|------|------------|-----------------|----------------------------------------------------------------------------------|
| 1.0  | 2025-08-04 | A. Patel (PE)   | Initial issue — auth, directory, notes endpoints.                                |
| 1.2  | 2025-09-19 | A. Patel (PE)   | Added Files and Team Links endpoints. Error envelope formalised.                 |
| 1.4  | 2025-11-30 | M. Okafor (PE)  | Added Messaging, Profile. Cookie catalogue moved to Appendix C.                  |
| 2.0  | 2026-03-12 | M. Okafor (PE)  | Major rewrite. Added Administration, Contact Import. Status code reference revised. |
| 2.1  | 2026-04-22 | M. Okafor (PE)  | Tightened auth model (§3); added rate-limit table (§4.6); reformatted §5 schemas. |

### Distribution List

- Internal Tooling Squad
- Information Security — AppSec
- QA — Test Engineering
- Site Reliability — Tier-3 on-call rota

### Approval Record

| Role                    | Name              | Signature             | Date        |
|-------------------------|-------------------|-----------------------|-------------|
| Author                  | M. Okafor         | _signed electronically_ | 2026-04-22 |
| Technical Reviewer      | A. Patel          | _signed electronically_ | 2026-04-23 |
| Security Reviewer       | J. Bartoszewicz   | _signed electronically_ | 2026-04-24 |
| Counterparty (AppSec)   | J. Bartoszewicz   | _signed electronically_ | 2026-04-24 |
| Counterparty (QA)       | S. Romero         | _signed electronically_ | 2026-04-25 |
| Sponsor / Approver      | R. Lindqvist      | _signed electronically_ | 2026-04-26 |

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Service Overview](#2-service-overview)
3. [Authentication & Session Model](#3-authentication--session-model)
4. [Common Conventions](#4-common-conventions)
5. [Resource Specifications](#5-resource-specifications)
6. [Data Model](#6-data-model)
7. [Operational Contract](#7-operational-contract)
8. [Change Control](#8-change-control)
9. [Appendix A — HTTP Status Code Reference](#appendix-a--http-status-code-reference)
10. [Appendix B — Error Code Catalogue](#appendix-b--error-code-catalogue)
11. [Appendix C — Cookie Catalogue](#appendix-c--cookie-catalogue)

---

## 1. Introduction

### 1.1 Purpose

This Interface Agreement (IFA) is the binding specification of the HTTP interface presented by the CompanyHub portal to its sole consumer — the operator's web browser. The document is the contract between the implementation team (Internal Tooling Squad) and the consuming parties (AppSec, QA, SRE).

### 1.2 Scope

This document covers the request/response contract for every routed path served by the CompanyHub origin. It does NOT cover:

- The reverse-proxy contract upstream of CompanyHub (governed by CORP-OPS-014).
- The corporate audit-sink envelope (governed by CORP-OBS-008).
- Static asset packaging, which is a build-time concern with no runtime contract.

### 1.3 Audience

| Audience       | Use of this document                                                       |
|----------------|----------------------------------------------------------------------------|
| AppSec         | Threat-model input; verification of the authorisation model (§3, §5).      |
| QA             | Test-case derivation. All examples in §5 are normative.                    |
| SRE            | Operational contract (§7) for SLOs and incident handling.                  |
| Engineering    | Implementation reference. Discrepancies between code and this document     |
|                | SHALL be reported via the change-control process (§8).                     |

### 1.4 Document Hierarchy

This IFA is downstream of and consistent with:

- CHUB-SRS-002 — CompanyHub Software Requirements Specification, v2.1
- CORP-API-005 — Corporate HTTP Interface Conventions, v3.0
- CORP-ISP-014 — Corporate Information Security Policy, v6.2

In any conflict, the SRS prevails. This IFA refines the SRS with concrete request/response detail.

---

## 2. Service Overview

### 2.1 Service Name

`companyhub-portal` (corporate registry CMDB-04219)

### 2.2 Service Owner

Internal Tooling Squad, Platform Engineering. On-call rotation: `companyhub-portal-oncall`. Slack channel: `#companyhub-portal`.

### 2.3 Endpoints by Environment

| Environment | URL                                              | Notes                                       |
|-------------|--------------------------------------------------|---------------------------------------------|
| Development | `http://localhost:60012`                          | Loopback only; no external exposure.        |
| Test        | `https://companyhub.test.internal`                | Behind corporate WAF.                       |
| Staging     | `https://companyhub.stg.internal`                 | Mirrors production except for data fixture. |
| Production  | `https://companyhub.internal`                     | Tier-3 production service.                  |

### 2.4 Versioning Policy

CompanyHub is browser-only and renders HTML; the URL space is therefore **unversioned**. Backwards-incompatible changes to a path's contract SHALL be released only via a major SRS revision, accompanied by an updated IFA and at least 14 calendar days of pre-announcement.

---

## 3. Authentication & Session Model

### 3.1 Authentication Mechanism

CompanyHub uses an interactive form-based login (`POST /login`) to authenticate operators. On success, the server establishes a server-side session and issues a session cookie. There is no bearer-token API surface.

### 3.2 Session Cookie

| Attribute     | Required value                                                  |
|---------------|------------------------------------------------------------------|
| Name          | `PHPSESSID`                                                      |
| Path          | `/`                                                              |
| Domain        | (origin-bound; no `Domain` attribute issued)                     |
| `Secure`      | Required in test, staging, production.                           |
| `HttpOnly`    | Required in all environments.                                    |
| `SameSite`    | `Lax`                                                            |
| Lifetime      | Browser session (no `Max-Age`).                                  |

### 3.3 Persistent Sign-In Cookie

| Attribute     | Required value                                                                                  |
|---------------|--------------------------------------------------------------------------------------------------|
| Name          | `remember_me`                                                                                    |
| Value         | Opaque, signed, version-prefixed token of the form `v1.<base64url-payload>.<hex-signature>`.    |
| `Secure`      | Required.                                                                                        |
| `HttpOnly`    | Required.                                                                                        |
| `SameSite`    | `Lax`                                                                                            |
| `Max-Age`     | `2592000` (30 days)                                                                              |

The signature is HMAC-SHA-256 over the payload using a server-only key. Tampered cookies SHALL be rejected.

### 3.4 CSRF Protection

All state-changing requests (`POST`, `PUT`, `PATCH`, `DELETE`) SHALL include a per-session anti-CSRF token. The token SHALL be:

- Issued on session creation and rotated on `/logout` and on role change.
- Embedded in every form rendered by the server as a hidden field `_csrf`.
- Validated server-side prior to dispatching the action; failures return `403 Forbidden` with error code `CSRF_INVALID` (Appendix B).

### 3.5 Authorisation Levels

| Level            | Cookie/session state                            | Access                                                                 |
|------------------|--------------------------------------------------|------------------------------------------------------------------------|
| Anonymous        | No session                                       | `/login`, `/register`, `/forgot`, `/reset`, static assets only.        |
| Operator         | Authenticated session with `role=user`           | All workspace endpoints (§5.2 – §5.8).                                 |
| Administrator    | Authenticated session with `role=admin`          | All Operator endpoints plus `/admin/*` (§5.9).                          |

The `role` attribute is read **from the server-side session**, not from any cookie or header. This requirement is non-negotiable.

---

## 4. Common Conventions

### 4.1 Request Conventions

- All requests SHALL be made over HTTPS in test, staging, and production.
- Form bodies SHALL use `application/x-www-form-urlencoded` (default for HTML forms) or `multipart/form-data` (for file uploads).
- The `Accept` header is advisory; CompanyHub returns HTML for navigational requests and JSON only for endpoints explicitly marked as such in §5.

### 4.2 Response Conventions

- Successful responses SHALL return HTML (`text/html; charset=utf-8`) for browser-navigational requests.
- Error responses SHALL render an HTML error page in the same visual chrome as the application, with a stable error code in the page metadata (`<meta name="x-error-code">`).
- Redirects after `POST` SHALL use HTTP `303 See Other` per the post/redirect/get pattern.

### 4.3 Date & Time Format

All timestamps in HTML and in storage SHALL be ISO 8601 in UTC (e.g. `2026-04-22T14:30:00Z`). Browser rendering MAY localise via the `<time>` element.

### 4.4 Identifier Format

Resource identifiers SHALL be 64-bit unsigned integers presented as decimal strings in URLs. Sequential disclosure of identifiers does not imply read access — see NFR-SEC-007 (SRS).

### 4.5 Pagination

Endpoints returning lists SHALL paginate at 50 records per page using the query parameters `?page=N&per_page=M`. The default is `page=1, per_page=50`. The maximum `per_page` is `200`.

### 4.6 Rate Limits

The corporate WAF enforces global rate limits per source IP. The application enforces per-account limits as follows:

| Surface                       | Limit                                | Exceeded behaviour                          |
|-------------------------------|---------------------------------------|---------------------------------------------|
| `POST /login`                 | 5 attempts / account / 5 minutes      | `429 Too Many Requests`, 15-minute lockout. |
| `POST /forgot`                | 3 attempts / account / 60 minutes     | `429 Too Many Requests`.                    |
| `POST /import`                | 10 imports / account / 24 hours       | `429 Too Many Requests`.                    |
| `GET /links/preview`          | 60 fetches / session / hour           | `429 Too Many Requests`.                    |
| All other endpoints           | (No application-level limit)          | WAF protections apply.                      |

### 4.7 Security Response Headers

Every response SHALL include:

```
Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; frame-ancestors 'none'
Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

---

## 5. Resource Specifications

The following sub-sections define every routed path. Each entry is normative.

### 5.1 Authentication

#### 5.1.1 `GET /login`

- **Auth required**: No
- **Description**: Renders the sign-in form.
- **Query parameters**:
  - `next` *(optional, string)* — Same-origin path to redirect to after successful sign-in. Values not matching `^/[^/]` SHALL be discarded server-side.
- **Responses**: `200 OK` (HTML).

#### 5.1.2 `POST /login`

- **Auth required**: No
- **Form fields**:
  - `email` *(required, string ≤ 255)*
  - `password` *(required, string)*
  - `next` *(optional)* — see `GET /login`.
  - `_csrf` *(required)*
- **Responses**:
  - `303 See Other` → resolved `next` (or `/dashboard`) on success.
  - `303 See Other` → `/login` on failure, with `flash` of code `AUTH_INVALID`.
  - `429 Too Many Requests` if rate-limit exceeded (§4.6).

#### 5.1.3 `GET /register`, `POST /register`

- **Auth required**: No.
- **Form fields**: `email`, `display_name`, `department`, `password`, `_csrf`.
- **Constraints**:
  - `email` SHALL match the corporate domain allowlist (`@companyhub.local`).
  - `password` SHALL be at least 12 characters and SHALL pass the corporate password-policy validator.
- **Responses**: `303 See Other` → `/dashboard` on success; `303 See Other` → `/register` on failure.

#### 5.1.4 `GET /logout`

- **Auth required**: Yes.
- **Behaviour**: Invalidates session, clears `PHPSESSID` and `remember_me`.
- **Response**: `303 See Other` → `/login`.

#### 5.1.5 `GET /forgot`, `POST /forgot`

- **Auth required**: No.
- **`POST` form fields**: `email`, `_csrf`.
- **Behaviour**: Issues a reset token if the account exists; the response SHALL be identical regardless of whether the account exists, to prevent enumeration.
- **Response**: `303 See Other` → `/forgot` with a generic flash.

#### 5.1.6 `GET /reset`, `POST /reset`

- **Auth required**: No.
- **Query parameter (`GET`)**: `token` *(required, opaque string ≥ 32 chars)*.
- **`POST` form fields**: `token`, `password`, `_csrf`.
- **Response**: `303 See Other` → `/login` on success; `303 See Other` → `/forgot` on invalid or expired token.

### 5.2 Directory

#### 5.2.1 `GET /directory`

- **Auth required**: Operator.
- **Response**: `200 OK` rendering the employee list (paginated, §4.5).

#### 5.2.2 `GET /directory/search?q=…`

- **Auth required**: Operator.
- **Query parameter**: `q` *(string, ≤ 80 chars)*.
- **Response**: `200 OK` rendering the filtered list. The query SHALL be encoded when reflected on the page (FR-DIR-003).

#### 5.2.3 `GET /directory/{id}`

- **Auth required**: Operator.
- **Path parameter**: `id` *(positive integer)*.
- **Response**: `200 OK` or `404 Not Found`.

### 5.3 Notes

| Method | Path                       | Auth      | Notes                                                                                  |
|--------|----------------------------|-----------|----------------------------------------------------------------------------------------|
| GET    | `/notes`                   | Operator  | Lists notes the operator owns + public notes from others.                               |
| GET    | `/notes/new`               | Operator  | Renders the new-note form. Includes `_csrf` token.                                     |
| POST   | `/notes`                   | Operator  | Creates a note. Required: `title`, `body`, optional `is_public`, required `_csrf`.     |
| GET    | `/notes/{id}`              | Operator¹ | Reads a note.                                                                          |
| GET    | `/notes/{id}/edit`         | Operator¹ | Renders edit form.                                                                     |
| POST   | `/notes/{id}`              | Operator¹ | Updates a note. Form fields as for create.                                              |
| POST   | `/notes/{id}/delete`       | Operator¹ | Deletes a note. Form: `_csrf`.                                                          |

¹ — Subject to ownership: the operator must own the note, **or** the note must be marked `is_public=1` (read only).

### 5.4 Messages

| Method | Path                | Auth      | Notes                                                                                          |
|--------|---------------------|-----------|------------------------------------------------------------------------------------------------|
| GET    | `/messages`         | Operator  | Inbox: messages where the operator is the recipient.                                            |
| GET    | `/messages/new`     | Operator  | Compose form. `?to={id}` may pre-select a recipient.                                           |
| POST   | `/messages`         | Operator  | Send. Required: `recipient_id`, `body`, `_csrf`.                                               |
| GET    | `/messages/{id}`    | Operator¹ | Renders a message thread. `_csrf` required for any state change.                                |

¹ — Subject to participation: only the sender or recipient may read.

### 5.5 Files

| Method | Path                            | Auth      | Notes                                                                                              |
|--------|---------------------------------|-----------|----------------------------------------------------------------------------------------------------|
| GET    | `/files`                        | Operator  | Listing of available files; upload form.                                                            |
| POST   | `/files`                        | Operator  | Upload via `multipart/form-data` field `file`. ≤ 16 MiB. MIME allowlist: NFR-SEC-016.               |
| GET    | `/files/download?id=…`          | Operator¹ | Download by file id. The `id` parameter SHALL be a positive integer.                                |

¹ — Ownership and visibility rules apply (FR-FILE-004).

The `?file=` parameter form is **not** part of the contract. Any request with `?file=` SHALL be rejected with `400 Bad Request` and error code `PARAMETER_NOT_SUPPORTED`.

### 5.6 Team Links

| Method | Path                  | Auth      | Notes                                                                                                |
|--------|-----------------------|-----------|------------------------------------------------------------------------------------------------------|
| GET    | `/links`              | Operator  | List + add-link form.                                                                                 |
| POST   | `/links`              | Operator  | Required: `url`, optional `title`, required `_csrf`.                                                  |
| GET    | `/links/preview?url=` | Operator  | Server-side fetch. Subject to NFR-SEC-015 (deny-list of internal/link-local addresses) and §4.6 limit. |

### 5.7 Profile

| Method | Path                       | Auth      | Notes                                                                                              |
|--------|----------------------------|-----------|----------------------------------------------------------------------------------------------------|
| GET    | `/profile`                 | Operator  | Renders own profile.                                                                                |
| POST   | `/profile/avatar`          | Operator  | Avatar upload, `multipart/form-data` field `avatar`. MIME allowlist: `image/png`, `image/jpeg`, `image/webp`. |
| POST   | `/profile/preferences`     | Operator  | Required: `theme` ∈ {`light`,`dark`,`sepia`}, optional `compact_mode`, required `_csrf`. Persisted server-side. |

### 5.8 Contact Import

| Method | Path        | Auth          | Notes                                                                                          |
|--------|-------------|---------------|------------------------------------------------------------------------------------------------|
| GET    | `/import`   | Administrator | Renders upload form.                                                                            |
| POST   | `/import`   | Administrator | `multipart/form-data` field `xml`. Schema: `Contacts.xsd` v2.0. XXE protections per NFR-SEC-020. |

`Contacts.xsd` (excerpt) — informational:

```xml
<xs:element name="contacts">
  <xs:complexType>
    <xs:sequence>
      <xs:element name="contact" maxOccurs="unbounded">
        <xs:complexType>
          <xs:sequence>
            <xs:element name="name"  type="xs:string"/>
            <xs:element name="email" type="xs:string"/>
            <xs:element name="note"  type="xs:string" minOccurs="0"/>
          </xs:sequence>
        </xs:complexType>
      </xs:element>
    </xs:sequence>
  </xs:complexType>
</xs:element>
```

### 5.9 Administration

All administration endpoints require an authenticated session whose **server-side** role attribute is `admin`. Cookie-supplied role indicators are not consulted.

| Method | Path                            | Auth          | Notes                                                                       |
|--------|---------------------------------|---------------|-----------------------------------------------------------------------------|
| GET    | `/admin`                        | Administrator | Overview cards.                                                              |
| GET    | `/admin/users`                  | Administrator | User list. Password material SHALL NOT be rendered.                          |
| POST   | `/admin/users/{id}/role`        | Administrator | Form: `role` ∈ {`user`,`admin`}, `_csrf`.                                    |
| GET    | `/admin/banner`                 | Administrator | Renders banner editor.                                                       |
| POST   | `/admin/banner`                 | Administrator | Form: `banner_html` (safe-HTML subset, FR-ADM-005), `_csrf`.                 |
| GET    | `/admin/stats`                  | Administrator | Aggregate counts + engine version.                                            |

### 5.10 Diagnostics

CompanyHub exposes **no** runtime diagnostic endpoints. Production builds SHALL NOT contain `phpinfo()`, `var_dump`, debug toolbars, or directory listings.

---

## 6. Data Model

The persistent data model is shown below. Foreign-key edges are read as "owns one" or "is referenced by".

```
users (1) ──── (∞) notes
users (1) ──── (∞) messages.sender_id
users (1) ──── (∞) messages.recipient_id
users (1) ──── (∞) files
users (1) ──── (∞) links
users (1) ──── (∞) password_resets
```

### 6.1 `users`

| Column          | Type         | Notes                                                  |
|-----------------|--------------|--------------------------------------------------------|
| `id`            | INT PK       |                                                        |
| `email`         | VARCHAR(255) | Unique. Domain allowlisted on insert.                  |
| `password_hash` | VARCHAR(255) | Argon2id-encoded (NFR-SEC-009).                        |
| `display_name`  | VARCHAR(120) |                                                        |
| `department`    | VARCHAR(80)  |                                                        |
| `role`          | ENUM         | `user`, `admin`. Server-controlled.                    |
| `avatar_path`   | VARCHAR(255) | Storage path; not directly served.                     |
| `created_at`    | DATETIME     |                                                        |

> Implementation note: the column name `password_hash` reflects the contract (Argon2id). The current implementation column may differ — see CHUB-CR-2026-014 for migration tracking.

### 6.2 `notes`, `messages`, `files`, `links`, `password_resets`

See the entity diagrams in CHUB-DM-002 §3 (data-model addendum).

---

## 7. Operational Contract

### 7.1 Service Level Objectives

| Metric                      | Target            | Measurement window        |
|-----------------------------|-------------------|---------------------------|
| Availability                | 99.5%             | Calendar month            |
| Read latency (p95)          | ≤ 350 ms          | Rolling 5 minutes         |
| Write latency (p95)         | ≤ 600 ms          | Rolling 5 minutes         |
| Error rate (5xx, excl. 503) | < 0.1%            | Rolling 5 minutes         |

### 7.2 Monitoring & Alerting

- Uptime checks every 60 s from each datacentre.
- p95 latency alerts page the on-call when > 500 ms for 5 consecutive minutes.
- Error-rate alerts page the on-call when 5xx > 1% for 2 consecutive minutes.

### 7.3 Incident Response

Severity classification follows CORP-OPS-019. Service owner contact is documented in §2.2.

### 7.4 Deprecation Policy

A deprecated endpoint SHALL emit `Deprecation: <RFC 9745 date>` and `Link: <…>; rel="successor-version"` headers for at least one full release cycle prior to removal.

---

## 8. Change Control

Changes to this document SHALL be raised as a Change Request via the engineering RFC process and SHALL be approved by the parties listed in the Approval Record. Editorial corrections (spelling, formatting) MAY be applied without an RFC.

Implementation drift — that is, divergence between this contract and the running code — SHALL be raised by AppSec or QA as a defect against the implementation, **not** as a documentation update, unless the SRS-level requirement has changed.

---

## Appendix A — HTTP Status Code Reference

| Code                      | When CompanyHub uses it                                                              |
|---------------------------|--------------------------------------------------------------------------------------|
| `200 OK`                  | Successful read responses (HTML).                                                    |
| `303 See Other`           | After a successful state-changing request (post/redirect/get).                       |
| `400 Bad Request`         | Malformed input or use of unsupported request parameters.                            |
| `401 Unauthorized`        | Authentication required. Always served via `303 → /login` for browser flows.         |
| `403 Forbidden`           | Authenticated but not authorised; CSRF rejection.                                    |
| `404 Not Found`           | Resource does not exist or is not visible to the requester.                          |
| `409 Conflict`            | Constraint violation (e.g. duplicate email on register).                             |
| `413 Payload Too Large`   | Upload exceeds the 16 MiB limit.                                                     |
| `415 Unsupported Media Type` | MIME outside the allowlist.                                                       |
| `422 Unprocessable Entity`| Submitted form fails server-side validation.                                         |
| `429 Too Many Requests`   | Rate limit exceeded (§4.6). Includes `Retry-After`.                                  |
| `500 Internal Server Error` | Unhandled server fault. Surfaces the correlation id only.                          |
| `503 Service Unavailable` | Maintenance window or downstream outage. Includes `Retry-After`.                     |

## Appendix B — Error Code Catalogue

All error pages embed `<meta name="x-error-code" content="…">`. Stable error codes:

| Code                          | Meaning                                                            |
|-------------------------------|--------------------------------------------------------------------|
| `AUTH_INVALID`                | Invalid credentials submitted to `/login`.                          |
| `AUTH_RATE_LIMITED`           | Too many login attempts; account temporarily locked.                |
| `AUTH_RESET_INVALID`          | Reset token unknown, expired, or already used.                      |
| `AUTH_REGISTER_DOMAIN`        | Email outside the allowlisted domain.                               |
| `AUTH_REGISTER_DUPLICATE`     | Email already registered.                                            |
| `CSRF_INVALID`                | Anti-CSRF token missing or invalid.                                 |
| `AUTHZ_DENIED`                | Authenticated but not authorised for the resource.                  |
| `RESOURCE_NOT_FOUND`          | Resource id resolves to nothing visible.                            |
| `INPUT_INVALID`               | Form input failed validation.                                       |
| `UPLOAD_TOO_LARGE`            | Upload exceeds the 16 MiB limit.                                    |
| `UPLOAD_TYPE_REJECTED`        | MIME not on the allowlist.                                          |
| `PARAMETER_NOT_SUPPORTED`     | Caller used a query parameter that is not part of the contract.     |
| `PREVIEW_TARGET_FORBIDDEN`    | Link-preview target resolved to an internal address.                |
| `PREVIEW_TARGET_UNREACHABLE`  | Link-preview target was unreachable within the timeout budget.      |
| `IMPORT_SCHEMA_INVALID`       | Uploaded XML did not validate against `Contacts.xsd`.               |
| `RATE_LIMITED`                | Per-account rate limit exceeded (§4.6).                              |
| `INTERNAL_FAULT`              | Unhandled server fault. Correlation id surfaced; details suppressed. |

## Appendix C — Cookie Catalogue

| Cookie              | Purpose                                            | Set by                  | Required attributes                                              |
|---------------------|-----------------------------------------------------|-------------------------|------------------------------------------------------------------|
| `PHPSESSID`         | Server-session identifier.                          | `POST /login` etc.      | `Secure; HttpOnly; SameSite=Lax`; session lifetime.              |
| `remember_me`       | Long-lived "stay signed in" cookie.                 | `POST /login`           | Signed; `Secure; HttpOnly; SameSite=Lax`; `Max-Age=2592000`.     |
| `_csrf`             | Optional double-submit anti-CSRF token (defence-in-depth alongside the form-field token). | Server on session start | `Secure; SameSite=Lax`. NOT `HttpOnly` (read by client form code). |

CompanyHub SHALL NOT set any cookie that conveys authorisation state independently of the server-side session — explicitly, no `role` cookie, no `is_admin` cookie, and no client-controlled preferences cookie that is read back via `unserialize()` or any equivalent native-deserialization sink.

---

*— END OF DOCUMENT —*
