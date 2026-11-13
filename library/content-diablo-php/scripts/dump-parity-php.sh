#!/usr/bin/env bash
# Dump geometry dump frames A–E → tmp/parity/php/
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MPQ="${DIABLO_DATA:-/Users/math/Desktop/DIABDAT.MPQ}"
OUT="$ROOT/tmp/parity/php"
mkdir -p "$OUT"
PHP=(php -d ffi.enable=true -d opcache.enable_cli=0 "$ROOT/bin/diablo.php" --data="$MPQ")

echo "== A Town startup =="
"${PHP[@]}" --parity-scenario=A --town --dump-frame="$OUT/A_town_fb.png"

echo "== B Town building =="
"${PHP[@]}" --parity-scenario=B --town --force-pos=60,68 --dump-frame="$OUT/B_town_building_fb.png"

echo "== C L1 seed 12345 =="
"${PHP[@]}" --parity-scenario=C --level=1 --seed=12345 --dump-frame="$OUT/C_l1_fb.png"

echo "== D L1 stairs =="
"${PHP[@]}" --parity-scenario=D --level=1 --seed=12345 --force-pos=56,68 --dump-frame="$OUT/D_l1_stairs_fb.png"

echo "== E L1 combat (actors on) =="
"${PHP[@]}" --parity-scenario=E --level=1 --seed=12345 --dump-frame="$OUT/E_l1_combat_fb.png"

echo "PHP parity dumps → $OUT"
ls -la "$OUT"
