#!/usr/bin/env bash
# Deterministic Town/L1 PNG dumps for side-by-side compare (tmp/shots/).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MPQ="${DIABLO_DATA:-/Users/math/Desktop/DIABDAT.MPQ}"
OUT="${ROOT}/tmp/shots"
PHP=(php -d ffi.enable=true "${ROOT}/bin/diablo.php" --data="${MPQ}")
mkdir -p "${OUT}"

run() {
  local name="$1"
  shift
  echo "==> ${name}"
  "${PHP[@]}" "$@" --debug-static-frame --dump-frame="${OUT}/${name}.png"
}

run town_fb --town --fullbright
run town_lit --town
run town_specials --town --fullbright --render-layer=specials
run town_actors --town --fullbright --render-layer=actors
run l1_fb --level=1 --seed=12345 --fullbright
run l1_lit --level=1 --seed=12345
run l1_walls --level=1 --seed=12345 --fullbright --render-layer=walls
run l1_specials --level=1 --seed=12345 --fullbright --render-layer=specials
run l1_actors --level=1 --seed=12345 --fullbright --render-layer=actors

echo "Shots written under ${OUT}"
echo "
ls -la "${OUT}"/*.png 2>/dev/null || true
