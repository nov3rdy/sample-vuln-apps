<!--
═══════════════════════════════════════════════════════════════════════════════
  CompanyHub  —  User Access Matrix
  Document classification: INTERNAL USE ONLY
═══════════════════════════════════════════════════════════════════════════════
-->

# CompanyHub — User Access Matrix

| | |
|---|---|
| **Document ID**       | CHUB-UMX-002 |
| **Document Title**    | CompanyHub Internal Portal — User Access Matrix |
| **Version**           | 2.1 |
| **Status**            | APPROVED |
| **Classification**    | Internal Use Only |
| **Issue Date**        | 2026-04-22 |
| **Effective Date**    | 2026-05-01 |
| **Next Review**       | 2026-07-22 (Quarterly) |
| **Owner**             | Identity & Access Management — Information Security |
| **Joint Owner**       | Internal Tooling Squad — Platform Engineering |
| **Supersedes**        | CHUB-UMX-001 (v1.4, 2025-11-30) |

### Revision History

| Rev. | Date       | Author              | Summary of Change                                                                  |
|------|------------|---------------------|------------------------------------------------------------------------------------|
| 1.0  | 2025-08-04 | I. Chen (IAM)       | Initial issue — Anonymous, Operator, Administrator role definitions.               |
| 1.2  | 2025-09-19 | I. Chen (IAM)       | Added Files and Team Links matrices.                                               |
| 1.4  | 2025-11-30 | I. Chen (IAM)       | Added Messaging, Profile matrices. SoD section formalised.                         |
| 2.0  | 2026-03-12 | I. Chen (IAM)       | Major rewrite. Added Administration matrix and access-review cadence.              |
| 2.1  | 2026-04-22 | I. Chen (IAM)       | Reaffirmed SoD-04 (admin self-promotion); added Appendix C provisioning template. |

### Distribution List

- Identity & Access Management
- Information Security — AppSec
- Internal Tooling Squad
- People Operations — Workplace Systems
- Internal Audit
- Legal & Compliance — Data Governance

### Approval Record

| Role                       | Name              | Signature             | Date        |
|----------------------------|-------------------|-----------------------|-------------|
| Author                     | I. Chen           | _signed electronically_ | 2026-04-22 |
| Technical Reviewer         | M. Okafor         | _signed electronically_ | 2026-04-23 |
| Security Reviewer          | J. Bartoszewicz   | _signed electronically_ | 2026-04-24 |
| People Operations Reviewer | T. Brennan        | _signed electronically_ | 2026-04-25 |
| Sponsor / Approver         | R. Lindqvist      | _signed electronically_ | 2026-04-26 |

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Role Catalogue](#2-role-catalogue)
3. [Permission Legend](#3-permission-legend)
4. [Feature × Role Permission Matrices](#4-feature--role-permission-matrices)
5. [Sensitive Operations Register](#5-sensitive-operations-register)
6. [Access Provisioning & Deprovisioning](#6-access-provisioning--deprovisioning)
7. [Access Review Programme](#7-access-review-programme)
8. [Segregation of Duties](#8-segregation-of-duties)
9. [Appendix A — Glossary](#appendix-a--glossary)
10. [Appendix B — Role Assignment Matrix (RACI)](#appendix-b--role-assignment-matrix-raci)
11. [Appendix C — Provisioning Request Template](#appendix-c--provisioning-request-template)

---

## 1. Introduction

### 1.1 Purpose

This document is the authoritative catalogue of user roles defined within the CompanyHub portal and the operations each role is permitted to perform. It is the binding reference used by:

- Identity & Access Management when granting, modifying, or revoking access.
- Information Security and Internal Audit when reviewing entitlements.
- Engineering when implementing or modifying authorisation logic.

### 1.2 Scope

In scope:
- All role definitions exposed by the CompanyHub portal.
- Mapping between roles and the operations defined in CHUB-IFA-002.
- The processes governing the assignment, review, and removal of those roles.

Out of scope:
- Authentication mechanisms (covered by CHUB-SRS-002 §3.1, CHUB-IFA-002 §3).
- Network-level access controls (covered by CORP-NET-009).

### 1.3 Audience

| Audience                | Use of this document                                                                                  |
|-------------------------|-------------------------------------------------------------------------------------------------------|
| IAM Operations          | Authoritative reference when actioning provisioning tickets.                                          |
| AppSec / Internal Audit | Baseline for entitlement reviews, attestation, and SoD analysis.                                      |
| Engineering             | Implementation reference; any new endpoint must be classified against this matrix prior to release.   |
| People Operations       | Reference for joiner/mover/leaver workflow design.                                                    |
| Service Desk            | Reference when responding to access-related tickets.                                                  |

### 1.4 Reference Documents

| Ref.   | Title                                                                                |
|--------|--------------------------------------------------------------------------------------|
| [R-1]  | CHUB-SRS-002 — CompanyHub Software Requirements Specification, v2.1                  |
| [R-2]  | CHUB-IFA-002 — CompanyHub Interface Agreement, v2.1                                  |
| [R-3]  | CORP-IAM-022 — Identity & Access Management Standard, v4.1                           |
| [R-4]  | CORP-IAM-023 — Joiner / Mover / Leaver Workflow, v3.0                                |
| [R-5]  | CORP-AUD-007 — Entitlement Review Standard, v2.2                                     |
| [R-6]  | CORP-ISP-014 — Corporate Information Security Policy, v6.2                           |

---

## 2. Role Catalogue

CompanyHub defines four mutually-exclusive principals. Every authenticated session SHALL resolve to exactly one of `Operator` or `Administrator`. `Anonymous` and `System` are implicit.

| Role           | Identifier value | Assigned by                           | Removed by                            | Maximum holders |
|----------------|------------------|----------------------------------------|----------------------------------------|------------------|
| Anonymous      | (none)           | n/a — default state for an unbound session | First successful authentication      | n/a              |
| Operator       | `user`           | IAM, on processing an approved JML record | IAM, on offboarding ticket            | Unlimited        |
| Administrator  | `admin`          | IAM, on Administrator-Grant ticket countersigned by the Sponsor | IAM, on Administrator-Revoke ticket | 8 concurrent     |
| System         | (machine)        | Configuration management                | Configuration management              | n/a              |

The `System` principal is reserved for non-interactive maintenance tasks (seed loader, retention sweeper, telemetry agent) executed inside the application's container or its sidecars. It SHALL NOT be granted an interactive session.

### 2.1 Administrator Cap

The Administrator population SHALL NOT exceed 8 concurrent holders. New grants beyond the cap SHALL require the prior revocation of an existing holder. The Administrator population is reviewed quarterly (§7.2).

---

## 3. Permission Legend

The matrices in §4 use the following symbols. Read them column-by-row: "may the role on the left perform the action in the column".

| Symbol | Meaning                                                                                                  |
|--------|----------------------------------------------------------------------------------------------------------|
| **F**  | Full — the role may perform the operation without restriction.                                            |
| **O**  | Owner-only — the role may perform the operation only against records they own.                            |
| **P**  | Participant-only — the role may perform the operation only when they are a participant in the record (e.g. sender or recipient of a message). |
| **U**  | Public-only — the role may perform the operation only when the target record is marked publicly visible.  |
| **R**  | Read-only — the role may read but not modify.                                                             |
| **—**  | Denied — the operation MUST be refused for this role.                                                     |

Operations are abbreviated **C**reate, **R**ead, **L**ist, **U**pdate, **D**elete.

---

## 4. Feature × Role Permission Matrices

### 4.1 Authentication & Session

| Operation                                   | Anonymous | Operator | Administrator |
|---------------------------------------------|-----------|----------|---------------|
| Submit credentials (`POST /login`)          | F         | F        | F             |
| Register account (`POST /register`)         | F         | —        | —             |
| Request reset (`POST /forgot`)              | F         | F        | F             |
| Submit reset (`POST /reset`)                | F         | F        | F             |
| Sign out (`GET /logout`)                    | —         | F        | F             |

### 4.2 Employee Directory

| Operation                                   | Anonymous | Operator | Administrator |
|---------------------------------------------|-----------|----------|---------------|
| List all employees                          | —         | F        | F             |
| Search employees                            | —         | F        | F             |
| Read an employee profile                    | —         | F        | F             |

### 4.3 Notes

| Operation                                   | Anonymous | Operator                   | Administrator              |
|---------------------------------------------|-----------|----------------------------|----------------------------|
| Create note (C)                             | —         | F                          | F                          |
| Read own note (R)                           | —         | O                          | O                          |
| Read public note authored by another (R)    | —         | U                          | U                          |
| Read private note authored by another (R)   | —         | —                          | —                          |
| List notes (L)                              | —         | F (own + public)           | F (own + public)           |
| Update note (U)                             | —         | O                          | O                          |
| Delete note (D)                             | —         | O                          | O                          |

> Administrators have no implicit override on private notes. Any administrative review of private content SHALL follow CORP-LEG-014 (Lawful Access Procedure) and SHALL leave an audit trail.

### 4.4 Messages

| Operation                                   | Anonymous | Operator               | Administrator         |
|---------------------------------------------|-----------|-------------------------|------------------------|
| Send message (C)                            | —         | F                       | F                      |
| Read message (R)                            | —         | P (sender or recipient) | P (sender or recipient) |
| List inbox (L)                              | —         | F (own inbox)           | F (own inbox)          |

Administrators SHALL NOT read messages they neither sent nor received except via the lawful-access procedure cited in §4.3.

### 4.5 Files

| Operation                                   | Anonymous | Operator               | Administrator         |
|---------------------------------------------|-----------|-------------------------|------------------------|
| Upload file (C)                             | —         | F                       | F                      |
| Download file (R)                           | —         | F                       | F                      |
| List files (L)                              | —         | F                       | F                      |
| Delete file (D)                             | —         | O                       | F (with audit entry)   |

> Administrative deletion is a sensitive operation — see §5.

### 4.6 Team Links

| Operation                                   | Anonymous | Operator    | Administrator |
|---------------------------------------------|-----------|-------------|---------------|
| Add link (C)                                | —         | F           | F             |
| List / read links (L,R)                     | —         | F           | F             |
| Server-side preview (R)                     | —         | F           | F             |
| Remove link (D)                             | —         | O           | F             |

### 4.7 Profile

| Operation                                   | Anonymous | Operator | Administrator |
|---------------------------------------------|-----------|----------|---------------|
| Read own profile (R)                        | —         | F        | F             |
| Update own avatar / preferences (U)         | —         | F        | F             |
| Read another operator's profile             | —         | R via §4.2 | R via §4.2  |

### 4.8 Contact Import

| Operation                                   | Anonymous | Operator | Administrator |
|---------------------------------------------|-----------|----------|---------------|
| Open import form                            | —         | —        | F             |
| Submit import payload (`POST /import`)      | —         | —        | F             |

### 4.9 Administration

| Operation                                   | Anonymous | Operator | Administrator |
|---------------------------------------------|-----------|----------|---------------|
| Open admin overview (`GET /admin`)          | —         | —        | F             |
| List all users (`GET /admin/users`)         | —         | —        | F             |
| Change another operator's role              | —         | —        | F (subject to §8 SoD-04) |
| Edit site banner                            | —         | —        | F             |
| Read database statistics                    | —         | —        | F             |

---

## 5. Sensitive Operations Register

The following operations are classified **Sensitive** under CORP-IAM-022 §6 and SHALL leave an audit-trail entry in the corporate audit sink within 5 seconds of completion.

| ID       | Operation                                                  | Audit category | Approval requirement                  |
|----------|------------------------------------------------------------|----------------|---------------------------------------|
| SEN-01   | Successful authentication                                  | `auth.success` | None                                  |
| SEN-02   | Failed authentication                                      | `auth.failure` | None (but rate-limited per CHUB-IFA §4.6) |
| SEN-03   | Password reset issued                                      | `auth.reset`   | None                                  |
| SEN-04   | Password reset consumed                                    | `auth.reset.completed` | None                          |
| SEN-05   | Role change (`POST /admin/users/{id}/role`)                | `iam.role.change` | Two-person rule (§8 SoD-04)        |
| SEN-06   | Site banner update                                         | `content.banner.update` | None                          |
| SEN-07   | Administrative file deletion                               | `content.file.delete.admin` | Service-desk ticket required  |
| SEN-08   | Contact import                                             | `data.import` | None                                   |
| SEN-09   | Administrator override of private content (lawful access)  | `iam.lawful_access` | Legal sign-off                   |

---

## 6. Access Provisioning & Deprovisioning

### 6.1 Joiner

1. People Operations creates the JML record in the corporate HRIS.
2. The JML record triggers an automated provisioning ticket to IAM.
3. IAM verifies the email address matches the corporate domain allowlist (§CHUB-IFA-002 §5.1.3) and issues an Operator account.
4. Operator credentials are delivered to the joiner via the corporate single-channel notification system. The temporary password expires on first use.

### 6.2 Mover (department or role change)

1. People Operations updates the HRIS record.
2. IAM reviews CompanyHub entitlements against the new role profile.
3. Where a department change implies access to formerly-restricted material (e.g. moving into HR), the change SHALL be confirmed by the new line manager before any private records become visible.

### 6.3 Administrator Grant

1. Sponsor (VP, IT) raises an "Administrator Grant" ticket nominating the candidate.
2. AppSec reviews and confirms the candidate has completed the Annual Security Refresher (CORP-SEC-101).
3. IAM verifies the Administrator cap (§2.1) is not exceeded and processes the grant.
4. The grant is logged under `iam.role.change` (SEN-05).

### 6.4 Administrator Revoke

Revocation is initiated immediately on:

- Sponsor request, or
- Termination of the holder's Administrator-Grant business need, or
- Failure of an entitlement review (§7), or
- A People Operations leaver event.

### 6.5 Leaver

1. People Operations finalises the JML record on the leaver's last working day.
2. The JML event triggers an automated deprovisioning job that:
   - Invalidates all sessions and the persistent sign-in cookie for the leaver.
   - Marks the user record as `inactive` (preserving foreign-key relationships).
   - Removes the leaver from the directory listing within 1 hour.
3. Notes, files, links, and messages owned by the leaver are retained per CHUB-SRS-002 §6.1 and reassigned to the leaver's manager only on explicit request.

---

## 7. Access Review Programme

### 7.1 Operator Reviews

A campaign-style review of the Operator population SHALL be run **semi-annually** (Q1 and Q3) by IAM in conjunction with line managers. The review confirms that each Operator's continued access aligns with their current role.

### 7.2 Administrator Reviews

Administrator entitlements SHALL be reviewed **quarterly** by the Sponsor. Outcomes:

- Reaffirm — the holder retains the role.
- Revoke — the holder is moved to Operator on the next business day.
- Suspend — the holder is moved to Operator pending investigation; access cannot be restored without a fresh §6.3 workflow.

### 7.3 Out-of-cycle Reviews

An out-of-cycle review SHALL be triggered by:

- Any incident where the integrity of an Administrator account is suspect.
- Promulgation of a new corporate IAM standard.
- A material change to CompanyHub's role catalogue (i.e. a new revision of this document).

---

## 8. Segregation of Duties

| ID       | Constraint                                                                                                  | Compensating Control                                              |
|----------|-------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------|
| SoD-01   | No principal SHALL hold both Operator and Administrator roles simultaneously.                               | Enforced by the role attribute being mutually exclusive.          |
| SoD-02   | An Administrator SHALL NOT alter their own role record.                                                     | Server-side guard rejects the request; audit entry retained.      |
| SoD-03   | An Administrator SHALL NOT delete their own audit trail.                                                    | The audit sink is append-only and write-once (CORP-OBS-008).      |
| SoD-04   | A role-change request from `user` to `admin` SHALL be approved by a second Administrator before taking effect. | Implementation: dual-control flow on `POST /admin/users/{id}/role`. |
| SoD-05   | The Sponsor SHALL NOT also act as the Technical Reviewer or Security Reviewer of any change to this document. | Enforced by the change-control board membership policy.        |
| SoD-06   | Personnel performing People Operations duties SHALL NOT also hold the Administrator role.                   | Enforced at IAM provisioning time.                                |

---

## Appendix A — Glossary

| Term      | Definition                                                                                  |
|-----------|---------------------------------------------------------------------------------------------|
| IAM       | Identity & Access Management (corporate function and the team operating it)                 |
| JML       | Joiner / Mover / Leaver workflow                                                            |
| RACI      | Responsible, Accountable, Consulted, Informed                                               |
| SoD       | Segregation of Duties                                                                        |
| Sponsor   | Business owner of CompanyHub — VP, Information Technology                                    |

## Appendix B — Role Assignment Matrix (RACI)

For the lifecycle activities of CompanyHub access management:

| Activity                              | IAM | AppSec | Sponsor | People Ops | Engineering | Service Desk |
|---------------------------------------|-----|--------|---------|------------|-------------|--------------|
| Operator provisioning (joiner)        | R/A | I      | I       | C          | I           | I            |
| Operator deprovisioning (leaver)      | R/A | I      | I       | C          | I           | I            |
| Administrator grant                   | R   | C      | A       | I          | I           | I            |
| Administrator revoke                  | R/A | C      | C       | I          | I           | I            |
| Quarterly admin review                | R   | C      | A       | I          | I           | I            |
| Semi-annual operator review           | R/A | I      | I       | C          | I           | I            |
| Out-of-cycle review                   | R   | A      | C       | I          | I           | I            |
| Lawful-access (SEN-09)                | R   | C      | I       | I          | I           | I            |
| Maintain this matrix                  | R/A | C      | A       | C          | C           | I            |

R — Responsible · A — Accountable · C — Consulted · I — Informed.

## Appendix C — Provisioning Request Template

The following fields SHALL be present on every CompanyHub access provisioning ticket. The ticket SHALL be raised through the corporate IAM workflow and not by direct request to the team.

```
Ticket type:               [ ] Joiner   [ ] Mover   [ ] Leaver
                           [ ] Administrator Grant  [ ] Administrator Revoke

Subject identifier:        <employee id>
Subject name:              <full legal name>
Subject email:             <work email>
Subject department:        <as recorded in HRIS>
Subject manager:           <manager employee id>

Effective date:            YYYY-MM-DD
Expiration (if temporary): YYYY-MM-DD or "n/a"

Requested role:            [ ] Operator    [ ] Administrator
Justification:             <free text — required for Administrator>

Sponsor approval:          <signature, required for Administrator>
AppSec acknowledgement:    <signature, required for Administrator>

Cross-reference:           <HRIS JML record id, if applicable>
```

---

*— END OF DOCUMENT —*
