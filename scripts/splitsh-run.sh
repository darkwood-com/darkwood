#!/usr/bin/env bash
#
# Push subtree splits to satellite repositories (see splitsh.json).
#
# Usage:
#   scripts/splitsh-run.sh <package-id>     # e.g. navi, flow
#   scripts/splitsh-run.sh --all            # all packages in splitsh.json
#   scripts/splitsh-run.sh --list           # list package ids
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
CONFIG="${ROOT_DIR}/splitsh.json"
SPLITSH_LITE="${SPLITSH_LITE:-splitsh-lite}"
BRANCH="$(jq -r '.branch' "$CONFIG")"
ORIGIN="${SPLITSH_ORIGIN:-refs/heads/${BRANCH}}"

if ! command -v "$SPLITSH_LITE" >/dev/null 2>&1 && [ ! -x "$SPLITSH_LITE" ] && [ ! -f "$SPLITSH_LITE" ]; then
    echo "splitsh-lite not found: ${SPLITSH_LITE}" >&2
    echo "Install from https://github.com/splitsh/lite or set SPLITSH_LITE=/path/to/splitsh-lite" >&2
    exit 1
fi

if [ -n "${DYLD_LIBRARY_PATH:-}" ]; then
    export DYLD_LIBRARY_PATH
elif [ -d "${HOME}/.local/libgit2-1.5/lib" ]; then
    export DYLD_LIBRARY_PATH="${HOME}/.local/libgit2-1.5/lib"
fi

list_packages() {
    jq -r '.subtrees | keys[]' "$CONFIG" | sort
}

resolve_package() {
    local package_id="$1"
    local prefix remote

    prefix="$(jq -r --arg id "$package_id" '.subtrees[$id] // empty' "$CONFIG")"
    if [ -z "$prefix" ]; then
        return 1
    fi

    remote="$(jq -r --arg id "$package_id" '.remotes[$id]' "$CONFIG")"
    echo "${prefix}|${remote}"
}

run_split() {
    local package_id="$1"
    local resolved prefix remote

    if ! resolved="$(resolve_package "$package_id")"; then
        echo "Unknown package id: ${package_id}" >&2
        echo "Available packages:" >&2
        list_packages | sed 's/^/  /' >&2
        exit 1
    fi

    prefix="${resolved%%|*}"
    remote="${resolved##*|}"

    echo "::group::splitsh ${package_id} (${prefix} → ${remote})"
    cd "$ROOT_DIR"

    current_branch="$(git rev-parse --abbrev-ref HEAD)"
    if [ "$current_branch" != "$BRANCH" ]; then
        echo "Warning: monorepo is on branch '${current_branch}' (split uses ${ORIGIN})." >&2
    fi

    SHA="$("$SPLITSH_LITE" --path=. --prefix="${prefix}" --origin="${ORIGIN}")"
    echo "Split SHA: ${SHA}"

    if [ "${SPLITSH_FORCE:-}" = "1" ]; then
        echo "Force push enabled (SPLITSH_FORCE=1)"
        git push --force "${remote}" "${SHA}:refs/heads/${BRANCH}"
    else
        git push "${remote}" "${SHA}:refs/heads/${BRANCH}"
    fi
    echo "::endgroup::"
    echo "Pushed ${package_id} to ${remote} (${BRANCH})"
}

case "${1:-}" in
    --list|-l)
        list_packages
        exit 0
        ;;
    --all|-a)
        while IFS= read -r package_id; do
            run_split "$package_id"
        done < <(list_packages)
        echo "All splits pushed."
        ;;
    -h|--help|help)
        echo "Usage: $0 <package-id> | --all | --list"
        echo "Branch: ${BRANCH} (split origin: ${ORIGIN}, override with SPLITSH_ORIGIN=...)"
        echo "First push / history rewrite: SPLITSH_FORCE=1 $0 <package-id>"
        list_packages | sed 's/^/  /'
        ;;
    '')
        echo "Package id required. Use --list or --help." >&2
        exit 1
        ;;
    *)
        run_split "$1"
        ;;
esac
