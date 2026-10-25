# Threat model and release gates, R0 and R1 (issue #41)

Scope: `timetables.divengine.com`, a free SaaS where every account is approved by the maintainer. This document covers the two releases defined in #40 and #62. It is deliberately limited to what each release **exposes**.

## 1. Trust boundaries

| Boundary | R0 | R1 |
| --- | --- | --- |
| Internet → Caddy (TLS) | portal only | portal + application |
| Caddy → PHP-FPM | portal pool (`/public` front controller only) | + application pool |
| PHP → PostgreSQL | `timetables_control` with the runtime role `tt_control_app` | + one database per organization, each with its own runtime role (ADR-001) |
| PHP → email provider (HTTPS API) | verification and decision emails | + invitations, password reset |
| Operator → system | SSH + operator CLI (no web admin) | same; MFA for any web admin surface added later |
| Scheduling job → database | — | worker with time/CPU/memory limits, one tenant per job |

## 2. R0 assets and threats

Assets:
- applicant records: name, email, organization, country, role, intended use, optional website;
- decisions and audit trail;
- the HMAC key and the email provider token;
- the host's other services.

| Threat | Control in R0 |
| --- | --- |
| Bot flooding / spam requests | Honeypot field; a signed form timestamp (minimum 3 s, maximum 2 h); rate limits per IP (stored as an HMAC, never the raw IP) and per email; a global daily cap; payload length limits |
| Email bombing (requests in a victim's name) | At most one verification email per address per hour, and three per day; generic wording; no applicant-controlled text in the email beyond the name |
| Account / request enumeration | Every syntactically valid submission gets the same response and status code (new, duplicate, rate-limited by email, or already approved). There is no public status page. |
| Automatic activation | None. Approval is a separate human decision through the operator CLI. In R0 it creates **no** account. Verification only moves a request to `pending_review`. |
| Token theft / replay | Verification tokens: 256-bit random, stored as a SHA-256 hash, valid 24 h, single use. Verification takes a POST from the landing page, so link prefetchers cannot consume tokens. |
| CSRF | A signed double-submit token bound to a `__Host-` cookie on every POST; `SameSite=Lax`; `Origin` check |
| XSS | Every output goes through context-aware escaping (`htmlspecialchars` with `ENT_QUOTES | ENT_SUBSTITUTE`); strict CSP with no inline script; the operator CLI strips control characters (terminal-escape injection) |
| SQL injection | PDO with native prepared statements (no emulation) and bound values only |
| Information leakage | No stack traces (`display_errors` off); PSR-3 logs mask emails and never contain tokens or secrets; security headers; `noindex` until launch |
| Exposure of the legacy app | The portal is a separate code base and deployment. No legacy route, database or file is reachable through it. Tests assert that legacy paths return 404. |
| Data retention | Retention rules (privacy notice): unverified requests deleted after 7 days; rejected requests after 90 days; approved requests kept while the account exists; a purge command runs on a timer |
| Resource exhaustion on the shared host | FPM pool limits (2 children, 64M, 10 s); body size limit; the rate limits above |
| Operator mistakes / abuse | Every transition is audited (operator, OS user, time, from/to, reason); an allowed-transition map is enforced; terminal states cannot be reopened |

## 3. R0 gate (evidence required before the R0 GO, #62)

- [ ] Portal tests pass: unit, integration against PostgreSQL, and HTTP E2E with negative cases (CSRF, honeypot, timing, rate limit, enumeration, XSS/SQLi payloads, token reuse/expiry, no activation, legacy paths absent, headers).
- [ ] Tested on PHP 8.5 and PostgreSQL 18, the versions of the target host.
- [ ] `composer audit` clean; the lockfile is committed.
- [ ] Privacy notice and terms published on the portal.
- [ ] Backup and one restore of `timetables_control` tested.
- [ ] Deployment and rollback rehearsed in private staging; the host's other sites unaffected.
- [ ] Email provider account configured by the maintainer, its token stored outside the repository.
- [ ] Maintainer R0 GO recorded (commit, time, rollback owner).

## 4. R1 additions (summary; detailed in #44–#49 and #54–#60)

| Area | Gate |
| --- | --- |
| SQL injection | No interpolated SQL in any **exposed** generated or handwritten path (#44); adversarial tests |
| Authentication | Invitation after approval, the user sets a password through a single-use link; password reset; session rotation; no default accounts or MD5 |
| Authorization | Per-operation checks; tenant context comes **only** from server-side membership; `creador` is not a security boundary |
| Isolation | One database and one runtime role per organization (ADR-001); a two-tenant negative E2E suite (IDOR/BOLA through IDs, routes, exports, jobs) |
| Web | CSRF on every state change; output escaping in the new UI; CSP; no diagnostic or superuser routes |
| Scheduling | Background jobs with time/CPU/memory limits, cancellation, one concurrent job per tenant, Free-plan quotas |
| Operations | Per-tenant backup and restore tested, monitoring/alerts, rollback; capacity proof on the R1 host (see `host-assessment.md`) |
| Sign-off | No unresolved exploitable critical/high finding on exposed paths; separate R1 GO |

## 5. Out of scope for R0/R1

- Unreachable legacy features: the jQuery desktop and the old event dispatcher are not exposed in R1; they are replaced.
- Payments, plans other than Free, self-service provisioning, a web operator console, analytics.
