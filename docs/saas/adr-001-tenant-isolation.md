# ADR-001: Tenant isolation, a database per organization (issue #43)

Status: **proposed** (decision record for maintainer approval); date 2026-10-25.

## Context

- A tenant is an educational organization or an independent project. Every user belongs to one tenant through a membership that the maintainer approved.
- The academic data and the MPI/Prioriza scheduler live in PostgreSQL: the scheduler is PL/pgSQL running against the academic schema.
- The legacy schema has no tenant column. Its only ownership marker, `creador`, is not a security boundary.
- The first cohort is small, and every tenant is provisioned manually by an operator.

## Decision

1. **Control plane.** One database, `timetables_control`, holds:
   - access requests, verification tokens and decisions;
   - organizations, users, memberships, invitations and plan entitlements;
   - the audit trail.

   It holds no academic data.
2. **One database per tenant**, `tt_<tenant-id>`, with the full academic schema and MPI installed by the maintained installer (`dev/sql/install.sh`).
3. **Two roles per tenant:**
   - `tt_<id>_owner`: NOLOGIN. Owns the schema; used only for installation and migrations, through the operator CLI.
   - `tt_<id>_app`: LOGIN, **DML and EXECUTE only**, no DDL, no `CREATE` on the schema. `CONNECT` on the tenant database is granted to this role alone; `PUBLIC` is revoked.

   No application role can connect to another tenant's database. The control plane has its own owner and runtime roles.
4. **Tenant resolution.**
   - The application derives the tenant from the authenticated session's server-side membership, never from a request parameter.
   - It opens a connection to that tenant's database with that tenant's runtime credentials.
   - Within a tenant, authorization is by membership role. `creador` is ignored for security.
5. **Scheduling jobs** run in a worker against one tenant database, with that tenant's runtime role and `statement_timeout`. They have OS-level time and memory limits and cancellation, and at most one job per tenant at a time.
6. **Provisioning** (`divengine` operator CLI, #54) does five steps:
   - create the database and roles;
   - install the schema;
   - grant the runtime role;
   - write the tenant's credential file;
   - record the tenant in the control plane.

   **Retirement** does the reverse, after a final backup. Both steps are audited.
7. **Backups:** a `pg_dump` per tenant database, plus the control plane. A restore of a single tenant is tested.

## Alternatives considered

| Option | Why not, for now |
| --- | --- |
| Shared database with a `tenant_id` column and RLS | Needs a tenant column on about 90 tables, and RLS policies across every PL/pgSQL function of the MPI. That is a large, risky change to characterized SQL. A single missed policy leaks data. |
| Schema per tenant in one database | The legacy SQL uses fixed schema names (`public`, `matrix`, `prioriza`, `seguridad`, `generador`) and unqualified names. Relocating them per tenant changes the MPI sources. |
| One server or container per tenant | Strongest isolation, but it does not fit the host (see `host-assessment.md`) and adds operational cost per tenant. |

## Consequences

- **Pros:**
  - The isolation is enforced by PostgreSQL connection privileges, not by query discipline.
  - The MPI SQL stays unchanged, and its characterization keeps applying.
  - Backup, restore and deletion work per tenant.
- **Cons:**
  - Each tenant costs a few MB, plus connection overhead.
  - Migrations run per tenant: the CLI loops over tenants and records the schema version.
  - The web process can read every tenant's credential file. Mitigations:
    - the files are readable only by the app user;
    - the tenant is resolved from server state;
    - two-tenant negative tests.

    Per-tenant pools or credential brokering can come later if needed.
- **Scale:** fine for tens of tenants on one PostgreSQL server; revisit beyond about 100 tenants.
- **Tests required (R1 gate):** a two-tenant E2E. Tenant A's session cannot read or change B's records, exports or jobs by altering IDs, routes or parameters, and A's runtime role cannot `CONNECT` to B's database.
