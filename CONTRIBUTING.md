# Contributing to Darkwood

Thank you for contributing. This document describes how to work in the **monorepo**
([darkwood-com/darkwood](https://github.com/darkwood-com/darkwood)).

## Versioning

Darkwood uses **unified global versioning** (Symfony-style):

- One version number for the whole project (`1.3.0`, `1.3.1`, …).
- One git tag (`v1.3.0`) on the monorepo **and** on each satellite repository.
- Changelog entries use the **Darkwood version**, not independent package semver.

See [CHANGELOG-8.1.md](CHANGELOG-8.1.md), [RELEASING.md](RELEASING.md), and
[UPGRADE-8.1.md](UPGRADE-8.1.md).

## Branches

| Change type | Target branch |
|-------------|---------------|
| Bug fixes | `8.1` |
| New features | `8.1` |
| Breaking changes | `8.1` + document in package `CHANGELOG.md`, [CHANGELOG-8.1.md](CHANGELOG-8.1.md), and [UPGRADE-8.1.md](UPGRADE-8.1.md) |

There is currently a single maintained line (`8.1`). Satellite repositories use the same branch name.

## Before you start

1. Search existing issues on the monorepo or the relevant satellite repository.
2. Identify which **package** your change belongs to (see [README.md](README.md)).
3. Make changes only inside that package directory unless the change is monorepo-wide (docs, `link`, CI, release tooling).

## Development workflow

### 1. Clone and branch

```bash
git clone git@github.com:darkwood-com/darkwood.git
cd darkwood
git checkout 8.1
git checkout -b fix-or-feature-short-description
```

### 2. Install dependencies in the affected package

```bash
cd src/Darkwood/Component/Navi   # example
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
make php-cs-fixer   # PHP CS Fixer ^3.94
make phpstan        # PHPStan ^2.0
make phpunit        # PHPUnit ^13.0
make symfony-lsp    # Symfony-aware diagnostics (requires Symfony CLI 5.20+)
```

Replicate CI locally: `.github/ci-run-package.sh navi` (replace `navi` with the package id you changed).

Fix style issues with the package PHP CS Fixer config under `tools/php-cs-fixer/`.

### 5. Changelog

For user-visible changes, add entries under the upcoming **Darkwood version** (or an `Unreleased` section until the release is cut):

1. **Package** [`CHANGELOG.md`](src/Darkwood/Component/Navi/CHANGELOG.md) in the affected package directory — use the Darkwood version as the section header (for example `## 1.3.1`), not an independent package semver.
2. **Monorepo** [CHANGELOG-8.1.md](CHANGELOG-8.1.md) — one line per notable change, prefixed with `bug`, `feature`, or `[BC BREAK]`, and the package name in brackets when relevant.

Maintainers consolidate entries when cutting a release (see [RELEASING.md](RELEASING.md)).

### 6. Pull request

Open a PR against `8.1` on `darkwood-com/darkwood`. Fill in the PR template checklist.

Releases are cut from `8.1` after merge; contributors do not tag satellite repositories themselves.

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
**Navi** is listed as `reference_package` in `splitsh.json`. Keep this file updated when adding or renaming packages.

Push splits with:

```bash
scripts/splitsh-run.sh --all
```

## Review expectations

- CI must pass: **Fabbot** (`.github/workflows/fabbot.yml`) and **QA** (`.github/workflows/ci.yml`).
- QA runs only on packages modified by your PR; replicate locally with `.github/ci-run-package.sh navi` (or the relevant package id).
- Tests cover the change or explain why not (docs-only PRs).
- No secrets, `.env.local`, or large binary artefacts in commits.

## Security

Do not open public issues for security vulnerabilities. See [SECURITY.md](SECURITY.md).
