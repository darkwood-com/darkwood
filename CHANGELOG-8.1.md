CHANGELOG for 8.1
===================

This changelog references the relevant changes done in 8.1 releases of Darkwood.

All packages (`darkwood/navi`, `darkwood/flow`, `darkwood/media-bundle`,
`darkwood/ia-exception-bundle`) share the **same version** as this monorepo.
Tags use the format `vMAJOR.MINOR.PATCH` (for example `v1.3.0`).

To get the diff between two versions:
https://github.com/darkwood-com/darkwood/compare/v1.0.0...v1.3.0

Note: the monorepo tag `v1.0.0` (2026-05-20) was created before unified
versioning was in place. **`v1.3.0` is the first official unified release**
for the whole project.

* v8.1.1 (2026-06-28)

 * :arrow_up: to symfony v8.1.1
 * :arrow_up: upgrade symfony/flex, symfony/ai, php-cs-fixer, rector, phpstan, and phpunit
 * :sparkles: [Flow] Add TrueAsyncDriver

* v8.1.0 (2026-06-07)

 * :arrow_up: to symfony v8.1.0
 * :sparkles: add rector to tools

* v8.0.13 (2026-05-28)

 * :arrow_up: to symfony v8.0.13
 * :sparkles: Move monorepo packages under `src/Darkwood/`
 * :fire: Cleanup

* v8.0.12 (2026-05-24)

 * :arrow_up: to symfony v8.0.12

* v1.3.0 (2026-05-21)

 * :sparkles: Introduce unified global versioning for all Darkwood packages (monorepo + satellites)
 * :sparkles: Add root `composer.json` meta-package `darkwood/darkwood` with `replace` aliases
 * :sparkles: Add release tooling (`scripts/splitsh-run.sh`, `scripts/release.sh`) and `RELEASING.md`
 * :sparkles: Add monorepo CI matrix (Fabbot, PHP CS Fixer, PHPStan, PHPUnit per package)
 * :sparkles: Add `splitsh.json` and subtree split workflow to satellite repositories
 * :sparkles: [Navi] Migrate from MySQL to PostgreSQL
 * :sparkles: [Navi] Add Castor-based QA tools and Nix environment for PHP 8.5
 * :sparkles: [Navi] Update namespace from App to Navi
 * :sparkles: [MediaBundle] Add Seedance 2.0 fast preset and video pipeline updates
 * :sparkles: [MediaBundle] Enhance configuration with `when@dev` support
 * :sparkles: [IaExceptionBundle] Render AI exception analysis asynchronously
 * :bug: [IaExceptionBundle] Replace default Bootstrap danger icon with AI danger icon
 * :bug: Align inter-package Composer constraints to `^1.3` (unified line)
