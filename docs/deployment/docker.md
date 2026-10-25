# Local installation with Docker Compose

Issue: #32. This guide installs Áncora GOH on one computer, for development and demonstration. You do not need to know the history of the project, and you do not need PHP or PostgreSQL on the host.

> **Local use only.** The legacy application still has SQL injection, missing CSRF protection and missing output escaping (`docs/modernization/php-auth-hardening.md` §6). The web port is published on `127.0.0.1` only. Do not change that address, do not put a reverse proxy in front of it, and do not open it to a network. This is not a production deployment.

## What runs

| Service | Image | Role | Network | Host port |
| --- | --- | --- | --- | --- |
| `web` | built from `docker/Dockerfile`, target `web` (PHP 8.4.27, Apache 2.4, Debian 13) | The application. Serves only `www/`. Runs as `www-data` on port 8080 with a read-only file system. | `backend`, `frontend` | `127.0.0.1:8087` (configurable) |
| `db` | `postgres:16.15-bookworm` | PostgreSQL 16. Its data is in the named volume `ancora-goh_pgdata`. | `backend` (internal) | none |
| `setup` | target `setup` (PHP 8.4 CLI, `psql`, the SQL sources in `dev/sql`) | One-off database installer. Runs only when you call it. | `backend` | none |

The `backend` network is internal: the database has no route to the host or to the internet, and no published port. The Compose project is called `ancora-goh`, so its containers, network and volume names all start with `ancora-goh`.

Nothing secret is in the images or in Git:
- The two database passwords are Docker secrets read from `secrets/`, which is git-ignored.
- The web container writes its connection file at start (`docker/web/entrypoint.sh`); `bin/ancoragoh.conf` in the image is only a link to that file.
- `.dockerignore` is an allowlist. It keeps `.git`, `docs/`, `dev/` (except the SQL sources), the historical dumps in `dev/backup`, local configuration and the debug scripts out of every image. `docker/e2e.sh` checks this.

## Prerequisites

- Docker Engine with the Compose plugin (`docker compose version` prints v2 or later). Your user must be allowed to run `docker`.
- A free local port; the default is 8087.
- Bash, `curl` and `ss` (from iproute2), for the helper scripts and the end-to-end test.
- About 1 GB of disk space for the images.

## First-time installation

From the repository root:

```sh
cp docker/env.example .env              # optional: change ANCORA_HTTP_PORT here
docker/manage.sh init-secrets           # creates secrets/postgres_password and secrets/db_password
docker/manage.sh up                     # builds the images, starts db and web, waits until healthy
docker/manage.sh init-db                # installs the database from dev/sql
docker/manage.sh set-admin-password     # asks for the password of the user "admin"
```

Then open <http://127.0.0.1:8087/> (or your port) and log in as `admin`.

What each step does:

- **`init-secrets`** creates two random passwords: one for the PostgreSQL superuser and one for the application role. It never overwrites an existing file. The directory is mode 0700 and the files are mode 0644, because the files are bind-mounted into containers that run as unprivileged users; the directory keeps them private on the host.
- **`up`** refuses to start if the port is already in use.
- **`init-db`** runs the `setup` service (`docker/setup/init-db.sh`):
  1. It creates the role `ancoragoh` (not a superuser, cannot create roles).
  2. It installs the schema and the default data with the maintained installer `dev/sql/install.sh --admin-install --with-data`, as that role, so the role owns every object.
  3. It removes the temporary right to create databases.

  It never uses a historical dump. It is safe to run again: if the database is already there it changes nothing, and if a database with that name exists but is not an Áncora schema it refuses.
- **`set-admin-password`** runs `bin/setpassword.php -nick admin` inside the web container. The password is read without echo and is stored with `password_hash()`.

## Initial administrator

A new database contains one account, `admin`, with **no usable password**: the seed stores a value that no password matches. Until you set a password, nobody can log in. Legacy MD5 passwords are disabled and the anonymous timetable viewer is off (`bin/security.conf`).

Choose one of these:

```sh
docker/manage.sh set-admin-password                          # interactive, at least 12 characters
docker/manage.sh set-admin-password --generate ~/ancora-admin.txt   # random; file mode 0600
```

With `--generate`, read the password from the file, store it somewhere safe and delete the file.

The same command resets a forgotten password. Without the helper:

```sh
docker compose exec -T -w /var/www/ancora/bin web php setpassword.php -nick admin
```

That command reads the password from standard input, and the characters you type are shown on the screen.

Add more users with `bin/adduser.php` in the same way (`docker compose exec -w /var/www/ancora/bin web php adduser.php -nick NAME -nombre "Full name" -correo user@example.org`). Then give them roles in the application.

## Everyday use

| Task | Helper | Plain Compose |
| --- | --- | --- |
| Start | `docker/manage.sh up` | `docker compose up -d --wait db web` |
| State and health | `docker/manage.sh status` | `docker compose ps` |
| Logs | `docker/manage.sh logs [web\|db]` | `docker compose logs --tail=200 web` |
| Stop, keep containers | `docker/manage.sh stop` | `docker compose stop` |
| Start stopped containers | `docker/manage.sh start` | `docker compose start` |
| Remove containers, keep data | `docker/manage.sh down` | `docker compose down` (never add `-v`) |

The containers restart automatically after a reboot of Docker (`restart: unless-stopped`), unless you stopped them.

Apache writes its access and error logs, including PHP errors, to the `web` logs. PostgreSQL writes its log to the `db` logs.

## Rebuilding after code changes

The application code is copied into the image, so a code change needs a rebuild:

```sh
docker/manage.sh rebuild        # or: docker compose up -d --build --wait db web
```

A rebuild keeps the data. It does **not** change the database: changes to `dev/sql` reach a database only through a new installation. There is no upgrade path for an existing database yet.

Sessions are kept in memory in the web container, so a rebuild or restart logs everybody out.

## Data persistence

All data is in the Docker volume `ancora-goh_pgdata`. It survives `stop`, `restart`, `down`, `rebuild`, Docker restarts and reboots. It is removed only by the explicit [reset](#reset-deletes-all-data) below, or by `docker volume rm` / `docker compose down -v`, which you should not use on this project.

## Backup and restore

```sh
docker/manage.sh backup                 # writes backups/ancoragoh-<UTC time>.dump (custom format)
docker/manage.sh restore backups/ancoragoh-20261025T120000Z.dump
```

Without the helper, a backup is:

```sh
docker compose exec -T db pg_dump -U postgres --format=custom -d ancoragoh > ancoragoh.dump
```

- A backup contains all data, including password hashes. Keep it private. `backups/` is git-ignored and mode 0700.
- **Restore replaces the whole database.** Everything entered after that backup is lost. The helper:
  1. asks you to type the database name (or reads `ANCORA_CONFIRM=ancoragoh` in scripts);
  2. makes a safety backup of the current database in `backups/`;
  3. stops `web`, recreates the database owned by the application role, loads the dump and starts `web` again.
- Restore needs a database that `init-db` has initialized at least once, because the application role must exist.
- Restoring a dump of a different schema version is not supported.

## Changing the port

Set `ANCORA_HTTP_PORT` in `.env` and run `docker/manage.sh up` again. The address is always `127.0.0.1`. To reach the application from another device, do not change the bind address. Use an SSH tunnel to this computer (`ssh -L 8087:127.0.0.1:8087 host`), and only on a network you trust.

## End-to-end test

```sh
docker/manage.sh e2e            # or: docker/e2e.sh
```

This builds the images and starts a **separate, disposable** Compose project named `ancora-goh-e2e-<random>`. It has its own volume, its own throwaway secrets and a free port in 18087–18187. On that project the test:
1. initializes the database twice; the second run must change nothing;
2. sets the administrator password;
3. runs the HTTP scheduling flow of `dev/e2e/lib.sh`: login, an access denial, entering a teacher and its assignments, generation, timetable readback and logout;
4. checks that only `127.0.0.1` is published and that the images contain no secret;
5. restarts the stack (`down`, then `up`) and checks that the timetable, the teacher and the password are still there.

At the end it removes that project, including its volume and its images, and nothing else. CI runs the same test (`.github/workflows/docker.yml`). The data is synthetic (`dev/e2e/fixture.sql`).

To run the same HTTP flow against **your** deployment, loading the synthetic organization as demo data:

```sh
ANCORA_E2E_ADMIN_PASSWORD_FILE=~/ancora-admin.txt docker/e2e.sh --deployed
docker/manage.sh restart
ANCORA_E2E_ADMIN_PASSWORD_FILE=~/ancora-admin.txt docker/e2e.sh --verify-persisted
```

Notes on `--deployed`:
- It refuses if the synthetic data is already loaded.
- It does not change the administrator password.
- It leaves a plan with 8 placed activities, which you can browse in the application.
- It removes its temporary user and its helper schema.

## Troubleshooting

| Symptom | Cause and fix |
| --- | --- |
| `missing secrets/...` | Run `docker/manage.sh init-secrets`. |
| `127.0.0.1:8087 is already in use` | Another program uses the port. Set `ANCORA_HTTP_PORT` in `.env`. |
| The browser shows a database connection error | The database is not initialized (`docker/manage.sh init-db`), or `db` is not healthy (`docker/manage.sh status`, `logs db`). |
| Login always fails for `admin` | No password has been set yet: `docker/manage.sh set-admin-password`. |
| `init-db` says the database exists but is not an Áncora schema | A database with that name was created by hand. Nothing was changed. Back it up if needed, then use [reset](#reset-deletes-all-data). |
| `db` stays unhealthy after you changed `secrets/postgres_password` | The superuser password is set only when the volume is first created. Restore the old file, or reset. |
| The web container exits with `ancora-entrypoint: ...` | The `db_password` secret is missing, shorter than 16 characters or not letters and digits only. |
| A PHP error appears | Look in `docker/manage.sh logs web`. `display_errors` is off, so errors never appear in the page. |
| Generation takes very long | Generation time on realistic datasets is unknown. Apache waits up to 600 s per request. |

## Reset (deletes all data)

> **Warning: data loss.** A reset removes the volume `ancora-goh_pgdata` and every timetable, user and setting in it. It cannot be undone. Make a backup first.

```sh
docker/manage.sh backup
docker/manage.sh reset          # asks you to type "delete ancora-goh"
docker/manage.sh up && docker/manage.sh init-db && docker/manage.sh set-admin-password
```

A reset:
- removes the containers and that one volume, after checking that the volume belongs to the `ancora-goh` project;
- keeps `secrets/`, the images and the backups;
- never runs automatically. No other command removes data.

## Security limits

These limits apply until the separate security gates of the modernization pass:
- **Local only.** SQL injection in the generated layer, missing CSRF protection, missing output escaping and incomplete ownership checks remain.
- **Inside the `db` container, local connections are trusted.** That is the default of the official image. Anyone who can run `docker exec` on this host can read the database. Only TCP connections from `web` and `setup` need a password.
- **The application role owns the schema.** It is not a superuser and cannot create databases or roles. There is not yet a separate, less-privileged role for the application.
- **The images are pinned to exact versions.** Run `docker/manage.sh rebuild` to pick up rebuilt base images of the same versions.
