# studio.divengine.com: read-only host assessment (issue #42)

Inspected on 2026-10-25 over SSH (`root@studio.divengine.com`), with **read-only** commands only:
- system and service listing;
- `ss`, `ufw status`, `free`, `df`, `vmstat`, `sar`;
- reading `/etc/caddy/Caddyfile` and the PHP-FPM pool configuration;
- listing database names and sizes.

Nothing was installed, started, stopped, reloaded or written. No database content or secrets were read.

## 1. What runs there

| Item | Finding |
| --- | --- |
| Machine | DigitalOcean droplet, **1 vCPU, 956 MiB RAM, 2 GiB swap (946 MiB in use)**; disk 24 GB, 14 GB free; Ubuntu 26.04.1 LTS; hostname `odoo` |
| Load | Idle at inspection time (load 0.05). Memory pressure is low but nonzero (PSI `some avg60=0.10`). RSS is dominated by Odoo (about 280 MiB in Python workers). |
| Ingress | **Caddy 2.11.7**, native (systemd), on :80/:443 (UFW allows 22, 80, 443/tcp+udp). Access logs are JSON, rotated. |
| Wildcard site | `*.divengine.com` → Odoo (127.0.0.1:8070/8073) with **on-demand TLS**. `caddy-ask` (127.0.0.1:5555) issues a certificate only when an Odoo database named like the subdomain exists. |
| Studio | `studio.divengine.com` → Drupal 11 from `/opt/divengine/studio/web`, through **PHP 8.5-FPM** pool `studio` (ondemand, max 3 children, 256M); hardened Caddy rules |
| Databases | **PostgreSQL 18** (native, `postgresql@18-main`), on localhost only; `pg_hba` uses peer locally and scram-sha-256 over TCP. Databases: `homeadmin` (129 MB, Odoo), `studio` (68 MB, Drupal). `max_connections=100`, `shared_buffers=128MB`. |
| Containers | **Docker is not installed.** Every service is native. |
| Other | fail2ban, unattended-upgrades, DigitalOcean agent, sysstat |
| Backups | Only one manual dump was seen (`/root/backups/studio-pre-oauth-*.sql.gz`). **No scheduled database backup was found** for `studio` or `homeadmin`: a risk for the existing services, reported here, not changed. |
| `timetables.divengine.com` | DNS already resolves to this droplet (178.128.150.68). It falls under the wildcard site, and the ask endpoint refuses a certificate, so **HTTPS currently fails and nothing is served**. |

## 2. Consequences for the SaaS

1. **No Docker on the host.** A Docker Compose deployment there means installing Docker Engine, a host mutation with roughly 100–200 MiB of extra resident memory. On this machine that would push the other services further into swap. It also needs a second PostgreSQL server, or a bridge to the native one.
2. **Capacity.** The host has no headroom for MPI scheduling jobs: one vCPU, already swapping. R0, a small request portal, fits. **R1 does not fit without more capacity:**
   - resize the droplet to at least 2 vCPU / 4 GiB; or
   - use a separate droplet for the scheduling service.

   This is a cost decision for the maintainer.
3. **Versions.** The host runs PHP 8.5 and PostgreSQL 18. Our tested stack is PHP 8.4 and PostgreSQL 16. The R0 portal must be tested on PHP 8.5 and PostgreSQL 18 before cutover. The MPI characterization suite must pass on PostgreSQL 18 before R1 runs there.
4. **Ingress.** An explicit `timetables.divengine.com { ... }` site block in the Caddyfile takes precedence over the `*.divengine.com` wildcard. It gets a normal ACME certificate, and the on-demand ask endpoint is not involved. Rollback is removing the block and running `caddy reload`. A backup of the Caddyfile is taken first, as the maintainer already does (`Caddyfile.bak-*`).

## 3. Recommended deployment (R0), pending the R0 GO (#62)

The lowest-risk option for this host is **native, isolated, and reversible**:

| Resource | Setting |
| --- | --- |
| System user | `timetables` (no shell, no sudo); code in `/opt/divengine/timetables/portal`, owned by root, read-only for the pool |
| PHP | Separate PHP 8.5-FPM pool `timetables-portal` running as `timetables`: `pm=ondemand`, `max_children=2`, `memory_limit=64M`, `max_execution_time=10`, socket `/run/php/php8.5-fpm-timetables.sock`, `open_basedir` limited to the portal and its data directory |
| Database | `timetables_control` in the existing PostgreSQL 18, owned by role `tt_control_owner` (NOLOGIN). The runtime role `tt_control_app` has only DML on the portal tables; `CONNECT` is revoked from `PUBLIC`. |
| Secrets | Files under `/etc/divengine/timetables/` (mode 0640, group `timetables`): database password, HMAC key, email provider token |
| Ingress | Caddy site block serving only `/public` through the new socket, with security headers; nothing else in that directory tree is executable |
| Jobs | systemd timers: retention purge (daily), `pg_dump` of `timetables_control` (daily, kept 14 days, plus a monthly restore test) |
| Rollback | Remove the Caddy block, reload Caddy, stop and disable the pool and timers. The database and files can stay or be dropped. Other sites are untouched at every step. |

**Docker Compose** (`portal/deploy/compose.yaml`) remains available, for:
- private staging on a workstation;
- the host, if the maintainer prefers containers *and* approves installing Docker and the extra memory.

Both deployments use the same application, configuration variables and tests.

## 4. Not done, and needs approval

- Any change on the host: user, pool, database, Caddy block, timers, Docker installation.
- A droplet resize or a new droplet for R1.
- An account with the transactional email provider. DigitalOcean blocks outbound SMTP by default, so email goes over an HTTPS API, through the provider's own account and token.
