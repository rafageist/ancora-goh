# Áncora GOH legacy assessment — summary

Issue: #2 (Phase 0 — Assessment & Recovery, epic #1). Status: documentation-only assessment. No application, SQL, asset or configuration file was changed.

Companion documents:

- [architecture.md](architecture.md) — PHP request lifecycle, module map, security-sensitive patterns, third-party inventory.
- [database-assessment.md](database-assessment.md) — schema/function inventory, installation reproducibility, generator trace and experiments.
- [functional-inventory.md](functional-inventory.md) — feature-by-feature comparison of wiki, PHP/PostgreSQL and VB6.
- [roadmap.md](roadmap.md) — small, independently reviewable follow-up tasks (proposals only; no issues were created).

## 1. Evidence vocabulary

Every claim in these documents carries one of these labels:

| Label | Meaning |
| --- | --- |
| **Documented intent** | Stated by the wiki (2010 thesis adaptation) or README/docs; not evidence of implementation. |
| **Observed (static)** | Read in source code at the cited path/line; behavior inferred, not executed. |
| **Verified (experiment)** | Reproduced by executing code in a disposable environment (see §4); the conditions are stated. |
| **Hypothesis** | Plausible defect or behavior that still needs a reproduction. |
| **Unknown** | Not determinable from the available sources. |

## 2. Executive summary

1. **The repository contains a real, substantially implemented system, but it cannot be installed or run on any supported runtime as committed.** The PostgreSQL side is the most complete part (61 tables, ~195 PL/pgSQL/SQL functions in source, 7 triggers, 26 rules). The PHP side is a code-generated CRUD layer (≈60 entities × Entity/Model/Collection + add/set/del/combo/gallery controllers) plus ~100 hand-written "custom" controllers and a jQuery 1.3 desktop-like UI.
2. **The MPI generator is real and works on small data when its environment is reconstructed.** In a disposable PostgreSQL 16 cluster with three syntax repairs, one dependency reordering and `plpgsql.variable_conflict = use_variable` (emulating pre-9.0 PL/pgSQL name resolution), `generar()` placed 4/4 activities of a synthetic dataset collision-free, honored continuation (re-run does not duplicate) and cancellation (`generador.cancelar`). The 2010 database dump in `dev/backup/ancoragoh.sql` restores on PostgreSQL 16 and contains a historical generated timetable (39 placements) that has **no teacher, place or brigade overlaps**.
3. **On unmodified PostgreSQL ≥ 9.0 the generator cannot run**: every restriction evaluation fails with `column reference "id" is ambiguous` (`dev/sql/tiempo/restriccion/valor_restriccion_generica.sql:26`). This is a **verified** blocker, and it has a one-line class of fix (rename parameters or declare `#variable_conflict use_variable`), which is a behavior-preserving modernization task.
4. **Installation from the repository is not reproducible.** The documented bundle (`dev/sql/result.sql`) is stale relative to its sources, its builder (`dev/sql/compilasql.php`) crashes on PHP 8, the source tree contains version-independent DDL syntax errors (`organizacion/grupo.sql:4`, `actividad/plantilla_de_actividad.sql:10`), `deploy.priority` orders `grupo` before the tables it references, omits 30 source functions (including `valor_restriccion_heredada` and `valor_restriccion_otro_recurso`, which the generator calls) and omits the occupancy RULEs in `dev/sql/ancoragoh.sql`. The historical dump is currently the only faithful, installable snapshot of the 2010 database.
5. **Several generator/constraint defects are verified, not hypothetical** (details and reproductions in [database-assessment.md](database-assessment.md) §6):
   - `posible_inicio` iterates PostgreSQL 1-based arrays from index 0 and stops before the last element, so a template's only eligible teacher/place is never checked (**verified**); the generator masks this for teachers/places by re-filtering later, but the MPI is over-permissive.
   - The `k` variable in the other-resources loop of `posible_inicio` is not re-initialized: with no teacher/place required it is `NULL` and the function **loops forever** (**verified**, the case raised in issue #2); otherwise essential-resource unavailability is ignored (**verified**).
   - `generar` sorts the Prioriza totals with a double swap that detaches totals from activities (**verified** on an extracted copy of lines 456–491: totals `{5,3,4,1}` give order `{4,3,1,2}` instead of `{4,2,3,1}`).
   - `generar` loops forever when the day search range is empty (`EXIT WHEN kk = CicloFin` with `CicloIni > CicloFin`) (**verified** with one teacher fully unavailable on day 1).
   - HRT is effectively limited to other-resource→other-resource inheritance because `check_hrt`, `dame_tipo_hijo_hrt/_padre_hrt` and `valor_restriccion_heredada` overwrite their result with successive `iif` calls (**verified**: a teacher→teacher HRT row is rejected by the CHECK constraint); on PostgreSQL 16, `dame_tipo_hijo_hrt` additionally fails with an unknown-type polymorphic error (**verified**).
   - `crear_cadena_aleatoria` returns an undeclared variable (`RETURN r`), so every DB-side default ID fails in the source tree (**verified**); the 2010 dump contains a correct version, proving source/database divergence.
6. **The PHP application has critical security defects and PHP 8 blockers** (static evidence): login SQL injection with unsalted MD5 (`control/Application.php:35-37`), an unauthenticated execution path (`access=public` runs any event handler, `control/Application.php:197-201`), event-level RBAC that never applies because of a key-parsing bug (`lib/basic/file/PropertiesFile.php:26`), `eval` of request-derived data in gallery templates, pervasive SQL string interpolation, committed database credentials in three files, PHP-4 constructors in 210 of 219 classes (silently not constructors in PHP 8) and 328 undefined-constant expressions in generated models (fatal `Error` in PHP 8).
7. **Functional coverage is uneven.** Implemented and wired: CRUD for planning, periods, organization, subjects/breakdowns/templates, resources, eligibility/priorities, period/zone restrictions, generator queue window, read-only timetable explorer and guest view. **Schema-only or broken**: conditional restrictions, real-date restrictions UI, HRT, educational workload, travel costs UI, manual timetable editing, analyses/statistics, export/print, audit trace, host restrictions. VB6 has working counterparts for manual editing, analyses, HTML/RTF/Excel publication and rejected-activity diagnostics that are candidates for recovery.

## 3. Confidence / evidence matrix

| Area | Finding | Evidence | Confidence |
| --- | --- | --- | --- |
| Entry point & lifecycle | Single front controller `www/index.php` → `Application::Run()` → POST `app_event_*` handlers included from `control/` | Observed: `www/index.php`, `control/Application.php:155-227` | High |
| Event wiring | 145 of 406 handlers are referenced from JS; all 61 `view/gallery` and 61 `view/combo` handlers and ~45 add/set/del handlers are unreferenced; 4 referenced events have no handler | Observed: event-name cross-reference (architecture.md §3) | High (literal references only) |
| Auth / RBAC | Login SQLi, MD5, public bypass, RBAC map never applied | Observed (static) | High; not exploited at runtime |
| PHP 8 compatibility | 4 parse errors (vendored libs), 210 PHP-4 constructors, 328 undefined constants, `eval`-based config | Verified: `php -l` (PHP 8.4.27) on 939 files; static grep | High |
| Schema installability | Committed bundle and current sources fail to install; DDL syntax errors; wrong order; omitted objects | Verified on PostgreSQL 16.15 (database-assessment.md §4) | High |
| Historical DB | 2010 dump restores on PG 16 (only role/debugger errors); 200 functions; 187 identical to sources; data with small historical timetable | Verified | High |
| PL/pgSQL compatibility | `id` ambiguity blocks every restriction check; `0THEN`; quoted `CREATE LANGUAGE`; BOMs; polymorphic `iif('', '…')` | Verified | High |
| Generator behavior | Works end-to-end on synthetic data under compatibility mode; continuation and cancel work; several logic defects reproduced | Verified with stated repairs | Medium-high (small data only) |
| Prioriza | Discrete leveling matches the wiki example; 8 criteria and senses match wiki Table 4; unweighted sum; ordering defect | Verified (`prioriza.nivelar`) + static | High |
| HRT semantics | Intended directional/non-transitive design visible in code, but only other-resource HRT can be stored | Verified | High |
| Conditional restrictions | Evaluation function references undefined variables; no UI wiring | Observed (static) | High |
| Educational workload | Functions exist; cost of the candidate template is added per existing activity instead of each activity's own cost; no UI | Observed (static) | Medium |
| Cancellation from UI | Likely blocked by PHP session file lock while the generation request is running | Hypothesis (static: `session_start()` without `session_write_close()`) | Medium |
| Concurrency | No locking; two generations on shared resources can double-book (uncommitted occupancy rows invisible to each other) | Hypothesis | Medium |
| Performance | 2010 log: 7 activities in 212 s; `fue_cancelado` polled hundreds of times per activity; full pass repeated `cantActEnCurso` times | Observed (`dev/sql/generador/generador.log`, `generar.sql`) | Medium |
| VB6 parity | VB6 is a richer desktop product (manual editing, analyses, publication) but lacks hygiene, conditional and real-date restrictions | Static (subagent source review of `rafageist/ancora-vb6`) | Medium-high |
| Licensing | `ancora-goh` has no LICENSE file; README asserts GPL v3 "inherited" from VB6 whose LICENSE was added in 2026; several vendored libraries lack license files | Observed | High (that the matter is unresolved) |

## 4. Validation performed

All experiments used throwaway copies under the session scratch directory and a **temporary PostgreSQL 16.15 cluster** created with `initdb` on a private Unix socket (port 55432, no TCP listener). The user's existing PostgreSQL cluster and the repository working tree were not touched. Nothing was deployed.

| Check | Tooling | Result |
| --- | --- | --- |
| PHP syntax lint | `php -l`, PHP 8.4.27, 939 `.php/.tpl/.lib/.frm` files | 935 pass; 4 parse errors, all in vendored libs (`lib/parsers/json/JSON.php:156`, `lib/enterprise/nusoap/{class.soapclient.php:365,nusoapmime.php:136,nusoap.php:7366}`) |
| Deprecated/security pattern scan | grep | see architecture.md §5 |
| Event wiring cross-reference | grep of `app_event_*` literals vs handler files | see architecture.md §3 |
| Install committed `dev/sql/result.sql` | `psql -f`, PG 16 | 68 errors (cascading from DDL errors) |
| Regenerate bundle from sources | `compilasql.php` on PHP 8.4 (scratch copy) | Fatal `TypeError` at `compilasql.php:55`; after a one-character scratch fix it produced a 7,689-line bundle that differs from the committed `result.sql` |
| Install regenerated bundle | PG 16 | 88 errors |
| Install repaired bundle (scratch only) | BOM strip, drop `CREATE LANGUAGE`, `0THEN`→`0 THEN`, two DDL fixes, `grupo` moved after `nivel`, prepend `generador/esquema_generador.sql`, append `ancoragoh.sql` | 2 errors remaining (`restringe_por_uso_de_otro_recurso`, compile error) |
| Restore historical dump | `dev/backup/ancoragoh.sql`, PG 16 | Loads; 301 errors are only missing role `rafa` and the `pldbgapi` debugger library |
| Function diff dump vs sources | normalized `prosrc` md5 | 187 identical; 12 only in dump; 5 only in sources; 1 different |
| Behavioral tests of `valor_restriccion_*`, `posible_inicio`, HRT, occupancy rules, `prioriza.nivelar`, ordering, `generar` | synthetic fixture (no personal data) | see database-assessment.md §6 |

Not performed: running the PHP web application in a browser or HTTP server; testing PHP 5.x or PostgreSQL 8.x; testing PostgreSQL 17/18; load or concurrency tests; any action against production or persistent systems.

## 5. Primary risks

1. **Behavioral fidelity vs. defect repair.** Several "historical" behaviors are defects (array indexing, HRT `iif`, sort). Fixing them changes timetables. Each fix needs a characterization test first and an explicit maintainer decision (historical behavior vs verified defect), per the epic.
2. **Source tree ≠ 2010 database.** The dump differs from sources in 18 functions and includes rules/objects not reproducible from `deploy.priority`. Choosing the dump, the sources or a reconciled baseline is a prerequisite for every DB task.
3. **Security.** The PHP application must not be exposed to any network before the authentication/authorization, SQL-injection and `eval` issues are removed.
4. **Privacy and licensing.** The dump contains a real (small) dataset including a user account with an MD5 password hash and teacher names; VB6 sample files contain apparent full teacher names. Neither repository currently has a resolved license position for all historical and vendored code.
5. **Generator non-termination.** Two reproduced infinite loops (`posible_inicio` `k`; empty day range in `generar`) plus a NULL-`orden` template hang mean any UI exposure needs timeouts/cancellation that actually work.
6. **Generated-code volume.** ≈670 generated PHP files share the same defects; fixing the generator (`dev/cogephp*.php`) or replacing the generated layer is cheaper than hand-editing, but must be decided deliberately.

## 6. Unknowns and verification questions

1. Which state is authoritative for Phase 2: `dev/backup/ancoragoh.sql` (dumped 2010-10-06, after the July 2010 code snapshots) or `dev/sql/` sources? Note the git import dates (July 2010) precede the dump date.
2. Did the 2010 deployment run on PostgreSQL 8.4 (where the `id` name collision silently resolved to the parameter)? The dump header and `bin/ancoragoh.conf` say "8.x"; the exact server version is not recorded in the dump.
3. Were HRT for brigades/teachers/places ever usable in practice, given `check_hrt`? (The dump contains no HRT rows.)
4. Are conditional restrictions (`restriccion_condicional`, 60+ columns) a finished design or a work-in-progress? The evaluator references undefined variables.
5. Intended semantics of `zpriori` values in PHP (the zone loop only constrains placement when `parche1 = true`); VB6 tries zone 1 first and treats 0 as never eligible.
6. Intended unit for educational workload (per activity vs per slot), and whether teacher/brigade scales differ (wiki *Educational-Workload* questions): code adds a per-activity cost, but uses the candidate template's cost for every existing activity.
7. Whether publication should be gated by `planificacion.activa` (guest view currently lists all plans).
8. License intent for this repository and consent of all historical authors; licensing of icons/wallpapers under `www/images/`.
9. Whether the wiki's "two traversal orders" map exactly to `forma = 1/2` (code: `forma = 1` iterates classifications outer, activity levels inner; otherwise the reverse).

## 7. Wiki-to-code discrepancy summary (for Phase 1, #4)

Full list in [functional-inventory.md](functional-inventory.md) §4. Highlights:

- *Architecture*: class names `AncoraApplication`, `EntityModel`, `Service`, `AncoraServer` do not exist; the code uses `Application`, `<Entity>Model`, `PersonsService`, `PublicDatabaseViews`, `<Schema>Functions` and the JS namespace `ancoragoh.server.*`.
- *Generation-Algorithm*: the code uses **8 criteria + a total column** (matching Table 4), **unweighted** (VB6 weights teachers/places ×2 and uses 9 criteria); subjects are sorted by descending activity count as documented (`dame_asig_segun_esp_anno_per`), but classifications are taken globally from all `clasificacion_de_actividad` rows ordered by `prioridad` (`dame_lista_id_clasif`), not per planning project; teacher selection is "least loaded first, then busiest days for that teacher".
- *MPI-and-Placement*: the alternative OR-combination of resources is not implemented; `and_mpi(listaidbrg, cd, ct, idplantilla, zona, excepto)` has an unused `zona` parameter.
- *Constraints-and-Inheritance*: HRT directionality/non-transitivity is present in the evaluator design, but storage is limited by `check_hrt`; exceptions (`excepcion_de_hrt`) are implemented in the evaluator.
- *Educational-Workload*: functions exist (`es_higienico_profesor/brigada`) and are called by the generator; answers to the page's questions are in database-assessment.md §5.6.
- *Analytics-and-Reporting* / *Use-Cases* (analysis, publication, manual editing): not implemented in PHP.
- *Data-Model*: "62 tables" (README) vs 61 found; "200+ functions" — 195 distinct names in sources, 200 in the dump.
- The repository's own `docs/` (April 2026) contains illustrative SQL that does not match source signatures (e.g. `and_mpi` in `docs/03-algoritmo/mpi-sql.md:194-215`, `forma` semantics at line 55-66) and links to documents that do not exist (`docs/index.md`).
