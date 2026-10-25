# License and provenance

**Áncora GOH is distributed under the GNU General Public License, version 3 only** (SPDX `GPL-3.0-only`). The full, unmodified license text is in [`LICENSE`](../LICENSE).

## Why "version 3 only"

- The original Áncora GOH project declares **GPL v3**, inherited from its Visual Basic 6 predecessor. The VB6 project's README also names GNU GPL **version 3** and ships the GPLv3 text.
- Neither project grants the "or (at your option) any later version" permission. Without that grant, `GPL-3.0-only` is the expression the available attribution supports (license review on #39).
- Any future change to `GPL-3.0-or-later` must be an explicit decision by the copyright holder(s), recorded in its own issue and PR. Separately copyrighted material must be audited first.

## Provenance

| Part | Origin | License |
| --- | --- | --- |
| Áncora GOH application (2010 snapshot, `b42aa03`) and its 2026 recovery and modernization | Rafael Rodríguez Ramírez (original author, 2010 graduation thesis); recovery and modernization in this repository | GPL-3.0-only |
| Code generator: cogePHP (2010), re-implemented as Divengine Codegen (`dev/codegen/`) | Rafael Rodríguez Ramírez / Divengine | GPL-3.0-only (`dev/codegen/composer.json`) |
| Drupal 11 application (`modern/`): custom modules and theme | this repository | GPL-3.0-only (`modern/composer.json`) |

## Third-party components and compatibility

| Component | Where | License | With GPL-3.0-only |
| --- | --- | --- | --- |
| Drupal core, Drush and their Composer dependencies | `modern/` (installed by Composer, not committed) | Drupal core GPL-2.0-or-later; dependencies mostly MIT/BSD | compatible (used under GPLv3) |
| `divengine/div`, `divengine/functions` | `dev/codegen/` (build-time) | GPL-3.0-or-later | compatible (used under v3) |
| jQuery 1.x, jQuery UI and plugins | `www/js/jquery/` (legacy UI) | jQuery: dual MIT / GPL; plugins: various, mostly MIT/GPL. Not yet audited one by one. | to be audited before any redistribution of the legacy UI |
| PEAR `Services_JSON` | `lib/parsers/json/JSON.php` | BSD-style (header in the file) | compatible |
| Other Composer dev tools (PHPUnit, PHP_CodeSniffer, Drupal Coder) | installed by Composer, not committed | BSD-3-Clause / GPL-2.0-or-later | development only |

Copyright and license notices in third-party files must be kept. `composer licenses` in `modern/` and `dev/codegen/` lists the exact licenses of the locked dependencies.
