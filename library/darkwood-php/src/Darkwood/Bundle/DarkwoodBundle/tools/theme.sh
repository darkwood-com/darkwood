#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

out=$(mktemp -d)
trap 'rm -rf "$out"' EXIT
sass=./node_modules/.bin/sass
options=(--load-path=node_modules --no-source-map --style=compressed)
for name in darkwood darkwood-shared product; do
    "$sass" "${options[@]}" "assets/sass/$name.scss" "$out/$name.css"
done
for name in tokens identity; do
    "$sass" "${options[@]}" "assets/sass/_$name.scss" "$out/$name.css"
done

for name in darkwood darkwood-shared product tokens identity; do
    if [[ ${1:-} == --check ]]; then
        if ! cmp -s "$out/$name.css" "assets/styles/$name.css"; then
            echo "assets/styles/$name.css is stale. Run npm run build:css." >&2
            exit 1
        fi
    else
        cp "$out/$name.css" "assets/styles/$name.css"
    fi
done
