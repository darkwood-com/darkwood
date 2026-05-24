# Releasing Darkwood

Darkwood uses **unified global versioning**: one tag (`vX.Y.Z`) applies to the
monorepo and to every satellite package published on Packagist.

**Default branch:** `8.0`  
**Reference package for splits:** `navi` (see `splitsh.json`)

## Prerequisites

1. [splitsh-lite](https://github.com/splitsh/lite) installed and available as
   `splitsh-lite` on your `PATH`, or set `SPLITSH_LITE` to its full path.
2. On macOS with a custom libgit2 build: `export DYLD_LIBRARY_PATH=$HOME/.local/libgit2-1.5/lib`
3. GitHub CLI (`gh`) authenticated, for GitHub Releases.
4. Write access to `darkwood-com/darkwood` and the four satellite repositories.

## Semver rules

| Bump | When |
|------|------|
| **PATCH** (`1.3.0` → `1.3.1`) | Backward-compatible bug fixes |
| **MINOR** (`1.3.x` → `1.4.0`) | Backward-compatible features |
| **MAJOR** (`8.0` → `9.0.0`) | Breaking changes (document in `UPGRADE-8.0.md`) |

All packages are tagged with the **same** version number.

During monorepo development, package `composer.json` files carry the target
version (for example `1.3.0`) and `repositories` path entries so `composer install`
can resolve `^1.3` before the unified tag exists on Packagist.

## Release checklist

### 1. Prepare the changelog

- Add entries under the new version in [`CHANGELOG-8.0.md`](CHANGELOG-8.0.md).
- Add matching sections in each affected package `CHANGELOG.md` under the
  **Darkwood version** (not an independent package semver).

### 2. Run QA locally

From the monorepo root:

```bash
.github/ci-run-package.sh navi
.github/ci-run-package.sh flow
.github/ci-run-package.sh media-bundle
.github/ci-run-package.sh ia-exception-bundle
```

### 3. Commit and merge

```bash
git checkout 8.0
git pull
# merge your PR, then:
git pull
```

Use a release commit message when only changelog/versioning files changed:

```text
chore(release): Darkwood v1.3.0
```

### 4. Tag, split, and publish

Use the release script (recommended):

```bash
scripts/release.sh v1.3.0
```

Dry run first:

```bash
scripts/release.sh v1.3.0 --dry-run
```

Or manually:

```bash
git tag -a v1.3.0 -m "Darkwood v1.3.0"
git push origin v1.3.0

scripts/splitsh-run.sh --all

# Tag each satellite at the tip of 8.0 after split
for repo in navi flow media-bundle ia-exception-bundle; do
  gh api repos/darkwood-com/$repo/git/refs -f ref="refs/tags/v1.3.0" \
    -f sha="$(gh api repos/darkwood-com/$repo/git/ref/heads/8.0 --jq .object.sha)"
done

gh release create v1.3.0 --repo darkwood-com/darkwood \
  --title "Darkwood v1.3.0" --notes-file /tmp/darkwood-v1.3.0-notes.md
```

### 5. Verify Packagist

Within a few minutes, each package should expose the new tag:

- https://packagist.org/packages/darkwood/navi
- https://packagist.org/packages/darkwood/flow
- https://packagist.org/packages/darkwood/media-bundle
- https://packagist.org/packages/darkwood/ia-exception-bundle

## Split details

Configuration: [`splitsh.json`](splitsh.json)

```bash
scripts/splitsh-run.sh --list          # list package ids
scripts/splitsh-run.sh navi            # push one split
scripts/splitsh-run.sh --all           # push all splits
```

If a satellite branch history must be rewritten on first split:

```bash
SPLITSH_FORCE=1 scripts/splitsh-run.sh navi
```

Override split origin ref (advanced):

```bash
SPLITSH_ORIGIN=refs/heads/8.0 scripts/splitsh-run.sh --all
```

## GitHub repository settings

These steps are manual (or via `gh` API) and only need to be done once:

1. **Default branch:** set to `8.0` (Settings → General → Default branch).
2. **Branch protection** on `8.0`: require PR, require status checks `CI` and `CS` (Fabbot).
3. **Labels:** `bug`, `feature`, `BC break`, `release`, `documentation`.

## Pre-releases (optional)

Use annotated tags with suffixes when needed:

- `v1.4.0-BETA1`
- `v1.4.0-RC1`

Document pre-releases in `CHANGELOG-8.0.md` before the stable tag.
