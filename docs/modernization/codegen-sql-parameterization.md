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

## 3. What is not covered (hand-written code)

The raw-fragment methods `getAllBy`, `getLimitBy`, `setBy`, `set`, `removeBy`, `getJoinWithXBy(Phrase)` and `Load` still take a **trusted SQL fragment** (`$where`, `$set`). The callers put ORDER BY clauses and function calls in them, so the fragment cannot be bound as a whole. Each method now takes an optional `$params` array, which binds `$1..$n` in the fragment.

The hand-written callers have not been migrated. A scan of `control/`, `view/`, `www/`, `bin/` and `lib/` (generated folders excluded) finds **68 call sites in 52 files** that build the fragment from variables:

- 39 interpolate `$_SESSION['user']`;
- one interpolates `$_POST` directly (`get_browser_Brigadas_filtradas.php`);
- most of the others interpolate locals read from `$_POST`.

They remain SQL injection points, and are the next slice of this work.

Related findings, not changed here:

- **Ownership filter lost.** 9 calls `Load(null, null, "creador = '...'")` pass the ownership filter as the *field list*. `Load()` then reads every row; the filter was never applied (an ownership gap, also reported in #14).
- **`Load()` limit bug.** `Load()` contains `$limit=null` (an assignment), so a limit is never applied. That is a runtime defect for the PHP 8 slice of #35.
- **MySQL driver.** `getAsArrayParams()` was added to `PostgresDatabaseImpl` only, like the #14 methods. The unused MySQL driver is untouched.

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

CI: `.github/workflows/codegen-sql.yml` runs both new suites.

## 5. Regenerating

```sh
dev/ci/regenerate.sh        # regenerate from dev/src + dev/services, copy back changed files
dev/ci/check-generated.sh   # must report that every file matches
```

`regenerate.sh` copies back only the files whose content changed apart from the `@updated:` lines, and it keeps the committed timestamps in those lines. A generator change therefore shows up as its semantic diff only (312 files in this change). Making the timestamps themselves deterministic belongs to the reproducibility slice of #35.
