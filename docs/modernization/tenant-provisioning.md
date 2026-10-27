# Single-database tenants: privileges and provisioning (#96)

Issue: #96, under #91 and #66. It builds on [single-db-workspace.md](single-db-workspace.md) (#93) and [placement-promotion.md](placement-promotion.md) (#95), and changes the provisioning of [drupal-multisite.md](drupal-multisite.md) (#72).

A new organization site now gets **one database**. The unchanged engine lives in schema `scheduler` of the site's own database, every run gets a job-scoped workspace there, and the site file selects the workspace mode. Nothing of it needs a superuser: the operator role of the host (`LOGIN CREATEDB CREATEROLE`) provisions everything.

Sites provisioned before keep the two-database layout (#71) until they are migrated. The migration is a separate step, `ancora-site.sh migrate-single-db` (see [tenant-migration.md](tenant-migration.md) once it lands).

The legacy engine SQL is unchanged and no business rule was added. **0 LBR bytes are claimed.**

## 1. Roles of one tenant

All names derive from the database name `tt_<slug>`, which is unique in the cluster. Two tenants never share a role.

| Role | Attributes | What it is for |
| --- | --- | --- |
| `tt_<slug>_owner` | LOGIN | Owns the database and Drupal's tables. Used only by the operator's command line (#72). |
| `tt_<slug>_app` | LOGIN | Drupal at runtime (PHP-FPM, drush, the worker's Drupal connection). |
| `tt_<slug>_engine_owner` | **NOLOGIN, CREATEROLE**, nothing else | Owns the engine schemas and every engine object, including the five SECURITY DEFINER provisioning functions. It creates the job group and the job roles. |
| `tt_<slug>_jobs` | NOLOGIN | Group of the tenant's job roles. Created by the engine owner, which therefore holds ADMIN OPTION on it. |
| `tt_<slug>_job_<job>_<attempt>` | NOLOGIN, member of `tt_<slug>_jobs` | One per job attempt, created when the worker opens the workspace and dropped with it. Owns the workspace schema `scheduler_job_<job>_<attempt>`. |
| `tt_<slug>_engine_app` | LOGIN, NOINHERIT | The site's engine worker (the password file `engine-db-password`, as in the two-database layout). |

A job role's name is never truncated: if it does not fit in 63 bytes, opening the workspace fails (SQLSTATE 42622). A slug has at most 31 characters, so the longest job role name is 58 bytes.

A new slug may not end in `-engine`. The engine database and roles of site `<a>` are `tt_<a>_engine` and `tt_<a>_engine_*`, so a site `<a>-engine` would share them. This collision already existed in the two-database layout (`tt_<a>_engine_app` was both site `<a>`'s engine role and site `<a>-engine`'s runtime role). Existing sites are not affected.

## 2. Privileges

| On | `_app` (Drupal) | `_engine_app` (worker) | `_jobs` (job roles) | `_engine_owner` |
| --- | --- | --- | --- | --- |
| Database: CONNECT | yes | yes | yes (the job roles are NOLOGIN) | yes, WITH GRANT OPTION |
| Database: TEMPORARY | yes | **no** | yes (the closure's TEMP table) | yes, WITH GRANT OPTION |
| Database: CREATE | no | no | no | **yes** (engine schemas, workspaces) |
| Schema `public` (Drupal) | USAGE, DML (#72) | **nothing** | **nothing** | nothing |
| `scheduler` and the legacy schemas | **nothing** | nothing | USAGE only; no privilege on any template table | owner |
| `scheduler_control` | nothing | USAGE; EXECUTE on the five provisioning functions only | USAGE; EXECUTE on the closure functions; SELECT on the stores | owner |
| `seguridad.usuario` | nothing | nothing | SELECT, INSERT, REFERENCES (#93) | owner |
| Its workspace | nothing | nothing (member WITH INHERIT FALSE, must SET ROLE) | the job role owns its own | inherits each job role |

No runtime, worker or job role can create anything in `public` or in an engine schema, or create a schema. No function of `scheduler_control` is executable by PUBLIC. `ancora-site.sh` checks this after every provision (`workspace_check`), and the provision fails otherwise.

### The five SECURITY DEFINER functions

`open_job_workspace`, `mark_workspace`, `close_job_workspace`, `request_cancel` and `worker_cleanup`. They are owned by `tt_<slug>_engine_owner` and executable only by the worker.

- Every one is declared `SET search_path = pg_catalog, pg_temp` and schema-qualifies every object. A workspace's DDL is generated with `search_path = scheduler, pg_temp`, then executed with `search_path = <workspace>, scheduler, pg_temp`. `pg_temp` is always last.
- Keys are checked (`^[0-9]{1,12}_[0-9]{1,6}$`) before they become identifiers, and identifiers go through `format('%I')`.
- `request_cancel(job key, specialty, level, period)` replaces the worker's direct INSERT into `cancel_request` (#95). The worker now has no privilege on any control table. The request row still belongs to the job role derived from the key, so only that attempt sees it.

## 3. What needed a superuser, and how it is solved

The #93/#95 prototype was installed by a superuser. Each superuser-only step was replaced as follows. Every row was tested as a non-superuser on PostgreSQL 16.15 locally, and on 16 and 18 in CI (`tenant-verify.sh`).

| Step | Why it needed more than a database owner | Now |
| --- | --- | --- |
| `CREATE ROLE agoh_engine` (one group for the whole cluster) | CREATEROLE, and the group was shared by every tenant | Per tenant: `tt_<slug>_jobs`, created by the tenant's engine owner while the engine is installed |
| Owner of the SECURITY DEFINER functions | They create and drop roles, so the owner needs CREATEROLE. The installer was a superuser | `tt_<slug>_engine_owner`: NOLOGIN, CREATEROLE only. The operator installs as it (`SET ROLE`) |
| `CREATE ROLE <job> IN ROLE <group>`, `GRANT <job> TO <worker> WITH INHERIT FALSE, SET TRUE` | Since PostgreSQL 16, a CREATEROLE role needs ADMIN OPTION on both roles | The engine owner creates both roles, so it holds ADMIN OPTION on them |
| `CREATE SCHEMA … AUTHORIZATION <job>`, `ALTER TABLE … OWNER TO <job>` | The executing role must be able to SET ROLE to the job role | `open_job_workspace` is declared `SET createrole_self_grant = 'set, inherit'`, so the engine owner becomes a member of each job role it creates |
| `DROP SCHEMA <workspace> CASCADE` | Needs the privileges of the schema's owner (the job role) | The same membership, with INHERIT |
| `GRANT TEMPORARY ON DATABASE … TO <job>` per job (#95) | Grant option on the database | Removed. The job roles inherit TEMPORARY from `tt_<slug>_jobs` |
| `GRANT CONNECT, TEMPORARY ON DATABASE … TO <group>`, `TO <worker>` | Grant option on the database | The database owner grants them to the engine owner WITH GRANT OPTION (`ancora-site.sh`) |
| `DROP TABLE generador.cancelar` (replaced by the per-job view) | Ownership | The engine owner loaded the bundle, so it owns it |
| `cleanup()` finds the live backend of a job in `pg_stat_activity` | `usename` and `application_name` of other roles' sessions | Both columns are visible to every role. Checked: a live job is not marked failed, a killed one is |

**The legacy bundle needs no superuser.** The 268 entries of `dev/sql/compilasql.php` were scanned for `CREATE EXTENSION`, `CREATE LANGUAGE`, `LANGUAGE c` or `internal`, `ALTER … OWNER`, `SECURITY DEFINER`, `ALTER SYSTEM`, `SET plpgsql.*`, roles and grants:

- The only privileged-looking statement is `CREATE EXTENSION IF NOT EXISTS plpgsql`. It is a no-op: PL/pgSQL is preinstalled in every database since PostgreSQL 9.0, and it is a trusted language.
- `#variable_conflict use_variable` is a per-function compiler option inside 3 function bodies. It is not the `plpgsql.variable_conflict` setting.
- There is no other language, no extension, no ownership change and no setting.

The two-database layout already installed it as the operator (`engine-db.sh --admin-install`). `pg_trgm`, which Drupal needs, is a trusted extension created by the operator as before.

**What still needs a superuser: nothing in provisioning, in the worker or in the migration.** The operator role needs:

- `LOGIN CREATEDB CREATEROLE`, and PostgreSQL 16 or later: the CREATEROLE rules and the `GRANT … WITH INHERIT/SET` syntax are from 16;
- the privileges of the site roles it creates. This is already the case on the host for #72: `CREATE DATABASE … OWNER tt_<slug>_owner` requires it. The verification sets `createrole_self_grant = 'set, inherit'` on the operator, as `multisite-verify.sh` and `r1-journey.sh` do;
- membership in `tt_<slug>_engine_owner` WITH SET, to install as it. `ancora-site.sh` grants this itself, with INHERIT FALSE, because the operator holds ADMIN OPTION on a role it created.

The engine owner's CREATEROLE is bounded:

- It cannot log in, and it is reached only through the five fixed functions, or by the operator.
- Since PostgreSQL 16 it administers only the roles it created: its own job group and job roles.
- It cannot grant SUPERUSER, CREATEDB, REPLICATION or BYPASSRLS, and it cannot grant membership in any role it did not create (no `pg_*` role, no other tenant's role).

The only remaining superuser use is in the verification: test-only objects (a slow-placement trigger) created in disposable clusters.

## 4. Provisioning

```
ancora-site.sh provision <slug> --name <site name> --admin-mail <mail> [--engine-layout single|two-database]
```

The default for a new site is `single`. The site file is the source of truth for an existing site: a `site.ini` without `ENGINE_MODE` was written before #96 and is a two-database site, and a re-run keeps its layout. `--engine-layout two-database` exists for the existing layout's tests (`r1-journey.sh`) and is not meant for new organizations.

In the single-database layout, step 2 (database) becomes:

1. The database, owner and runtime roles, as before (#72).
2. `workspace_ensure`:
   - the engine owner and the worker role, with their attributes checked;
   - the operator's SET membership in the engine owner;
   - `CREATE` and `CONNECT, TEMPORARY WITH GRANT OPTION` on the database for the engine owner;
   - then, once, `dev/workspace/install.sh --admin-install --no-measure --owner tt_<slug>_engine_owner --worker tt_<slug>_engine_app tt_<slug>`. It runs in ONE transaction: the bundle with `search_path = scheduler`, `workspace.sql`, the worker's grants. A failed install leaves nothing, and the re-run is the recovery.
   - On re-runs: the worker's grants again, then `workspace_check`.

`site.ini` gets `ENGINE_MODE = "workspace"`, `ENGINE_DB_NAME = "tt_<slug>"` and `ENGINE_DB_USER = "tt_<slug>_engine_app"`. `settings/ancora.org.settings.php` reads the mode from the site file only: a site can never be switched by the environment. The shared limits may still come from the environment (`ANCORA_ENGINE_*`, now including `ANCORA_ENGINE_WORKSPACE_RETENTION`).

The measurement schema `wsproof` (test code, `measure.sql`) is not installed in a tenant database.

`deprovision` drops the database, then the roles:

- every `tt_<slug>_job_<n>_<n>` left by a workspace that was never closed;
- `tt_<slug>_jobs`, `tt_<slug>_engine_owner`, `tt_<slug>_engine_app`, `tt_<slug>_app` and `tt_<slug>_owner`.

`list` shows each site's engine layout.

The worker is unchanged: `ancora-site.sh work <slug>` (drush `ancora-engine:work` with the site's roles). The site file decides whether it uses the engine database or the workspaces.

## 5. Verification: `modern/scripts/tenant-verify.sh`

```
dev/sql/tools/with-ephemeral-pg.sh modern/scripts/tenant-verify.sh [--skip-composer]
```

It runs in a disposable cluster only. The operator role is a non-superuser with `LOGIN CREATEDB CREATEROLE`, as on the host. Only the script's own checks and test-only objects use the superuser.

1. **Provisioning** of the control site, alpha and beta:
   - one database each, and no `tt_*_engine` database;
   - the workspace mode in the site file and in Drupal;
   - no site role with SUPERUSER, CREATEDB, REPLICATION or BYPASSRLS; only the engine owners have CREATEROLE, and they cannot log in;
   - each tenant's roles are its own, with no membership across tenants;
   - every engine schema is owned by the engine owner, and so are exactly five SECURITY DEFINER functions, each with the pinned search_path;
   - no object outside the catalogs and extensions is owned by a superuser;
   - the CREATE, USAGE, TEMPORARY and EXECUTE matrix of section 2;
   - no `wsproof`;
   - a re-run is idempotent (same `scheduler` schema), and `list` shows the layout.
2. **A job on each site** through `ancora-site.sh work --once`: 8/8 and the E2E digest from the placement entities. No workspace or job role is left.
3. **Isolation during a run** (test-only slow-placement trigger):
   - the job role is NOLOGIN, a member of its tenant's group only, and cannot log in;
   - beta's worker and runtime roles cannot connect to `tt_alpha`, and beta's worker cannot SET ROLE to alpha's job role;
   - alpha's runtime role can neither SET ROLE to the job role nor read the workspace, and alpha's worker cannot read it without SET ROLE;
   - the job groups have no privilege on another tenant's database.
4. **Recovery**:
   - a worker killed during a run is recovered: the lost attempt's workspace and role are dropped, and the next attempt publishes 8 once;
   - a workspace kept for diagnosis (retention 2 s) is dropped by the next worker cycle after it expires.
5. **Provisioning recovery and deprovisioning**:
   - a provision stopped after the database step is repaired by a re-run;
   - `alpha-engine` is refused;
   - deprovisioning removes the database and every `tt_gamma_*` role, including the job role of a workspace that was never closed.

**Result on the development workstation** (PHP 8.4, PostgreSQL 16.15): **54 checks, 54 PASS, 0 FAIL**, in about 90 s. CI runs it on PHP 8.4 + PostgreSQL 16 and PHP 8.5 + PostgreSQL 18 (job `tenant` in `modern.yml`).

The other suites were adapted to the per-tenant group name (`<database>_jobs` instead of `agoh_engine`), and stay green:

- `workspace-proof.sh` (91 checks) and `workspace-journey.sh` (37 checks);
- `multisite-verify.sh`: its engine checks now expect the single-database layout;
- `r1-journey.sh`: it provisions the two-database layout explicitly;
- `parity-diff.sh --workspace`, `engine-integration.sh` and `install-disposable.sh`.

## 6. Limits and open risks

- **Engine upgrades.** The engine is installed once. A later change to the legacy bundle or to `workspace.sql` needs an upgrade path for existing single-database tenants. `update-all` does not touch the engine. This is the same as for the engine databases of #71.
- **Role churn.** Every job attempt creates and drops a role (cluster catalog) and about 124 relations (#93 measurements). The 1 vCPU / 1 GiB host runs one job per site at a time (#95).
- **The engine owner has CREATEROLE.** It is bounded as described in section 3. A defect in the five functions could create roles, but only roles without attributes and without any membership the engine owner did not create.
- **One cluster, many tenants.** Roles are cluster-wide, so the names carry the tenant, and the checks prove no cross-tenant membership or privilege. A tenant's database name is the only namespace, and it is unique.
