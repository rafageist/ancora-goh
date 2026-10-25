# Fresh PostgreSQL installation from sources (DB-02…DB-04)

Issue: #9 (phase #6). This builds on #8 (PR #16, `sql-reconciliation.md`) and is stacked on that branch.

## One command

```sh
# Throwaway server (socket only, deleted afterwards):
dev/sql/tools/with-ephemeral-pg.sh dev/sql/install.sh agoh

# Your own disposable server (libpq environment: PGHOST, PGPORT, PGUSER, ~/.pgpass):
dev/sql/install.sh agoh                 # schema only
dev/sql/install.sh --with-data agoh     # + datos/datos_por_defecto.sql (default roles and admin)
```

`install.sh`:

1. Builds the bundle with `compilasql.php`.
2. Creates the database, which must not already exist.
3. Loads the bundle with `psql -v ON_ERROR_STOP=1 --single-transaction` and `check_function_bodies=on`.

**Any SQL error aborts the installation, exits non-zero and drops the half-created database.** It is an empty-database installer, not an upgrade path: many sources start with `DROP … CASCADE`.

`--with-data` creates an administrator with a well-known default password stored as unsalted MD5 (`security-privacy.md` C5). Use it only on disposable servers.

Maintainer decision (2026-10-25): a known seeded administrator password must never be valid in a production installation. Once #14 (PR #20) is in, legacy MD5 logins are refused unless `legacy_md5_login = true` is set in `bin/security.conf`, and the default is off. Set the administrator's password with `php bin/setpassword.php -nick <nick>` before first use. The seed file itself is unchanged. Replacing its hash with an unusable marker is a possible follow-up.

You need PHP ≥ 8.1 (CLI only) and the PostgreSQL ≥ 16 client. The ephemeral runner additionally needs the server binaries (`PG_BINDIR`).

## Changes

### `deploy.priority` (manifest)

| Problem (assessment §3) | Fix |
| --- | --- |
| `organizacion.grupo` declared only 2 of its 5 FK targets, so a fresh `grupo` failed and about 45 objects cascaded | Added `organizacion.especialidad`, `organizacion.nivel` and `tiempo.configuracion.planificacion` |
| Typo'd dependencies that the old bundler silently ignored: `seguridad.usurio`, `activida.asignatura`, `restriccion_de_obrigada`, `miscelanea.crea_id_de_replica`, `@tiempo.is_leap_year`, `tiempo.configuracion.dame_periodo_plantilla_de_actividad` | Corrected to the real entries |
| 33 maintained sources not listed: 25 functions (among them `valor_restriccion_heredada` and `valor_restriccion_otro_recurso`), 5 trigger files, `generador/esquema_generador.sql`, `portal/portal.sql` and the 26 RULEs in `ancoragoh.sql` | Appended in a new section with their CREATE-time dependencies (tables for SQL-language functions, row types in signatures, trigger tables, rule targets and functions) |

The assessment counted "30 omitted functions". The exact unlisted set is 25 function files plus 5 trigger files (each defines its trigger function). Every file is now either listed or explicitly excluded.

### `compilasql.php` (bundler, rewritten for PHP 8)

The old bundler had several defects:

- It matched dependency names loosely (`packsiguales`) and dropped unknown ones without telling anyone.
- It crashed on PHP 8 (`substr(...) - 1`).
- It copied UTF-8 BOMs into the middle of the bundle.
- It always wrote `./result.sql`.

The new bundler keeps the same manifest format and the same depth-first "dependencies first, otherwise manifest order" strategy. It is strict: the build **fails** (exit 1, message on stderr) when any of the following happens:

- a dependency is unknown, ambiguous, or a `prefix*` pattern matches nothing;
- there is a dependency cycle;
- an entry has no file, or is listed twice;
- a maintained `.sql` file under `dev/sql/` is not listed. The explicit exclusions are `trash/`, `tools/`, `generador/trash.sql` and the former `result.sql`/`tabla.sql`.

The output is deterministic:

- no timestamps;
- BOMs stripped;
- one trailing newline per file;
- `-- >>> key (path)` / `-- <<< key` markers around each file.

Two builds are byte-identical, on PHP 8.3 and on PHP 8.4 (verified; SHA-256 prefix `94e7e9b1c1a9a4eb` for the schema-only bundle at this commit).

Sources listed in `tools/known-broken.txt` (only `restringe_por_uso_de_otro_recurso`, decision D-05 in `sql-reconciliation.md`) are wrapped in `SET check_function_bodies = off` … `RESET`. This loads them exactly as the 2010 `pg_dump` did. The maintainer approved keeping D-05 as a documented known defect (2026-10-25), with the occupancy RULE left installed. Its repair belongs to a separate behavioral-fix issue. Everything else is compiled with body checks on.

`data:` entries are included only with `--with-data`.

Usage: `php dev/sql/compilasql.php [--with-data] [--out FILE]`, where the default output is stdout. `compilasql.sh` and `compilasql.bat` still write `./result.sql`.

### `result.sql` / `tabla.sql`: build artifacts instead of tracked output

- **Decision:** the committed `result.sql` and `tabla.sql` were stale and failed to install (68 errors on PostgreSQL 16, assessment §4.1). They are removed from version control and ignored (`dev/sql/.gitignore`). CI builds `result.sql` and `result-with-data.sql` on every run and uploads them as the `ancoragoh-sql-bundle-pg16` artifact.
- **Rationale:** generated output cannot drift from its sources if it is never committed.
- `compilatablas.php` was removed with `tabla.sql`. It was the tables-only variant of the old bundler, had the same loose matching and a reversed `strpos` (assessment §3.4), and nothing references it. The old files remain in Git history.
- The project `README.md` still mentions `tabla.sql`. It was left unchanged to avoid conflicting with the open README edit in PR #15. Update it after both are merged.

## Validation

| Check | Command | Result (local, PostgreSQL 16.15, PHP 8.4.27 / 8.3.6) |
| --- | --- | --- |
| Bundler self-test | `dev/sql/tools/test-bundler.sh` | Deterministic, and rejects all seven broken-manifest cases: unknown dependency, ambiguous dependency, unmatched pattern, cycle, missing file, duplicate entry, unlisted file |
| Every source compiles | `dev/sql/tools/check-sources.sh` | 268/268, 0 failed, 1 KNOWN (D-05) |
| Strict fresh install | `dev/sql/install.sh agoh` | 268 entries, **0 SQL errors** |
| Install with data seed | `dev/sql/install.sh --with-data agoh_data` | 269 entries, 0 errors |
| Installer fails on errors | `dev/sql/tools/test-installer.sh` | A broken source makes it exit non-zero and leaves no database |
| Inventory and critical objects | `dev/sql/tools/smoke-check.sh agoh` | Matches `tools/expected-objects.tsv`, and the generator / Prioriza / cancellation / occupancy objects are present. A dropped function or rule makes it fail (negative test run) |

Object counts of a fresh schema-only install:

| Kind | Count |
| --- | --- |
| Schemas | 7 |
| Tables | 61 |
| Columns | 359 |
| PK / FK / UNIQUE / CHECK constraints | 60 / 139 / 15 / 35 |
| Views | 2 |
| Composite types | 3 |
| Functions | 196 |
| Triggers | 12 |
| Rules | 26 |

**Equivalence with the #8 baseline (verified):**

- `compare-catalog.sh` between the fresh install and the fixpoint-loaded database from #8 reports every object identical.
- Against the 2010 dump, it reports exactly the 18 differences documented in `sql-reconciliation.md` §3. That run is local only, because the dump is private.

CI is `.github/workflows/db-install.yml`. It runs the same sequence on `ubuntu-24.04` with PHP 8.4 and PostgreSQL 16 server binaries, inside a socket-only cluster the job creates. There is no service container, no TCP listener, no dump and no credentials. PostgreSQL 17 is not in the matrix yet: Ubuntu 24.04 does not ship it, and adding the PGDG apt repository is a separate, small change.

`tools/expected-objects.tsv` is the reviewed baseline. When a change intentionally adds or removes objects, regenerate it with `dev/sql/tools/smoke-check.sh --update <db>` and review the diff in the PR.

## Not covered

- **Behavior.** The installer proves that objects are created, not that they behave correctly. Characterization is #10.
- **Run time.** `plpgsql.variable_conflict` is not needed to install. Tests that run the generator must set it per session (`sql-reconciliation.md` §6).
- **Upgrades.** There are no migrations and no upgrade path from the 2010 database.
