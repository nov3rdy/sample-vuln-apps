<!--
═══════════════════════════════════════════════════════════════════════════════
  CompanyHub  —  Software Requirements Specification
  Document classification: INTERNAL USE ONLY
═══════════════════════════════════════════════════════════════════════════════
-->

# CompanyHub — Software Requirements Specification

| | |
|---|---|
| **Document ID**       | CHUB-SRS-002 |
| **Document Title**    | CompanyHub Internal Portal — Software Requirements Specification |
| **Version**           | 2.1 |
| **Status**            | APPROVED |
| **Classification**    | Internal Use Only |
| **Issue Date**        | 2026-04-22 |
| **Effective Date**    | 2026-05-01 |
| **Next Review**       | 2026-10-22 |
| **Owner**             | Internal Tooling Squad — Platform Engineering |
| **Sponsor**           | VP, Information Technology |
| **Supersedes**        | CHUB-SRS-001 (v1.4, 2025-11-30) |

### Revision History

| Rev. | Date       | Author              | Summary of Change                                                                  |
|------|------------|---------------------|------------------------------------------------------------------------------------|
| 1.0  | 2025-08-04 | A. Patel (PE)       | Initial issue — auth, directory, notes.                                            |
| 1.2  | 2025-09-19 | A. Patel (PE)       | Added Files (FR-FILE) and Team Links (FR-LINK). NFR-SEC re-baselined.              |
| 1.4  | 2025-11-30 | M. Okafor (PE)      | Added Messaging (FR-MSG), Profile (FR-PROF). Withdrawn 2026-04 in favour of v2.x.  |
| 2.0  | 2026-03-12 | M. Okafor (PE)      | Major rewrite. Added Administration (FR-ADM), Contact Import (FR-IMP).             |
| 2.1  | 2026-04-22 | M. Okafor (PE)      | Clarified NFR-SEC-014 (CSRF), NFR-SEC-021 (deserialization), added Appendix C.     |

### Distribution List

- Internal Tooling Squad — all members
- Platform Engineering Leadership
- Information Security — AppSec team
- People Operations — Workplace Systems
- Legal & Compliance — Data Governance

### Approval Record

| Role                    | Name              | Signature             | Date        |
|-------------------------|-------------------|-----------------------|-------------|
| Author                  | M. Okafor         | _signed electronically_ | 2026-04-22 |
| Technical Reviewer      | A. Patel          | _signed electronically_ | 2026-04-23 |
| Security Reviewer       | J. Bartoszewicz   | _signed electronically_ | 2026-04-24 |
| Sponsor / Approver      | R. Lindqvist      | _signed electronically_ | 2026-04-26 |

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Overall Description](#2-overall-description)
3. [Functional Requirements](#3-functional-requirements)
4. [Non-Functional Requirements](#4-non-functional-requirements)
5. [External Interface Requirements](#5-external-interface-requirements)
6. [Other Requirements](#6-other-requirements)
7. [Appendix A — Glossary](#appendix-a--glossary)
8. [Appendix B — Traceability Matrix](#appendix-b--traceability-matrix)
9. [Appendix C — Open Items & Deferred Requirements](#appendix-c--open-items--deferred-requirements)

---

## 1. Introduction

### 1.1 Purpose

This Software Requirements Specification (SRS) defines the functional and non-functional requirements for **CompanyHub**, the company's internal employee portal. It is the authoritative source of requirements for the engineering, quality assurance, and information-security teams responsible for delivering and operating the system.

This document supersedes CHUB-SRS-001 (v1.4) and shall be the binding requirements baseline for release v2.x of CompanyHub.

### 1.2 Scope

CompanyHub is a single-tenant, browser-accessed internal web portal that consolidates personal note-taking, internal directory, peer-to-peer messaging, shared file storage, link curation, profile management, contact import, and administrative tooling for company staff.

In scope:
- Authenticated browser access by all employees on the corporate network or VPN.
- Administration features (user role management, banner editorial control, telemetry).
- Programmatic interfaces required to support browser sessions (HTML over HTTPS).

Out of scope:
- Cross-tenant or external (customer-facing) access.
- Native mobile applications (a responsive web view is provided instead — see NFR-USE-003).
- Federation with external identity providers (deferred to roadmap item ROADMAP-AUTH-19).
- Public REST/JSON API. CompanyHub exposes browser HTML only; there is no public API surface in v2.x.

### 1.3 Intended Audience

| Audience                          | Use of this document                                    |
|-----------------------------------|----------------------------------------------------------|
| Product Owner                     | Validate scope and prioritisation against business goals |
| Engineering — Platform team       | Implementation reference; acceptance criteria            |
| Quality Assurance                 | Test plan derivation and coverage tracking               |
| Information Security — AppSec     | Threat model input; control mapping                      |
| Operations — Site Reliability     | Capacity planning; SLO derivation                        |
| Legal & Compliance                | Data-handling and retention validation                   |

### 1.4 Document Conventions

- The keywords **SHALL**, **SHALL NOT**, **MUST**, **MUST NOT**, **SHOULD**, **SHOULD NOT**, **MAY** are interpreted as defined in IETF RFC 2119.
- Functional requirements are tagged `FR-<area>-<###>` and are uniquely identified.
- Non-functional requirements are tagged `NFR-<area>-<###>`.
- Items marked `[TBD]` are explicitly Not Yet Determined and SHALL be resolved before the next major revision.

### 1.5 Definitions, Acronyms & Abbreviations

| Term      | Definition                                                                                  |
|-----------|---------------------------------------------------------------------------------------------|
| AppSec    | Application Security team within Information Security                                       |
| BAU       | Business As Usual                                                                           |
| CSRF      | Cross-Site Request Forgery                                                                  |
| HRIS      | Human Resources Information System (system of record for employee data)                     |
| IAM       | Identity & Access Management                                                                |
| MD5       | Message-Digest algorithm 5 (cryptographic hash function — see NFR-SEC-009)                  |
| MFA       | Multi-Factor Authentication                                                                 |
| OWASP     | Open Worldwide Application Security Project                                                 |
| Operator  | Any authenticated employee using CompanyHub                                                 |
| PII       | Personally Identifiable Information                                                         |
| RBAC      | Role-Based Access Control                                                                   |
| SLO       | Service Level Objective                                                                     |
| SSO       | Single Sign-On                                                                              |
| XSS       | Cross-Site Scripting                                                                        |

### 1.6 Reference Documents

| Ref.   | Title                                                                                |
|--------|--------------------------------------------------------------------------------------|
| [R-1]  | CHUB-IFA-002 — CompanyHub Interface Agreement, v2.1                                   |
| [R-2]  | CORP-ISP-014 — Corporate Information Security Policy, v6.2                            |
| [R-3]  | CORP-DAT-007 — Data Classification & Handling Standard, v3.0                          |
| [R-4]  | CORP-IAM-022 — Identity & Access Management Standard, v4.1                            |
| [R-5]  | OWASP ASVS v4.0.3 — Application Security Verification Standard                        |
| [R-6]  | IETF RFC 2119 — Key words for use in RFCs to indicate requirement levels              |
| [R-7]  | CHUB-OPS-005 — CompanyHub Operations Runbook, v1.2                                    |
| [R-8]  | CORP-LEG-031 — Records Retention Schedule, v2.0                                       |

---

## 2. Overall Description

### 2.1 Product Perspective

CompanyHub is a self-contained, server-rendered web application. It is the successor to the legacy "TeamWiki" system (decommissioned 2025-Q3). It does not replace the HRIS [R-1 §2.2]; HRIS remains the system of record for employment data, and CompanyHub consumes only the directory subset described in §3.2.

CompanyHub's logical context is summarised below.

```
   ┌──────────────┐    HTTPS       ┌──────────────────────┐
   │   Operator   │ ─────────────▶ │  CompanyHub Portal   │
   │  (browser)   │                │   (this system)      │
   └──────────────┘                └──────────┬───────────┘
                                              │
                                              ▼
                                   ┌──────────────────────┐
                                   │  CompanyHub Database │
                                   │   (single-tenant)    │
                                   └──────────────────────┘
```

External integrations (HRIS sync, SSO, SMTP relay) are tracked separately and are NOT in scope for v2.x. See Appendix C, items OPN-09, OPN-12, OPN-15.

### 2.2 Product Functions

The system SHALL provide:

- (F-01) Account creation, sign-in, sign-out, and password reset.
- (F-02) Searchable employee directory.
- (F-03) Personal and shared notes (CRUD).
- (F-04) Internal direct messaging.
- (F-05) Shared file storage with upload and download.
- (F-06) Curated team links with server-side preview retrieval.
- (F-07) Profile management including avatar and personalisation preferences.
- (F-08) Bulk contact import via XML.
- (F-09) Administration: user role management, site banner editorial, database statistics.

### 2.3 User Classes & Characteristics

| Class             | Description                                                                                           | Typical actions                                            |
|-------------------|-------------------------------------------------------------------------------------------------------|------------------------------------------------------------|
| **Anonymous**     | Browser visitor not yet authenticated                                                                 | Sign in, register (subject to IT approval), reset password |
| **Operator**      | Authenticated employee with the `user` role                                                           | All workspace features in F-02 through F-08                |
| **Administrator** | Authenticated employee with the `admin` role; nominated by IT and approved by the Sponsor             | All Operator actions plus F-09                             |
| **System**        | Internal background processes (seed loader, telemetry agent, retention sweeper)                       | Maintenance only — no interactive surface                  |

### 2.4 Operating Environment

| Aspect                | Requirement                                                                |
|-----------------------|----------------------------------------------------------------------------|
| Browser support       | Latest two major versions of Chromium, Firefox, and Safari                 |
| Network               | Corporate LAN or company-issued VPN; corporate certificate authority chain |
| Server platform       | Linux (corporate hardened image); container runtime supporting OCI         |
| Server language       | PHP 8.2 or later                                                           |
| Datastore             | MySQL 8.0 or later                                                         |
| Concurrent operators  | Sized for 500 simultaneous sessions at p95 (NFR-PERF-002)                  |

### 2.5 Design Constraints

- DC-01. The product SHALL be deliverable as an OCI-compliant container image suitable for deployment via the corporate orchestrator.
- DC-02. The product SHALL NOT introduce dependencies licensed under copyleft terms incompatible with corporate policy CORP-LEG-018.
- DC-03. The product SHALL operate behind the corporate reverse-proxy fleet and SHALL NOT terminate TLS itself.
- DC-04. All persistent storage SHALL reside on corporate-managed infrastructure within the EEA region for data-residency compliance.
- DC-05. The product MUST NOT call out to non-corporate domains during normal operation, except where explicitly enumerated in §5.4.

### 2.6 User Documentation

The following user-facing documentation SHALL be delivered with each release:

- DOC-01. End-user guide (HTML) accessible from the in-app footer.
- DOC-02. Administrator guide (HTML) accessible from `/admin/help`.
- DOC-03. Release notes (Markdown) published to the corporate intranet.

### 2.7 Assumptions & Dependencies

- A-01. Operators authenticate using credentials managed inside CompanyHub's own user store. SSO federation is deferred (Appendix C, OPN-09).
- A-02. Email delivery for password-reset and notifications relies on the corporate SMTP relay. Until the relay is integrated (Appendix C, OPN-12), reset tokens SHALL be surfaced through an in-product notice as a development/operational fallback.
- A-03. The corporate reverse-proxy enforces TLS 1.2+; the application is not required to negotiate TLS at the origin.
- A-04. The corporate WAF performs request-rate limiting at the edge; the application is not required to implement adaptive throttling internally (see NFR-SEC-018).

---

## 3. Functional Requirements

The functional requirements are grouped by feature area. Each requirement carries a unique identifier, a priority (`Must`, `Should`, `Could`), and a verification method (`Inspection`, `Demonstration`, `Test`, `Analysis`).

### 3.1 Authentication & Session Management — `FR-AUTH`

| ID            | Priority | Requirement                                                                                                                                                                              | Verification |
|---------------|----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-AUTH-001   | Must     | The system SHALL allow an Anonymous user to authenticate by submitting an email and password to the `/login` endpoint.                                                                   | Test         |
| FR-AUTH-002   | Must     | On successful authentication the system SHALL establish a server-side session and SHALL associate it with the operator's identifier, email, and role.                                     | Test         |
| FR-AUTH-003   | Must     | On unsuccessful authentication the system SHALL respond with a generic, non-enumerating error message ("Invalid email or password") regardless of whether the email is registered.        | Test         |
| FR-AUTH-004   | Must     | The system SHALL invalidate the server-side session and clear all session cookies on `/logout`.                                                                                          | Test         |
| FR-AUTH-005   | Must     | The system SHALL support a self-service "remember me" persistence with a cryptographically signed, opaque token bound to the originating user identifier and tamper-evident.              | Test         |
| FR-AUTH-006   | Must     | The system SHALL allow an Anonymous user to register a new account (`/register`) subject to email-domain allowlisting (`@companyhub.local`).                                              | Test         |
| FR-AUTH-007   | Must     | The system SHALL allow an Anonymous user to request a password reset (`/forgot`) by email; the reset token SHALL be at least 128 bits of entropy and expire after 1 hour.                | Test         |
| FR-AUTH-008   | Must     | The system SHALL invalidate any unused reset token issued for the same account on issue of a new reset.                                                                                  | Test         |
| FR-AUTH-009   | Should   | The system SHOULD honor a `next` parameter on `/login` only when its value resolves to a same-origin path (`/^\//`).                                                                     | Test         |
| FR-AUTH-010   | Could    | The system COULD prompt for a second authentication factor (TOTP) on first sign-in from a previously unseen browser. (Deferred — see Appendix C, OPN-04.)                                | Inspection   |

### 3.2 Employee Directory — `FR-DIR`

| ID         | Priority | Requirement                                                                                                                                       | Verification |
|------------|----------|---------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-DIR-001 | Must     | The system SHALL render a list of all active employees, including display name, email, department, and role.                                       | Test         |
| FR-DIR-002 | Must     | The system SHALL provide a free-text search across display name, email, and department, accessible at `/directory/search`.                         | Test         |
| FR-DIR-003 | Must     | The search query SHALL be safely encoded when reflected in the rendered page.                                                                     | Test         |
| FR-DIR-004 | Must     | A profile page SHALL be available at `/directory/{id}` for each employee, exposing only directory-class attributes (no contact preferences).      | Test         |
| FR-DIR-005 | Must     | All directory pages SHALL require an authenticated session.                                                                                       | Test         |

### 3.3 Notes — `FR-NOTE`

| ID          | Priority | Requirement                                                                                                                                                  | Verification |
|-------------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-NOTE-001 | Must     | An Operator SHALL create, view, edit, and delete notes that they own.                                                                                        | Test         |
| FR-NOTE-002 | Must     | A note SHALL carry an `is_public` flag controlling whether other Operators can list and read it.                                                             | Test         |
| FR-NOTE-003 | Must     | The system SHALL enforce ownership on all read, update, and delete operations on private notes.                                                              | Test         |
| FR-NOTE-004 | Must     | Note bodies SHALL be safely encoded on render so that user-supplied HTML cannot execute.                                                                     | Test         |
| FR-NOTE-005 | Must     | All state-changing note operations SHALL be protected by a per-session anti-CSRF token (NFR-SEC-014).                                                        | Test         |

### 3.4 Messages — `FR-MSG`

| ID         | Priority | Requirement                                                                                                                                                | Verification |
|------------|----------|------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-MSG-001 | Must     | An Operator SHALL view their inbox (messages addressed to them).                                                                                           | Test         |
| FR-MSG-002 | Must     | An Operator SHALL send a direct message to any other Operator selected from the directory.                                                                 | Test         |
| FR-MSG-003 | Must     | An Operator SHALL view the full content of any message addressed to or sent by them, and SHALL NOT view messages they neither sent nor received.           | Test         |
| FR-MSG-004 | Must     | Message bodies SHALL be safely encoded on render.                                                                                                          | Test         |

### 3.5 Files — `FR-FILE`

| ID          | Priority | Requirement                                                                                                                                                                          | Verification |
|-------------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-FILE-001 | Must     | An Operator SHALL upload files up to 16 MiB via a multipart/form-data request to `/files`.                                                                                            | Test         |
| FR-FILE-002 | Must     | The system SHALL reject uploads whose detected MIME type is not in the allowlist defined in NFR-SEC-016.                                                                              | Test         |
| FR-FILE-003 | Must     | The system SHALL store uploaded files outside the web-root or, where stored under the web-root, SHALL prevent direct execution by the request handler.                                | Test         |
| FR-FILE-004 | Must     | An Operator SHALL list available files and download by file identifier (`/files/download?id=…`); ownership and visibility rules SHALL apply.                                          | Test         |
| FR-FILE-005 | Must     | The system SHALL canonicalise any path supplied to a download endpoint and SHALL reject paths that escape the configured upload root.                                                 | Test         |

### 3.6 Team Links — `FR-LINK`

| ID          | Priority | Requirement                                                                                                                                                                                | Verification |
|-------------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-LINK-001 | Must     | An Operator SHALL submit a URL with an optional title to `/links` for inclusion in the shared list.                                                                                       | Test         |
| FR-LINK-002 | Must     | The system SHALL render a server-side fetched preview of a stored URL via `/links/preview?url=…`.                                                                                         | Test         |
| FR-LINK-003 | Must     | The preview fetcher SHALL restrict outbound requests to a documented allowlist of public-internet schemes (`https`, `http`) and SHALL reject any URL resolving to an internal address.    | Test         |
| FR-LINK-004 | Must     | The preview response body SHALL be capped at 1 MiB and SHALL be surfaced to the requester only after stripping HTML to a plain-text excerpt.                                              | Test         |

### 3.7 Profile — `FR-PROF`

| ID          | Priority | Requirement                                                                                                                                                  | Verification |
|-------------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-PROF-001 | Must     | An Operator SHALL view their own profile at `/profile`.                                                                                                      | Test         |
| FR-PROF-002 | Must     | An Operator SHALL upload an avatar image; the system SHALL accept only `image/png`, `image/jpeg`, and `image/webp` after server-side content sniffing.       | Test         |
| FR-PROF-003 | Must     | An Operator SHALL persist personalisation preferences (theme, compact mode); preferences SHALL be stored in a server-controlled record, not on the client.    | Test         |

### 3.8 Contact Import — `FR-IMP`

| ID         | Priority | Requirement                                                                                                                                                                                                  | Verification |
|------------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-IMP-001 | Must     | An Administrator SHALL upload an XML payload conforming to the `Contacts.xsd` schema to `/import` for bulk-creation of directory accounts.                                                                    | Test         |
| FR-IMP-002 | Must     | The XML parser SHALL operate with external-entity resolution disabled and DTD loading disabled.                                                                                                              | Test         |
| FR-IMP-003 | Must     | Imported records SHALL be persisted with a generated, non-disclosable initial password and a `must-change-on-first-login` flag.                                                                              | Test         |

### 3.9 Administration — `FR-ADM`

| ID         | Priority | Requirement                                                                                                                                                                       | Verification |
|------------|----------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------|
| FR-ADM-001 | Must     | An Administrator SHALL access an overview page at `/admin` linking to all administration features.                                                                                | Test         |
| FR-ADM-002 | Must     | An Administrator SHALL list all accounts with their role, department, and last-sign-in timestamp.                                                                                  | Test         |
| FR-ADM-003 | Must     | An Administrator SHALL change another account's role between `user` and `admin`.                                                                                                  | Test         |
| FR-ADM-004 | Must     | The role of an account SHALL be derived from the server-side user record. The system SHALL NOT trust any client-supplied role indicator.                                           | Test         |
| FR-ADM-005 | Must     | An Administrator SHALL edit the site banner; the banner editor SHALL accept a constrained safe-HTML subset (`b`, `strong`, `em`, `a` with `rel="noopener noreferrer"`).            | Test         |
| FR-ADM-006 | Must     | An Administrator SHALL view a database statistics page exposing only aggregate counts and the engine version.                                                                      | Test         |
| FR-ADM-007 | Must     | All `/admin` routes SHALL be access-checked against the authenticated operator's role attribute as held server-side.                                                               | Test         |

---

## 4. Non-Functional Requirements

### 4.1 Performance — `NFR-PERF`

| ID            | Priority | Requirement                                                                                                                              |
|---------------|----------|------------------------------------------------------------------------------------------------------------------------------------------|
| NFR-PERF-001  | Must     | 95th-percentile latency for any read endpoint SHALL NOT exceed 350 ms under nominal load.                                                |
| NFR-PERF-002  | Must     | The system SHALL sustain 500 concurrent authenticated sessions at the latency target above.                                              |
| NFR-PERF-003  | Should   | The static asset payload of any page SHOULD NOT exceed 250 KB compressed.                                                                |

### 4.2 Security — `NFR-SEC`

| ID           | Priority | Requirement                                                                                                                                                                                              |
|--------------|----------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| NFR-SEC-001  | Must     | All user input rendered into HTML SHALL be encoded according to context (HTML body, attribute, URL).                                                                                                     |
| NFR-SEC-002  | Must     | All database access SHALL use parameterised queries; string-concatenated SQL SHALL NOT be used.                                                                                                          |
| NFR-SEC-003  | Must     | The `Authorization` to access any non-public route SHALL be derived from the server-side session, never from a header or cookie set by the client.                                                       |
| NFR-SEC-004  | Must     | The system SHALL emit `Content-Security-Policy`, `Strict-Transport-Security`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, and `Referrer-Policy: strict-origin-when-cross-origin` headers. |
| NFR-SEC-005  | Must     | Sensitive cookies (`PHPSESSID`, `remember_me`) SHALL carry the `Secure`, `HttpOnly`, and `SameSite=Lax` attributes.                                                                                       |
| NFR-SEC-006  | Must     | Authorization decisions SHALL be enforced server-side; client-rendered affordances are advisory only.                                                                                                    |
| NFR-SEC-007  | Must     | Direct object references SHALL be access-checked against the requesting operator's ownership or read entitlement.                                                                                        |
| NFR-SEC-008  | Must     | File downloads SHALL be served via a path-canonicalised handler that rejects traversal sequences (`..`, encoded equivalents).                                                                            |
| NFR-SEC-009  | Must     | Passwords SHALL be stored using a memory-hard, salted KDF (Argon2id, parameters per CORP-ISP-014 §4.5). MD5, SHA-1, and unsalted hashes SHALL NOT be used.                                                |
| NFR-SEC-010  | Must     | Diagnostic endpoints (e.g. `phpinfo()`-equivalent) SHALL NOT be reachable in any deployed environment, including pre-production.                                                                          |
| NFR-SEC-011  | Must     | Detailed error pages SHALL be limited to non-production environments. Production responses SHALL surface a generic message and a correlation identifier.                                                  |
| NFR-SEC-012  | Must     | Directory listing on the upload location SHALL be disabled at the reverse proxy.                                                                                                                         |
| NFR-SEC-013  | Must     | Uploaded files SHALL be scanned by the corporate AV gateway (CORP-IS-027) before being made available for download.                                                                                      |
| NFR-SEC-014  | Must     | All state-changing requests SHALL carry a per-session, one-shot anti-CSRF token validated server-side.                                                                                                   |
| NFR-SEC-015  | Must     | Outbound HTTP from the link-preview feature SHALL be filtered by a deny-list including RFC 1918, link-local (`169.254/16`), loopback (`127/8`), and IPv6 site-local.                                      |
| NFR-SEC-016  | Must     | Upload MIME allowlist SHALL be: `image/png`, `image/jpeg`, `image/webp`, `application/pdf`, `text/plain`. All other types SHALL be rejected.                                                              |
| NFR-SEC-017  | Must     | Open redirects SHALL NOT be possible from any system-controlled redirect parameter.                                                                                                                      |
| NFR-SEC-018  | Should   | Login attempts SHOULD be subject to per-account exponential back-off (5 failed attempts within 5 minutes triggers a 15-minute lockout). The corporate WAF provides additional edge protection.            |
| NFR-SEC-019  | Must     | Password-reset tokens SHALL be at least 128 bits of entropy, single-use, and time-limited to 60 minutes.                                                                                                  |
| NFR-SEC-020  | Must     | XML parsers SHALL be configured with `LIBXML_NONET`, external entity resolution disabled, and DTD loading disabled.                                                                                       |
| NFR-SEC-021  | Must     | Server-side deserialization of untrusted input SHALL NOT use language-native deserializers (`unserialize()`, `pickle`, etc.). Structured data exchanged with the client SHALL use JSON.                   |
| NFR-SEC-022  | Must     | Third-party dependencies SHALL pass the corporate SCA gate (`security-blocking` enabled) at every release. Components with known critical CVEs SHALL NOT be released.                                     |
| NFR-SEC-023  | Must     | All authentication events (success / failure), password resets, role changes, and administrative actions SHALL be logged to the corporate audit sink (`audit.companyhub.local`) within 5 seconds.         |

### 4.3 Availability — `NFR-AVAIL`

| ID            | Priority | Requirement                                                                                                |
|---------------|----------|------------------------------------------------------------------------------------------------------------|
| NFR-AVAIL-001 | Must     | Service SHALL achieve 99.5% monthly availability (corporate "Tier 3" service class).                       |
| NFR-AVAIL-002 | Must     | Maintenance windows SHALL be communicated at least 5 business days in advance via the corporate channel.   |

### 4.4 Maintainability — `NFR-MAINT`

| ID             | Priority | Requirement                                                                                                            |
|----------------|----------|------------------------------------------------------------------------------------------------------------------------|
| NFR-MAINT-001  | Must     | The codebase SHALL pass the team's static analysis baseline (PHPStan level 7) at every merge to `main`.                |
| NFR-MAINT-002  | Must     | Test coverage of executable lines SHALL NOT fall below 70%.                                                            |
| NFR-MAINT-003  | Should   | All public functions SHOULD carry phpDoc type signatures; preferred over inline assertions.                            |

### 4.5 Compliance — `NFR-COMP`

| ID            | Priority | Requirement                                                                                                                  |
|---------------|----------|------------------------------------------------------------------------------------------------------------------------------|
| NFR-COMP-001  | Must     | Personal data handling SHALL comply with the Records Retention Schedule [R-8] §3.4 (employee directory data).                |
| NFR-COMP-002  | Must     | The system SHALL satisfy the controls mapped to OWASP ASVS Level 1 [R-5].                                                    |
| NFR-COMP-003  | Must     | All processing SHALL remain within the EEA region (DC-04).                                                                   |

---

## 5. External Interface Requirements

### 5.1 User Interfaces

The user interface SHALL be a single web application served at the URL prefix issued by Operations. The interface SHALL meet WCAG 2.2 Level AA accessibility for all primary navigation, forms, and tables.

### 5.2 Hardware Interfaces

None. The system runs on shared corporate compute and has no specific hardware dependency beyond what the orchestration platform provides.

### 5.3 Software Interfaces

| Interface             | Direction | Description                                                                                  |
|-----------------------|-----------|----------------------------------------------------------------------------------------------|
| MySQL                 | Outbound  | Persistence for all stateful data. Driver: `pdo_mysql`. Credentials supplied via env vars.    |
| Corporate SMTP relay  | Outbound  | Transactional email (password reset, banner-change notice). Deferred — see A-02.              |
| Audit sink            | Outbound  | Append-only audit events (NFR-SEC-023). Format: corporate JSON-Lines envelope.                |
| Corporate SSO         | Outbound  | Deferred for v2.x (Appendix C, OPN-09).                                                       |

### 5.4 Communications Interfaces

All HTTP traffic SHALL terminate TLS at the corporate reverse proxy. Origin-to-origin communication uses HTTP/1.1 over the private network only.

---

## 6. Other Requirements

### 6.1 Data Retention

- DR-01. Direct messages SHALL be retained for 24 months from creation, after which they are purged by the retention sweeper.
- DR-02. Files SHALL be retained for 36 months from last access, after which they are flagged for owner review and purged after 30 days unless retained.
- DR-03. Audit events SHALL be retained for 7 years (CORP-LEG-031 §5.1).

### 6.2 Backup & Recovery

- BR-01. Database backups SHALL be taken nightly with a 30-day retention.
- BR-02. Recovery time objective (RTO): 4 hours. Recovery point objective (RPO): 24 hours.

### 6.3 Localisation

- L-01. The user interface SHALL be delivered in `en-GB` for v2.x. Additional locales are roadmap items.

---

## Appendix A — Glossary

See §1.5.

## Appendix B — Traceability Matrix

The full traceability matrix mapping each requirement to test cases, source code modules, and risk register entries is maintained separately in **CHUB-TRC-002** and is governed by the same revision cadence as this SRS.

## Appendix C — Open Items & Deferred Requirements

| ID      | Description                                                                                                       | Owner            | Target release |
|---------|-------------------------------------------------------------------------------------------------------------------|------------------|----------------|
| OPN-04  | Multi-factor authentication (TOTP) on first-seen browser (FR-AUTH-010).                                            | AppSec           | v2.3           |
| OPN-09  | Federated sign-in via corporate SSO; remove local password store thereafter.                                       | IAM              | v3.0           |
| OPN-12  | Integration with corporate SMTP relay for transactional email (assumption A-02).                                   | Platform         | v2.2           |
| OPN-15  | HRIS-driven directory synchronisation (currently a manual administrative process).                                  | Platform         | v2.4           |
| OPN-18  | Localisation framework and locale negotiation (L-01).                                                              | Platform         | v3.0           |

---

*— END OF DOCUMENT —*
