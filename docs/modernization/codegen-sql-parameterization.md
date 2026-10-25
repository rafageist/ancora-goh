# Parameterized SQL in the code generator (issue #35, security slice)

Issue: #35, under #12, after #13 (PHP 8.4) and #14 (authentication). The generator is still called cogePHP here; its rename waits for the maintainer's confirmation of the new name (the naming inventory is a separate PR).

The generator is the authority. Every change below was made in `dev/cogephp.php`, `dev/cogephpface.php`, `dev/cogephpservice.php` or `dev/cogephp.common.php`, and the outputs were regenerated from the committed JSON model. No generated file was edited by hand.

## 1. What the generator emits now

| Emitted code | Before | Now |
| --- | --- | --- |
| `XModel::add(...)` | Values pasted into `INSERT ... VALUES ('$id', '".$nombre."', ...)` | `INSERT ... VALUES ($1,$2,...)` with every value bound. Booleans are bound as `t`/`f`. `md5`-typed fields are bound as `PasswordUtil::hash()` (#14). |
| `XModel::get($pk)`, `remove($pk)` | `WHERE id = '$id'` | `WHERE id = $1` (composite keys `$1 AND $2 ...`), bound |
| `X::setField($v)` (item setters) | `setBy("id = '".$this->id."'", "field = '$value'")` | `UPDATE t SET field = $1 WHERE id = $2`, bound |
| `X::getChildTable()` (foreign-key getters) | `Load(..., "fk = '".$this->id."'")` | `Load(..., "fk = $1", array(...))`, bound |
| `XModel::getLimit($limit, $offset)` | Pasted | `LIMIT $1 OFFSET $2`, bound as numbers |
| `XFunctions::Name(...)` (function wrappers) | `select f('$a', $b)` | `select f($1, $2)`, bound |
| `XService::method(...)` | The service SQL with `$name` / `'$name'` interpolated | Each reference is replaced by `$n` at generation time and bound |
| `getJoinWithXByPhrase`, `getAllWithPhraseIn` | Words pasted into `LIKE '%word%'`. Also broken: `explode()` with one argument, `$this-getAnything` | `CAST(col AS text) LIKE $n`, words bound; columns and table names checked |
| `control/data/set/set_X.php`, `del_X.php` | A field that was not posted became the string `"null"` and was written: `'null'` into text columns, `NULL` into numbers, `true` into booleans | A field that was not posted is PHP `null`, and its setter is not called |

The value-carrying queries go through the new `PartModel` helpers, which are emitted by `dev/cogephp.php`:

- **`getAnythingParams($sql, $params, $asobjects)`** runs `pg_query_params` through `PostgresDatabaseImpl::getAsObjectsParams()` or the new `getAsArrayParams()`. A failed query throws `RuntimeException`. Its message contains only the SQL text with placeholders, never a value. Before, a failed query reached `pg_fetch_all(false)`, a `TypeError` (PHP-09); now the error is explicit, and it is no longer silently read as "no rows".
- **`PartModel::sqlValue($value, $kind)`** converts a value strictly. The kind comes from the field type in the model:
  - **`bool`**: `bool` and `boolean`;
  - **`number`**: the types that were pasted unquoted (`integer`, `bigint`, `long`, `double`);
  - **`text`**: everything else.

  The conversion rules:
  - Only `null` (`===`) becomes SQL `NULL`. `0`, `"0"` and `""` are values.
  - A number must be an int, a finite float or a numeric string.
  - A boolean is one of `true/false`, `1/0`, `"1"/"0"`, `t/f`, `true/false`, `y/n`, `yes/no`, `on/off`, in any case.
  - Text is a string, an int or a finite float.
  - Anything else throws `InvalidArgumentException` before any SQL is sent.
- **`PartModel::sqlIdentifier()`** checks table and column names: `name`, `table.column` or `schema.table.column`.
- **`PartModel::sqlSelectList()`** checks a select list. It accepts `*`, names, function calls and `AS` aliases, and rejects quotes, literals, `$`, `;` and comments. It guards `$fields` in every read method and the column of `length()`.

## 2. Behaviour changes

- **`NULL` is strict.** An omitted text value was stored as `''` and is now `NULL`. A `NOT NULL` column then rejects it (as `RuntimeException`), as it already did for numbers, where the old code produced a syntax error.
- **Booleans.** The old emitted test `$v == true || ... "t"` treats any non-empty string as true in PHP 8, so `"f"` and `"false"` were stored as **true**. They are now false, and unknown values are rejected.
- **Invalid numbers** (`""`, `"abc"`, `"1; DROP ..."`) are rejected in PHP. Before, they produced a SQL syntax error, or worse.
- **Update forms** no longer overwrite fields that were not posted (see the table above).
- **Function arguments are untyped parameters**, like the quoted literals used before, so PostgreSQL resolves the function from its declared types. Integers and booleans used to be pasted as typed constants. This could only matter for overloaded functions with the same number of arguments; three are in the model (`First`, `goIf`, `Greatest`), and no PHP code calls them.
- `getJoinWithX()` now honours `$asobjects`; it ignored it before. Nothing calls it.
- The HTTP end-to-end runs (local and Docker) are unchanged: 8/8 placed, 0 collisions, same placement digest.

## 3. Hand-written code (#44)

The raw-fragment methods `getAllBy`, `getLimitBy`, `setBy`, `set`, `removeBy`, `getJoinWithXBy(Phrase)` and `Load` still take a **trusted SQL fragment** (`$where`, `$set`). The callers put ORDER BY clauses and function calls in them, so the fragment cannot be bound as a whole. Each method takes an optional `$params` array, which binds `$1..$n` in the fragment.

### 3.1 Migrated call sites

The hand-written callers are now migrated (#44). The scope is `control/custom/`, `control/*.php`, `view/custom/`, `view/windows.php`, `view/wins/`, `www/` (not the third-party `www/js/`), `bin/` and the application code in `lib/` (not `lib/parsers/`, and not the database drivers in `lib/enterprise/gendb/`, which are the sink).

Before #44 the scope had **78 call sites in 59 files** that put a value into the fragment:

| Values | Call sites |
| --- | --- |
| `$_SESSION['user']`, the ownership filter `creador = ...` (two also bind a second value) | 44 |
| `$_POST` read inline (`get_browser_Brigadas_filtradas.php`) | 1 |
| Locals read from `$_POST`, including the unquoted numbers `dia`, `turno` and `prioridad` | 27 |
| Row values in templates and loops (`{$esp['id']}`, `{$r['id']}`, ...) | 5 |
| `bin/deluser.php` (the nick typed at the console) | 1 |

The previous count (68 in 52) came from a grep. The token scan also finds the calls in templates and `bin/`.

Each call site now has a constant fragment with `$n` placeholders and passes its values as `$params`, for example:

```php
$m->getAllBy("creador = '{$_SESSION['user']}' ORDER BY descripcion");                    // before
$m->getAllBy('creador = $1 ORDER BY descripcion', "*", false, array($_SESSION['user']));  // now
```

Other changes:

- **No dynamic identifiers.** ORDER BY clauses, select lists and function names are constant text. No call site took them from input.
- **No trailing `;`.** The one fragment that ended in `;` (`get_browser_DesgloseDetalles.php`) lost it.
- **`get_minibrowser_publico.php`** already bound its values (#14), but it built its SQL text from an internal allowlist. It now makes four calls with constant SQL text, and the last posted id still wins.

Nothing remains to migrate. `dev/tests/codegen/run.php` scans the scope and has no allowlist.

### 3.2 Behaviour changes

- **The ownership filter is applied.** 10 calls `Load(null, null, "creador = '...'")` passed the filter as the *field list*, so `Load()` read every row of every user. They now pass `Load(null, null, "*", 'creador = $1', array($_SESSION['user']))`, so each user sees only their own rows. The calls are in:
  - `get_data_especialidades`, `get_data_niveles`, `get_data_planificaciones`;
  - `get_minibrowser_especialidades`, `get_minibrowser_niveles`;
  - `get_browser_Organizacion` (3 calls);
  - `custom_getui_ExploradorHorarios`;
  - `get_form_edit_zpriori`.

  The earlier count of 9 missed one.
- **Generated `Load()`.** It tested `$limit=null` (an assignment), so a limit was never applied. It now applies limit and offset when both are given, and an offset of `0` counts as given. `Reload()` used undefined variables. `Load()` now records its arguments in `$last_offset`, `$last_limit`, `$last_fields`, `$last_where` and `$last_params`, and `Reload()` clears the collection and repeats the last `Load()`. No hand-written code passes a limit or calls `Reload()`.
- **Failed queries throw.** A query that fails now throws `RuntimeException`, because parameterized queries go through `getAnythingParams()`. Before, it was read as "no rows". This happens, for example, when a non-numeric `prioridad` is posted.
- **Missing values are `NULL`.** A posted value that is missing is now bound as `NULL` instead of `''`. Both match no row.

### 3.3 Not changed

- **MySQL driver.** `getAsArrayParams()` was added to `PostgresDatabaseImpl` only, like the #14 methods. The unused MySQL driver is untouched.
- **`bin/deluser.php` and `bin/deluser_byid.php`** include files that no longer exist (`../lib/console.lib`, `../modelo/...`), so they cannot run. Only the call site was migrated.
- **Ownership is enforced only on reads.** Updates and deletes by id (`setBy('id = $1', ...)`, `removeBy(...)`, generated `set_X` / `del_X`) do not check `creador`. A user who knows another user's id can still change that row. This is an authorization gap (#14), not an injection.

## 4. Tests and evidence

| Test | What it shows | Result |
| --- | --- | --- |
| `php dev/tests/codegen/run.php` (no database) | A static scan of the tracked generated files finds no variable in SQL text except the trusted fragments; a negative control shows the scan detects the pre-#35 emitted code. Recorded calls show that injection probes are bound through `add`, `get`, `remove`, setters, foreign-key getters, `getLimit(By)`, phrase search, function wrappers and the service class. `sqlValue` NULL / 0 / `"0"` / `""` / boolean semantics; select-list and identifier checks; the service generator refuses unknown `$` references, references inside literals and `{$...}`; set/del handlers have no `"null"` placeholder | 322 passed |
| `dev/sql/tools/with-ephemeral-pg.sh dev/tests/codegen/pg-probes.sh` | Real PostgreSQL 16 through `PostgresDatabaseImpl` on a disposable cluster: hostile values are stored and read back byte for byte; hostile keys match and delete nothing; the tables survive; `''`/`0` are values and `null` is `NULL` on real columns; booleans; no SQL syntax error in the server log | 18 passed |
| Negative control: the same probes against `master` | The first probe, `x'); DROP TABLE profesor; --`, changes the statement: `INSERT has more target columns than expressions` | fails, as expected |
| `php dev/tests/security/run.php` (#14) | The `UsuarioModel::add` / `setContrasena` test now checks bound parameters | 617 passed |
| `dev/ci/check-generated.sh` | A full regeneration reproduces all 679 tracked generated files, apart from `@updated` | ok |
| `dev/ci/php-lint.sh`, `dev/ci/php84-check.sh` | 935 files; 935 files, 130 classes | ok |
| `with-ephemeral-pg.sh dev/e2e/run.sh` | HTTP login, data entry, generation | 28/28; 8/8 placed, 0 collisions, digest `4199818b…` |
| `docker/e2e.sh` | The Docker Compose deployment | 55/55 |

Hand-written call sites (#44), measured on the #44 branch:

| Test | What it shows | Result |
| --- | --- | --- |
| `php dev/tests/codegen/run.php` | Adds a token scan of 225 hand-written files, with no allowlist. Every SQL-text argument of a sink is constant, and no SQL string interpolates (`$x`, `{$x}`, `${x}`) or concatenates a variable. 13 negative patterns are detected, and so are five pre-#44 files read from git history. `Load()` applies limit and offset, including an offset of 0. `Reload()` repeats the last `Load()` without duplicating rows | 363 passed |
| `php dev/tests/security/run.php` | Dispatches migrated handlers against `StubDatabase`: `get_browser_Brigadas_filtradas`, `get_minibrowser_profesores`, `get_browser_GruposXClasif`, `get_data_especialidades`, `get_browser_Organizacion` (fixed `Load()`), `custom_set_Restricciones_Periodo` and `set_prioridad_pxact` (numbers). Probes appear only in params, the ownership filter is in the SQL text, and only the user's own rows are listed | 654 passed |
| `with-ephemeral-pg.sh dev/tests/codegen/pg-probes.sh` | Unchanged | 18 passed |
| `check-generated.sh`, `php-lint.sh`, `php84-check.sh`, `check-secrets.sh` | 679 generated files match; 937 files lint-clean; 130 classes | ok |
| `with-ephemeral-pg.sh dev/e2e/run.sh` | HTTP journey | 28/28; 8/8 placed, 0 collisions, digest `4199818b55d4f1c44f2cba24f0367e29` |
| `docker/e2e.sh` | Docker Compose deployment | 55/55, same digest |

CI: `.github/workflows/codegen-sql.yml` runs both new suites.

## 5. Regenerating

```sh
dev/ci/regenerate.sh        # regenerate from dev/src + dev/services, copy back changed files
dev/ci/check-generated.sh   # must report that every file matches
```

`regenerate.sh` copies back only the files whose content changed apart from the `@updated:` lines, and it keeps the committed timestamps in those lines. A generator change therefore shows up as its semantic diff only (312 files in this change). Making the timestamps themselves deterministic belongs to the reproducibility slice of #35.
