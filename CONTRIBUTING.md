# Contributing to Darkwood

Thank you for contributing. This document describes how to work in the **monorepo**
([darkwood-com/darkwood](https://github.com/darkwood-com/darkwood)).

## Branches

| Change type | Target branch |
|-------------|---------------|
| Bug fixes | `1.x` |
| New features | `1.x` |
| Breaking changes | `1.x` + document in package `CHANGELOG.md` (and `UPGRADE-*.md` when introduced) |

There is currently a single maintained line (`1.x`). Satellite repositories use the same branch name.

## Before you start

1. Search existing issues on the monorepo or the relevant satellite repository.
2. Identify which **package** your change belongs to (see [README.md](README.md)).
3. Make changes only inside that package directory unless the change is monorepo-wide (docs, `link`, CI).

## Development workflow

### 1. Clone and branch

```bash
git clone git@github.com:darkwood-com/darkwood.git
cd darkwood
git checkout -b fix-or-feature-short-description
```

### 2. Install dependencies in the affected package

```bash
cd src/Component/Navi   # example
composer install
```

For tools isolated under `tools/` (PHPStan, PHPUnit, PHP CS Fixer), run `composer install` in each `tools/*` directory when CI does, or use the package `Makefile` / `composer` scripts.

### 3. Write code and tests

- Add or update tests for behavior changes.
- Keep public API changes backward compatible unless the changelog explicitly documents a break.
- Do not commit `vendor/`, `var/`, `.phpunit.cache/`, or generated video artefacts.

### 4. Run quality checks

From the package directory:

```bash
make php-cs-fixer   # Flow, Navi
make phpstan
make phpunit
```

Fix style issues with the package PHP CS Fixer config (`tools/php-cs-fixer/` or documented `make` target).

### 5. Changelog

For user-visible changes, add an entry to the package **`CHANGELOG.md`** under a new version or `Unreleased` section:

- **Bug fix:** one line describing the fix.
- **Feature:** one line describing what users gain.
- **BC break:** describe before/after with a short code sample when helpful (see Symfony component changelogs for inspiration).

### 6. Pull request

Open a PR against `1.x` on `darkwood-com/darkwood`. Fill in the PR template checklist.

If the change should also ship on Packagist, plan a subtree sync to the satellite repository before tagging (see `splitsh.json`).

## Coding standards

- Follow [Symfony coding standards](https://symfony.com/doc/current/contributing/code/standards.html).
- Use `declare(strict_types=1);` in new PHP files where the package already does.
- Prefer explicit checks over `empty()`.
- Run PHP CS Fixer before pushing.

## Monorepo utilities

### `link` — local development in consuming apps

```bash
./link /path/to/project         # symlink vendor/darkwood/* → monorepo
./link --copy /path/to/project  # copy instead of symlink (Windows-friendly)
./link --rollback /path/to/project
```

The target project must have run `composer install` and depend on at least one `darkwood/*` package.

### `splitsh.json` — satellite repositories

Maps monorepo paths to GitHub repository names used for subtree splits and releases.
Keep this file updated when adding or renaming packages.

## Review expectations

- CI (fabbot + package workflows) must pass.
- Tests cover the change or explain why not (docs-only PRs).
- No secrets, `.env.local`, or large binary artefacts in commits.

## Security

Do not open public issues for security vulnerabilities. Contact the maintainers privately.
