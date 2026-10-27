# R0 portal: deployment runbook

The portal is the only public part of R0 (`docs/saas/threat-model.md`). It shares no code, route or database with the legacy Áncora application.

## Private staging (Docker, loopback only)

```sh
portal/deploy/docker/stage.sh init-secrets
portal/deploy/docker/stage.sh up            # http://127.0.0.1:8088/ ; email goes to the outbox volume
portal/deploy/docker/stage.sh verify        # 17-check HTTP journey: request, email, verify, operator approval, audit
portal/deploy/docker/stage.sh operator list # the operator CLI inside the container
portal/deploy/docker/stage.sh backup && portal/deploy/docker/stage.sh restore-test
portal/deploy/docker/stage.sh down          # keeps data; `reset` deletes staging data
```

Staging uses the host's versions: PHP 8.5 and PostgreSQL 18.

## Production on studio.divengine.com (native): needs the R0 GO (#62)

Read `docs/saas/host-assessment.md` first. The host has no Docker. These steps add resources and change nothing that exists, except one Caddy site block.

1. **Prepare (maintainer):**
   - an account with an HTTPS transactional email provider (the `postmark` driver is implemented; another provider needs a small `Mailer` adapter) and a verified sender address;
   - the controller name and the privacy contact address for the privacy notice;
   - a legal review of `templates/privacy.php` and `templates/terms.php`.
2. **User and files:**
   - `useradd --system --no-create-home --shell /usr/sbin/nologin timetables`;
   - code in `/opt/divengine/timetables/portal`, owned by root and read-only for the pool, deployed from a tagged commit with `composer install --no-dev --classmap-authoritative`;
   - `/var/log/timetables` owned by `timetables`.
3. **Secrets** in `/etc/divengine/timetables/` (mode 0640, `root:timetables`):
   - `db_password`: 24+ letters and digits;
   - `hmac`: 32+ random bytes;
   - `postmark_token`;
   - `cli.env`, from `deploy/native/cli.env.example`.
4. **Database** in the existing PostgreSQL 18, as `postgres`. Run the same statements as `deploy/docker/setup-db.sh`:
   - roles `tt_control_owner` (NOLOGIN) and `tt_control_app` (password from `db_password`);
   - database `timetables_control` owned by `tt_control_owner`, with `REVOKE ALL ... FROM PUBLIC` and `GRANT CONNECT` to the app role only;
   - `sql/schema.sql` as the owner, then `sql/grants.sql` with `-v app_role=tt_control_app`.
5. **PHP-FPM:**
   - copy `deploy/native/php-fpm-timetables.conf` to `/etc/php/8.5/fpm/pool.d/timetables.conf` and fill in the `SET_BY_MAINTAINER` values;
   - run `php-fpm8.5 -t`, then `systemctl reload php8.5-fpm`. The studio pool is unaffected.
6. **Timers:** install the `deploy/native/*.service|timer` units, create `/var/backups/timetables` (owned by `postgres`, mode 0700), then `systemctl enable --now` the purge and backup timers.
7. **Rehearse before exposure:** `curl --resolve timetables.divengine.com:443:127.0.0.1 ...` against Caddy is not possible before the block exists. Instead:
   - run the HTTP journey against the pool through a temporary loopback-only Caddy site, as `tests/e2e/run.sh` does in native mode;
   - run `bin/portal list` with `cli.env`.
8. **Cutover (R0 GO):**
   - `cp /etc/caddy/Caddyfile /etc/caddy/Caddyfile.bak-$(date +%Y%m%d-%H%M%S)`;
   - append `deploy/native/timetables.caddy`;
   - `caddy validate --config /etc/caddy/Caddyfile`, then `systemctl reload caddy`;
   - check `https://timetables.divengine.com/healthz`, the security headers, and that `studio.divengine.com` and an Odoo subdomain still answer as before.
9. **Monitoring:**
   - an external uptime check on `/healthz` (only `ok`, no information);
   - alerts on 5xx in `/var/log/caddy/access.log`;
   - the size of `/var/backups/timetables`;
   - the `systemctl status` of the timers.

### Rollback

1. Restore the Caddyfile backup (or delete the `timetables.divengine.com` block) and run `systemctl reload caddy`. The hostname is then served by nothing, as before.
2. If needed:
   - remove `/etc/php/8.5/fpm/pool.d/timetables.conf` and reload php8.5-fpm;
   - disable the timers.

The database and files can stay, for analysis, or be dropped after a final backup. Nothing that existed before is modified at any step.

### Backup and restore

- **Backup:** `timetables-portal-backup.timer` runs a daily `pg_dump -Fc`, keeps 14 days, mode 0600.
- **Restore drill** (monthly, and before the R0 GO):
  1. `createdb restore_check`;
  2. `pg_restore --no-owner -d restore_check <dump>`;
  3. compare the counts of `portal.access_request` and `portal.request_event`;
  4. `dropdb restore_check`.

  `stage.sh restore-test` does the same in staging.
- **Full restore:** stop the pool, `dropdb timetables_control`, recreate it as in step 4 without the schema, run `pg_restore --no-owner --role=tt_control_owner`, re-apply `grants.sql`, then start the pool.

### Host hardening noticed (maintainer decision; touches existing services)

- On this host PostgreSQL grants `CONNECT` on databases to `PUBLIC` by default. The portal's runtime role could therefore connect to `studio` and `homeadmin`, although it has no rights on their tables. Consider `REVOKE CONNECT ON DATABASE studio, homeadmin FROM PUBLIC` after checking which roles use them.
- No scheduled database backup was seen for the existing services.
