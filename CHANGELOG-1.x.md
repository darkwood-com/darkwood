CHANGELOG for 1.x
===================

This changelog references the relevant changes done in 1.x releases of Darkwood.

All packages (`darkwood/navi`, `darkwood/flow`, `darkwood/media-bundle`,
`darkwood/ia-exception-bundle`) share the **same version** as this monorepo.
Tags use the format `vMAJOR.MINOR.PATCH` (for example `v1.3.0`).

To get the diff between two versions:
https://github.com/darkwood-com/darkwood/compare/v1.3.0...v1.3.1

Note: the monorepo tag `v1.0.0` (2026-05-20) was created before unified
versioning was in place. **`v1.3.0` is the first official unified release**
for the whole project.

* 1.3.0 (2026-05-21)

 * feature Introduce unified global versioning for all Darkwood packages (monorepo + satellites)
 * feature Add root `composer.json` meta-package `darkwood/darkwood` with `replace` aliases
 * feature Add release tooling (`scripts/splitsh-run.sh`, `scripts/release.sh`) and `RELEASING.md`
 * feature Add monorepo CI matrix (Fabbot, PHP CS Fixer, PHPStan, PHPUnit per package)
 * feature Add `splitsh.json` and subtree split workflow to satellite repositories
 * feature [Navi] Migrate from MySQL to PostgreSQL
 * feature [Navi] Add Castor-based QA tools and Nix environment for PHP 8.5
 * feature [Navi] Update namespace from App to Navi
 * feature [MediaBundle] Add Seedance 2.0 fast preset and video pipeline updates
 * feature [MediaBundle] Enhance configuration with `when@dev` support
 * feature [IaExceptionBundle] Render AI exception analysis asynchronously
 * bug [IaExceptionBundle] Replace default Bootstrap danger icon with AI danger icon
 * bug Align inter-package Composer constraints to `^1.3` (unified line)
