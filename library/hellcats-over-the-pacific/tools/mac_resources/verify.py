#!/usr/bin/env python3
"""Verify Hellcats resource extraction inventories for determinism and integrity."""

from __future__ import annotations

import argparse
import hashlib
import json
import sys
from pathlib import Path


def sha256_file(path: Path) -> str:
    h = hashlib.sha256()
    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()


def load_json(path: Path) -> dict:
    return json.loads(path.read_text(encoding="utf-8"))


def canonical(obj) -> str:
    return json.dumps(obj, indent=2, sort_keys=True) + "\n"


def verify_inventory(path: Path) -> list[str]:
    errors: list[str] = []
    data = load_json(path)
    root = path.parent
    for entry in data.get("entries", []):
        for key in ("data_file", "rsrc_file"):
            rel = entry.get(key)
            if not rel:
                continue
            f = root / rel
            if not f.is_file():
                errors.append(f"missing file for {entry.get('path')}: {rel}")
                continue
            digest_key = "data_sha256" if key == "data_file" else "rsrc_sha256"
            expected = entry.get(digest_key)
            actual = sha256_file(f)
            if expected and expected != actual:
                errors.append(
                    f"hash mismatch {rel}: expected {expected} actual {actual}"
                )
            size_key = "data_size" if key == "data_file" else "rsrc_size"
            if entry.get(size_key) != f.stat().st_size:
                errors.append(
                    f"size mismatch {rel}: inventory {entry.get(size_key)} file {f.stat().st_size}"
                )
    return errors


def compare_two(a: Path, b: Path) -> list[str]:
    if canonical(load_json(a)) != canonical(load_json(b)):
        return [f"inventories differ: {a} vs {b}"]
    return []


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("inventory", type=Path, help="HFS inventory.json to verify")
    ap.add_argument(
        "--compare",
        type=Path,
        help="Second inventory.json that must match byte-for-byte after canonicalization",
    )
    args = ap.parse_args()
    if not args.inventory.is_file():
        print(f"missing {args.inventory}", file=sys.stderr)
        return 1
    errors = verify_inventory(args.inventory)
    if args.compare:
        errors.extend(compare_two(args.inventory, args.compare))
    if errors:
        print("FAIL")
        for e in errors:
            print(" -", e)
        return 1
    print(f"PASS {args.inventory}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
