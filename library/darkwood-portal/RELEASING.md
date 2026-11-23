# Releasing darkwood.com

The [darkwood-com/darkwood-com](https://github.com/darkwood-com/darkwood-com)
repository uses **application versioning** (`v1.0.x`), independent from the
unified Darkwood library line (`darkwood-com/darkwood` at `v8.x`).

**Default branch:** `main`  
**Latest tag:** see [GitHub releases](https://github.com/darkwood-com/darkwood-com/releases)

## Semver rules

| Bump | When |
|------|------|
| **PATCH** (`1.0.6` → `1.0.7`) | Backward-compatible bug fixes and small features |
| **MINOR** (`1.0.x` → `1.1.0`) | Larger backward-compatible features |
| **MAJOR** (`1.x` → `2.0.0`) | Breaking changes (document in `UPGRADE-1.0.md`) |

## Release checklist

### 1. Prepare the changelog

Add a `## vX.Y.Z` section at the top of [`CHANGELOG.md`](CHANGELOG.md).

Document dependency or deploy changes in [`UPGRADE-1.0.md`](UPGRADE-1.0.md) when
they affect upgrades from the previous tag.

### 2. Run QA locally

```bash
make php-cs-fixer
make phpstan
make phpunit
```

Or run the same jobs as CI (PHP 8.5).

### 3. Commit and merge to `main`

```bash
git checkout main
git pull
```

### 4. Tag and publish

```bash
git tag -a v1.0.7 -m "Release v1.0.7"
git push origin v1.0.7
gh release create v1.0.7 --notes-file <(sed -n '/^## v1.0.7$/,/^## v1.0.6$/p' CHANGELOG.md | head -n -1)
```

Adjust the version in the commands above.

### 5. Deploy

Every Darkwood deployment to VPS-4 must use the version already pushed to the
remote Git repository. Check the production worktree first, then update it with
`git pull --ff-only`; never replace the application tree with a local copy.

On VPS-4:

```bash
cd /var/www/darkwood
git status --short --branch
git fetch origin
git pull --ff-only origin main
test "$(git rev-parse HEAD)" = "$(git rev-parse origin/main)"
cd library/darkwood-portal
make deploy
```

Ensure `.env.local` includes any new optional variables (for example `TELEGRAM_DSN`).
