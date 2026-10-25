# SQL tool safety: disposable clusters only (issue #26)

Issue: #26 (phase #6). It is stacked on #10 (PR #19), which is stacked on #9 (PR #18) and #8 (PR #16).

## Why

During the integration review one tool was started without `with-ephemeral-pg.sh`. It used the libpq defaults and tried to reach the maintainer's own PostgreSQL server through the system socket on port 5432. Authentication failed, so nothing happened, but the tools must not depend on luck. They now **fail closed**: any tool that connects to a server refuses to run unless it can prove that the server is a disposable cluster started by the launcher.

## How it works

`dev/sql/tools/with-ephemeral-pg.sh` (the launcher) creates a random 256-bit token per run and records it in three places only it controls:

| Where | What |
| --- | --- |
| environment | `AGOH_DISPOSABLE_TOKEN`, plus `AGOH_DISPOSABLE_DATA` (the data directory) |
| file | `<socket directory>/agoh-disposable.id`, mode 600 |
| server | the custom setting `agoh.disposable_token`, given on the server command line at start |

`dev/sql/tools/disposable-guard.sh` provides `agoh_require_disposable`. Every tool calls it before its first client command.

**Checks before connecting.** Nothing is sent to any server until all of these pass:

1. `PGHOSTADDR`, `PGSERVICE` and `PGSERVICEFILE` are not set. They would override `PGHOST`.
2. `PGHOST` is set to exactly one absolute directory. A host name, TCP and the system socket directories (`/var/run/postgresql`, `/run/postgresql`, `/tmp`, …) are refused.
3. `$PGHOST/.s.PGSQL.$PGPORT` is a socket owned by the current user.
4. The marker file exists, is a regular file owned by the current user, and holds exactly `AGOH_DISPOSABLE_TOKEN`.
5. `AGOH_DISPOSABLE_DATA` is a directory owned by the current user. Its `postmaster.pid` names a live server process of the same user, serving that same data directory, socket directory and port, with an empty listen address (no TCP).

**Check after connecting.** One read-only query against the `postgres` database: the server itself must report the same token, data directory and an empty `listen_addresses`. A forged marker plus a matching environment token does not get past this check.

**On failure** the tool prints one `[REFUSED]` line naming the failed check, never a token or a password, and exits with status 3. No environment variable turns the check off.

## Covered tools

| Tool | Connects | Guarded |
| --- | --- | --- |
| `dev/sql/install.sh` | creates and loads a database | yes. `--admin-install` is the only alternative (below) |
| `dev/sql/tools/check-sources.sh` | creates and drops a database | yes |
| `dev/sql/tools/smoke-check.sh` | reads the catalog | yes |
| `dev/sql/tools/compare-catalog.sh` | reads two catalogs | yes. It is read-only, but it runs against whatever server libpq picks, so it is guarded too |
| `dev/sql/tools/test-installer.sh` | runs a copy of `install.sh` | yes (and the copy is guarded again) |
| `dev/sql/tests/run.sh` | creates template and case databases | yes |
| `dev/sql/tools/test-bundler.sh`, `dev/sql/compilasql.php` | no server | not needed |

## Deliberate installation into another server (`--admin-install`)

This opt-in exists for a maintainer who really means to install into a server they chose, for example a private machine:

```sh
AGOH_ADMIN_INSTALL_CONFIRM=<dbname> PGHOST=<host> dev/sql/install.sh --admin-install [--with-data] <dbname>
```

- The flag, a confirmation that repeats the database name, and an explicit `PGHOST`, `PGHOSTADDR` or `PGSERVICE` are all required.
- Tests and CI never use it.
- It is never the default: without the flag the disposable checks apply.
- The other tools have no such mode.

After a `--with-data` install, set the administrator password with `bin/setpassword.php` (#14) before the first login.

## Tests

`dev/sql/tools/with-ephemeral-pg.sh dev/sql/tools/test-guard.sh` runs in CI, in the `db-install.yml` job. The CI job also checks that running `check-sources.sh` directly is refused.

**Part 1, no server involved.** All 7 tool invocations run under 8 unsafe or forged environments:

- `PGHOST` unset;
- `PGHOST=localhost`;
- `PGHOSTADDR`;
- `PGSERVICE`;
- the system socket directory;
- a relative path;
- a forged marker with no server;
- the real socket without a token.

`psql`, `createdb`, `dropdb` and `pg_dump` are replaced on `PATH` by stubs that record any call. Every run must exit 3 with a `[REFUSED]` line, and no stub may run. That means no connection was attempted. No refusal message may contain a token.

**Part 2, inside the launcher's cluster.**

- A wrong environment token is refused (marker mismatch).
- Another data directory is refused (no running server).
- A forged marker plus a matching token is refused by the server-side identity check.
- The list of databases is unchanged after all refused runs.
- The genuine identity is accepted.
- `--admin-install` is refused without its confirmation and with a confirmation naming another database. With the right confirmation it installs (into the disposable cluster).

Verified locally on PostgreSQL 16.15: 18/18 checks pass. The existing suites are unchanged:

- check-sources: 268 loaded, 0 failed, 1 KNOWN;
- strict install and `--with-data` install: 0 errors;
- smoke-check OK;
- installer negative test OK;
- bundler self-test OK;
- characterization: 16 PASS, 17 XFAIL.

The bundle is not touched.

## Limits

- The checks use GNU `stat -c`, `/proc` or `ps`, and `od`. They target Linux, which is what CI and the documented setup use.
- The token is visible in the local process list of the disposable server. It is a per-run identity nonce, not a credential. Forging the identity would also need files and a server process owned by the same user.
- The guard protects the repository's tools. It cannot stop someone from typing `psql` by hand.
