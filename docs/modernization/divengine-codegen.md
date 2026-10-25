# Divengine Codegen

Issues: #35, #39. Divengine Codegen (CLI `divengine-codegen`, directory `dev/codegen/`) is the model-driven generator of the Áncora GOH data access layer, views and handlers. It replaces the procedural emitters of **cogePHP** ("C-oge O-tro GE-nerador para PHP", by Rafael Rodríguez Ramírez, 2010). The generated code is unchanged: the regeneration guard proves that the new generator writes the same bytes as cogePHP, apart from the `@updated:` timestamps.

It is a build-time tool. It never runs in the web application, and no Docker image contains it (`.dockerignore` is an allowlist that keeps `dev/` out, apart from `dev/sql/`).

## 1. Pipeline

```
JSON model (dev/src/*.json, dev/services/*.json)
  -> ModelLoader      read the relaxed JSON, validate, build typed objects
  -> *Generator       prepare the data of each template (names, packages, kinds of bound parameters)
  -> Renderer (Div)   render the external templates (dev/codegen/templates/**)
  -> Writer / check   write under an output root (never deletes), or compare with the repository
```

| Step | Code |
| --- | --- |
| Relaxed JSON reader | `src/Model/JsonReader.php`: comments, unquoted keys, trailing commas, as in the 2010 files. A test checks that it decodes every committed model file exactly like `Services_JSON` (`lib/parsers/json/JSON.php`). |
| Validation | `src/Model/ModelLoader.php` rejects a model before rendering, with the file and the reason: <br>- missing names; <br>- unknown field types; <br>- names that are not SQL/PHP identifiers; <br>- duplicate tables or fields; <br>- primary or foreign keys on missing columns; <br>- model file names with a directory part; <br>- backticks (the template tag delimiter). |
| Naming, types | `src/Naming/Naming.php` (class names, packages, titles), `src/Sql/SqlTypes.php` (kind of bound parameter: `bool`, `number`, `text`), `src/Sql/ServiceSql.php` (`$name` -> `$n` placeholders of a service query, refusing what it cannot bind). |
| Generators | `src/Generator/ModelGenerator.php`, `UiGenerator.php` and `ServiceGenerator.php`: data preparation only. |
| Rendering | `src/Template/Renderer.php` and `src/Template/Dialect.php` (Div). |
| CLI | `bin/divengine-codegen`, `src/Cli/Application.php`. |

## 2. Templates

All generated code is in `dev/codegen/templates/`; no generated code body remains in PHP strings.

| Family | Template | Output (Áncora profile) |
| --- | --- | --- |
| SQL creator / dropper | `model/creator.sql.tpl`, `model/dropper.sql.tpl` | `model/AncoraGOH.{creator,dropper}.sql` |
| PartModel | `model/PartModel.php.tpl` | `model/data/PartModel.php` |
| Database views | `model/DatabaseViews.php.tpl` | `model/data/views/PublicDatabaseViews.php` |
| Table model / collection / item | `model/Model.php.tpl`, `model/Collection.php.tpl`, `model/Item.php.tpl` | `model/<package>/<Class>{Model,Collection,}.php` |
| Function wrappers | `model/Functions.php.tpl` | `model/<package>/<Schema>Functions.php` |
| Services | `services/Service.php.tpl` | `model/data/services/<Name>Service.php` |
| Gallery, grid and combo views | `ui/gallery.tpl.tpl`, `ui/grid.tpl.tpl`, `ui/combo.tpl.tpl` | `view/{gallery,grid,combo}/` |
| Gallery and combo controllers | `ui/gallery-controller.php.tpl`, `ui/combo-controller.php.tpl` | `control/view/{gallery,combo}/` |
| add / set / del handlers | `ui/add.php.tpl`, `ui/set.php.tpl`, `ui/del.php.tpl` | `control/data/{add,set,del}/` |

The templates keep the bound-parameter semantics of #35/#44 exactly: every value is a `$n` placeholder converted by `PartModel::sqlValue()`. Only the trusted `$where`/`$set`/`$extrawhere` fragments remain SQL text.

### Template syntax (Div, backtick dialect)

Div's default tags (`{$x}`, `[$list]`, `?$x`, `<? ?>`) are everywhere in PHP and SQL. In this dialect every Div tag is wrapped in backticks, a character that the outputs and the models never contain, so everything else is literal:

| Tag | Meaning |
| --- | --- |
| `` `{$name}` `` | value; `` `{$rt.db_global}` `` is a member of an array |
| `` `[$list]` ... `[/$list]` `` | loop; `` `{$value}` `` is a scalar item, `_order` is 1-based, `_is_last` is true on the last item |
| `` `?$flag` ... `$flag?` `` / `` `!$flag` ... `$flag!` `` | rendered when the flag is true / false; `` `@else@` `` is the else branch |
| `` `<!--{ ... }-->` `` | template comment |
| `` `{\s}` `` | one space |

The conventions are the following:

1. **No trailing white space in a template.** Editors strip it, so a meaningful trailing space is written `` `{\s}` `` (a test enforces this).
2. **The final newline of a template file is not output.**
3. **Block tags go at the end of the line before the block.** The block body then starts with its own newline: `` X`[$l]`\nBODY`[/$l]`\nY ``. Div strips one leading and one trailing space from a conditional body, so a body must not start or end with a literal space; use `` `{\s}` ``.
4. **Unresolved tags are refused.** A rendered output that still contains a backtick fails.

The templates never use Div expressions, macros or includes, so no PHP code is evaluated while rendering.

## 3. Profile

`dev/codegen/profiles/ancoragoh.php` (the **Áncora GOH profile**) holds everything Áncora-specific that was in `dev/cogephp.conf`, with the same values:

- the project name and the model location;
- the introspected schemas, and the ignored tables and columns;
- the class naming rules and the PHP keyword prefix;
- the packages (schema selector → `model.*`);
- the type maps;
- the UI spelling and title tables, and the combo descriptions;
- the output layout;
- the runtime conventions of the generated code (`$dbconnection`, `getReplica`, `PasswordUtil::hash`, the view and `fdm_*` hook paths).

The generator logic and the templates read project settings only through `Profile`.

There are two documented legacy quirks that the profile keeps for byte parity:

1. **SQL types of the creator script.** The creator script looks the model types up in the database-to-model map, as cogePHP did, so most types are written unchanged.
2. **Combo descriptions.** They are keyed `schema.table`, which is `.table` for public tables. The historical `planificacion` entry therefore never matched.

`Naming::className` fixes one latent cogePHP bug. The public-table test was an assignment that rewrote the schemas. No Áncora table has the name of its schema, so the fix does not change any output.

## 4. CLI

```
composer --working-dir=dev/codegen install        # once; vendor/ is not committed
dev/codegen/bin/divengine-codegen model|ui|services|all [--output DIR] [--profile FILE] [--root DIR]
dev/codegen/bin/divengine-codegen check
```

- **Paths.** Paths are resolved from the repository root, not from the working directory.
- **Writes.** Generation only creates or overwrites files. It never deletes, and it refuses paths that leave the output root.
- **`check`.** It renders everything in memory and compares each file with the repository, ignoring the `@updated:` lines. It never writes.
- **`SOURCE_DATE_EPOCH`.** When it is set, the `@updated:` timestamp is that moment in UTC, so two runs write identical bytes.

The CI scripts call this CLI:

- `dev/ci/check-generated.sh` runs `divengine-codegen check`.
- `dev/ci/regenerate.sh` runs `divengine-codegen all` into a temporary directory and copies back the semantic changes only, keeping the committed timestamps.

### Regenerating after a template change

1. Edit the template (or the data preparation in `src/Generator/`).
2. Run `dev/ci/regenerate.sh`.
3. Run `dev/ci/check-generated.sh`, which must pass.
4. Commit the template and the regenerated files together.

## 5. Checks

The `divengine-codegen` job of `.github/workflows/php-lint.yml` runs these checks:

- `composer validate --strict`;
- `composer install` (locked);
- `composer audit`;
- `phpcs` (PSR-12 on `src`, `bin`, `tests`, `profiles`);
- PHPUnit;
- `dev/ci/check-generated.sh`.

The legacy PHP 8 token checker (`dev/ci/php84-check.sh`) excludes `dev/codegen/`: it misreads `declare(strict_types=1)` and named arguments.

The parity check reports **681 generated files** that match:

- the 679 files that carry `@updated:` timestamps (the count the previous guard reported);
- the 2 SQL scripts, which have no timestamp.

A broken template makes the check fail. For example, changing `return null;` in `model/Collection.php.tpl` makes 61 files differ.

## 6. Dependencies and licenses

| Package | Version (locked) | License | Use |
| --- | --- | --- | --- |
| `divengine/div` | 6.1.3 | GPL-3.0-or-later | template engine |
| `divengine/functions` | 1.0.1 (dependency of div) | GPL-3.0-or-later | helpers |
| `phpunit/phpunit` | 13.x (dev) | BSD-3-Clause | tests |
| `squizlabs/php_codesniffer` | 4.x (dev) | BSD-3-Clause | PSR-12 |

Div is GPL-3.0-or-later. Áncora GOH is GPL v3 (README), so the licenses are compatible.

Div is used only at build time. The generated files are the output of the program, not a derived work of Div, and they are not shipped with Div. Neither Div nor `vendor/` is part of the web runtime or of any image. Redistributing `dev/codegen` together with its `vendor/` would have to follow the GPL (source and license notices). The `composer.json` license field (`GPL-3.0-only`) follows the README's "GPL v3". The maintainer may change it when choosing the distribution license.

## 7. Provenance and remaining work

cogePHP was written by Rafael Rodríguez Ramírez in 2010. The generated JSON model files keep their cogePHP banners: they change only when the model is refreshed.

Remaining work (not in this PR):

- **Introspector.** Port the PostgreSQL → JSON introspector (`dev/crea_modelo_pg.php`) as `divengine-codegen introspect`, against disposable databases only.
- **Retire cogePHP.** Delete the historical cogePHP entrypoints:
  - `dev/cogephp.php`, `dev/cogephpface.php` and `dev/cogephpservice.php`;
  - `dev/cogephp.common.php`, `dev/cogephp.conf` and `dev/path.php`;
  - the destructive `dev/*.sh` and `dev/*.bat` wrappers.

  They still work, and nothing calls them any more. Before deleting them:
  1. port the introspector;
  2. move the `serviceSqlParams` subprocess test of `dev/tests/codegen/run.php` to `ServiceSql` (already covered by `tests/ServiceSqlTest.php`);
  3. update the remaining references (README tree, `lib/parsers/json/JSON.php` comment, the `php84-check.sh` MySQL allow-list comment).
- **Second-profile proof.** Generate a tiny non-Áncora model with its own profile and lint the output, to prove the generic/profile split.
- **PSR-4 and PSR-12 for generated classes.** This changes the output, so it needs a separately reviewed diff (#39, steps 3 and 4).
