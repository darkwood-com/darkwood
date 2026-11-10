#!/usr/bin/env bash
# Walked-to tile vs fresh static at same ViewPosition (floor layer must match).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MPQ="${DIABLO_DATA:-/Users/math/Desktop/DIABDAT.MPQ}"
OUT="${ROOT}/tmp/walk-capture"
PHP=(php -d ffi.enable=true "${ROOT}/bin/diablo.php" --data="${MPQ}")
mkdir -p "${OUT}"

rm -rf "${OUT}"
mkdir -p "${OUT}"
"${PHP[@]}" --town --fullbright --render-layer=floor --debug-walk-capture
POS=$(cat "${OUT}/final_pos.txt" | tr -d '\n')
cp "${OUT}/STATIC_AFTER_MOVE.png" "${OUT}/AFTER_floor.png"

rm -rf "${OUT}-reload" 
# static reload floor
"${PHP[@]}" --town --fullbright --force-pos="${POS}" --render-layer=floor --debug-static-frame \
  --dump-frame="${OUT}/RELOAD_floor.png"

php -r '
$a=imagecreatefrompng($argv[1]); $b=imagecreatefrompng($argv[2]);
$w=imagesx($a); $h=min(352,imagesy($a)); $diff=0; $tot=0;
for($y=0;$y<$h;$y++) for($x=0;$x<$w;$x++) { $tot++; if(imagecolorat($a,$x,$y)!==imagecolorat($b,$x,$y)) $diff++; }
$pct=round(100*$diff/$tot,4);
$ok=$diff===0;
file_put_contents($argv[3], "# Walked vs static equivalence\n\npos=".$argv[4]."\nfloor_diff_pixels=$diff/$tot ($pct%)\nresult=".($ok?"MATCH":"MISMATCH")."\n");
echo ($ok?"EQUIV_FLOOR_MATCH":"EQUIV_FLOOR_MISMATCH")." pos={$argv[4]} diff=$diff\n";
exit($ok?0:1);
' "${OUT}/AFTER_floor.png" "${OUT}/RELOAD_floor.png" "${OUT}/EQUIV.md" "${POS}"
