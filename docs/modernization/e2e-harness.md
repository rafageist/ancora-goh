# End-to-end scheduling harness (issue #25, E2E-01)

Issue: #25, under phase #6, with a PHP-phase dependency on #12. It needs the SQL installer (#9, PR #18), the PL/pgSQL fix (#22, PR #28) and the authentication work (#14, PR #20). The branch therefore contains both stacks:

- the SQL chain #16 → #18 → #19 → #27 → #28 → #29 → #30, as its base;
- merges of #15 and of #20, which includes #17.

The commits of #25 itself are the seed change and `dev/e2e/`. The PR is based on #30 (#24), so its diff also shows #15, #17 and #20 until those are merged.

## One command

```sh
dev/sql/tools/with-ephemeral-pg.sh dev/e2e/run.sh
```

The same command runs in CI (`.github/workflows/e2e.yml`) on Ubuntu 24.04 with PHP 8.4 (`pgsql` extension) and PostgreSQL 16 server binaries. It takes about 10 s locally.

## Safety model

- **Database.** The harness refuses to run unless the target is a verified disposable cluster (`sql-test-safety.md`, #26). The cluster is socket-only and is deleted by the launcher. The schema is installed from the tracked sources with the data seed. The organization comes from the synthetic `dev/e2e/fixture.sql`: invented names, `example.org` addresses, no historical dump and no `.anc` file.
- **Application.** It runs from a temporary copy of the git-tracked files, and the working tree is never written. The harness writes a throwaway `bin/ancoragoh.conf` for the cluster (trust authentication on the socket, so there is no real credential). Passwords are random for each run and never printed.
- **Web server.** The PHP built-in server is bound to `127.0.0.1` only, and the harness checks the listening socket with `ss`. **The legacy application must never be exposed to a network.** SQL injection in the generated layer, missing CSRF protection and missing output escaping remain (`php-auth-hardening.md` §6). Nothing here claims production readiness.
- **Time limits.** Every HTTP request has a hard limit: 30 s, or 120 s for generation. The application's queries run with `statement_timeout`, set through `PGOPTIONS`. The CI step has a 10-minute limit.
- **Teardown.** The web server and the temporary copy are removed on exit, also after a failure. The cluster is removed by the launcher.

## What a run covers (28 checks)

| Area | Checks | Path |
| --- | --- | --- |
| Accounts | the seeded administrator has no usable password; `bin/setpassword.php` stores a `password_hash`; `bin/adduser.php` creates a user without roles | CLI, as an operator would |
| Listener | only `127.0.0.1:<port>` | `ss` |
| Anonymous | the login form, without the viewer icon; the viewer event is refused; an anonymous data change is refused and writes nothing | `GET /`, `POST ?execute` |
| Login | wrong password denied; the administrator logs in; **the session id changes at login**; the cookie is `HttpOnly` and `SameSite=Lax` | `POST btnLogin` |
| Denial | the user without roles logs in and is denied `add_Profesor`; nothing is written | `app_event_add_Profesor` |
| Input | `add_Profesor` creates "Teacher Beta", owned by the administrator; the explorer list shows it; `add_ProfesorParaActividad` assigns it to templates B1 and B2; `custom_ActualizaProfesorParaGrupo` assigns both groups | the events and fields that `www/js/app/server/*.js` posts |
| Generation | `generar` through `control/custom/generar.php` returns within the limit. In the database: **demands = 8, placed = 8, unplaced = 0, collisions = 0, overflow = 0**; every placement uses a teacher and a room of its template; the placements equal the reviewed baseline `dev/e2e/expected-placements.md5` | `app_event_generar` → `PublicFunctions::Generar` → `public.generar()` |
| Readback | the group timetable of B1 shows both subjects and both teachers; Teacher Beta's timetable shows its 4 activities | `get_minibrowser_horarios` |
| End | the anonymous timetable is still refused after generation; after logout, events are refused | `get_minibrowser_publico`, `?logout` |
| Logs | no PHP warning, fatal error or `TypeError` in the server's error log; no `ERROR`/`FATAL` in the PostgreSQL log. PHP notices and deprecations are counted and listed but do not fail the run (0 today) | |

The placement metrics use the same definitions as the SQL characterization suite (`tst.metrics`):

- **demands:** template × group pairs;
- **collisions:** overlapping placements sharing a teacher, a place or a brigade;
- **overflow:** placements past the last slot.

## Results (local, PHP 8.4.27, PostgreSQL 16.15)

- **28/28 checks pass** in three consecutive runs, each with the identical placement digest `4199818b…`. The 8 placements are listed in `expected-placements.md5`.
- **Negative controls:**
  - a direct run without the launcher is refused (exit 3);
  - a copy with #22's directive removed from `dame_clasif_de_asignaturas` fails 7 checks. The HTTP 500 comes from the `42702` ambiguity, which shows up as a PHP warning, a `TypeError` and a PostgreSQL error, and nothing is placed. This is exactly the pre-#22 behaviour.

## Defects found by the first real HTTP run, and fixed

| Defect | Where it was fixed |
| --- | --- |
| **Session id not regenerated at login, cookie not expired at logout.** `www/index.php` prints the header template before `Application::Run()`, so `session_regenerate_id()` failed with "headers already sent". The #14 CLI tests buffer output and could not see it. | PR #20: `ob_start()` right after `SessionUtil::start()`, plus a test that checks the order |
| `Undefined array key "height"/"width"` warnings in `view/custom/app.tpl` for windows defined without a size | PR #17. The printed value is unchanged |
| The seeded administrator had a well-known default password | this PR (maintainer decision): an unusable value; `bin/setpassword.php` is required before the first login |

## Not covered, and known limits

- **Not covered:**
  - other resources: the generator does not book them (#24 finding);
  - restrictions entered through the UI;
  - real-date restrictions;
  - cancellation while a generation runs. The session lock (PHP-10) is not exercised: the cancel request would need a second, concurrent session.
- **The organization is entered by SQL.** Plan, period, specialty, level, brigades, groups, subjects, templates, rooms and Teacher Alpha come from the fixture. Only the teacher and assignment flow goes through HTTP. The other forms (`add_Planificacion`, `add_Asignatura`, …) are reachable but not driven yet.
- **Error-handling defect.** A failed query makes `PostgresDatabaseImpl::getAsArray()` call `pg_fetch_all(false)`, which throws a `TypeError` on PHP 8 (HTTP 500). It was seen in the negative control and is not fixed here (PHP-09).
- **Timing only covers this fixture.** One small dataset (8 activities) generates in under a second. Performance on realistic data is still unknown (CHAR-05).
