#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DEST="$ROOT/var/compiler/sqlite"
VERSION="3460000"
URL="https://www.sqlite.org/2024/sqlite-amalgamation-${VERSION}.zip"

mkdir -p "$DEST"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

curl -fL "$URL" -o "$TMP/sqlite.zip"
unzip -q "$TMP/sqlite.zip" -d "$TMP"
cp "$TMP/sqlite-amalgamation-${VERSION}/sqlite3.c" "$DEST/"
cp "$TMP/sqlite-amalgamation-${VERSION}/sqlite3.h" "$DEST/"

echo "Installed SQLite 3.46.0 amalgamation to $DEST"
