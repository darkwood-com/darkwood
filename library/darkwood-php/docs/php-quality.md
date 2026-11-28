# PHP quality with Darkwood Navi

The public reusable workflow lives in the [Navi distribution repository](https://github.com/darkwood-com/darkwood-php-component-navi/blob/main/.github/workflows/php-quality.yml).
Its source is the corresponding file under
`library/darkwood-php/src/Darkwood/Component/Navi/` in the Darkwood monorepo.
The workflow checks out the caller’s revision and uses read-only repository
permissions. It needs no production credentials.

## A standalone repository

Add `.github/workflows/ci.yml` in a project with a `composer.json`.
This example expects CS Fixer and PHPStan in the project and omits PHPUnit.

```yaml
name: PHP quality
on: [push, pull_request, workflow_dispatch]
permissions:
  contents: read
jobs:
  php-quality:
    uses: darkwood-com/darkwood-php-component-navi/.github/workflows/php-quality.yml@d88a15da45bc368b0a482f7edec0a251c68c2f1e
    with:
      php-version: '8.5'
      phpunit: false
```

Use `working-directory` for a project below the root and `extensions` for a
comma-separated list required by that project. All three tools are enabled
by default. Set the relevant boolean to `false` for a tool the project
does not use. An enabled tool that is missing causes the job to fail.

Darkwood uses `@main` internally. Pin the validated Navi proxy SHA or use
a verified tag containing the workflow when a stable reference is needed.
Existing published PHP package tags may predate the workflow.

## Local commands and tool ownership

From `library/darkwood-php`, run `castor darkwood-theme` to compile
the shared Sass theme or `castor darkwood-theme --check` to verify it.
Install the DarkwoodBundle npm lockfile first. The task delegates to the
same npm commands as the monorepo and mirror workflows.

Projects with Castor expose the aliases `php-cs-fixer`, `phpstan` and
`phpunit` in `castor.php` or `tools/castor.php`. Keep project-specific
configuration and versions with the project. Composer installs the project
and the manifests under `tools/php-cs-fixer`, `tools/phpstan` and
`tools/phpunit` for enabled checks. For a project with the latter layout:

```bash
composer install
composer --working-dir=tools/php-cs-fixer install
composer --working-dir=tools/phpstan install
composer --working-dir=tools/phpunit install
castor --castor-file=tools/castor.php php-cs-fixer --dry-run
castor --castor-file=tools/castor.php phpstan
castor --castor-file=tools/castor.php phpunit
```

Without Castor tasks, the workflow runs the project’s Composer-installed
binaries from its root and looks for the tools’ usual configuration files.
For PHPStan it uses a project script named `phpstan` when present. For
PHPUnit it uses a `phpunit` or `test` script when present, then the PHPUnit
binary or Symfony PHPUnit Bridge. PHP CS Fixer always runs in dry-run mode.

Castor can also import tasks through its Composer-based remote imports.
That would require publishing shared task code and coordinating its API
with the project-specific config paths and tool versions. The current
workflow uses local tasks and Composer binaries to keep local and CI
commands aligned. [Castor remote imports](https://castor.jolicode.com/)

Composer scripts run by default. Set `composer-scripts: false` when project
installation would boot a deployed application that is configured outside CI.
`prepare-dotenv` creates an empty test environment with `APP_ENV=test` and
`APP_DEBUG=0` only if the checked-out project has no `.env`. Project fixtures
and test variables still belong beside their tests.

Enable `mysql` only for suites that already use the isolated MySQL 5.7
service on port 3311. `prepare-task` runs an optional Castor fixture task
before PHPUnit. A project may supply a locked Nix shell via `nix-file`
and a JSON array of `nix-args`. The marker `%project%` in those arguments
expands to the working directory.

## Darkwood discovery and publication

### Generated Symfony configuration references

For every new Symfony application, including nested bundle test applications,
add this block to the application's own `.gitignore`:

```gitignore
# Symfony generated configuration references
/config/reference.php
/config/schema.json
```

Keep `config/preload.php`, source configuration and application lockfiles in
Git. Symfony 8.2 generates the PHP reference and the JSON schema when the
debug container is compiled. Use
`APP_ENV=dev APP_DEBUG=1 php bin/console cache:clear` in the project's
configured local environment when an IDE needs fresh copies.

Applications that have migrated pin Symfony Flex to `8.2.*` and commit their
application lockfiles. Public libraries retain their supported Symfony floors.
See the [Symfony 8.2 guide](symfony-8.2-release.md) and the
[feature review](symfony-8.2-features.md) for upgrade and adoption decisions.

Quality jobs install Composer dependencies from a clean clone. The existing
project fixture and kernel boot steps prepare Symfony as needed. If a new
project uses types from the generated file, generate it as part of that
project's quality preparation and keep it available to the analyser for
symbol discovery. Exclude it from the analysed paths and CS Fixer scope.
The shared workflow does not require a container build for projects whose
checks can run without one.

Run `php .github/scripts/php-quality-matrix.php` from the monorepo. The
script reads tracked Composer manifests under `library/` and uses the
first directory below it as the card identifier. Each package in
`darkwood-php` gets its own entry. Composer manifests in tools, vendors,
fixtures, caches and build outputs are excluded. Versioned distribution
metadata provides PHP and extensions for the relevant mirrors. Files and
Composer requirements identify the available checks. An invalid manifest
or an unsupported PHP constraint fails discovery with the project path.

`Darkwood PHP quality` runs the full matrix with `fail-fast: false`.
Packages with no configured quality tool still run Composer installation.
Flow and Navi keep their pinned PHP 8.6 shell with OpenSwoole. Their pinned
PHP CS Fixer cannot parse PHP 8.6, so PHPStan and PHPUnit are the active
checks for those two packages. MediaBundle keeps its Symfony LSP check.

A change to a component invokes the same reusable workflow in that
component’s mirror pipeline. The publishing job needs its successful
quality result and checks that the mirror tree is exactly the source
subtree at the checked-out SHA. The two profile mirrors run their usual
publication without a PHP job. Existing Pages and release mechanisms
keep their own purpose.

Use `workflow_dispatch` for a complete quality run. A future change
filter can start with the manifest paths and explicitly expand shared
bundle changes to known consumers before introducing a dependency graph.
Review each failed step in GitHub Actions. The jobs keep failures visible
and run remaining enabled tools after a tool failure, provided dependency
installation succeeded.

If GitHub refuses a mirror update because the distribution App lacks
workflow-write permission, an authorized maintainer can synchronize the
exact subtree with a fast-forward push from an isolated checkout. Future
edits to the workflow require that permission or the same manual step.
