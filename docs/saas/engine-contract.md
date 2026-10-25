# Scheduling engine contract: Drupal ↔ PostgreSQL MPI/Prioriza (issue #71)

Status: **proposed** (design record); date 2026-10-25. It depends on ADR-002 (#72) for the per-organization databases.

The MPI/Prioriza optimizer stays the characterized PL/pgSQL implementation (`dev/sql/generador`, `dev/sql/prioriza`). It is not rewritten in PHP (#66 §3). Drupal never runs `generar()` inside a web request. It submits a job, and a separate worker runs it with limits.

## 1. What the engine already guarantees (characterized)

| Property | Evidence |
| --- | --- |
| Deterministic placements for a fixed input | HTTP E2E digest `4199818b…` (8/8 placed, 0 collisions), stable across runs, in Docker and in CI |
| Termination for invalid day ranges, missing order and bad Prioriza input (#23) | cases 023, 025, 041, 044, 064, 065 |
| **Cooperative cancellation:** a row in `generador.cancelar (especialidad, nivel, periodo)` makes `generar()` stop at its next check, return normally, and keep what it has placed; the row is consumed | case 062 |
| **Hard timeout:** `statement_timeout` aborts the single `generar()` statement, and the whole run rolls back (one transaction) | case 066 |
| No collisions between teachers, places or brigades; other-resource occupancy (#24) | cases 080–083, E2E metrics |

## 2. Job contract

```
EngineJob {
  id            uuid
  site          the organization's site (ADR-002), never from user input
  scope         { specialty, level, period }   ids in the engine database
  options       { forma, parche, compactar, parche1, buscalugar }   validated enums/ints
  limits        { wall_seconds (Free plan: 120), statement_timeout = wall_seconds,
                  memory: worker cgroup / MemoryMax, one running job per site }
  state         queued -> running -> succeeded | failed | cancelled | timed_out
  result        { demands, placed, unplaced, collisions, overflow, digest, duration_ms }
  requested_by  Drupal user id (audited)
}
```

## 3. Data flow

1. **Drupal** (the organization's site) writes its academic entities. A **sync step** writes them into the site's engine database `tt_<slug>_engine`, using that site's engine runtime role and bound parameters only. The engine schema is the maintained legacy schema, installed by `dev/sql/install.sh` at provisioning.
2. Drupal creates the job: a row in a `job` table of the engine database, or a Drupal queue item. Its state is `queued`.
3. **The worker** is a CLI process (systemd service with `MemoryMax`, `CPUQuota`, `RuntimeMaxSec`), not PHP-FPM.
   - It claims one job per site with `SELECT … FOR UPDATE SKIP LOCKED`.
   - It sets `statement_timeout`, then calls `generar(...)` with bound parameters.
   - It records the metrics (the definitions of `tst.metrics`) and a placement digest.
4. **Cancellation** by a member of the site:
   - the worker inserts the `generador.cancelar` row, so the engine stops at its next check and the job ends `cancelled`, keeping what it placed;
   - the timeout stays as the hard stop (`timed_out`, everything rolled back).
5. **Read-back:** Drupal reads placements from the engine database through Views or a small read model, scoped to its site by construction: the connection belongs to that site.

## 4. Isolation and limits

- **One engine database and runtime role per organization** (ADR-001/002). A worker process handles one site's job at a time, with that site's credentials, which it reads from server-side configuration keyed by the job's site.
- **Free-plan quotas** (#55), enforced before queueing:
  - one running job per site;
  - a daily number of runs;
  - an input size limit (templates × groups).
- **Global concurrency** on the shared host: a fixed number of workers (1 at first). The capacity decision for R1 is #76.

## 5. Tests required (R1 gate)

- The engine regression suite (`dev/sql/tests`) and the placement digest run against the engine database installed by provisioning, on **PostgreSQL 18** (the host version), in CI.
- A job run through the worker on the E2E fixture gives the same digest as today.
- A cancel request ends the job `cancelled`. A forced timeout ends it `timed_out` with nothing kept.
- Two sites: a job of site A can never read or write site B's engine database. Its role has no `CONNECT` there, and a job id from A is rejected on B.
