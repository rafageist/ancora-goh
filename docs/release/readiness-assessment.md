# Self-hosted release readiness assessment (#145)

Issue: #145. Charter: #142 (decision 1 "open source first, distributed as-is", decision 8 "public hosting is optional", gate D5 "usable open-source release"). Cross-references: #143 (workload ladder, `docs/evidence/workload-assessment.md` on PR #149), #144 (interoperability contract).

**This is an assessment and a specification. It implements nothing.** It adds no packaging, no deployment, no engine or schema change, and creates no issue. Every proposal below needs a separate, approved issue.

Baseline: `master` at `f299f10` (2026-11-01). PR #148 (S1b) and PR #149 (#143) were open and are not assumed merged.

Evidence labels:

- **Executed**: run for this assessment on disposable infrastructure; results in §2.
- **Verified**: read in the tracked tree; cited as `file:line` or `file` §section.
- **Documented**: stated by a repository document, not re-run here.
- **Unknown**: no evidence either way.

---

## 1. Summary

Áncora GOH contains two applications:

1. the **legacy PHP application** (`control/`, `model/`, `view/`, `www/`, `lib/`), kept as the oracle. It is the only one with a packaged installer (Docker Compose, `compose.yaml`), and that installer is explicitly **local use only** because the legacy code still has SQL injection, CSRF and escaping gaps (`compose.yaml:3-6`, `docs/deployment/docker.md:5`, §"Security limits");
2. the **Drupal 11 application** (`modern/`) with the PostgreSQL MPI/Prioriza engine run by a Drupal worker. This is the product that #142 means. Its installation is well tested, but only through **test harnesses** and a **multisite operator tool designed for the private host**. There is no packaged, documented, single-installation path for a third party.

**Verdict: not releasable yet as a self-hosted product.** The code paths a self-hoster needs mostly exist and are reproducible on a clean system without any maintainer credential (executed, §2). What is missing is the operator-facing layer around them, plus the product gaps of export and import.

Top blockers (full list in §9):

| # | Blocker | Evidence |
|---|---|---|
| B1 | No supported installation path for the Drupal application. `install-disposable.sh` is a test harness (random secrets in a temporary directory, deleted on exit); `ancora-site.sh` assumes the control-site + `/o/<slug>/` multisite topology of the private host; no install guide for a third party exists. | `modern/scripts/install-disposable.sh:49-58`; `docs/modernization/drupal-multisite.md:261-282` ("What the private deployment on the host will need (not done here)") |
| B2 | No worker service definition. The worker must run continuously for any timetable to be generated; the docs require a systemd unit with `MemoryMax`, `CPUQuota` and `RuntimeMaxSec`, but the repository ships none, and no cron for Drupal either. | `docs/modernization/engine-worker.md:92`; `docs/modernization/r1-scheduling-journey.md:289`; `docs/modernization/drupal-multisite.md:281,289`; no `*.service`/`*.timer` file is tracked |
| B3 | No engine upgrade path. `update-all` updates Drupal but never the engine installed in schema `scheduler`; any release that touches `dev/sql/**` or `dev/workspace/**` cannot reach an existing installation. | `docs/modernization/tenant-provisioning.md:163` |
| B4 | No timetable export and no data import for an operator. Only a synthetic fixture loader and an internal engine-row export exist. | `docs/modernization/r1-scheduling-journey.md:285`; `ancora-academic:load-fixture`, `ancora-academic:export --legacy` (`AcademicCommands.php`); #144 |
| B5 | No editor role is shipped: `config/sync` has only `anonymous` and `authenticated`. A new installation has an administrator and nothing to give planners. | `modern/config/sync/user.role.*.yml`; `docs/modernization/r1-scheduling-journey.md:290`; #73 |
| B6 | The legacy application and its unauthenticated debug pages are in the same tree, and the only packaged installer installs the legacy application. A release must say clearly what is shipped and what is not. | `www/test.php`, `www/test2.php` (`docs/modernization/security-privacy.md:112`); `compose.yaml` |
| B7 | Release governance files are missing: no `SECURITY.md` (security contact), no `CONTRIBUTING.md`, no `NOTICE`/third-party notices file, no `CHANGELOG`, no tag and no GitHub release. | `git ls-files`; `git tag` and `gh release list` are empty |
| B8 | Historical personal data is tracked and redistributed with every clone (`dev/backup/ancoragoh.{sql,custom,tar}`), and the decision on it is still open for the release. | `docs/modernization/security-privacy.md:37-38,111` |

---

## 2. What was executed

All runs used `dev/sql/tools/with-ephemeral-pg.sh` (a fresh `initdb` cluster in a temporary directory, Unix socket only, `listen_addresses=''`, deleted on exit). No TCP port 5432, no `16/main` cluster, no production host and no real credential was used. Each command was wrapped in `/usr/bin/time -v`.

Workstation: 2 vCPU, 3 GiB RAM (+3 GiB swap), Ubuntu 24.04, PHP 8.4.27, PostgreSQL 16.15, Caddy 2.11.7, PHP-FPM 8.4, Composer 2.

| # | Command | Result | Wall time | Largest process RSS |
|---|---|---|---|---|
| E1 | `composer install --no-interaction` in `modern/` (with dev dependencies, from `composer.lock`) | OK; `vendor/` 174 MB, `web/core` 161 MB | 0:23 | 95 MB |
| E2 | `with-ephemeral-pg.sh modern/scripts/install-disposable.sh --skip-composer` | **all checks passed**: `site:install --existing-config` (Drupal 11.4.11), configuration export equals `config/sync` (91 files), engine in workspace mode, synthetic fixture (8 demands), phpcs, **PHPUnit Kernel + Functional 156 tests, 7542 assertions, OK** (2 notices), **HTTP smoke 25/25** | 9:19 (site install 6 s; tests 8:50) | 159 MB |
| E3 | `with-ephemeral-pg.sh modern/scripts/multisite-verify.sh --skip-composer` (control site + 2 organizations behind the real Caddy and PHP-FPM templates) | **169 PASS, 0 FAIL** (routing, Host-header tricks, sessions, files, caches, database roles, first administrator, provisioning recovery, suspend/deprovision, `update-all` failure and recovery); no leftover `web/o` or process | 1:48 | 122 MB |
| E4 | `with-ephemeral-pg.sh modern/scripts/tenant-verify.sh --skip-composer` (single-database tenants, non-superuser operator, a job per site, isolation, recovery) | **84 PASS, 0 FAIL** (non-superuser operator `LOGIN CREATEDB CREATEROLE`; control site and tenants with one database each; one job per site reproduces the E2E digest; isolation during a run; killed-worker recovery; two-database to single-database migration and rollback) | 2:16 | 122 MB |
| E5 | Sizing run (scratch script, inside `with-ephemeral-pg.sh`): site install only, then `dev/workspace/install.sh`, then `dev/sql/install.sh --with-data` | site database 13 MB after `site:install`; 16 MB after the engine (154 engine relations); standalone engine database 10 MB; whole cluster data directory 72 MB | site install 7 s; engine install < 1 s; standalone engine 1 s | 122 MB |
| E6 | `docker/e2e.sh` (legacy application, separate Compose project `ancora-goh-e2e-<random>`, removed on exit with its volume and images) | **55 PASS, 0 FAIL** (idempotent `init-db`, HTTP scheduling flow, loopback-only exposure, internal database network, no secret in images, persistence across `down`/`up`); afterwards the project's containers, volume, network and images were gone and the pre-existing Docker containers, volume and image list were unchanged | 3:07 | — |
| E7 | `composer licenses` (`modern/`), `composer.lock` of `dev/codegen`, `dev/browser/package-lock.json`, license headers of `www/js/**` | §4 | — | — |

What these runs prove: on a clean system with PHP 8.4, Composer and PostgreSQL 16, the Drupal application, its configuration, the engine (both layouts), the worker, the multisite routing and the operator tool all install and pass their tests **without any maintainer-specific host, credential or private service**. Network access was needed only for Composer packages (packages.drupal.org, Packagist) and, for E6, Docker Hub.

What they do not prove: a long-running installation (worker under a service manager, cron, log rotation, TLS, upgrades between releases), password authentication in `pg_hba.conf` (the disposable cluster uses `trust` over its socket, `docs/modernization/drupal-multisite.md` §"Not proven here"), PostgreSQL 18 or PHP 8.5 on this workstation (CI covers them, `.github/workflows/modern.yml:59-61`), and any realistic workload size (#143 G1).

---

## 3. Clean-system installation audit

### 3.1 Component by component

| Component | Path today | Reproducible on a clean system? | Notes |
|---|---|---|---|
| Composer / Drupal code | `modern/composer.json`, `composer.lock`; `composer install` | **Yes** (E1). PHP `^8.4` (`modern/composer.json:18`), Drupal 11.4, Drush 13, no contrib modules | A production build should use `--no-dev`; documented in `drupal-foundation.md` §"Upgrade policy", not exercised here |
| Drupal schema and configuration | `drush site:install --existing-config` from `modern/config/sync` | **Yes** (E2: 6 s; export equals `config/sync`) | Only through the harness or `ancora-site.sh install_site`; no operator guide for one site |
| Engine, two-database layout | `dev/sql/install.sh [--with-data] <db>` | **Yes** (E5, and CI `db-install.yml`) | Refuses anything but a disposable cluster unless `--admin-install` + `AGOH_ADMIN_INSTALL_CONFIRM=<db>` + explicit `PGHOST` (`dev/sql/install.sh:11-15`). Labelled "not an upgrade path" (`:22`) |
| Engine, single-database workspace layout | `dev/workspace/install.sh [--worker <role>] [--owner <role>] [--admin-install] <db>` | **Yes** (E2, E4, E5) | One transaction; re-run is the recovery (`dev/workspace/install.sh:8-9`). New sites use this layout (`ancora-site.sh:73-78`) |
| Worker | `drush ancora-engine:work [--once|--max-jobs|--max-runtime|--idle-sleep]`; `ancora-site.sh work <slug>` (`ancora-site.sh:778-783`) | **Yes as a command** (E2, E4) | **No service definition** in the repository (B2). No cron runner for Drupal either |
| File permissions | `install-disposable.sh` (0600 secrets in a temporary dir); `ancora-site.sh secret()` (0640 runtime / 0600 owner, `ANCORA_WEB_GROUP`) | **Yes** in the tool (E3 checks file modes 0640/0600 and "no secret in the web root") | No documented owner/group layout for a single-server install other than the host section of `drupal-multisite.md:263-272` |
| Runtime configuration | `modern/settings/ancora.settings.php` (environment `ANCORA_*`, secrets via `*_FILE`); `ancora.org.settings.php` (per-site `site.ini`) | **Yes**; fails closed on missing DB name, user, salt, or trusted hosts in production (`ancora.settings.php:36-38,159-171`) | Good basis. Missing: one reference file listing every variable for a self-hoster (the list is spread over `ancora.settings.php:13-50`, `engine-worker.md` §6 and `ancora-site.sh:23-46`) |
| Multisite, Caddy and PHP-FPM | `modern/deploy/caddy/ancora.caddy`, `modern/deploy/php-fpm/ancora.conf`; `multisite-verify.sh` runs them | **Yes** (E3) | Templates carry host values (§3.2). Only the multisite topology is templated; there is no single-site template |
| Backup | `ancora-site.sh update-all --backup-dir` and `deprovision --backup-dir` take `pg_dump -Fc` | Partial | No standalone `backup` command, no scheduled backup, no files backup (private files directory) |
| Restore | none for Drupal sites | **No** | `drupal-multisite.md` §"Update procedure" says "restore the dumps" without a command. The legacy Compose stack has `docker/manage.sh restore` |
| Upgrade | `ancora-site.sh update-all` (updatedb, config:import, drift check, maintenance mode) | Partial (E3 runs it, including a failure and a re-run) | **Engine not upgraded** (B3). No release-to-release upgrade test |
| Remove | `ancora-site.sh deprovision <slug> --backup-dir <dir> --confirm <slug>` | **Yes** for an organization (E3) | No removal of the control site or of the whole installation |
| Two-database to single-database migration | `ancora-site.sh migrate-single-db`, `rollback-single-db`; `docs/modernization/tenant-migration.md` | **Yes** (E4 sections 6–9) | Only relevant to the private demo, which is the only two-database site |

### 3.2 Maintainer-specific assumptions

None of these is a secret, and none blocks the disposable runs. They block a third party who follows the templates literally.

| Location | Assumption | Portable? |
|---|---|---|
| `modern/deploy/php-fpm/ancora.conf:104-106,120,136,140,147-154` | `/srv/ancora` (checkout), `/etc/ancora` (secrets), `/var/lib/ancora` (files), `/var/log/ancora`, PHP **8.5** socket path `/run/php/php8.5-fpm-ancora.sock`, Unix user `ancora`, Caddy group `caddy`, `tt_control` database names | Template with a comment to replace them; `ANCORA_TRUSTED_HOSTS = SET_BY_MAINTAINER` (`:146`) |
| `modern/deploy/caddy/ancora.caddy:6-8` | Example paths and the PHP 8.5 socket | Placeholders (`{$ANCORA_CADDY_SITE}` etc.); portable |
| `modern/scripts/ancora-site.sh:23-46` | Operator environment, examples `/etc/ancora/sites`, `/var/lib/ancora/sites` | Portable (variables) |
| `docs/modernization/drupal-multisite.md:261-282` | The private host: PostgreSQL 18, PHP 8.5, `timetables.divengine.com` | Host-specific by design |
| `docs/modernization/runbook-demo-single-db.md` | The private `demo` site, operator role `ancora_operator`, 1 vCPU / 1 GiB host | Private demo runbook; not for self-hosters |
| `docs/saas/**` | `studio.divengine.com` host assessment, the SaaS threat model for `timetables.divengine.com` | Private / optional-hosting material (#142 decision 8) |
| `docs/modernization/drupal-foundation.md` §"Upgrade policy" | "The CI matrix always contains the production host's versions" | The support matrix is defined by the private host |
| `docs/ux/img/demo-*.jpg` | Screenshots of the private demo | Documentation only |

No hard-coded credential, private URL or private service is required at runtime by `modern/**`, `dev/sql/**` or `dev/workspace/**` (verified with `git grep` for `divengine.com`, `/etc/ancora`, `/srv/ancora`: matches are only in the templates, docs and the test stack helper `modern/scripts/lib/stack.sh`).

### 3.3 Private-demo tooling versus portable release

| Private demo or private-host only | Portable, reusable for a release |
|---|---|
| `runbook-demo-single-db.md`; `migrate-single-db` / `rollback-single-db` (only the demo is two-database) | `modern/` application, `config/sync`, `modern/settings/*.php` |
| Control-site + `/o/<slug>/` topology, `admin-link` approval flow, `suspend`/`resume`, audit log for approved organizations (#72, ADR-002) | `dev/sql/install.sh`, `dev/workspace/install.sh`, the worker command |
| `docs/saas/**`, the threat model of the free SaaS | `ancora-site.sh provision`/`update-all`/`deprovision`, reusable for one organization |
| Host values in the PHP-FPM pool | Caddy and PHP-FPM templates once parameterized |
| Screenshots of the private demo | Harnesses: `install-disposable.sh`, `multisite-verify.sh`, `tenant-verify.sh`, `r1-journey.sh`, `docker/e2e.sh` |

A self-hoster can use the multisite tool with **one** organization: `control-install`, then `provision <slug>`, then serve `/o/<slug>/`. That works today (E3, E4), but the control site is then an empty extra site, and the URL carries `/o/<slug>/`. The control site gets no engine (`workspace_ensure` runs only in `provision`, `ancora-site.sh:411-413`), so scheduling on `/` alone needs the `ANCORA_ENGINE_*` variables set by hand, as `install-disposable.sh:136-141` does. Neither variant is documented for a third party. Decision D-1 (§15).

### 3.4 Minimum resources

Measured here (E1–E6) and documented elsewhere. **No claim beyond these numbers.**

| Quantity | Value | Source |
|---|---|---|
| Disk, code | repository without `.git` 372 MB, of which `modern/vendor` 174 MB and `modern/web/core` 161 MB (with dev dependencies) | E1 |
| Disk, database after site install | 13 MB | E5 |
| Disk, engine in the site database | +3 MB, 154 relations (documented: about +10 MB, `runbook-demo-single-db.md`; each running job adds a workspace of about 1–1.5 MB, `single-db-workspace.md` §8) | E5 |
| Site install time | 6 s (`site:install --existing-config`) | E2 |
| Engine install time | < 1 s (documented 0.8–1.1 s, `single-db-workspace.md` §8) | E5 |
| Memory, largest single process during install and tests | 159 MB (PHPUnit functional run) | E2 |
| PHP-FPM | template: `pm.max_children = 4`, `memory_limit = 256M` (`ancora.conf:125,130`) | verified |
| Smallest host in use | 1 vCPU, about 1 GiB RAM, PostgreSQL 18, one job per site at a time | documented, `runbook-demo-single-db.md`, `tenant-provisioning.md:164` |
| Generation time and memory at realistic sizes | **unknown**; the job time limit defaults to and is capped at 120 s (`EngineSettings.php:63-64`) | #143 G1 |
| Concurrent users | **unknown** | — |

Proposed published minimum for the first release, labelled as such: 1 vCPU, 1 GiB RAM, 2 GB disk for code, database and backups of a small site; "larger institutions: unknown until the #143 ladder is measured".

---

## 4. License and assets

### 4.1 Declared license

- `LICENSE`: the unmodified GNU GPL version 3 text.
- `docs/licensing.md`: **GPL-3.0-only**, with the reasoning (no "or any later version" grant in the original projects) and the rule that changing to `-or-later` is a decision of the copyright holder(s).
- `modern/composer.json:5` and `dev/codegen/composer.json:5`: `"license": "GPL-3.0-only"`.
- Consistent. **No change proposed or made.**
- Source availability: public repository; GPLv3 §6 is met by the repository for source distributions. A binary artifact (container image, tarball with `vendor/`) must carry or point to the corresponding source for the exact version (§12).

### 4.2 Dependency licenses (E7)

| Set | Result | Compatibility with GPL-3.0-only |
|---|---|---|
| `modern/` runtime (`composer licenses --no-dev`) | 88 packages: MIT 73, GPL-2.0-or-later 6 (Drupal core, Drush, scaffold, recipe-unpack, webflo/drupal-finder, chi-teck/drupal-code-generator), BSD-3-Clause 6, BSD-2-Clause 3 | compatible |
| `modern/` with dev | 174 packages: adds MIT, BSD-3-Clause, GPL-2.0-or-later (`drupal/coder`, `drupal/core-dev`) and **Apache-2.0** (8: `open-telemetry/*`, `tbachert/spi`, `lullabot/php-webdriver`) | Apache-2.0 is compatible with GPLv3; dev only, not shipped by `--no-dev` |
| `dev/codegen` (lock file) | runtime `divengine/div`, `divengine/functions` GPL-3.0-or-later; dev 28 packages MIT / BSD-3-Clause | compatible; build-time only |
| `dev/browser` (npm, test-only) | `@playwright/test`, `playwright`, `playwright-core` Apache-2.0; `@axe-core/playwright`, `axe-core` **MPL-2.0** | test tooling, never distributed (`dev/browser/package.json` "description") |
| Legacy `www/js/**` | 48 files carry an MIT/GPL or MIT header (jQuery, jQuery UI, plugins), 1 BSD/MIT; **44 third-party files carry no header** (`www/js/jquery/ui/i18n/*.js` ×41, `jquery-ui-i18n.js`, `external/jsdiff/jsdiff.js`, the treeview demo); the other 48 header-less files are the application's own (`www/js/app/**`) | Already flagged "to be audited before any redistribution of the legacy UI" (`docs/licensing.md`) |
| `lib/parsers/json/JSON.php` | PEAR Services_JSON, BSD-style header | compatible |

### 4.3 Notices, contribution and security contacts

- No `NOTICE` or third-party notices file. Drupal core and the jQuery files carry their own notices in place. A binary artifact that bundles `vendor/` should ship the `composer licenses` output (Q-L3).
- No `CONTRIBUTING.md`, no Developer Certificate of Origin or CLA statement, no `CODE_OF_CONDUCT.md`. Only `.github/pull_request_template.md`.
- No `SECURITY.md`; no private vulnerability reporting channel is documented. `modern/composer.json` "support" lists only issues and source.
- No `AUTHORS` or copyright header convention for `modern/**` (the custom modules carry no copyright line).

### 4.4 Historical assets

| Asset | Status | Question |
|---|---|---|
| `dev/backup/ancoragoh.{sql,custom,tar}` (2.7 MB) | 2010 dump with a user password hash, 9 personal names and about 350 linked rows; "private historical evidence, not suitable for redistribution" (`security-privacy.md:37-38`); tracked since `b42aa03`; excluded from Docker images (`.dockerignore`) | Q-L1 |
| VB6 `.anc` faculty datasets in `rafageist/ancora-vb6` | "keep private", public repository (`security-privacy.md:47`; #143 DEC-7) | Q-L2 (outside this repository) |
| `www/images/**` (104 files), `www/images/wallpapers/background*.jpg` (11), `www/images/emo/*.gif` | No provenance recorded; wallpapers may be third-party photographs | Q-L4 |
| `www/js/**` third-party JS without headers | see §4.2 | Q-L5 |
| `docs/ux/img/*.jpg` (13) | screenshots of the private demo and disposable runs, synthetic data | low risk; confirm no real names (they show fixture names) |
| VB6 references in docs (`docs/05-comparacion/vb6-php.md`, `timetable-editing.md`) | citations of `rafageist/ancora-vb6` by path and line | none; no VB6 code is in this repository |
| `www/test.php`, `www/test2.php` | unauthenticated pages in the web root (`security-privacy.md:112`); excluded from the Docker image | not shipped by Drupal; release artifact must exclude the legacy tree or these files |

### 4.5 Open license and asset questions (not resolved here)

- **Q-L1** Does the self-hosted release (source archive, tags) keep `dev/backup/**` dumps? They are in every commit since `b42aa03`; any source tarball made with `git archive` includes them unless excluded by `.gitattributes export-ignore`. Removal from history is option B/C of `security-privacy.md` §4 (not chosen).
- **Q-L2** Exposure of the VB6 faculty datasets (#143 DEC-7).
- **Q-L3** Which notices file the binary artifacts carry (proposal: generated `THIRD-PARTY-NOTICES` from `composer licenses --no-dev` plus the Drupal core `LICENSE.txt`).
- **Q-L4** Provenance and license of `www/images/wallpapers/*` and other legacy images, if the legacy UI is ever redistributed.
- **Q-L5** Per-file audit of the legacy jQuery UI i18n files and `jsdiff.js`, same condition.
- **Q-L6** Contribution terms: inbound = outbound GPL-3.0-only with a DCO sign-off, or a CLA. A CLA would matter only if a future license change is wanted; #142 decision 1 does not ask for one.
- **Q-L7** Copyright holder statement for the 2026 modernization (single author, or "Áncora GOH contributors").

---

## 5. Security posture for a self-hoster

### 5.1 Authentication and authorization

- Drupal core accounts and sessions; `register: admin_only`, e-mail verification on, password strength on (`modern/config/sync/user.settings.yml`).
- Permissions by action are documented in `docs/modernization/r1-scheduling-journey.md` §"Permissions" and proposed in `permission-matrix-proposal.md`. Records are owner-scoped: a planner sees only plans they own; administrators see all.
- Access tests (cited, run in E2): `ancora_academic` `AcademicAccessTest::testOwnerFieldAccess`, `AssignmentEditorUiTest::testAccess`, `BreakdownTest::testAccess`, `PeriodInputsTest`/`TimeRestrictionTest`/`SharedResourcesTest`/`RoomConstraintsTest::testAccessAndCascade`; `ancora_scheduling` `PlanWorkspaceUiTest::testAccess` (forged or foreign values 404, another owner's plan 403, `:178,241,252`), `PlacementMoveUiTest::testRefusalsAndAccess`, `SchedulingPagesTest` (another user does not see the plan, `:56`), `TimetableExplorerTest::testAccessAndSelectors`, `CrossPlanConflictDetectorTest::testAccessIsolationAndReadOnly`, `ViewerTest::testAnonymousDenied`/`testAccessPolicy`; `ancora_engine` `JobPagesTest::testPermissionsAndQueue`/`testCancelAccess`; `ancora` `StatusPageTest::testRouteAccess`; `http-smoke.sh` anonymous 403 checks.
- Gap: no shipped planner/editor role (B5).

### 5.2 Private-data isolation per deployment mode

| Mode | Isolation | Evidence |
|---|---|---|
| One site (one organization) | Owner-scoped records inside the site; Drupal permissions | tests above |
| Multisite, one database per organization | separate databases, owner and runtime roles per site, CONNECT revoked from PUBLIC, per-site salt, files and session cookie paths; Host-header and path tricks refused | E3 (`multisite-verify.sh`, 169/169 PASS) |
| Engine workspace mode | per-job schema and NOLOGIN job role per tenant; other tenants' worker and runtime roles cannot connect or `SET ROLE` | E4 (`tenant-verify.sh` section 3) |
| Known residual risks | one PHP-FPM pool reads every site's runtime secrets; same origin for all sites; the web pool can read the engine role password | `drupal-multisite.md:245-258`; `r1-scheduling-journey.md` §"Open risks" |

For a single-organization self-hoster, the residual multisite risks do not apply.

### 5.3 Generation job limits

- Generation never runs in a web request; only the CLI worker runs `generar()` (`engine-worker.md:92`).
- Hard stop `statement_timeout` = job time limit, default 120 s and maximum 120 s (`EngineSettings.php:63-64`); wall-clock guard limit + 10 s; cooperative cancellation, then `pg_cancel_backend` after `cancel_grace` 15 s; `max_attempts` 2; heartbeat and stale recovery (`engine-worker.md` §§3–6).
- One job per site per worker process; cross-site concurrency and quotas are not implemented (`engine-worker.md:171`).
- Process limits (memory, CPU) are expected from the service manager, which is not shipped (B2).

### 5.4 CSRF, XSS, IDOR

- **CSRF:** state changes go through the Drupal Form API (form tokens). The one non-form state-changing route, `ancora_academic.order.move`, has `_csrf_token: 'TRUE'` (`ancora_academic.routing.yml:103-109`); `OrderUiTest` checks a token-less request by another planner is 403 (`:69-71`). There is **no explicit negative test** that a valid user without a token is refused; Drupal core covers the mechanism.
- **XSS:** Twig autoescape; no `|raw` and no `Markup::create` in custom code (verified with `grep`). There is **no XSS payload test** and **no Content-Security-Policy** (`drupal-multisite.md:289`). Response headers checked: `X-Content-Type-Options`, `X-Frame-Options` (`http-smoke.sh:49-50`).
- **IDOR:** forged and foreign ids give 404 or 403 and record nothing (`PlanWorkspaceUiTest:178-252`; `PlacementMoveUiTest::testRefusalsAndAccess`; cross-site ids 404 by design, `r1-scheduling-journey.md` §"Permissions").
- **Legacy application:** SQL injection, missing CSRF and escaping remain (`docs/deployment/docker.md` §"Security limits"). It is not suitable for exposure; a release must not ship it as a product.

### 5.5 Default credentials

**None found in any install path.**

- Docker Compose: both database passwords are random Docker secrets created by `docker/manage.sh init-secrets`, git-ignored; the legacy seed admin has an unusable password until `set-admin-password` (`docs/deployment/docker.md` §"Initial administrator"); legacy MD5 login and the anonymous viewer are off (`bin/security.conf:25,34`).
- `install-disposable.sh`: random admin password in a 0600 file, never printed (`:63-66,97-120`).
- `ancora-site.sh provision`: the first administrator is blocked with no password until `admin-link` issues a one-time login (`ancora-site.sh:445,464`; E3 "First administrator").
- Fixtures (`dev/e2e/*.sql`, `SyntheticFixture.php`): no accounts or passwords.
- History: credentials C1–C4 remain in Git history and are treated as compromised (`security-privacy.md` §4, option A "rotate"). A self-hoster is not affected unless they reuse those values.

### 5.6 HTTPS, errors, logging

- **HTTPS:** the Caddy template obtains certificates automatically when `ANCORA_CADDY_SITE` is a public name; there is no HSTS header in the template and no documented TLS guidance for other servers (Apache, nginx). `ANCORA_REVERSE_PROXY_ADDRESSES` exists for proxies (`ancora.settings.php:181-185`).
- **User-facing errors:** production hides PHP errors (`ancora.settings.php:194-198`; PHP-FPM `display_errors off`); Caddy errors are plain `status text` (`ancora.caddy:98-101`); missing configuration fails closed with an exception, not the installer; a suspended site answers 503.
- **Logging:** Drupal `dblog` (in the database; no rotation policy documented beyond core's row limit); PHP errors to `/var/log/ancora/php.log` in the pool template; operator actions to `$ANCORA_SITES_DIR/audit.log`; job history in Drupal job entities. No `syslog` option, no log rotation file.
- **Known limitations to publish:** §5.2 residual risks, 120 s job limit, no CSP, no export, no import, unknown capacity (#143 G1), #143 G2–G7 correctness caveats (a "final" timetable can have overflow, cross-plan conflicts are warnings only, some legacy constraint kinds are inert).

---

## 6. Operator journey

Status: **exists** (implemented and tested), **partial** (implemented for some cases, or only as a developer/operator command), **missing**.

| Step | Status | What exists | Gap |
|---|---|---|---|
| Install | partial | harnesses and `ancora-site.sh` (E2–E4) | B1, B2; no guide |
| Configure | partial | environment and `*_FILE` settings, fail closed | no single reference of variables; no planner role (B5) |
| Create resources, activities, restrictions | exists (partially complete model) | Drupal entities and forms: plans, periods, catalogues, assignments and templates, breaks, group rooms, load limits, priority zones, restrictions, shared resources, travel costs (`/ancora/planning/*` routes) | some legacy constraint kinds not modelled or inert (#143 G7) |
| Import | **missing** | only `drush ancora-academic:load-fixture` (synthetic) and the private two-database migration | #144 |
| Run the scheduler | exists | request form `/ancora/scheduling/request`, job pages, worker | service definition (B2); 120 s limit |
| Visualize | exists | timetable page, explorer, viewer, plan workspace (`/ancora/planning/{plan}/workspace`) | S1b (PR #148) in review |
| Adjust placements | partial | move one placement with server-side validation (#130); undo per `timetable-editing.md` §9 | swap and fixed activities deferred (#138) |
| Validate | partial | job validation, legacy validation, cross-plan conflict warnings | no "publishable" rule (#143 §2.6, G2) |
| Publish | partial | placements become entities on a final job (`PlacementPublisher`) | no draft/approved/published states for consumers (#144) |
| Export | **missing** | `ancora-academic:export --legacy` is the engine input, not a timetable export; no PDF, CSV, iCalendar or print (`r1-scheduling-journey.md:285`) | #144 |
| Back up | partial | `pg_dump` inside `update-all` and `deprovision` | no backup command, no files backup, no schedule |
| Restore | **missing** (Drupal) | — | |
| Upgrade | partial | `update-all` (Drupal) | engine (B3); no versioned release to upgrade between |
| Remove | partial | `deprovision` of one organization | no full uninstall procedure |

---

## 7. Demo workload specification (quick start)

Goal: a privacy-safe dataset that a new operator loads in one command, sees generate in seconds, and can explore. It must not use any historical record (`security-privacy.md` §3; #142).

- **Source:** generated from the schema only, never derived from `dev/backup/**` or `.anc` files. Names follow the existing convention (`Profesor 01`, `Aula A1`, `Grupo 1A`; English labels in Drupal: "Teacher Alpha"); addresses only `example.org`.
- **Size:** #143 rung **L1 small**, feasible variant F (`workload-assessment.md` §3.3): 1 plan, 1 period, 5 days × 6 slots, 1 scope, 4–6 subjects, 2–4 brigades, 20–60 demands, 4–8 teachers, 4–6 rooms, ≤ 5 % restriction density, tightness ≤ 0.5. The existing synthetic fixture (8 demands, L0) stays the smoke fixture.
- **Shape:** seeded and deterministic (generator version, rung, variant, seed) with a SHA-256 manifest, as #143 §4.4 and §6 propose; the quick-start dataset is one fixed seed of that generator, not a second generator.
- **Delivery:** loaded through the Entity API like `ancora-academic:load-fixture`, under an explicit owner; refuses to load into a site that already has data unless asked; removable.
- **Acceptance:** generates within the default 120 s limit on the minimum host; every invariant of the L1 sidecar holds (#143 §3.5); the placement digest is identical across two runs.
- **Later:** an L2 "medium" demo for evaluators and the L3 complex workload, after #143 DEC-1/DEC-2. Neither is a release prerequisite.

---

## 8. Proposed versioning, release-notes and support policy

Proposal only, consistent with GPL-3.0-only and #142 decision 1.

- **Versioning:** SemVer-shaped `MAJOR.MINOR.PATCH` tags on `master` (`v0.1.0` first). While `0.x`, any minor release may need manual steps; each release states them. A **MAJOR** or **0.MINOR** change is any change to the database schema, the engine bundle, the export contract (#144) or the configuration variables. `PATCH` releases contain only fixes and need no manual step.
- **What a version identifies:** one Git tag, its `composer.lock`, the engine bundle checksum (`dev/sql/compilasql.php` output) and the supported platform row (§11).
- **Release notes:** one `CHANGELOG.md` section per tag: Added / Changed / Fixed / Security / Upgrade steps / Known limitations, with issue links. Security fixes name the affected versions.
- **Support posture (as-is):** the GPL-3.0-only disclaimer of warranty and limitation of liability (GPLv3 §§15–16) apply. There is no service level, no guaranteed response time and no hosted service. Fixes are made on the latest release line only. This does **not** remove the self-hoster's own security and privacy obligations as a data controller, nor the project's intent to fix reported vulnerabilities in good faith.
- **Security reports:** a `SECURITY.md` naming a private channel (GitHub private vulnerability reporting), the supported version (latest minor), and a disclosure practice (fix, release, advisory).
- **Deprecation:** settings or commands removed only in a 0.MINOR or MAJOR release, announced one release earlier.

---

## 9. Gap checklist (ranked)

### Blocker (before a first self-hosted release)

- [ ] B1 Supported single-installation path for the Drupal application, with an operator guide (decision D-1 chooses the topology).
- [ ] B2 Worker and cron service definitions (systemd units as templates, with the documented limits), plus a portable fallback (`drush ancora-engine:work --max-runtime` from cron).
- [ ] B3 Engine upgrade path for installed sites, run by the update procedure, with a release-to-release upgrade test.
- [ ] B4 First export of a published timetable (#144 minimal slice) and a first import of planning data (CSV/JSON per #144).
- [ ] B5 A shipped planner role in `config/sync` (#73 or a narrower issue).
- [ ] B6 Release artifact that excludes the legacy application, `www/test*.php` and `dev/backup/**`; clear statement that the legacy Docker stack is a local oracle, not the product.
- [ ] B7 `SECURITY.md`, `CONTRIBUTING.md`, third-party notices, `CHANGELOG.md`, first tag.
- [ ] B8 Maintainer decision on the historical dumps in releases (Q-L1).

### Important (should be in the first release; can follow quickly)

- [ ] I1 Backup and restore commands for a site (databases and private files), and a restore test.
- [ ] I2 Quick-start demo dataset (§7).
- [ ] I3 One configuration reference page (all `ANCORA_*` variables, defaults, secrets).
- [ ] I4 Templates without host values: PHP version as a variable, no `SET_BY_MAINTAINER`, single-site Caddy block, HSTS guidance.
- [ ] I5 Content-Security-Policy and an XSS payload test; a negative CSRF test for the token route.
- [ ] I6 Published known-limitations page (§5.6, #143 G1–G7).
- [ ] I7 Clean-CI acceptance journey (§10) as a workflow.
- [ ] I8 Uninstall procedure.
- [ ] I9 Logging guidance (dblog row limit, PHP log rotation, audit log location).

### Deferred (after the first release, or separate decisions)

- [ ] D1 Container image for the Drupal application (§12).
- [ ] D2 Per-organization PHP-FPM pools; read-only engine role for the web pool.
- [ ] D3 Representative capacity numbers (#143 ladder L2/L3).
- [ ] D4 Legacy UI redistribution audit (Q-L4, Q-L5), only if the legacy UI is ever shipped.
- [ ] D5 REST/async job API (#144 later slices).
- [ ] D6 Public managed hosting (#40/#56/#62/#73), out of scope by #142 decision 8.

---

## 10. Smoke and acceptance journey for clean disposable CI (specification)

Not implemented. Intended as one workflow on a stock `ubuntu-24.04` runner (and later a Debian 13 container), using only public package sources, no repository secret and no maintainer host.

**Inputs:** a release candidate artifact (§12, or the checkout at the tag), PHP and PostgreSQL from the platform row (§11).

**Steps and pass criteria:**

1. **Prepare a clean system:** install PostgreSQL (PGDG), PHP-FPM, Caddy from their official packages; start PostgreSQL with `scram-sha-256` in `pg_hba.conf` (closing the "trust only" gap of E3). Pass: services up.
2. **Install by the guide only:** follow the operator guide (B1) literally, with commands copied from it by a script that extracts the fenced blocks. Pass: no step needs a value not in the guide; no maintainer path appears in the resulting configuration.
3. **Start services:** PHP-FPM, Caddy on `127.0.0.1` with an internal CA, the worker and cron from the shipped units (B2) in a systemd-enabled container or `systemd-run --user`. Pass: `systemctl is-active` for each.
4. **First login:** the one-time administrator link or password file; create a planner with the shipped role. Pass: login succeeds; anonymous gets 403 on planning routes.
5. **Load the quick-start dataset** (§7). Pass: counts match the manifest.
6. **Generate:** request a timetable through HTTP as the planner; wait for the worker (bounded wait). Pass: job `succeeded`, placements equal the L1 sidecar, digest matches the manifest.
7. **Visualize and adjust:** fetch timetable, explorer and workspace pages; move one placement through the form; check refusal of an invalid move and an undo. Pass: HTTP 200/403/404 as specified; digest changes and returns after undo.
8. **Validate and export:** run validation; export the published timetable (#144 format). Pass: export validates against its schema; re-export is byte-identical.
9. **Back up:** run the backup command. Pass: dump and files archive exist, mode 0600.
10. **Upgrade:** install the previous release first (from step 2 on the previous tag), then upgrade to the candidate with the documented procedure, including the engine (B3). Pass: no drift (`config:status` clean), engine version updated, step 6 passes again.
11. **Restore:** drop the site database and files, restore from step 9. Pass: digest from step 7 is back.
12. **Remove:** uninstall procedure. Pass: no database, role, file or service of Áncora remains.
13. **Security spot checks:** response headers (`nosniff`, `SAMEORIGIN`, CSP once added, HSTS when TLS), no PHP error text in pages, `install.php`/`update.php`/`composer.json` 404, no secret readable under the web root, no default password accepted.

**Teardown:** everything inside the runner or a disposable container; no artifact uploaded except logs and the export file.

Existing building blocks to reuse: `install-disposable.sh`, `http-smoke.sh`, `r1-journey.sh`, `multisite-verify.sh` (Caddy/PHP-FPM bring-up), `tenant-verify.sh` (non-superuser operator), `docker/e2e.sh` (artifact secret checks), `dev/browser` (Playwright).

---

## 11. Supported-platform matrix (rough)

| Component | Supported (tested in CI) | Also verified here | Notes |
|---|---|---|---|
| OS | Ubuntu 24.04 (CI runners) | Ubuntu 24.04 | Debian 13 is the base of the legacy images; other Linux: untested |
| PHP | 8.4, 8.5 (`modern.yml:60`) | 8.4.27 | `composer.json` requires `^8.4` |
| PostgreSQL | 16, 18 (`modern.yml:61`) | 16.15 | Only PostgreSQL; `pg_trgm` required; encoding UTF8, locale `C.UTF-8` for identical ordering |
| Web server | Caddy 2.11.7 + PHP-FPM (`multisite-verify.sh`) | Caddy 2.11.7, PHP-FPM 8.4 | Apache/nginx: untested, no template |
| Drupal | core 11.4 (locked), Drush 13 | 11.4.11 | Drupal 12: not yet |
| Architecture | x86_64 | x86_64 | arm64: unknown |
| Container runtime | Docker Compose v2, legacy application only | not re-run for Drupal | No Drupal image (D1) |
| Browsers | Chromium (Playwright, `browser.yml`) | — | others untested |

---

## 12. Distribution artifact plan

Proposal, smallest first. None is implemented.

1. **Signed Git tag + GitHub release** (first release). Assets:
   - a source archive made with `git archive`, with `.gitattributes export-ignore` for `dev/backup/**`, the legacy tree and private-host docs if D-2 decides so;
   - a "ready to run" tarball: `modern/` with `composer install --no-dev --optimize-autoloader` output, `dev/sql/**` and `dev/workspace/**` (the engine sources the installer needs), `modern/deploy/**`, the operator tool, `LICENSE`, generated `THIRD-PARTY-NOTICES`, `CHANGELOG.md`;
   - `SHA256SUMS` and a signature (Sigstore/cosign keyless or a published GPG key: decision D-6);
   - an SBOM (CycloneDX from `composer.lock`).
2. **Container image for the Drupal application** (deferred D1): PHP-FPM + Caddy or Apache, worker as a second service, PostgreSQL as a separate official image, secrets as Docker secrets (reuse the practices of `compose.yaml`: internal network, read-only file system, no published database port, loopback by default). Built in CI from the tag, with the image secret checks of `docker/e2e.sh`.
3. **Not planned:** distribution packages (`.deb`), a Drupal.org distribution profile, a hosted service.

The legacy application is **not** part of any artifact; it stays in the repository as the oracle (#142 decision 4).

---

## 13. Security, privacy and license checklist (for each release)

Security:

- [ ] `composer audit` clean for `--no-dev`; Drupal core on a supported patch release.
- [ ] No default credential: first administrator by one-time link or random file; database passwords generated.
- [ ] Production settings fail closed (trusted hosts, salt, database).
- [ ] Access, IDOR and isolation tests pass (§5.1, E3, E4).
- [ ] CSRF and XSS checks (I5) pass.
- [ ] Response headers checked; HTTPS guidance published.
- [ ] Generation limits documented and enforced (120 s default, worker under a service with memory and CPU limits).
- [ ] Known limitations page updated.
- [ ] `SECURITY.md` reporting channel works.

Privacy:

- [ ] No historical personal record in any artifact (`dev/backup/**`, `.anc`), checked by listing the artifact.
- [ ] Demo and test data synthetic only, `example.org` addresses.
- [ ] Backups documented as containing personal data and password hashes; mode 0600.
- [ ] Logs: what personal data they hold (user names, IPs in dblog) and how long.

License:

- [ ] `LICENSE` unmodified GPL-3.0; `composer.json` license fields `GPL-3.0-only`.
- [ ] `composer licenses --no-dev` reviewed; no new license family without review.
- [ ] Third-party notices file generated for binary artifacts.
- [ ] Corresponding source linked from every binary artifact (GPLv3 §6).
- [ ] No legacy JS/images in artifacts until Q-L4/Q-L5 are resolved.

---

## 14. Proposed issue breakdown

Small, separately approvable issues. **Not created.**

1. **Single-installation operator guide and topology** (B1, D-1): choose one-site-at-`/` or one organization under `/o/<slug>/`; write `docs/release/install.md`; make `ancora-site.sh` (or a thin wrapper) support the chosen topology including the engine; acceptance by §10 steps 1–4.
2. **Worker and cron service templates** (B2): `modern/deploy/systemd/ancora-worker@.service`, `ancora-cron@.timer`, with `MemoryMax`, `CPUQuota`, `RuntimeMaxSec`; a cron fallback; verified in a systemd container.
3. **Engine upgrade path** (B3): version marker for the installed engine, an idempotent upgrade step called by `update-all`, and an upgrade test from the previous tag. Design first; it touches the engine install, not its semantics.
4. **Backup, restore and uninstall commands** (I1, I8).
5. **Planner role in `config/sync`** (B5; may be part of #73).
6. **Minimal export and import slice** (B4): as recommended by #144.
7. **Quick-start dataset** (I2): one seed of the #143 generator.
8. **Release governance files** (B7): `SECURITY.md`, `CONTRIBUTING.md` (DCO), `CHANGELOG.md`, notices generation; versioning policy of §8.
9. **Release artifact and clean-CI acceptance workflow** (B6, I7, §10, §12 item 1).
10. **Hardening follow-ups** (I4, I5, I9): template parameterization, CSP, XSS/CSRF negative tests, logging guidance.

---

## 15. Decisions needed from the maintainer

| # | Decision | Options |
|---|---|---|
| D-1 | Supported self-hosted topology | (a) one site at `/` with the engine; (b) the multisite tool with one organization under `/o/<slug>/`; (c) both |
| D-2 | What a release artifact contains | Drupal application + engine sources only (recommended), or the whole repository |
| D-3 | Historical dumps in releases and in the repository (Q-L1, Q-L2) | exclude from artifacts only (`export-ignore`); or also remove from the tree; history rewrite stays option B/C of `security-privacy.md` §4 |
| D-4 | Contribution terms (Q-L6) and copyright statement (Q-L7) | DCO with GPL-3.0-only inbound=outbound (recommended), or CLA |
| D-5 | First release scope | blockers B1–B8 only, or also I1–I9 |
| D-6 | Artifact signing | Sigstore keyless in CI, or a maintainer GPG key |
| D-7 | Officially supported platform rows | adopt §11 as "tested"; anything else "community, untested" |
| D-8 | Default job time limit for self-hosters | keep 120 s (the Free-plan value of #55) or make the default/maximum operator-configurable with a documented default; depends on #143 measurements |

---

## LBR impact

**0 bytes retired.** Documentation only; no code, legacy file, schema or engine source is changed.
