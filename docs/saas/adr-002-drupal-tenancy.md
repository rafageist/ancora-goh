# ADR-002: Tenancy on Drupal 11, multisite with a database per organization (issue #72)

Status: **proposed**, for maintainer approval; date 2026-10-25. Supersedes the application part of ADR-001 after the Drupal decision (#66). ADR-001's isolation requirements still apply.

## Context

- Áncora is remanufactured on Drupal 11 (#66). The MPI/Prioriza optimizer stays a separate PostgreSQL engine with bounded jobs (#71).
- Every account is approved by a person. The first cohort is small and provisioned by an operator.
- The legacy academic schema and the MPI PL/pgSQL have no tenant column and use fixed schema names (`public`, `matrix`, `prioriza`, `seguridad`, `generador`).
- Host facts (#42): one shared droplet with 1 vCPU and about 1 GiB RAM, already swapping, with PostgreSQL 18 and Caddy. R1 needs more capacity in any design (#76).

## Options

| | A. Drupal multisite, one site + database per organization | B. One Drupal site, tenant boundary in entities (e.g. the Group module) |
| --- | --- | --- |
| **Isolation of academic data** | **Physical.** Separate database and runtime role; a query cannot reach another organization's tables. | **Logical.** Every entity, View, query, cache tag, file and job must carry and enforce the tenant. One missed access check or View filter leaks data. |
| **Sessions, users, files, caches** | Separate per site: cookie name/path per site base, own `files/`, own cache bins, own users table | Shared. Separation relies on access handlers and cache contexts. |
| **MPI engine storage** | One engine database per organization (or per site), unchanged SQL | Engine databases per tenant still needed; Drupal-side mapping table per tenant |
| **Two-tenant negative tests (#75)** | Mostly prove routing and credentials; small surface | Must cover every route, View, export, file and job: large surface |
| **Operations** | Per site: install, `updb`, config import, cron, backup. Can be scripted with Drush `@sites` and the operator CLI. | One install to update; simpler cron and backup |
| **Memory/CPU** | Same per request. Opcache shares code; each site adds database and cache-bin size only. | Same |
| **Cross-organization features** | Not available. Not needed in R1. | Possible |
| **Contrib dependence** | None for tenancy | Group (or similar) on the security boundary; its updates become security-critical |

## Decision

**A. Drupal multisite: one Drupal codebase, a site and a database per organization.**

1. **Sites:**
   - `default` is the **control site** at `https://timetables.divengine.com/`. It holds the public pages, the R0 access requests and review (#73) and the operator tooling. No academic data.
   - Each approved organization gets a site in a **subdirectory**, `https://timetables.divengine.com/o/<slug>/`, mapped in `sites/sites.php`. That means one certificate and no wildcard DNS or on-demand TLS, so there is no interaction with the Odoo wildcard. The session cookie path and name are per site base, so sessions never cross organizations.
2. **Databases and roles:** following ADR-001, per organization:
   - `tt_<slug>` for Drupal;
   - `tt_<slug>_engine` for the MPI schema;
   - an owner role (NOLOGIN, migrations only);
   - runtime roles with DML only, and exclusive `CONNECT`.

   The control site has its own database and roles.
3. **Identity:**
   - Drupal users per site. The applicant is approved on the control site.
   - Provisioning creates the organization's site and its first administrator account, which is blocked until set-up. A single-use password link is sent; there are no default passwords.
   - Additional members go through the same human approval: a request on the control site, then a provisioning step.
   - Operator and tenant-admin roles are separate.
4. **Provisioning and retirement:**
   - Provisioning is an operator Drush/CLI command. It creates the databases and roles, installs the site from the committed configuration (`--existing-config`), installs the engine schema, and writes per-site settings and secrets. It is idempotent and audited on the control site.
   - Suspend: block all users and disable the site route. Retire: take a final backup, drop the site and databases.
5. **Updates:**
   - one codebase;
   - `drush @sites updb` and `drush @sites config:import` in the release procedure;
   - a release is done only when every site is updated, and a failed site blocks the rollout and triggers rollback.
6. **Engine jobs (#71):** a job runs for exactly one site and one engine database, with that site's engine credentials and the job limits. The worker is told the site and never takes it from user input.

## Consequences

- **Pros:**
  - The tenant boundary is enforced by PostgreSQL credentials and Drupal's site separation, not by query discipline.
  - No contrib module on the security boundary.
  - The MPI SQL stays unchanged.
  - Backup, restore and deletion work per organization.
- **Cons:**
  - Per-site update and cron loops.
  - Configuration drift must be prevented: config is installed only from `config/sync`, and a check reports drift.
  - A small fixed cost per organization: two databases and cache tables.
- **Scale:** fine for tens of organizations on one PostgreSQL server; revisit beyond about 100. It needs the capacity decision of #76 before R1 either way.
- **Required tests (R1 gate):** two organizations. A session, a cookie or any ID from one cannot read or write the other, through:
  - routes and Views;
  - files;
  - exports;
  - engine jobs.

  Also: one organization's runtime roles cannot connect to the other's databases.

## Rejected for now

- **B (single site + Group):** larger security surface for the R1 cohort. It can be revisited when cross-organization features are actually needed.
- **Subdomain per organization:** needs wildcard DNS and certificates, and would interact with the existing on-demand TLS wildcard on the host.
