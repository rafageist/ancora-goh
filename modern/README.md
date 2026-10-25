# Áncora GOH: Drupal 11 application

This directory is the new Áncora GOH application: a Composer-managed Drupal 11 project (issues #66 and #69). The legacy application in the rest of the repository is not part of it.

- Drupal core 11.4 (`drupal/core-recommended`, locked), Drush 13, PHP 8.4+ and PostgreSQL 16+. No contrib modules.
- `web/` is the web root. Custom code lives in `web/modules/custom/ancora` and `web/themes/custom/ancora_theme`.
- `config/sync/` holds the exported configuration. Every install uses `drush site:install --existing-config`.
- `web/sites/default/settings.php` holds no secrets. Settings come from `ANCORA_*` environment variables, and secrets from files named by `*_FILE` variables (see `settings/ancora.settings.php`).

Install from scratch into a disposable PostgreSQL cluster and run every check:

```sh
# from the repository root
dev/sql/tools/with-ephemeral-pg.sh modern/scripts/install-disposable.sh
```

The checks are phpcs, PHPUnit (Kernel and Functional) and an HTTP smoke test. Full documentation: [`docs/modernization/drupal-foundation.md`](../docs/modernization/drupal-foundation.md).
