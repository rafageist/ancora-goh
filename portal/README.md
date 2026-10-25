# Divengine Timetables: public portal (R0)

Small, separate PHP 8.4+/8.5 application for `timetables.divengine.com` R0: service description, access-request form, email verification, privacy notice and terms. Every account is approved by a person through the operator CLI. Submitting or verifying never creates an account, and nothing of the legacy scheduling application is reachable here.

- **Code:** Composer (`composer.lock` committed), PSR-4 `Ancora\Portal\` in `src/`, PSR-12 (`vendor/bin/phpcs`), PSR-3 logging (`psr/log`).
- **Tests:**
  - `vendor/bin/phpunit --testsuite unit`;
  - integration: `PORTAL_TEST_DSN=... vendor/bin/phpunit --testsuite integration` against a disposable database;
  - HTTP E2E: `dev/sql/tools/with-ephemeral-pg.sh portal/tests/e2e/run.sh`; add `PORTAL_E2E_SERVER=native` to run it through the production Caddy block and PHP-FPM pool.
- **Operator CLI:** `bin/portal help`.
- **Deployment and rollback:** `deploy/README.md`. Design and threat model: `docs/saas/`.
