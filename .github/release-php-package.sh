#!/usr/bin/env bash
set -euo pipefail

package=${1:?Usage: release-php-package.sh darkwood/PACKAGE vMAJOR.MINOR.PATCH[.FIX] [canonical-revision] [distribution-branch]}
version=${2:?Provide a stable SemVer tag}
revision=${3:-origin/main}
branch=${4:-main}
if [[ ! $version =~ ^v(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)(\.([1-9][0-9]*))?$ ]]; then
    printf 'Expected a stable vMAJOR.MINOR.PATCH tag with an optional numeric Darkwood fix.\n' >&2
    exit 1
fi
git check-ref-format "refs/heads/$branch"

cd "$(git rev-parse --show-toplevel)"
card=$(jq -ec --arg package "$package" '.cards[] | select(.git.composer == $package)' .github/distribution-cards.json)
source_path=$(jq -r .source <<< "$card")
repository=$(jq -r .repository <<< "$card")
commit=$(git rev-parse --verify "$revision^{commit}")
git merge-base --is-ancestor "$commit" origin/main
expected_tree=$(git rev-parse "$commit:$source_path")

checkout=$(mktemp -d)
trap 'rm -r -- "$checkout"' EXIT
git clone --depth=1 --no-tags --branch "$branch" "git@github.com:$repository.git" "$checkout"
proxy_commit=$(git -C "$checkout" rev-parse HEAD)
if [[ $(git -C "$checkout" rev-parse 'HEAD^{tree}') != "$expected_tree" ]]; then
    printf 'The distribution tree does not match %s:%s. Synchronize it before tagging.\n' "$commit" "$source_path" >&2
    exit 1
fi

tag_ref="refs/tags/$version"
existing=$(git -C "$checkout" ls-remote --tags origin "$tag_ref" "$tag_ref^{}")
if [[ -n $existing ]]; then
    tagged_commit=$(awk -v ref="$tag_ref" '
      $2 == ref { tag = $1 }
      $2 == ref "^{}" { peeled = $1 }
      END { print (peeled ? peeled : tag) }
    ' <<< "$existing")
    if [[ $tagged_commit != "$proxy_commit" ]]; then
        printf '%s already points to %s.\n' "$version" "$tagged_commit" >&2
        exit 1
    fi
    printf '%s already points to %s.\n' "$version" "$proxy_commit"
    exit 0
fi

git -C "$checkout" tag -a "$version" "$proxy_commit" -m "Release ${version#v}"
git -C "$checkout" push origin "$tag_ref"
test "$(git -C "$checkout" ls-remote --tags origin "$tag_ref^{}" | cut -f1)" = "$proxy_commit"
printf '%s %s -> %s (canonical %s)\n' "$package" "$version" "$proxy_commit" "$commit"
