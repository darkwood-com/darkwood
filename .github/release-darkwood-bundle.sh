#!/usr/bin/env bash
set -euo pipefail

version=${1:?Usage: release-darkwood-bundle.sh vMAJOR.MINOR.PATCH [canonical-revision]}
if [[ ! $version =~ ^v(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$ ]]; then
    printf 'Expected a stable SemVer tag with a v prefix.\n' >&2
    exit 1
fi

cd "$(git rev-parse --show-toplevel)"
prefix=library/darkwood-php/src/Darkwood/Bundle/DarkwoodBundle
remote=${DARKWOOD_BUNDLE_REMOTE:-git@github.com:darkwood-com/darkwood-bundle.git}
revision=${2:-origin/main}
commit=$(git rev-parse --verify "$revision^{commit}")
git merge-base --is-ancestor "$commit" origin/main
git cat-file -e "$commit:$prefix/composer.json"

split=$(git subtree split --prefix "$prefix" "$commit")
test "$(git rev-parse "$split^{tree}")" = "$(git rev-parse "$commit:$prefix")"

existing=$(git ls-remote "$remote" "refs/tags/$version")
if [[ -n $existing ]]; then
    if [[ $existing != "$split"$'\t'"refs/tags/$version" ]]; then
        printf 'The distribution tag already points to a different commit.\n' >&2
        exit 1
    fi
    printf '%s already points to %s.\n' "$version" "$split"
    exit 0
fi

git push --atomic "$remote" "$split:refs/heads/main" "$split:refs/tags/$version"
printf '%s -> %s (canonical %s)\n' "$version" "$split" "$commit"
