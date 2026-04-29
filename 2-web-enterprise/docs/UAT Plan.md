<!--
═══════════════════════════════════════════════════════════════════════════════
  CompanyHub  —  User Acceptance Testing Plan
  Document classification: INTERNAL USE ONLY
═══════════════════════════════════════════════════════════════════════════════
-->

# CompanyHub — User Acceptance Testing Plan

| | |
|---|---|
| **Document ID**       | CHUB-UAT-002 |
| **Document Title**    | CompanyHub Internal Portal — User Acceptance Testing Plan |
| **Version**           | 2.1 |
| **Status**            | APPROVED |
| **Classification**    | Internal Use Only |
| **Issue Date**        | 2026-04-22 |
| **Effective Date**    | 2026-05-01 |
| **UAT Window**        | 2026-05-04 → 2026-05-22 (15 business days) |
| **Owner**             | QA — Test Engineering |
| **Sponsor**           | VP, Information Technology |
| **Supersedes**        | CHUB-UAT-001 (v1.4, 2025-11-30) |

### Revision History

| Rev. | Date       | Author              | Summary of Change                                                                  |
|------|------------|---------------------|------------------------------------------------------------------------------------|
| 1.0  | 2025-08-04 | S. Romero (QA)      | Initial issue — auth, directory, notes scenarios.                                   |
| 1.2  | 2025-09-19 | S. Romero (QA)      | Added Files and Team Links scenarios.                                              |
| 1.4  | 2025-11-30 | S. Romero (QA)      | Added Messaging, Profile scenarios. Defect-management process formalised.          |
| 2.0  | 2026-03-12 | S. Romero (QA)      | Major rewrite. Added Administration and Contact Import scenarios; new sign-off form. |
| 2.1  | 2026-04-22 | S. Romero (QA)      | Updated entry/exit criteria; added accessibility checks; cross-referenced UMX-002. |

### Distribution List

- QA — Test Engineering
- UAT participants (per §3.3)
- Internal Tooling Squad — Platform Engineering
- Information Security — AppSec
- People Operations — Workplace Systems
- Sponsor and direct reports

### Approval Record

| Role                       | Name              | Signature             | Date        |
|----------------------------|-------------------|-----------------------|-------------|
| Author                     | S. Romero         | _signed electronically_ | 2026-04-22 |
| Engineering Reviewer       | M. Okafor         | _signed electronically_ | 2026-04-23 |
| Security Reviewer          | J. Bartoszewicz   | _signed electronically_ | 2026-04-24 |
| Business Reviewer          | D. Ferreira       | _signed electronically_ | 2026-04-25 |
| UAT Lead                   | S. Romero         | _signed electronically_ | 2026-04-22 |
| Sponsor / Approver         | R. Lindqvist      | _signed electronically_ | 2026-04-26 |

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Approach](#2-approach)
3. [Roles, Responsibilities & Schedule](#3-roles-responsibilities--schedule)
4. [Test Environment](#4-test-environment)
5. [Test Data](#5-test-data)
6. [Entry & Exit Criteria](#6-entry--exit-criteria)
7. [Test Scenarios](#7-test-scenarios)
8. [Defect Management](#8-defect-management)
9. [Sign-Off](#9-sign-off)
10. [Risks & Assumptions](#10-risks--assumptions)
11. [Appendix A — Glossary](#appendix-a--glossary)
12. [Appendix B — Daily Status Report Template](#appendix-b--daily-status-report-template)
13. [Appendix C — Defect Log Template](#appendix-c--defect-log-template)
14. [Appendix D — Sign-Off Form](#appendix-d--sign-off-form)

---

## 1. Introduction

### 1.1 Purpose

This document describes the User Acceptance Testing (UAT) approach, schedule, scenarios, and acceptance criteria for the v2.x release of CompanyHub. It is the binding plan against which the Sponsor's acceptance is given.

### 1.2 Scope

In scope:
- All functional requirements catalogued in CHUB-SRS-002 §3.
- Authorisation behaviour as catalogued in CHUB-UMX-002 §4.
- Surface behaviour as contracted in CHUB-IFA-002 §5.

Out of scope:
- Performance, load, and stress testing — covered by CHUB-PRF-002 (Performance Test Plan).
- Penetration testing — performed independently by AppSec under engagement CHUB-PEN-2026-Q2.
- Disaster-recovery rehearsal — covered by CHUB-DRP-002.

### 1.3 Audience

| Audience              | Use of this document                                                       |
|-----------------------|----------------------------------------------------------------------------|
| UAT participants      | Source of test scenarios and acceptance criteria.                           |
| QA                    | Coordination, defect triage, daily status, sign-off custodian.              |
| Engineering           | Defect remediation; environment support.                                    |
| Sponsor               | Final acceptance authority.                                                 |
| Internal Audit        | Evidence of UAT completion and sign-off.                                    |

### 1.4 Reference Documents

| Ref.   | Title                                                                               |
|--------|-------------------------------------------------------------------------------------|
| [R-1]  | CHUB-SRS-002 — CompanyHub Software Requirements Specification, v2.1                 |
| [R-2]  | CHUB-IFA-002 — CompanyHub Interface Agreement, v2.1                                 |
| [R-3]  | CHUB-UMX-002 — CompanyHub User Access Matrix, v2.1                                  |
| [R-4]  | CORP-QA-006 — Acceptance Testing Standard, v3.1                                     |
| [R-5]  | CORP-DEF-004 — Defect Management Standard, v2.4                                     |
| [R-6]  | WCAG 2.2 — Web Content Accessibility Guidelines                                     |

---

## 2. Approach

### 2.1 Testing Method

Scenario-based functional acceptance testing is performed by representative end-users from the candidate user community, following the scripted scenarios in §7. Each scenario records:

- A unique scenario identifier (`UAT-<area>-<###>`).
- The role under test.
- Pre-conditions.
- Numbered steps.
- The expected result.
- A pass/fail outcome and tester name on completion.

Where applicable, scenarios are mapped to one or more functional requirements in the SRS (the **trace** column) so that requirement coverage can be reported.

### 2.2 Test Levels

| Level                | In scope here? | Owner                |
|----------------------|----------------|----------------------|
| Unit                 | No             | Engineering          |
| Integration          | No             | Engineering          |
| System               | No             | QA — Test Engineering |
| **User Acceptance**  | **Yes**        | **QA + UAT Participants** |
| Performance          | No             | Performance Engineering |
| Security             | No             | Information Security |

### 2.3 Out of Scope

The following classes of testing are explicitly out of scope and are tracked under their own plans:

- Browser-compatibility regression on legacy IE/Edge — these browsers are no longer in the corporate support matrix.
- Native-mobile coverage — CompanyHub v2.x does not ship a native mobile app.
- Locale negotiation — `en-GB` only in v2.x (CHUB-SRS-002 L-01).

---

## 3. Roles, Responsibilities & Schedule

### 3.1 UAT Lead

The UAT Lead (S. Romero, QA) is responsible for:
- Coordinating the schedule and daily stand-ups.
- Maintaining the defect log and triaging incoming defects.
- Producing the daily status report (Appendix B) and the final sign-off recommendation (Appendix D).

### 3.2 Engineering Liaison

A nominated engineer (rotating, see daily report) is on-call throughout the UAT window for:
- Environment fixes within 1 business hour of report.
- Defect triage attendance at 16:00 daily.
- Hot-fix releases within the agreed defect-class SLAs (§8.3).

### 3.3 UAT Participants

The following roles SHALL be represented by at least one named tester for the duration of the UAT window. Names are recorded on the daily attendance sheet.

| Role under test     | Recommended participants                                            | Minimum |
|---------------------|---------------------------------------------------------------------|---------|
| Anonymous           | Service Desk lead                                                   | 1       |
| Operator            | Two from each of: Engineering, Sales, HR, IT, Operations             | 8       |
| Administrator       | Two designated CompanyHub administrators                             | 2       |

### 3.4 Schedule

| Phase                          | Dates                       | Working days | Activities                                                                  |
|--------------------------------|-----------------------------|--------------|-----------------------------------------------------------------------------|
| Mobilisation                   | 2026-05-04 → 2026-05-05     | 2            | Environment walk-through, role assignment, scenario familiarisation.         |
| Cycle 1 — Functional pass      | 2026-05-06 → 2026-05-13     | 6            | Execute all scenarios in §7. Defects logged to the corporate defect tool.   |
| Cycle 2 — Regression of fixes  | 2026-05-14 → 2026-05-19     | 4            | Re-test of fixed defects + impacted scenarios.                               |
| Cycle 3 — Final sweep          | 2026-05-20 → 2026-05-21     | 2            | Final acceptance sweep against agreed scope.                                 |
| Sign-off                       | 2026-05-22                  | 1            | Sponsor sign-off (Appendix D) or formal rejection.                          |

---

## 4. Test Environment

### 4.1 Environment

UAT is performed on the dedicated **Staging** environment:

| Property              | Value                                            |
|-----------------------|--------------------------------------------------|
| Base URL              | `https://companyhub.stg.internal`                |
| Build pinned          | Build identifier issued at start of mobilisation |
| Database fixture      | UAT seed (see §5)                                |
| Email delivery        | Captured by the staging mail-sink (no external delivery) |
| Reset window          | Database reset on demand by Engineering Liaison   |

### 4.2 Tester Workstation

- Latest two major versions of Chromium, Firefox, or Safari.
- Corporate VPN active.
- Standard corporate AV / EDR enabled.
- Screen capture tooling available for defect evidence (see §8).

---

## 5. Test Data

A controlled fixture is loaded into Staging at the start of mobilisation. The fixture comprises:

| Data set            | Volume                                                  | Notes                                              |
|---------------------|----------------------------------------------------------|----------------------------------------------------|
| Users               | 5 fixed accounts (admin + 4 operators) + 25 generated   | Generated accounts use the `uat-*@companyhub.local` email prefix. |
| Notes               | 30 (mix of public / private)                             | At least 3 per fixed operator.                      |
| Messages            | 40 distinct threads                                      | Both inbound and outbound for each fixed operator.  |
| Files               | 12 files (mixed allowed MIME types)                      | Total volume < 30 MiB.                              |
| Team links          | 15                                                       | Includes both reachable and unreachable URLs.       |
| Password resets     | None pre-existing                                        | Generated as scenarios require.                     |

The fixed accounts are:

| Account email                  | Role          | Display name        |
|--------------------------------|---------------|---------------------|
| `admin@companyhub.local`       | Administrator | Avery Admin          |
| `alice@companyhub.local`       | Operator      | Alice Anderson       |
| `bob@companyhub.local`         | Operator      | Bob Brown            |
| `carol@companyhub.local`       | Operator      | Carol Carter         |
| `dave@companyhub.local`        | Operator      | Dave Davis           |

> Personally Identifiable Information SHALL NOT be substituted with real employee data during UAT. Any divergence requires a CHUB-DEF defect with classification `Compliance`.

---

## 6. Entry & Exit Criteria

### 6.1 Entry Criteria

UAT cycle 1 SHALL NOT begin until **all** the following are demonstrably true:

- E-01. Build identifier nominated and frozen for the duration of the cycle (changes require an entry waiver from the Sponsor).
- E-02. CHUB-SRS-002 §3 implementation declared "code complete" by Engineering.
- E-03. System Integration Testing (SIT) sign-off obtained from QA.
- E-04. Staging environment available, seeded per §5, and accessible to UAT participants.
- E-05. No open Severity-1 defects from prior cycles.
- E-06. UAT participants have completed scenario familiarisation (§3 mobilisation).

### 6.2 Exit Criteria

The UAT window SHALL be considered successfully concluded only when **all** the following are true:

- X-01. 100% of scenarios marked `MUST` in §7 have been executed.
- X-02. ≥ 95% of `MUST` scenarios pass.
- X-03. No open Severity-1 defects.
- X-04. No more than 2 open Severity-2 defects, and each is covered by an agreed deferral with a remediation plan.
- X-05. The Sponsor has signed Appendix D.

---

## 7. Test Scenarios

The scenarios that follow are normative. Where a scenario fails, a defect SHALL be raised per §8 before the cycle continues.

Priority key: **M**ust-have for sign-off, **S**hould-have, **C**ould-have.

### 7.1 Authentication

#### UAT-AUTH-001 — Operator signs in with valid credentials

- **Role**: Anonymous → Operator
- **Priority**: M
- **Trace**: FR-AUTH-001, FR-AUTH-002
- **Pre-conditions**: Account `alice@companyhub.local` exists with the documented password.
- **Steps**:
  1. Navigate to `/login`.
  2. Enter `alice@companyhub.local` and the documented password.
  3. Click **Sign in**.
- **Expected**: Tester is redirected to `/dashboard`. The sidebar shows "Alice Anderson" with role `user`. The Administration section is NOT visible.

#### UAT-AUTH-002 — Operator sees a generic error for invalid credentials

- **Role**: Anonymous
- **Priority**: M
- **Trace**: FR-AUTH-003
- **Steps**:
  1. Navigate to `/login`.
  2. Submit `alice@companyhub.local` with an incorrect password.
  3. Submit a non-existent email with any password.
- **Expected**: Both attempts produce an identical message ("Invalid email or password") and remain on `/login`. No information is revealed about whether the account exists.

#### UAT-AUTH-003 — Sign out invalidates the session

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-AUTH-004
- **Steps**:
  1. While signed in, click **Sign out**.
  2. Use the browser back button to return to a previously-loaded `/dashboard` page.
  3. Refresh.
- **Expected**: After step 3, the tester is redirected to `/login`. No protected content is rendered.

#### UAT-AUTH-004 — Password reset issues a single-use, time-limited token

- **Role**: Anonymous
- **Priority**: M
- **Trace**: FR-AUTH-007, FR-AUTH-008
- **Steps**:
  1. Submit `alice@companyhub.local` to `/forgot`.
  2. Capture the issued token (Engineering Liaison provides access to the staging mail-sink).
  3. Use the token at `/reset` to set a new password.
  4. Attempt to use the same token a second time.
- **Expected**: Step 3 succeeds; step 4 is rejected with the generic "invalid or expired token" message.

#### UAT-AUTH-005 — Open redirect protection on `next`

- **Role**: Anonymous
- **Priority**: M
- **Trace**: FR-AUTH-009, NFR-SEC-017
- **Steps**:
  1. Navigate to `/login?next=https://example.org`.
  2. Sign in with valid credentials.
- **Expected**: After authentication the tester is delivered to `/dashboard`, NOT to `https://example.org`. No off-origin redirect occurs.

### 7.2 Employee Directory

#### UAT-DIR-001 — Operator browses the directory

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-DIR-001
- **Steps**:
  1. Navigate to `/directory`.
- **Expected**: All seeded employees appear in the table with display name, email, department, and role.

#### UAT-DIR-002 — Search query is rendered safely

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-DIR-003
- **Steps**:
  1. Submit the search query `<script>alert(1)</script>` from `/directory/search`.
- **Expected**: The query is shown as literal text in the heading; no script executes.

#### UAT-DIR-003 — Profile page renders

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-DIR-004
- **Steps**:
  1. From `/directory`, follow the "View" link for `bob@companyhub.local`.
- **Expected**: The profile page shows Bob's name, email, department, and role.

### 7.3 Notes

#### UAT-NOTE-001 — Operator creates and reads their own note

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-NOTE-001
- **Steps**:
  1. Navigate to `/notes/new`, enter a title and body, leave "Visible to everyone" unchecked, save.
  2. Open the created note from `/notes`.
- **Expected**: The note is listed under "Your notes" and is readable. Body content is rendered safely (any HTML tags appear as literal text).

#### UAT-NOTE-002 — Public note is visible to other operators

- **Role**: Operator A → Operator B
- **Priority**: M
- **Trace**: FR-NOTE-002
- **Steps**:
  1. Operator A creates a note with the public flag set.
  2. Operator B navigates to `/notes` and locates the note under "From the team".
- **Expected**: Operator B can read the public note. Operator B cannot edit or delete it.

#### UAT-NOTE-003 — Private note is invisible to other operators

- **Role**: Operator A → Operator B
- **Priority**: M
- **Trace**: FR-NOTE-003
- **Steps**:
  1. Operator A creates a private note and notes its identifier.
  2. Operator B attempts to open `/notes/<id>` directly by URL.
- **Expected**: Operator B receives a 404 (Not Found). The private note's content is not exposed.

#### UAT-NOTE-004 — Stored content is rendered safely

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-NOTE-004
- **Steps**:
  1. Create a note whose body is `<img src=x onerror="alert(1)">`.
  2. Open the note.
- **Expected**: The HTML is shown as literal text. No script executes and no image-load is attempted.

#### UAT-NOTE-005 — State-changing operation requires CSRF token

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-NOTE-005, NFR-SEC-014
- **Steps**:
  1. Inspect the new-note form and confirm a hidden `_csrf` field is present.
  2. With Engineering Liaison, replay the create request with the `_csrf` field stripped.
- **Expected**: The replay attempt is rejected with HTTP 403 and the metadata `x-error-code` of `CSRF_INVALID`.

### 7.4 Messages

#### UAT-MSG-001 — Operator sends and receives a message

- **Role**: Operator A → Operator B
- **Priority**: M
- **Trace**: FR-MSG-001, FR-MSG-002
- **Steps**:
  1. Operator A composes a message to Operator B from `/messages/new`.
  2. Operator B navigates to `/messages`.
- **Expected**: Operator B sees the new message in their inbox; Operator A's name is shown as the sender.

#### UAT-MSG-002 — Non-participant cannot read a message

- **Role**: Operator A → Operator C
- **Priority**: M
- **Trace**: FR-MSG-003
- **Steps**:
  1. Operator A → Operator B exchange a message; identifier captured.
  2. Operator C attempts to open `/messages/<id>` directly.
- **Expected**: Operator C receives a 404. Message content is not exposed.

### 7.5 Files

#### UAT-FILE-001 — Upload and download cycle

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-FILE-001, FR-FILE-004
- **Steps**:
  1. Upload a 1 MiB PNG via `/files`.
  2. Locate the file in the listing and download it.
- **Expected**: Upload succeeds (303 redirect to `/files`); the downloaded bytes are identical to the uploaded bytes.

#### UAT-FILE-002 — Upload size cap

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-FILE-001 (16 MiB cap)
- **Steps**:
  1. Attempt to upload a file of 17 MiB.
- **Expected**: The upload is rejected with a clear "file too large" message; the user remains on `/files`.

#### UAT-FILE-003 — MIME allowlist enforced

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-FILE-002, NFR-SEC-016
- **Steps**:
  1. Attempt to upload an executable disguised as `report.png` (extension renamed; payload is an `.exe` or `.php`).
- **Expected**: The upload is rejected with `UPLOAD_TYPE_REJECTED`. No file is created in storage and no row is inserted in `files`.

### 7.6 Team Links

#### UAT-LINK-001 — Add a link

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-LINK-001
- **Steps**:
  1. Submit `https://example.com` and the title `Example` from `/links`.
- **Expected**: The link is added and visible in the listing.

#### UAT-LINK-002 — Preview an external URL

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-LINK-002
- **Steps**:
  1. Click the **Preview** affordance for `https://example.com`.
- **Expected**: The preview page renders the title and a plain-text excerpt. No active content executes.

#### UAT-LINK-003 — Preview rejects internal targets

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-LINK-003, NFR-SEC-015
- **Steps**:
  1. Submit `/links/preview?url=http://169.254.169.254/`.
  2. Submit `/links/preview?url=http://localhost/`.
- **Expected**: Both attempts are rejected with `PREVIEW_TARGET_FORBIDDEN`. No outbound request is made to the named hosts.

### 7.7 Profile

#### UAT-PROF-001 — Avatar upload and display

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-PROF-002
- **Steps**:
  1. From `/profile`, upload a 200 KiB JPEG avatar.
  2. Refresh the page and observe the avatar in the sidebar.
- **Expected**: The avatar is visible on the profile page and in the sidebar within the same session.

#### UAT-PROF-002 — Personalisation persists between sessions

- **Role**: Operator
- **Priority**: S
- **Trace**: FR-PROF-003
- **Steps**:
  1. From `/profile`, set theme to `dark` and enable compact mode. Save.
  2. Sign out, sign back in.
- **Expected**: The theme and compact-mode preference are restored from server-side storage.

### 7.8 Contact Import

#### UAT-IMP-001 — Administrator imports a valid XML payload

- **Role**: Administrator
- **Priority**: M
- **Trace**: FR-IMP-001
- **Steps**:
  1. From `/import`, upload an XML file containing 5 well-formed `<contact>` entries.
- **Expected**: All 5 contacts are created and visible in `/directory`. No external entities are resolved during parsing.

#### UAT-IMP-002 — Operator cannot reach the import surface

- **Role**: Operator
- **Priority**: M
- **Trace**: UMX §4.8
- **Steps**:
  1. While signed in as an Operator, navigate to `/import`.
- **Expected**: A 403 response is returned. The Import affordance is not present in the Operator's sidebar.

### 7.9 Administration

#### UAT-ADM-001 — Administrator changes another user's role

- **Role**: Administrator
- **Priority**: M
- **Trace**: FR-ADM-003, SoD-04
- **Steps**:
  1. From `/admin/users`, promote `bob@companyhub.local` to `admin`.
  2. Sign in as Bob (in a separate browser).
  3. Demote Bob back to `user` from a different Administrator's session per the dual-control flow.
- **Expected**: After step 1, Bob's role is `admin` server-side; after step 3, Bob's role is `user`. Both events appear in the audit log under `iam.role.change`.

#### UAT-ADM-002 — Operator cannot reach administration

- **Role**: Operator
- **Priority**: M
- **Trace**: FR-ADM-007, NFR-SEC-006
- **Steps**:
  1. While signed in as an Operator, attempt direct navigation to `/admin`, `/admin/users`, `/admin/banner`, `/admin/stats`.
  2. With Engineering Liaison, attempt the same with a forged `role` cookie set to `admin`.
- **Expected**: Both attempts are refused with HTTP 403. The role attribute is read from the server-side session record, not from any cookie.

#### UAT-ADM-003 — Site banner updates and renders

- **Role**: Administrator
- **Priority**: M
- **Trace**: FR-ADM-005
- **Steps**:
  1. Open `/admin/banner` and set a new banner using only allowed tags.
  2. Save and reload any other page.
- **Expected**: The new banner appears site-wide; disallowed HTML is stripped or escaped per the safe-HTML policy.

### 7.10 Cross-cutting

#### UAT-CC-001 — All pages render with security response headers

- **Role**: Operator
- **Priority**: M
- **Trace**: NFR-SEC-004
- **Steps**:
  1. Use browser developer tools to inspect response headers on `/dashboard`, `/notes`, `/admin`.
- **Expected**: Each response includes `Content-Security-Policy`, `Strict-Transport-Security`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, and `Referrer-Policy`.

#### UAT-CC-002 — No diagnostic endpoint reachable

- **Role**: Anonymous
- **Priority**: M
- **Trace**: NFR-SEC-010
- **Steps**:
  1. Attempt to navigate to `/debug.php`, `/phpinfo.php`, `/uploads/`, `/.env`, `/composer.json`.
- **Expected**: All return 404. None expose the contents of the server.

#### UAT-CC-003 — Accessibility spot check

- **Role**: Operator
- **Priority**: S
- **Trace**: NFR-COMP-002, WCAG 2.2 AA
- **Steps**:
  1. Run an automated accessibility scan (axe-core or equivalent) on `/dashboard`, `/notes`, `/messages`, `/profile`.
- **Expected**: No critical violations. Any minor violations are recorded against `CHUB-DEF` with `Accessibility` classification.

---

## 8. Defect Management

### 8.1 Logging

Defects SHALL be logged in the corporate defect-management tool (component `companyhub-portal`) by the tester within the same business day as discovery. Each defect entry includes:

- Title and concise description.
- Steps to reproduce.
- Actual vs expected behaviour.
- Screenshot or screen capture.
- Severity proposal (see §8.2).
- Tester identity and timestamp.

### 8.2 Severity Classification

| Severity | Definition                                                                                                              |
|----------|--------------------------------------------------------------------------------------------------------------------------|
| 1 — Critical | A `MUST` scenario cannot be completed; data loss or unauthorised access is observed; the environment is not usable.   |
| 2 — High     | A `MUST` scenario is partially blocked or workarounds are required; security configuration deviation is observed.      |
| 3 — Medium   | A `SHOULD` scenario fails or a `MUST` scenario fails on a non-primary path.                                            |
| 4 — Low      | Cosmetic, copy, or accessibility-minor issues; no functional impact.                                                   |

### 8.3 Triage SLA

The triage meeting is held daily at 16:00 with QA, the Engineering Liaison, and a representative tester. Hot-fix targets:

| Severity | Acknowledge | Fix on staging       |
|----------|-------------|----------------------|
| 1        | 1 business hour    | Same business day            |
| 2        | 2 business hours   | Within 2 business days       |
| 3        | Next business day  | Best-effort within the cycle |
| 4        | Next business day  | Deferred to backlog          |

### 8.4 Re-test

A defect is closed only after the original tester re-tests the fix on the nominated staging build and updates the defect record with **PASS** or **FAIL**. Two FAIL outcomes against the same defect escalate to the UAT Lead.

---

## 9. Sign-Off

The Sponsor SHALL sign Appendix D on the final day of the cycle to indicate one of:

- **ACCEPT** — The release is accepted into production scheduling.
- **ACCEPT WITH CONDITIONS** — Release accepted subject to documented deferred defects with a remediation plan.
- **REJECT** — Release not accepted; engineering returns to development with a defect remediation backlog.

Sign-off SHALL NOT be granted while any exit criterion (§6.2) is unmet.

---

## 10. Risks & Assumptions

| ID  | Risk / Assumption                                                                                       | Owner      | Mitigation                                                                |
|-----|---------------------------------------------------------------------------------------------------------|------------|---------------------------------------------------------------------------|
| RA-01 | Tester availability varies week-to-week.                                                              | UAT Lead   | Maintain backup tester roster from each represented function.             |
| RA-02 | Staging environment may be impacted by parallel SIT cycles for unrelated systems.                      | SRE        | Reserved staging window for the duration of the UAT cycle.                |
| RA-03 | Email-relay integration is deferred (CHUB-SRS-002 A-02); reset tokens read from the staging mail-sink. | QA         | Documented in scenario UAT-AUTH-004; UAT participants are briefed.        |
| RA-04 | Browser-version drift over the cycle.                                                                  | QA         | Browser version captured per defect in the daily report.                  |
| RA-05 | Sponsor unavailable for sign-off on 2026-05-22.                                                        | UAT Lead   | Delegated approver named in the kickoff email.                            |

---

## Appendix A — Glossary

| Term      | Definition                                                                                  |
|-----------|---------------------------------------------------------------------------------------------|
| SIT       | System Integration Testing                                                                  |
| UAT       | User Acceptance Testing                                                                     |
| Sponsor   | Business owner of CompanyHub — VP, Information Technology                                    |
| WCAG      | Web Content Accessibility Guidelines                                                         |

## Appendix B — Daily Status Report Template

```
Date:                  YYYY-MM-DD
Cycle:                 [ ] 1 — Functional   [ ] 2 — Regression   [ ] 3 — Final sweep
Build identifier:      <build id>
Engineering Liaison:   <name>

Scenarios planned:     <count>
Scenarios executed:    <count>
   …pass:              <count>
   …fail:              <count>
   …blocked:           <count>

Defects opened today:
  Sev-1:               <count>
  Sev-2:               <count>
  Sev-3:               <count>
  Sev-4:               <count>

Open defects:          <count>  (details in defect log)
Risks raised:          <free text>
Notes:                 <free text>

Reported by:           <name>
```

## Appendix C — Defect Log Template

```
Defect ID:             CHUB-DEF-YYYY-####
Title:                 <short title>
Reported by:           <name, role>
Reported on:           YYYY-MM-DD HH:MM
Build:                 <build id>
Browser / OS:          <browser version / OS version>

Severity:              [ ] 1   [ ] 2   [ ] 3   [ ] 4
Classification:        [ ] Functional [ ] Security [ ] Accessibility [ ] Compliance
Trace (SRS / IFA):     <list of requirement IDs>

Steps to reproduce:
  1. …
  2. …

Expected:
Actual:

Evidence:              <screenshot / video link>

Triage outcome:        [ ] Accept (target sev) [ ] Reject [ ] Duplicate of <id>
Owner:                 <engineer name>
Target build:          <build id>
Re-test outcome:       [ ] PASS  [ ] FAIL  retested by <name> on YYYY-MM-DD
```

## Appendix D — Sign-Off Form

> The Sponsor's signature on this form, with all of the boxes below ticked, constitutes formal acceptance of the v2.x release into the production scheduling queue.

```
Release identifier:          v2.1
Build identifier:            <build id>
UAT cycle window:            2026-05-04 → 2026-05-22

Exit criteria check:
[ ] X-01  100% of MUST scenarios executed
[ ] X-02  ≥ 95% of MUST scenarios pass
[ ] X-03  No open Severity-1 defects
[ ] X-04  No more than 2 open Severity-2 defects, each covered by a deferral
[ ] X-05  Sponsor signature obtained (this document)

Decision:
[ ] ACCEPT
[ ] ACCEPT WITH CONDITIONS  (deferred defects: ____________________)
[ ] REJECT                  (rationale: __________________________)


_________________________________            _________________________
Sponsor — VP, Information Technology          Date

_________________________________            _________________________
UAT Lead — QA, Test Engineering               Date

_________________________________            _________________________
Counter-signature — AppSec                    Date
```

---

*— END OF DOCUMENT —*
