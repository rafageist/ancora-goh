# PHP authentication and event-authorization hardening (issue #14, roadmap PHP-02)

Branch `fix/issue-14-auth-hardening`, stacked on `fix/issue-13-php84-compat`
(#13, PR #17). The credential-hygiene branch of #11 (PR #15) is a separate,
unmerged prerequisite; none of the files it changes were edited here.

This change removes the most dangerous attack paths of the PHP front end:
login SQL injection, unsalted MD5, the `access=public` bypass, ignored event
permissions, `eval` in configuration and gallery templates, and session
fixation. **It does not make the application secure.** Most of the SQL in
the application is still built by string interpolation (§6). Until that is
reviewed and fixed, and the items in §7 are verified over HTTP, the
application must not be deployed publicly.

Legend: **[verified]** = shown by a command on this branch (CLI, no
database); **[observed]** = read in the source, not executed;
**[hypothesis]** = plausible, not demonstrated.

## 1. What changed

| Area | Before | After | Files |
| --- | --- | --- | --- |
| Login query | `getAllBy("nick = '$user' AND contrasena = '$pass'")`, with `$pass = md5($password)` | `SELECT id, nick, contrasena FROM seguridad.usuario WHERE nick = $1` through `pg_query_params`; the password is checked in PHP | `control/Application.php`, `lib/enterprise/gendb/PostgresDatabaseImpl.php` |
| Password storage | unsalted `md5()` in `UsuarioModel::add()`; **plain text** in `Usuario::setContrasena()` | `password_hash(PASSWORD_DEFAULT)` everywhere; legacy MD5 migrated at login (§3) | `lib/basic/security/PasswordUtil.php`, `model/security/UsuarioModel.php`, `model/security/Usuario.php`, `dev/cogephp.php`, `control/data/set/set_Usuario.php`, `bin/adduser.php` |
| Anonymous access | POST `access=public` ran **any** handler without a session | removed; explicit allowlist of 6 read-only events (§2) | `control/Application.php` |
| Event permissions | `security.conf` never applied (key parsed as `event_permission`); both entries named non-existent events; every authenticated user could run every event | deny by default (§2.2); entries renamed to the real handlers | `lib/basic/file/PropertiesFile.php`, `bin/security.conf`, `control/Application.php` |
| Handler include | `include "../control/$folder/$f.php"` with `$f` taken from the POST key, so `app_event_../../x` reached files outside the event folders **[observed]** | event names must match `^[A-Za-z0-9_]+$` | `control/Application.php` |
| Role/permission lookup | interpolated `$_SESSION['user']` and role names | bound parameters | `control/Application.php` |
| Configuration | every value run with `eval()` | parsed as PHP literals only; anything else is ignored with a warning | `lib/basic/file/PropertiesFile.php` |
| Gallery templates | `eval('$link_ini = ...'.$fieldlink...)` and `eval('echo $item->'.$fielddescrip)`; `$fieldlink` comes from POST | property access, only for identifier names of existing properties; the href is HTML-escaped | `view/gallery/gallery_*.tpl` (61), `dev/cogephpface.php` |
| Sessions | `session_start()` with defaults, no regeneration, logout only unset `user` | strict mode, cookies only, HttpOnly, SameSite=Lax, Secure on HTTPS; `session_regenerate_id(true)` on login; logout destroys the session and expires the cookie | `lib/basic/security/SessionUtil.php`, `www/index.php`, `control/Application.php` |
| Debug SQL log | `getDefault($sql, true)` echoed the SQL and appended it to `query.log` in the web root | debug SQL goes to the PHP error log only | `lib/enterprise/gendb/PostgresDatabaseImpl.php` |
| Anonymous handlers | `get_minibrowser_publico` interpolated five POST values into SQL | bound parameters | `control/custom/get_minibrowser_publico.php`, `control/custom/get_public_list_otros_recursos.php` |

The new DB helpers (`queryParams`, `getAsObjectsParams`) are only on
`PostgresDatabaseImpl`. They were not added to `IDatabase`, so the unused
MySQL driver is untouched. The generated templates and the setter/insert
code produced by the generators (`dev/cogephp.php`, `dev/cogephpface.php`)
now match the committed files; for the gallery fragment this was checked by
running the generator's `fputs` lines (identical output) **[verified]**.

## 2. Authorization model

### 2.1 Anonymous allowlist (`Application::$anonymous_events`)

Login (POST `btnLogin`) and logout (GET `logout`) are handled by
`Application::Run()` and are not events. Before this change the client sent
`access: "public"` only from
`www/js/app/client/browsers/ancoragoh_client_browsers_publico.js`. That
script is loaded by `view/custom/header.tpl` and opened from the "horarios"
icon on the login page (`view/custom/authform.tpl:183`). These are the only
anonymous flows in the code **[observed]**, so the allowlist contains exactly
the events that script posts:

| Event | Why anonymous | Request input | Notes |
| --- | --- | --- | --- |
| `get_browser_publico` | opens the public timetable window | none | lists all plans and their periods |
| `get_minibrowser_publico` | the timetable of one group, teacher, place or resource | `idbrg`, `idprofe`, `idlug`, `idres`, `idperiodo` | **now bound parameters** (was SQL injection) |
| `get_public_treeBrigadas` | tree of groups in the viewer | none | the template runs SQL interpolating values read from the DB (second-order, §6) |
| `get_public_list_profesores` | list of teachers | none | |
| `get_public_list_lugares` | list of places | none | |
| `get_public_list_otros_recursos` | list of other resources | none | filtered by the session user; anonymous visitors get an empty list |

A request without a session runs events only if **all** of its events are
on the allowlist. Otherwise it gets the "session expired" message and
nothing runs. The `access` parameter is ignored. The client still sends it,
and no JavaScript was changed.

Whether the public viewer should exist at all is a product decision. It
publishes the names of teachers, places and groups and every timetable to
anonymous visitors, as the 2010 design did. Removing an entry from the
allowlist disables it.

### 2.2 Authenticated users (deny by default)

`Application::isEventAllowed($event, $authenticated, $permisos, $event_permissions)`:

1. The event name must match `^[A-Za-z0-9_]+$`, or it is denied.
2. If it is on the anonymous allowlist, it is allowed.
3. An unauthenticated request is denied.
4. A user holding the permission `todos` (exact, case-sensitive) is allowed.
   `todos` is seeded for the `superusuario` role in
   `dev/sql/datos/datos_por_defecto.sql`.
5. Otherwise the event needs an entry `event_permissions[<event>]` in
   `bin/security.conf`, and the user's roles must hold **all** listed
   permissions. The historical loop already required all of them. An empty
   `array()` explicitly allows any authenticated user. Unlisted events,
   non-literal values, unknown permission names and wrong case are all
   denied.

The user's permissions are the union of `seguridad.permiso.permiso` over
the user's `seguridad.rol_de_usuario` roles, read with bound parameters.
`seguridad.tiene_permiso(usuario, permiso)` (`dev/sql/seguridad/tiene_permiso.sql`)
implements the same check in SQL. It is not used, because the dispatcher
needs the whole permission set once per request. The `app.tpl` menu
filtering by `$permisos` is unchanged.

**Behaviour change:** the seed data grants permissions only to
`superusuario`, as `todos`. Users without `todos` can now run only events
listed in `security.conf`, which currently lists two. Before, any logged-in
user could run all ~400 handlers. Mapping the ~145 live events
(`architecture.md` §3) to permissions is a maintainer decision and is not
done here.

## 3. Password storage and legacy migration

`seguridad.usuario.contrasena` is `VARCHAR NOT NULL` with **no length**
(`dev/sql/seguridad/usuario.sql`, the same in `dev/sql/result.sql`), so
bcrypt (60 chars) and argon2 hashes fit. **No schema change.** The
historical dump and the seed data were not modified.

`PasswordUtil::verify($input, $stored)`:

| Stored value | Result |
| --- | --- |
| a `password_hash` value (recognised by `password_get_info`) | `password_verify`; `RESULT_OK_REHASH` if `password_needs_rehash` (e.g. old bcrypt cost) |
| exactly 32 hex characters (legacy MD5, any case) | `hash_equals(strtolower($stored), md5($input))` → `RESULT_OK_REHASH`, otherwise fail |
| anything else (plain text, truncated hex, empty) | always fails |
| empty or non-string input | fails before any query |

Login flow (`Application::VerifyAuth`):

1. Look up `nick = $1`. Exactly one row is required, and its `nick` must be
   byte-identical to the input. If the user is unknown, a dummy
   `password_verify` runs to reduce timing differences.
2. Verify as above.
3. On `RESULT_OK_REHASH`, run
   `UPDATE seguridad.usuario SET contrasena = $1 WHERE id = $2 AND contrasena = $3`
   with the new `password_hash`. The old value in the `WHERE` clause means a
   concurrent password change is never overwritten. From then on the account
   uses only the `password_verify` path.
4. `session_regenerate_id(true)`, then set `$_SESSION['user']`/`['nick']`.

If the UPDATE fails, the login still succeeds (the user did prove the
password), the MD5 value stays, and the next login retries. Typing the
stored MD5 hash itself as the password fails (tested).

Risks and limits:

- Accounts that never log in **keep their MD5 hash indefinitely**. Anyone
  who obtains the table can still crack those hashes offline. Options for
  the maintainer: force a reset or expire MD5 accounts after a deadline, or
  wrap them offline as `password_hash(md5)` with a marker (not done: it
  needs a format decision).
- The seeded administrator (`dev/sql/datos/datos_por_defecto.sql`) has a
  known default password stored as MD5. It works through the legacy path
  once and is then rehashed, but it remains a known password until changed.
  The installer (#9) must require a new one (#11 C5).
- The legacy path accepts any account whose stored value happens to be 32
  hex characters. A plain-text password of that shape would be treated as
  an MD5 digest, so it would not log in with itself.
- Brute force is not limited: there is no lockout, rate limit or CAPTCHA.
- New users: `bin/adduser.php` (rewritten, because it referenced files that
  no longer exist), `UsuarioModel::add()` (the `add_Usuario` handler) and
  `Usuario::setContrasena()` (the `set_Usuario` handler) all store
  `password_hash`. `set_Usuario` changes the password only when one is
  posted. Its `"null"` placeholder would otherwise have become a valid
  password once hashed. The other columns of `UsuarioModel::add()` are
  still interpolated (§6).

## 4. Sessions

`SessionUtil::start()` (called first in `www/index.php`) sets
`session.use_strict_mode=1`, `use_only_cookies=1`, `use_trans_sid=0`,
`cookie_httponly=1` and `cookie_samesite=Lax`, with `secure` when the
request is HTTPS. `regenerate()` runs on successful login only. `destroy()`
on logout empties `$_SESSION`, expires the cookie and calls
`session_destroy()`. The login form is then shown as before.

Not changed: the session lifetime, `session_write_close()` before long
requests (the generator-cancel hypothesis, `architecture.md` §6), CSRF
protection, and the GET logout link.

## 5. Tests

All CLI. No web server and no database: `dev/tests/security/StubDatabase.php`
records every SQL text with its bound parameters and emulates only the four
authentication statements against in-memory fixtures. Fixture passwords and
hashes are generated at run time; there are no historical credentials.

```sh
php dev/tests/security/run.php          # 536 checks, 0 failed (PHP 8.4.27)
/usr/bin/php8.3 dev/tests/security/run.php   # 536 checks, 0 failed (PHP 8.3.6)
dev/ci/php-lint.sh                      # 935 files, 935 ok, 0 failed, 0 exclusions
dev/ci/php84-check.sh                   # self-test 8/0; static check 935 files, 0 problems; smoke 130/0
```

**[verified]** Each test also fails on any unexpected PHP warning or notice.
As a negative control, the suite was run against the pre-change
`Application.php` (46 failures) and the pre-change `PropertiesFile.php` (19
failures). The old `PropertiesFile` actually ran the fixture's
`system('touch ...')` line.

| Acceptance item | Tests |
| --- | --- |
| Anonymous requests cannot invoke arbitrary handlers | `add_Usuario`, `del_Usuario`, `set_RolDeUsuario`, a gallery, `custom_set_Restricciones`, `get_minibrowser_horarios` with and without `access=public`; mixed allowlisted + protected request; `executeEvents()` directly; each allowlisted event does run |
| An authenticated user without the permission is denied | no mapping (deny by default); 2 of 3 required permissions; a permission no role has; wrong case (`Planificar`); non-literal config value; historical misspelt key (`get_form_editPlanificacion`); `TODOS` / `todos ` are not superuser; nonexistent event; `todos` allows everything; empty `array()` = any authenticated user, never anonymous |
| Event-name traversal | `../../bin/adduser`, `add_Usuario.php`, names with spaces or NUL are refused even for `todos` |
| SQL injection probes in login | 7 probes in user and password: constant SQL text, input only in `$1`, input never in any SQL text, no other statement, login fails; permission lookups with a hostile session id are parameterized |
| MD5 migration | legacy login allowed once and rehashed with a guarded UPDATE, then modern path only; wrong / case-changed / trailing-space passwords and the stored hash itself fail with no UPDATE; upper-case hex; failed UPDATE then retry; plain-text and 31-hex stored values rejected; outdated bcrypt cost rehashed |
| Sessions | strict mode / HttpOnly / SameSite; id changes on login, not on failure; logout destroys the session and shows the login form |
| Templates no longer eval request text | no `T_EVAL` token in `view/`, `control/`, `www/`, `model/`, `lib/basic`, `lib/ancora`; all 61 gallery templates render the link and description, do not run a code payload in `fieldlink`/`fielddescrip`, and escape the link |
| Config, user creation, public viewer | `PropertiesFile` literals and rejected expressions; `adduser`, `UsuarioModel::add`, `setContrasena` store `password_hash`; `get_minibrowser_publico` binds all five request values |

CI: `.github/workflows/php-security-tests.yml` ("PHP security tests") runs
the suite on PHP 8.4 with `shivammathur/setup-php@v2`. It has read-only
permissions and no services or secrets. `php-lint.yml` from #13 is
unchanged and lints the new files.

## 6. SQL built by interpolation that remains (not fixed)

Counts are from `grep` on this branch. They are lower bounds: dynamic SQL
built across several statements is not always caught. Only authentication
(`control/Application.php`), `bin/adduser.php` and the anonymous
`get_minibrowser_publico` use bound parameters now.

| Where | Count | What | Reachable by |
| --- | --- | --- | --- |
| Generated models `model/**/*Model.php` | 1,054 `getAnything("...$var...")` lines in 62 files (55 `model/data`, 6 `model/security`, 1 `model/generator`) | every `add`, `remove`, `removeBy($where)`, `set($set)`, `setBy($where,$set)`, `get($id)`, `getAll($fields)`, `getAllBy($where)`, `getLimit*`, `getJoin*` | everything below |
| Generated entities `model/**/<Entity>.php` | 329 `->data->setBy(...)` setters in 61 files | `"col = '$value'"` per setter | `control/data/set/*` |
| SQL function wrappers | 187 SQL strings: `model/data/PublicFunctions.php` 162 (incl. `generar`, line 1555), `model/util/MatrixFunctions.php` 20, `PriorizaFunctions.php` 2, `model/security/SeguridadFunctions.php` 2, `model/generator/GeneradorFunctions.php` 1 | `select f('$a', ...)` | custom handlers, generator UI |
| Generated data handlers `control/data/{add,set,del}` | 183 handlers | POST fields go unescaped into `add()`/setters/`remove()` | authenticated users with `todos` (or a mapping) |
| `getItem($var)` → generated `get($id)` | 90 calls in 80 files under `control/`, `view/` | id from POST, session or DB | authenticated |
| Hand-written handlers and templates | 79 call lines in 60 files: `control/custom` 67 (31 files read `$_POST`; 44 lines interpolate `$_SESSION['user']`), `view/custom` 10 (`asignaturas.tpl`, `browser_brigadas.tpl`, `desglose_detalles.tpl`, `minibrowser_asignaturas.tpl`, `public_tree_brigadas.tpl`, `tree_brigadas.tpl`), `bin/deluser.php` 1, `www/test.php` 1 | e.g. `get_minibrowser_horarios.php` (4), `get_browser_Organizacion.php` (3), `get_browser_Desgloses.php` (3), `custom_set_Restricciones*.php`, `get_form_edit_*`, `get_minibrowser_*` | authenticated (`public_tree_brigadas.tpl` anonymously, second-order: DB ids only) |
| `PartModel::getWHEREPartOfSearchFrase()` | 1 | search words concatenated into `LIKE '%...%'` | callers of `getAllWithPhraseIn` (itself broken, #13 §4) |

Interpolating `$_SESSION['user']` is not directly attacker-controlled (it
is a DB id), but it is still string-built SQL. A full fix needs either a
generator change that emits `pg_query_params`, or a per-handler review. That
is roadmap PHP-03…07/PHP-08 work, to be separately approved.

Other security issues still open: no CSRF tokens (login, events, GET
logout); no ownership checks on mutations (`architecture.md` §4); output is
generally not HTML-escaped (stored XSS, including the gallery description,
and `javascript:` in a posted gallery `link`); no login rate limiting.

`www/test.php` and `www/test2.php` are separate entry points, so the
dispatcher cannot protect them. `test.php` still runs `generar(...)` on any
POST, and both are unauthenticated. They were left untouched because #11
edits them. They must be deleted, or denied at the web server, before any
deployment. The tracked `www/query.log` is removed by #11. With this change
nothing writes to it any more.

## 7. Needs HTTP-level verification (after the PostgreSQL installer, #9)

Not exercised here: no request went through `www/index.php`, `pg_connect`
or a real `pg_query_params`. **[hypothesis]** until checked:

1. A real login against the installed seed: the admin's legacy MD5 row is
   rehashed (inspect `contrasena` before and after), and the second login
   uses `password_verify`.
2. Set-Cookie attributes (HttpOnly, SameSite=Lax, Secure behind HTTPS);
   strict mode rejecting a forged `PHPSESSID`; the id changing after login;
   the cookie expiring on logout.
3. The public timetable viewer working anonymously end to end (window,
   trees, lists, timetable), and returning nothing for injection probes.
4. Anonymous POSTs to protected events returning "session expired" with no
   side effects, and authenticated non-`todos` users getting "Acceso
   denegado" for unmapped events while the UI stays usable.
5. `get_form_edit_Especialidad` / `get_form_edit_Planificacion` with a role
   holding exactly the three listed permissions.
6. `PropertiesFile` reading the real `bin/ancoragoh.conf` (the #11 example
   format, quoted strings and an integer port, is covered by the literal
   parser tests).
7. PHP 8 `TypeError`s on empty or non-numeric POST fields (#13 §4.6), now
   that the handlers are reachable only by authorized users.
