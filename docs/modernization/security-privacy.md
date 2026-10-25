# Credential hygiene and privacy inventory

Issue: #11 (SEC-01), phase #7. Baseline: assessment in PR #5 (`docs/modernization/assessment.md`, "Risks").

This document records where credentials and personal data live in the repository, what this change removed from the **current tree**, and what still needs maintainer decisions. **No secret value, password hash, connection string or personal name is reproduced here.** Locations are given as file paths, plus line numbers or table names where that helps.

Evidence labels follow the assessment: **verified** means checked directly in the tracked tree or Git history. **Heuristic** means an automated pattern count that still needs human review.

## 1. Credentials in the tracked tree

| # | Location (before this change) | What it was | Classification | Status after this change |
|---|---|---|---|---|
| C1 | `bin/ancoragoh.conf` | Application DB role: host, port, db, user, password | Trivial, guessable default password (verified) | Replaced by `bin/ancoragoh.conf.example` with placeholder `CHANGE_ME`. Real file git-ignored. |
| C2 | `dev/cogephp.conf` (`$dbuser`, `$dbpass`) | Developer's **personal** PostgreSQL account used by the code generator | **Non-trivial personal password. Treat as compromised** (verified) | Read from `COGEPHP_DB_*` environment variables; password defaults to empty. |
| C3 | `dev/backup/drop_database.php` | `postgres` superuser connection string with inline password | Vendor-default style password (verified) | Uses libpq defaults (`PGHOST`/`PGPORT`/`PGUSER`/`PGPASSWORD` or `~/.pgpass`). |
| C4 | `www/test.php`, `www/test2.php` | Same superuser connection string, **inside the web root** | Same as C3 (verified) | Same as C3. These pages remain unauthenticated (see §6). |
| C5 | `dev/sql/datos/datos_por_defecto.sql:11` | Seed admin account with a well-known default password, stored as unsalted `md5()` | Documented default credential, not a leaked secret (verified) | **Replaced by #25** with an unusable value: the seeded administrator cannot log in until `bin/setpassword.php` sets a password. Installations made from the 2010 seed must still change it (checklist below). |
| C6 | `www/query.log` | Debug SQL log written by `PostgresDatabaseImpl::getDefault($sql, true)` into the current directory (the web root) | Contained a user id, role names and entity ids. No password or hash found (verified) | File removed from the tree and git-ignored. The debug code path is unchanged. |

Other places checked:

- **Backup and restore scripts** (`dev/backup/do_backup.*`, `restore.*`, `drop_database.*`) pass account names with `-U`, but no passwords. Verified.
- **Generator reintroduction path.** `dev/cogephp.php` (lines 118 and 1051) writes the current `$dbpass` into the default arguments of the generated `AncoraGOHModel`/`AncoraGOHControl` constructors. The generated files currently contain no password literal (verified with `git grep`). But regenerating with a password set in `COGEPHP_DB_PASSWORD` would write it into tracked PHP files. **Prefer `~/.pgpass` with an empty `COGEPHP_DB_PASSWORD`.** The CI guard (§5) flags literal password assignments, but cannot detect a password embedded as a default parameter value under another name.
- **No API keys, tokens, private keys, secret URLs or OAuth material were found.** Patterns searched: `pass`, `password`, `passwd`, `secret`, `token`, `api_key`, `pg_connect(`, `PGPASSWORD`, `postgres://` URIs carrying a user and password, and `.pem`/`.key` files. Matches in vendored NuSOAP are proxy-password *parameters*, not credentials. Verified. This was a pattern search, not an entropy scan, so a randomly named secret could be missed.
- **Request logging.** Apart from C6, no other code path writes request data to tracked files. Verified for `fopen(...,"a")` writers in `lib/`, `control/` and `www/`.

### Exposure in Git history (verified)

- C1, C3 and C4 have been present since the first historical import commit (`b42aa03`) and remain in every later commit on `master`.
- C2's personal password appears only in `dev/cogephp.conf`, and has also been present since `b42aa03`.
- This change removes them from the **current tree only**. They stay readable in history, in any clone or fork, and possibly in caches. Section 4 lists the options. **No history was rewritten and no credential was rotated.**

## 2. Personal and historical data

| Dataset | Content (counts only) | Classification | Handling |
|---|---|---|---|
| `dev/backup/ancoragoh.sql` | 2010 plain-format `pg_dump`. Includes 1 `usuario` row with an **unsalted MD5 password hash**, 1 user–role assignment, 9 `profesor` rows with personal names, and about 350 teacher-linked rows (assignments and restrictions) | **Private historical evidence. Not suitable for redistribution or as a test fixture** | Kept unchanged as the reference oracle (decision in #6/#8). Must not be ingested by CI or tests. |
| `dev/backup/ancoragoh.custom`, `dev/backup/ancoragoh.tar` | The same 2010 dump in custom and tar formats (binary) | Same as above | Same as above. They duplicate the exposure, so any history remediation must cover all three. |
| `dev/sql/generador/generador.log` | Generator trace: entity ids, timings and subject names. Only one capitalized word pair found, and it is a subject name, not a person | Low risk (verified) | Kept; the assessment uses it as evidence. |
| `dev/sql/datos/datos_por_defecto.sql` | Seed: roles plus one admin account with an `example.com` address (see C5) | Synthetic | Kept. |
| `www/test.php` | Hard-coded generator call with 2010 entity ids | Ids only | Kept (see §6). |

**VB6 sample files (`rafageist/ancora-vb6`, `archivos_ejemplos/**/*.anc`, not modified).** These are ISO-8859-1 text. A heuristic counted CSV fields shaped like `Capitalized Capitalized …`. Those can be people **or** subjects, so the counts are only a triage signal.

| Group | Files | Name-shaped fields | Recommendation |
|---|---|---|---|
| Faculty datasets | `electrica/electrica_S1_0607.anc`, `electrica/electrica_S2_0607.anc`, `informatica/informatica_S1_0809.anc`, `demo/AncoraDemo_Electrica Semestre I  2006-2007.anc`, `demo/d1.anc`, `citi.anc` | 13–68 each | **Keep private.** These appear to be real institutional timetables. Do not use or redistribute them until they are reviewed and anonymized. |
| Smaller faculty or test files | `arquitectura/arquitectura_s1_0607.anc`, `informatica/INFORMATICA2007_S2.ANC`, `informatica/Copia de INFORMATICA2007_S2.ANC`, `informatica/informatica_S2_0708.anc`, `informatica/nuevaprueba.anc`, `prueba_da_bateo_no_se.anc` | 2–10 each | Review needed before reuse. |
| Demo scenarios | `demo/AncoraDemo_Autom1 *.anc` (7 files), `demo/d2.anc`–`d4.anc`, `demo/AncoraDemo_Primaria.anc` | 3–10 each | Probably demo data, but **not verified**. Review before reuse. |
| Empty or minimal | `informatica/infos2.anc`, `informatica/new.anc`, `informatica/test.anc`, `prueba.anc`, `test.anc` | 0 | Low risk. |

**Maintainer decision (2026-10-25):** historical dumps and faculty datasets must not be used as public fixtures. The historical originals are preserved unchanged, and CI uses synthetic data only (§3).

## 3. Synthetic fixture strategy

- Database tests (#9, #10) use **only hand-written or generated synthetic fixtures** committed as SQL. Examples: teachers named `Profesor 01`…, rooms `Aula A1`…, groups `Grupo 1A`. Ids are deterministic. Addresses use `example.com` or `example.org` only.
- Fixtures are built from the **schema**, never by copying or transforming rows of the 2010 dump or the `.anc` files.
- An anonymized derivative of the dump is **not** produced here. If the maintainer wants one later:
  1. Transform every column on an explicit allowlist and drop every column not on it.
  2. Re-key ids.
  3. Drop all `seguridad.*` rows.
  4. Diff-check that no original free text remains.
  5. Have the maintainer review it before publishing.
- Use test accounts with throwaway passwords created by the test itself. Never use historical accounts.

## 4. Maintainer decisions

**Decided (2026-10-25): option A.** Rotate the affected credentials and preserve Git history. Rotating external credentials is a **maintainer action**: the agent is not authorized to do it, and did not. No history rewrite is planned (options B and C below are not chosen). Until the maintainer completes the checklist, treat every value listed in §1 as compromised.

### Credential rotation checklist

1. Treat the C2 password as compromised. Change it on every PostgreSQL server and service where that personal account or password was ever used (including password reuse outside PostgreSQL).
2. Change the `postgres` superuser password (C3/C4) and the application role password (C1) on any server that still uses those values. Prefer peer/socket or `scram-sha-256` authentication, and restrict `pg_hba.conf` to local connections.
3. Change the seed admin password (C5) on any existing installation. Check `seguridad.usuario` for other accounts that still use unsalted MD5 hashes.
4. Confirm that no historical database restored from `dev/backup/` is reachable from a network.
5. After rotating, record the date and scope in a private channel, not in public issues.

### Git history remediation options

| Option | What it does | Pros | Cons |
|---|---|---|---|
| **A. Accept and rotate (chosen)** | Leave history as is. Rotate per the checklist. | No disruption, history is preserved, PR #5 and branch references stay valid | Old values remain publicly readable. Only safe once rotation is complete. |
| B. Targeted rewrite | `git filter-repo --replace-text` on C1–C4, plus optionally removing `dev/backup/ancoragoh.{sql,custom,tar}` from all commits; then force-push all refs and ask GitHub Support to purge cached views and PR refs | Removes the values from the canonical repository | Changes every SHA (breaks PR #5 links, existing clones and forks). Forks and third-party caches keep copies. Requires coordinating every open branch. |
| C. Archive and re-publish | Make the current repository private or archived, and publish a sanitized new repository with a provenance note | Clean public history | Loses issue and PR history continuity in the same repository, and is the most disruptive option. |

Whichever option is chosen, **rotation (checklist above) is required**, because a rewrite cannot recall copies that have already been exposed.

## 5. Preventing reintroduction

- `.gitignore` excludes `bin/ancoragoh.conf`, `.env*`, `*.local.conf`, `www/query.log` and local `dev/backup/ancoragoh.backup` output.
- `dev/ci/check-secrets.sh` runs in `.github/workflows/secret-hygiene.yml` on every push and PR. It fails, **without printing values**, when:
  - `bin/ancoragoh.conf` or `.env` is tracked;
  - a `password = "…"` properties line is anything other than empty or `CHANGE_ME`;
  - a PHP `$…pass… = '…';` literal assignment appears;
  - a libpq connection string or a `postgres://` URI has an inline password;
  - a shell or YAML file sets `PGPASSWORD=`;
  - a `*.log` file is tracked under `www/`.
- The three historical dump files are explicitly excluded from content checks, as documented exceptions.
- The guard is deliberately narrow and dependency-free. It is not a general secret scanner. Adopting gitleaks/trufflehog would add a CI dependency and belongs to a separately approved task.
- Verified: the guard reports violations of 5 rules in 6 files on `master` (`2881353`), and passes on this branch.

## 6. Local setup procedure

1. Application: `cp bin/ancoragoh.conf.example bin/ancoragoh.conf`, then edit host, port, dbname, user and password. Values are PHP expressions read by `lib/basic/file/PropertiesFile.php` (which uses `eval`), so keep them as quoted strings. The file is git-ignored.
2. Developer scripts (`dev/backup/drop_database.php`, `www/test*.php`) use libpq defaults. Export `PGHOST`, `PGPORT` and `PGUSER`, and put the password in `~/.pgpass` (mode 0600) rather than in `PGPASSWORD`.
3. Code generator (`dev/cogephp*.php`): set `COGEPHP_DB_HOST`, `COGEPHP_DB_PORT` and `COGEPHP_DB_USER` as needed, and authenticate through `~/.pgpass`. Leave `COGEPHP_DB_PASSWORD` unset so no password is written into generated code (§1).
4. Use only disposable local, socket-only clusters for experiments (see `docs/modernization/database-assessment.md` in PR #5).

## 7. Outstanding exposures and follow-ups (not fixed here)

- History still contains C1–C4 and the three dump files (§1, §4). This needs a maintainer decision.
- `www/test.php` and `www/test2.php` are unauthenticated pages in the web root. `test.php` runs `generar(...)` on POST. They should be removed or protected before any deployment. Belongs to #14 or a later cleanup: this change only removed their credentials.
- `PostgresDatabaseImpl::getDefault($sql, true)` still appends any SQL called in debug mode to `query.log` in the current directory, which is the web root. The login query (`control/Application.php:36`) is not called in debug mode today, but it embeds the MD5 hash, so enabling debug there would log it. #14 should disable or redirect this logging.
- `PropertiesFile` evaluates configuration values with `eval`. Configuration files must therefore be trusted input, which is another reason to keep them out of the repository. Hardening belongs to #14.
- Seed admin default password and MD5 storage (C5): #9 (installer) and #14 (`password_hash`).
- VB6 `.anc` files need human review (§2). Nothing was changed in `rafageist/ancora-vb6`.
- No active compromise was observed. There are no access logs in the repository to analyse, so this is **not** an assurance that the credentials were never used.
