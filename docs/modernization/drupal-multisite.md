# Drupal multisite: a control site and one site per organization (issue #72)

Issue: #72, first implementation slice of ADR-002 (PR #81: Drupal core multisite, one site and one database per organization). It builds on the Drupal 11 foundation (#69, [`drupal-foundation.md`](drupal-foundation.md)).

This slice delivers:

- path-based site resolution;
- per-site settings, secrets and files;
- least-privileged database roles;
- an operator tool for provision, admin link, suspend, resume, deprovision and update;
- the Caddy and PHP-FPM templates;
- a verification that runs the real Caddy and PHP-FPM stack on 127.0.0.1 against a disposable PostgreSQL cluster.

Nothing is deployed. The production host is untouched.

## Sites and URLs

| Site | Directory | URL | Database | Settings come from |
| --- | --- | --- | --- | --- |
| Control site | `web/sites/default` | `/` | `tt_control` (name from the environment) | environment (`ANCORA_*`), as in #69 |
| Organization `<slug>` | `web/sites/org-<slug>` | `/o/<slug>/` | `tt_<slug>` (`-` becomes `_`) | `$ANCORA_SITES_DIR/<slug>/site.ini` |

A slug matches `^[a-z][a-z0-9-]{1,30}$`. Slugs contain no `_`, so the mapping to database identifiers is injective.

## How a request finds its site

Drupal core resolves a subdirectory site from the **script path** in `DrupalKernel::findSitePath()`. It does not use the request path. For a script `/o/alpha/index.php` on host `127.0.0.1:8080`, Drupal tries the keys `8080.127.0.0.1.o.alpha`, `127.0.0.1.o.alpha`, `0.0.1.o.alpha` and so on, then drops path segments from the end. Each key is looked up in `sites/sites.php`, or matched directly as a directory name. If nothing matches, it falls back to `sites/default`.

Several layers then make sure a request reaches the right site, and only that site.

1. **The web server runs the front controller as `/o/<slug>/index.php`.** `web/o/<slug>` is a symbolic link to the web root (`..`). The provisioning tool creates it as the **last** step, when the site is ready. This is Drupal's documented technique for subdirectory sites. It works with any server that sets `SCRIPT_NAME` from the file system: Caddy `php_fastcgi`, Apache and nginx.
   - Caddy (`modern/deploy/caddy/ancora.caddy`) matches `^/o/([a-z][a-z0-9-]{1,30})(/.*)?$`.
   - It answers **404** unless `web/o/<slug>/index.php` exists.
   - Otherwise it runs `/o/<slug>/index.php`. Its `try_files` falls back to the site's own front controller, never to `/index.php`.
   - Every other `/o` or `/o/...` path is a 404.
2. **`web/sites/sites.php`** is committed code, not a registry. When the script is exactly `/o/<slug>/index.php`, it maps the most specific key of the current request to `org-<slug>`. Drupal uses the mapping only if `sites/org-<slug>/settings.php` exists.
3. **Request guard** in `settings/ancora.settings.php`, for every web request:
   - The control site serves only the script `/index.php`, and never a path under `/o` (checked after decoding and collapsing slashes). So when an unknown organization falls back to `sites/default` in Drupal's discovery, the fallback ends in a 404 there. It never shows the control site.
   - An organization site serves only the script `/o/<slug>/index.php`, and only for request paths under `/o/<slug>/`. That rules out a selection by a crafted `Host` header such as `Host: org-alpha`, which Drupal would match directly as a directory name.
4. **Trusted hosts** (`ANCORA_TRUSTED_HOSTS`) apply to every site. A production Caddy site address names the host, so other `Host` values never reach PHP.
5. **On the command line**, Drush resolves the site from `--uri` through the same `sites.php`. The operator tool also sets `ANCORA_EXPECT_SITE_PATH`, and the settings stop if Drupal resolved any other site directory. This matters because `drush site:install` empties the database it resolves.

Why the link and not a Caddy-only rewrite:

- It is the mechanism Drupal documents, and it does not depend on `SCRIPT_NAME` and `SCRIPT_FILENAME` overrides in the FastCGI parameters.
- Static assets under `/o/<slug>/core/...` resolve naturally.
- Publishing and unpublishing a site is one atomic file-system operation (`ln -sfn` / `rm`), with no web server reload.

The link exposes the whole web root under `/o/<slug>/`, so the Caddy block allows below `/o/<slug>/sites/` only the site's own `sites/org-<slug>/files/`. It refuses a nested `/o/<slug>/o/...`. At the root it allows only `sites/default/files/`. As in the #69 block, only `index.php` executes, and Drupal's `.htaccess` file protections are repeated, because Caddy does not read `.htaccess`. Drupal's extension discovery does not scan `web/o` (only `core`, `profiles`, `modules`, `themes` and `sites`), so the symbolic link loop is never traversed.

## Per-site settings, secrets and files

```
web/sites/sites.php                      committed: site discovery
web/sites/org-<slug>/settings.php        copy of settings/org-site.settings.php; no secret
web/sites/org-<slug>/files/              the site's public files (/o/<slug>/sites/org-<slug>/files/)
web/o/<slug> -> ..                       published site (removed = unpublished)

$ANCORA_SITES_DIR/                       e.g. /etc/ancora/sites, mode 0751
  audit.log                              one line per operation: time, operator, action, site, result
  <slug>/                                0750, group = web server
    site.ini                             0640: SLUG, STATUS, SITE_NAME, DB_*, HASH_SALT_FILE, PRIVATE_PATH, TMP_PATH
    db-password                          0640: runtime role
    hash-salt                            0640
    owner-password                       0600: owner role, operator only; the web server cannot read it
    provision.state                      provisioning progress (INSTALLED, ADMIN, PROVISIONED)
$ANCORA_SITES_DATA_DIR/<slug>/private/   0770: private files, outside the web root
$ANCORA_SITES_DATA_DIR/<slug>/tmp/       0770: temporary files
```

`settings/ancora.org.settings.php` does the following:

- It takes the slug from the site directory that Drupal selected, never from the request.
- It reads `site.ini`, and requires that its `SLUG` matches.
- It passes the values to the shared settings file. The database name, user and passwords, the hash salt and the private and temporary paths are **site-only**: they are read from `site.ini`, never from the server environment. An organization can therefore never fall back to the control site's database or salt.
- Shared values, such as the environment, database host and port, trusted hosts, reverse proxy and configuration sync directory, may come from the PHP-FPM pool environment.

Per site, the settings also set:

- the public files directory, `sites/org-<slug>/files`;
- `session.cookie_path` to `/o/<slug>/`. Drupal already derives the session cookie name from host and base path.
- the site name, as a settings override.

The site name override keeps `config/sync` identical on every site, so `config:import` never reverts it.

A `site.ini` whose `STATUS` is not `active` (`provisioning`, `suspended` or `retired`) answers **503** to web requests before Drupal bootstraps.

**Configuration.** Every site installs from the same committed `config/sync`, with `drush site:install --existing-config`. The site UUID is the same on every site, which is what lets `config:import` work everywhere.

## Database roles

Per site, the tool creates:

- `tt_<slug>`, the database, owned by `tt_<slug>_owner`;
- `tt_<slug>_owner`: LOGIN, used only by the operator's command line (install, `updatedb`, `config:import`);
- `tt_<slug>_app`, the runtime role used by PHP-FPM.

| Privilege | Owner | Runtime | PUBLIC |
| --- | --- | --- | --- |
| `CONNECT` on the site database | yes (owner) | yes | **revoked** |
| `TEMPORARY` on the site database | yes | yes (Drupal's `queryTemporary`) | revoked |
| `CONNECT` on another site's database | no | no | no |
| `CREATE` on schema `public` | yes | **no** | revoked |
| Tables and sequences | owner | `SELECT, INSERT, UPDATE, DELETE, TRUNCATE`; sequences `USAGE, SELECT, UPDATE` (also by default privileges for future tables) | none |
| Superuser, `CREATEDB`, `CREATEROLE` | no | no | |

The settings use the owner credentials only when `PHP_SAPI` is `cli` and `ANCORA_DB_AS_OWNER=1` is set.

**What Drupal needs at runtime.** Drupal creates several tables lazily, on first use: cache bins, `cachetags`, `flood`, `batch`, `queue`, `semaphore`, `key_value_expire`, `sessions`, `router`. A runtime role without `CREATE` would fail on the first login or the first cache write.

The tool therefore runs `scripts/lib/site-tasks.php runtime-tables` as the owner, after the install and after every update. The task calls the same `ensureTableExists()` / `ensureBinExists()` that the first use would call, for:

- every cache bin, the tagged ones and the untagged ones declared in core and module service files, such as `cache.file_parsing`;
- every lazily created core storage.

The verification checks the outcome: PostgreSQL logs no `permission denied` during the web requests.

Two privileges are kept on purpose:

- `TRUNCATE`: the cache `deleteAll()` truncates.
- `TEMPORARY`.

What the runtime role cannot do is change the schema. Installing a module from the administration UI therefore fails. That is intended: modules and configuration come only from `config/sync`, through the update loop. A contrib or custom module that creates tables lazily needs its table added to `runtime-tables`. The verification would show the failure.

The control site uses the same scheme. Its names come from `ANCORA_DB_NAME`, `ANCORA_DB_USER` and `ANCORA_DB_OWNER_USER`, and its secrets from `ANCORA_*_FILE`.

## Operator tool: `modern/scripts/ancora-site.sh`

| Command | What it does |
| --- | --- |
| `control-install --admin-mail <mail>` | Creates the control site's secrets (if missing), its database and roles, and installs it from `config/sync` (if not installed). Blocks the first administrator and prepares the runtime tables and grants. |
| `provision <slug> --name <name> --admin-mail <mail>` | Creates or repairs one organization site (steps below). |
| `admin-link <slug\|control>` | Unblocks uid 1 and prints a **single-use login link**, valid for 24 hours, to the operator. The administrator sets a password on first use. |
| `suspend <slug>` | Sets `STATUS=suspended`: 503 at once. Then sets Drupal maintenance mode and ends all sessions, so a resume does not revive old sessions. |
| `resume <slug>` | Clears maintenance mode and sets `STATUS=active`. |
| `deprovision <slug> --backup-dir <dir> --confirm <slug>` | Refuses an active site. Unpublishes the site, takes a final `pg_dump -Fc`, then drops the database and both roles and deletes the site's files and record. The audit log keeps the history. |
| `update-all [--backup-dir <dir>]` | The update loop (below). |
| `list` | Sites and status. |

It needs an administrative PostgreSQL connection through the usual `PG*` variables: a superuser, or `CREATEDB` + `CREATEROLE` with membership in the site roles. It also needs `ANCORA_SITES_DIR`, `ANCORA_SITES_DATA_DIR`, `ANCORA_PUBLIC_URL` and the shared `ANCORA_DB_HOST`/`PORT`. `ANCORA_WEB_GROUP` sets the group of the files the pool must read.

Only one command runs at a time (`flock` on `$ANCORA_SITES_DIR/.lock`).

### Provisioning steps

1. **record**: site directory and secrets. Secrets are created once and kept on re-runs. `site.ini` is written with `STATUS=provisioning`, or the existing status is kept.
2. **database**: owner and runtime roles (created if missing, passwords set from the secret files). The database is created if missing. Then: `REVOKE ALL ... FROM PUBLIC`, `pg_trgm`, schema privileges and default privileges.
3. **files**: private and temporary directories, `web/sites/org-<slug>/settings.php`.
4. **install**: `drush site:install --existing-config` as the owner, unless `INSTALLED=1` is recorded and the tables exist. The account is `admin` with the given mail. Drush receives a random throwaway password that is never shown.
5. **grants**: the runtime tables, then DML grants to the runtime role.
6. **admin**: uid 1 is **blocked, with no password** (`pass` is NULL). This happens once: a re-run never re-blocks an administrator who has already set up.
7. **publish**: a cache rebuild **as the runtime role** proves that the role can run the site. Then `web/o/<slug>` is created and `STATUS=active` is set.

There are no default or shared passwords. Access starts only when the operator runs `admin-link` after the organization's administrator has been verified (human approval, #73).

### Failure recovery

Every step is idempotent, so **the recovery is to run the same command again**. Partial state is never published, because the link and `STATUS=active` are the last step.

| Failure during | State left behind | Effect | Recovery |
| --- | --- | --- | --- |
| record, database, files | site record, perhaps roles and an empty database | `/o/<slug>/` is 404 (no link) | re-run `provision`, or `deprovision` (allowed, because the site is not active) |
| install | database with a partial install, `INSTALLED` not recorded | 404 | re-run: `site:install` empties **this site's own** database (as its owner, which cannot connect to any other) and installs again |
| grants, admin, publish | installed site, not published | 404 | re-run: the install is skipped, and the later steps complete |
| `update-all` on site N | sites before N updated, site N in maintenance mode, the sites after N untouched | the loop stops and names the updated and pending sites | fix the cause and re-run `update-all`, or roll back (below) |

There is no distributed transaction across PostgreSQL, the file system and Drupal. Compensation is the idempotent re-run, or `deprovision` for a site that never went live.

## Update procedure

`update-all` runs over the control site, then every organization with status `active` or `suspended`, in alphabetical order. It stops at the **first failure**. It refuses to start if a site file is unreadable or has an unknown status. For each site:

1. Optional `pg_dump -Fc` to `--backup-dir`.
2. Maintenance mode (runtime role).
3. `drush updatedb`, then `drush config:import`, both as the owner.
4. The runtime tables and grants.
5. A cache rebuild.
6. A **drift check**: `config:status` must report no difference from `config/sync`.
7. Maintenance mode off, unless the site is suspended.

**Release rule** (ADR-002): a release is done only when every site is updated. On a failure:

- either fix and re-run (the steps are safe to repeat);
- or roll back: deploy the previous code and restore the dumps of the sites already updated in this run.

The site that failed stays in maintenance mode until then.

## Deployment templates

- `modern/deploy/caddy/ancora.caddy`: the site block. It uses Caddy environment placeholders `ANCORA_CADDY_SITE`, `ANCORA_WEB_ROOT` and `ANCORA_FPM`.
- `modern/deploy/php-fpm/ancora.conf`: one pool for all sites, `clear_env = yes`. It carries only the shared values and the control site's `env[]` lines, plus `open_basedir`, `disable_functions` and limits.

The verification runs these exact files. Only the user, group, socket, log and path lines are replaced.

## Verification: `modern/scripts/multisite-verify.sh`

```sh
dev/sql/tools/with-ephemeral-pg.sh modern/scripts/multisite-verify.sh [--skip-composer]
```

The verification runs as follows:

- It provisions the control site, `alpha` and `beta` with the operator tool.
- It runs PHP-FPM with the pool template, and Caddy 2.11.7 with the site block. Both listen on 127.0.0.1 only. Caddy runs with `admin off`, `auto_https off` and `persist_config off`, with `XDG_DATA_HOME` and `XDG_CONFIG_HOME` inside the temporary directory, so nothing is written to the home directory.
- It turns on PostgreSQL `log_connections`, to see which role connected to which database.
- A second listener, the PHP built-in server with `scripts/lib/naive-router.php`, hands any `/o/<x>/` to the front controller. That proves the Drupal-side guards without Caddy's filtering.

The test site address accepts any `Host`, so the Host tricks reach Drupal; a production address names the host. The verification refuses to run if `web/o` or `web/sites/org-*` already exist, and deletes everything on exit.

**Result on the development workstation** (PHP 8.4.27, PHP-FPM 8.4, Caddy 2.11.7, PostgreSQL 16.15): **159 checks, 159 PASS, 0 FAIL**, in about 100 s. Provisioning one organization takes about 7 s.

| Area | Checks |
| --- | --- |
| The stack | Loopback only, admin API off, Caddy data in the temporary directory, the pool from the template |
| Routing | `/` is the control site. `/o/alpha/` and `/o/beta/` are distinct sites (site name; markers in their own databases). `/o/alpha` redirects with 308. Login form actions stay under `/o/alpha/`. Core assets are served under the prefix. |
| Unknown sites and tricks | `/o/gamma/`, `/o/gamma/user/login`, `/o`, `/o/`, `/o/Alpha/`, `/o/a/`, `/o/alpha_x/`, `/o/alpha/o/beta/`, `/o/alpha/index.php/../../beta/`, `install.php`, `update.php`, `settings.php`, `composer.json`, another site's public files: all **404**, never a Drupal page. `/o/alpha/../beta/` and its encoded variants are either refused or normalized by Caddy to beta's own page, never mixed or control content. |
| Host header | `Host: org-alpha`, `org-beta`, `default`, `evil.example`, `127.0.0.1.o.alpha`, `localhost.o.beta`, on `/` and on `/o/beta/`: refused (4xx), never an organization page. `X-Forwarded-Host`, `X-Original-URL` and `X-Rewrite-URL` change nothing. |
| Defense in depth (careless router) | `/o/gamma/` falls back to `sites/default`, which answers 404, not the control site. `Host: org-alpha` selecting `sites/org-alpha` is refused. |
| First administrator | uid 1 is blocked with a NULL password. A guessed login is refused and leaves no session. The one-time link logs in and redirects to the account form of the same site. A second use fails. |
| Sessions | The session cookie names differ, and their paths are `/o/alpha/` and `/o/beta/`. Alpha's browser is anonymous on beta. Alpha's cookie, or alpha's session id under beta's cookie name, is anonymous on beta and on the control site. The session row exists only in `tt_alpha`. |
| Admin routes | Alpha's admin gets 200 on alpha and 403 on beta's and the control site's `/admin/people`. |
| Files | Private files go to each site's own private directory, outside the web root. Beta's private path and the control site's never return alpha's file. Public files exist per site, and another site's files are 404. |
| Caches | Alpha's admin render of the Views listing never reaches anonymous users or beta. An anonymous page-cache HIT on alpha leaves beta and the control site unchanged. |
| Databases | Web requests connected to `tt_control`, `tt_alpha` and `tt_beta`, each with its own `_app` role. No web connection used an `_owner` role. PostgreSQL logged no `permission denied`, and PHP logged no error. |
| Roles | `tt_alpha_app` connects to `tt_alpha`, but not to `tt_beta` or `tt_control` (PostgreSQL's CONNECT privilege refuses it). The same holds for the other roles. PUBLIC has no CONNECT on any site database. The runtime role cannot `CREATE` or `DROP` tables, but can write rows. No site role is a superuser or has `CREATEDB` or `CREATEROLE`. File modes are 0640/0600. No secret is in the web root. |
| Provisioning | A re-run is idempotent: no reinstall, markers kept, the admin session survives, the admin is not re-blocked. Invalid slugs are refused. A failure injected after the install leaves the site unpublished (404, `provisioning`), and a re-run recovers it. A failure during the database step can be cleaned up with `deprovision`. |
| Suspend and retire | A suspended site answers 503 with no login form. An active site cannot be deprovisioned. After a resume the site answers 200. Deprovision takes a final backup, then the site is 404 and its database, roles and files are gone. The audit log records it. |
| Update loop | `update-all` updates the control site, alpha and beta, with backups. When alpha's owner role cannot log in, the loop stops at alpha: control was updated, beta not attempted, alpha stays in maintenance mode (503) and beta keeps serving. After the fix, the loop succeeds. |

**CI** (`.github/workflows/modern.yml`, job `multisite`):

- Runs the same verification on PHP 8.4 + PostgreSQL 16 and PHP 8.5 + PostgreSQL 18.
- Uses PHP-FPM from the runner, or `php<version>-fpm` from apt.
- Uses the official Caddy 2.11.7 static build, pinned by its release SHA-512.

**Not proven here.** The disposable cluster authenticates with `trust` over its socket, so the role checks prove CONNECT and object privileges, not passwords. On the host, `pg_hba.conf` with `scram-sha-256` (or `peer`) adds the password layer.

There is no positive private-file download test. The `file` module is not part of `config/sync` yet, so no site grants private file access to anyone, and the checks prove only that one site's private files never reach another site. When file fields arrive (#70), add an authorized download on alpha and the same denial on beta.

## Limits and open risks

- **One PHP-FPM pool reads every site's runtime secrets.** A code execution bug in any site can read every organization's `site.ini`, runtime password and salt. The owner passwords are not exposed: they are 0600 and readable only by the operator.
  - Mitigation available now: one pool per organization, each with its own Unix user and `open_basedir`, and the `php_fastcgi` socket chosen per slug in the Caddy block.
  - Cost: one pool per site.
  - To decide (#76).
- **Same origin.** All sites share one origin. Cookie paths stop the browser from sending a session to another site. They are not a boundary against script running in the page: an XSS in one site could call another site's pages with the victim's cookies for that site. Mitigations:
  - strict output escaping (Twig autoescape);
  - a Content-Security-Policy, to add with the theme work;
  - Drupal's `SameSite=Lax` cookies.

  Subdomains would remove this risk, but were rejected in ADR-002 for TLS and DNS reasons. Revisit if any site accepts untrusted HTML.
- **Control site cookies have path `/`.** They are sent to `/o/...` requests too. Their name differs, so organization sites ignore them.
- **The maintenance database is not hardened.** `postgres` keeps PUBLIC CONNECT; the site roles can connect to it, but have no privileges there. Revoking it is a host-wide change that would affect other applications on the shared host (#76).
- **Runtime tables.** A future module that creates tables lazily must be covered by `runtime-tables`. The update loop runs it, and the verification fails loudly if it is missed.
- **Public files** of an organization are public by URL (`/o/<slug>/sites/org-<slug>/files/...`). Anything confidential must use the private scheme.
- **Scale.** Each site costs a database, about 40 tables, a site directory and a step in the update loop. ADR-002 expects tens of organizations.

## What the private deployment on the host will need (not done here)

- **Users:**
  - a Unix user and group for the pool, for example `ancora`;
  - Caddy in the socket group;
  - the operator runs the tool as root, or as a dedicated operator user, with `ANCORA_WEB_GROUP=ancora`.
- **Directories:**
  - `/etc/ancora/control` and `/etc/ancora/sites`: root-owned, group `ancora`;
  - `/var/lib/ancora/{control,sites}`: group `ancora`, writable;
  - `/var/log/ancora`;
  - a checkout with `composer install --no-dev`.
- **PostgreSQL 18:**
  - an administrative connection for the operator (peer as `postgres`);
  - `pg_hba.conf` lines restricting each `tt_*` database to its own roles (`scram-sha-256` over the socket or loopback);
  - consider `REVOKE CONNECT ON DATABASE postgres FROM PUBLIC` if the other tenants of the host allow it.
- **PHP-FPM:** `deploy/php-fpm/ancora.conf` in `/etc/php/8.5/fpm/pool.d/`, with the real paths, `ANCORA_TRUSTED_HOSTS` and the control site's names. Then reload PHP-FPM.
- **Caddy:** the site block with `ANCORA_CADDY_SITE=timetables.divengine.com`, `ANCORA_WEB_ROOT` and `ANCORA_FPM` set in Caddy's environment (systemd `Environment=`), or substituted literally. It takes precedence over the wildcard, and needs a normal certificate.
- **Install:** `ancora-site.sh control-install`, then `provision` per approved organization, then `admin-link` after verification.
- **Operations:**
  - `update-all --backup-dir ...` in each release;
  - cron per site (`drush --uri=<site> cron`, a loop like `update-all`; not scripted yet);
  - backups per database.

## Still to decide

- One FPM pool or a pool per organization (secret isolation versus memory), with the capacity decision (#76).
- Where the audit record lives. A file today; ADR-002 wants it on the control site, with the R0 entities (#73).
- The engine databases `tt_<slug>_engine` and their roles (#71). The tool's `db_ensure` is the place to add them.
- Per-site cron scheduling and its failure policy.
- Slug reuse after retirement. Allowed today; the old cookie names would be reused, but their sessions are gone.
- A Content-Security-Policy for all sites.

## LBR impact

**0 bytes retired.** New modern infrastructure only; no legacy file is moved or changed.
