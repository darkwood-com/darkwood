#!/bin/bash
# Build and run the game on the booted iOS Simulator (Apple Silicon), unsigned, no Apple team.
#
# The official Godot 4.7.2 iOS template's Simulator libgodot.a holds x86_64 code only; this machine's
# Simulator runs arm64. The template used here is the official one with that library made fat: its
# x86_64 slice plus an arm64 one built from Godot's source at the same commit (4.7.2-stable, ed1daf0bf):
#   scons platform=ios target=template_debug simulator=yes arch=arm64 generate_bundle=no
# kept outside the project at $TEMPLATE (godot/enhanced/README.md, iOS). The iOS preset points at it
# only while this script exports (export_presets.cfg restored on exit).
#
# Usage: tools/mobile/ios_simulator.sh <work dir>   (export, xcodebuild, install, launch)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
WORK="${1:?usage: $0 <work dir>}"
TEMPLATE="$HOME/Library/Caches/hellcats-ios-template/4.7.2/ios.zip"
GODOT=/Applications/Godot.app/Contents/MacOS/Godot
PRESETS="$ROOT/godot/export_presets.cfg"
BUNDLE=com.darkwood.hellcats
[ -f "$TEMPLATE" ] || { echo "missing $TEMPLATE (see the header)"; exit 1; }
DEVICE=$(xcrun simctl list devices booted | grep -oE '\([0-9A-F-]{36}\)' | head -1 | tr -d '()')
[ -n "$DEVICE" ] || { echo "no booted Simulator"; exit 1; }

cp "$PRESETS" "$WORK/export_presets.cfg.saved"
restore() { cp "$WORK/export_presets.cfg.saved" "$PRESETS"; }
trap restore EXIT
python3 - "$PRESETS" "$TEMPLATE" <<'PY'
import sys
p, t = sys.argv[1], sys.argv[2]
s = open(p).read()
head, sep, opts = s.partition("[preset.1.options]")
assert sep, "no iOS preset"
opts = opts.replace('custom_template/debug=""', 'custom_template/debug="%s"' % t, 1) if 'custom_template/debug=' in opts \
    else "\n\ncustom_template/debug=\"%s\"" % t + opts
open(p, "w").write(head + sep + opts)
PY
mkdir -p "$WORK/ios"
"$GODOT" --headless --path "$ROOT/godot" --export-debug "iOS" "$WORK/ios/Hellcats.xcodeproj"
restore
trap - EXIT
xcodebuild -project "$WORK/ios/Hellcats.xcodeproj" -scheme Hellcats -configuration Debug -sdk iphonesimulator \
  -destination "platform=iOS Simulator,id=$DEVICE" -derivedDataPath "$WORK/ios/dd" CODE_SIGNING_ALLOWED=NO build \
  > "$WORK/ios/build.log" 2>&1 || { grep -E "error:|BUILD FAILED" "$WORK/ios/build.log" | head -10; exit 1; }
APP=$(find "$WORK/ios/dd/Build/Products" -name "*.app" -maxdepth 2 | head -1)
xcrun simctl install "$DEVICE" "$APP"
xcrun simctl launch "$DEVICE" "$BUNDLE"
echo "launched $BUNDLE on $DEVICE from $APP"
