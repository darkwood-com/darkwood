#!/usr/bin/env bash
#
# Release a unified Darkwood version: tag monorepo, split satellites, tag
# satellites, create GitHub Releases.
#
# Usage:
#   scripts/release.sh v1.3.0
#   scripts/release.sh v1.3.0 --dry-run
#   scripts/release.sh v1.3.0 --skip-qa
#   scripts/release.sh v1.3.0 --skip-satellite-releases
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
CONFIG="${ROOT_DIR}/splitsh.json"
CHANGELOG="${ROOT_DIR}/CHANGELOG-1.x.md"
MONOREPO="${GITHUB_REPOSITORY:-darkwood-com/darkwood}"
BRANCH="$(jq -r '.branch' "$CONFIG")"

TAG=""
DRY_RUN=0
SKIP_QA=0
SKIP_SAT_RELEASES=0

usage() {
    echo "Usage: $0 vX.Y.Z [--dry-run] [--skip-qa] [--skip-satellite-releases]" >&2
    exit 1
}

while [ $# -gt 0 ]; do
    case "$1" in
        --dry-run)
            DRY_RUN=1
            ;;
        --skip-qa)
            SKIP_QA=1
            ;;
        --skip-satellite-releases)
            SKIP_SAT_RELEASES=1
            ;;
        -h|--help)
            usage
            ;;
        v*.*.*)
            TAG="$1"
            ;;
        *)
            echo "Unknown argument: $1" >&2
            usage
            ;;
    esac
    shift
done

[ -n "$TAG" ] || usage

if ! [[ "$TAG" =~ ^v[0-9]+\.[0-9]+\.[0-9]+(-[A-Za-z0-9.]+)?$ ]]; then
    echo "Invalid tag format: ${TAG} (expected vMAJOR.MINOR.PATCH)" >&2
    exit 1
fi

VERSION="${TAG#v}"
TITLE="Darkwood ${VERSION}"

run() {
    if [ "$DRY_RUN" = "1" ]; then
        echo "[dry-run] $*"
    else
        "$@"
    fi
}

cd "$ROOT_DIR"

current_branch="$(git rev-parse --abbrev-ref HEAD)"
if [ "$current_branch" != "$BRANCH" ]; then
    echo "Expected branch ${BRANCH}, on ${current_branch}" >&2
    exit 1
fi

if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    echo "Working tree is not clean. Commit or stash changes first." >&2
    git status --short
    exit 1
fi

if git rev-parse "$TAG" >/dev/null 2>&1; then
    echo "Tag ${TAG} already exists locally." >&2
    exit 1
fi

if [ "$SKIP_QA" != "1" ]; then
    echo "Running QA for all packages..."
    for pkg in navi flow media-bundle ia-exception-bundle; do
        run "${ROOT_DIR}/.github/ci-run-package.sh" "$pkg"
    done
else
    echo "Skipping QA (--skip-qa)."
fi

if [ ! -f "$CHANGELOG" ]; then
    echo "Missing ${CHANGELOG}" >&2
    exit 1
fi

if ! grep -q "^\* ${VERSION}" "$CHANGELOG"; then
    echo "No section '* ${VERSION}' found in ${CHANGELOG}" >&2
    exit 1
fi

NOTES_FILE="$(mktemp /tmp/darkwood-release-notes.XXXXXX.md)"
trap 'rm -f "$NOTES_FILE"' EXIT

awk -v ver="$VERSION" '
    $0 ~ "^\\* " ver { found=1; print; next }
    found && /^\* [0-9]/ { exit }
    found { print }
' "$CHANGELOG" > "$NOTES_FILE"

if [ ! -s "$NOTES_FILE" ]; then
    echo "Could not extract release notes for ${VERSION} from ${CHANGELOG}" >&2
    exit 1
fi

echo "Release notes preview:"
echo "---"
cat "$NOTES_FILE"
echo "---"

run git tag -a "$TAG" -m "${TITLE}"
run git push origin "$TAG"

run "${ROOT_DIR}/scripts/splitsh-run.sh" --all

while IFS= read -r package_id; do
    repo_name="$(jq -r --arg id "$package_id" '.remotes[$id]' "$CONFIG" | sed 's|.*github.com:||; s|\.git$||')"

    if [ "$SKIP_SAT_RELEASES" = "1" ]; then
        sha="$(gh api "repos/${repo_name}/git/ref/heads/${BRANCH}" --jq .object.sha)"
        if gh api "repos/${repo_name}/git/ref/tags/${TAG}" >/dev/null 2>&1; then
            echo "Tag ${TAG} already exists on ${repo_name}."
        else
            run gh api -X POST "repos/${repo_name}/git/refs" \
                -f ref="refs/tags/${TAG}" \
                -f sha="$sha"
        fi
        continue
    fi

    if gh release view "$TAG" --repo "$repo_name" >/dev/null 2>&1; then
        echo "GitHub Release ${TAG} already exists on ${repo_name}."
    else
        run gh release create "$TAG" --repo "$repo_name" \
            --target "$BRANCH" \
            --title "${TITLE} (${package_id})" \
            --notes-file "$NOTES_FILE"
    fi
done < <(jq -r '.subtrees | keys[]' "$CONFIG")

if gh release view "$TAG" --repo "$MONOREPO" >/dev/null 2>&1; then
    echo "GitHub Release ${TAG} already exists on ${MONOREPO}."
else
    run gh release create "$TAG" --repo "$MONOREPO" \
        --title "$TITLE" \
        --notes-file "$NOTES_FILE"
fi

echo ""
echo "Release ${TAG} complete."
echo "Verify Packagist packages expose ${TAG} within a few minutes."
