#!/usr/bin/env python3
"""Deterministic inventory of Hellcats Macintosh resource-related inputs.

Never modifies source files. Writes JSON with SHA-256 digests.
Does not invent resource IDs/names when parse fails.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import sys
from pathlib import Path

try:
    from parse_rsrc import parse_classic_resource_fork
except ImportError:  # allow `python3 tools/mac_resources/inventory.py` from repo root
    from tools.mac_resources.parse_rsrc import parse_classic_resource_fork  # type: ignore

REPO_DEFAULT = Path(__file__).resolve().parents[2]
SOURCES = REPO_DEFAULT / "sources"


def sha256_file(path: Path) -> str:
    h = hashlib.sha256()
    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()


def classify(rel: str, path: Path) -> str:
    name = path.name
    parent = path.parent.name
    if name.endswith(".bin") and parent == "CODE" and name.startswith("ID"):
        return "code-bin"
    if parent == "CODE" and name.startswith("ID") and path.suffix == "":
        return "code-hex"
    if parent == "CODE" and name.endswith(".c") and not name.endswith(".decomp.c"):
        return "ghidra-c"
    if parent == "CODE" and name.endswith(".decomp.c"):
        return "decomp-c"
    if name == "Pacific Conflict" and parent == "Hellcats":
        return "overlay"
    if name.endswith(".rsrc"):
        return "rsrc-candidate"
    if name in ("Hellcats", "Hellcats.bin") and parent == "Hellcats":
        return "app-stub"
    if path.suffix.lower() == ".dsk":
        return "disk-image"
    if path.suffix.lower() in {".png", ".pdf", ".txt", ".mov", ".sit"}:
        return "reference"
    if path.suffix.lower() == ".import":
        return "godot-import-meta"
    return "other"


def inventory_sources(sources: Path) -> dict:
    entries = []
    roots = [
        sources / "ResEdit" / "CODE",
        sources / "Hellcats",
        sources / "Screenshots",
        sources / "hellcatsoverthepacific" / "docs",
        sources / "hellcatsoverthepacific",
    ]
    seen: set[str] = set()
    for root in roots:
        if not root.is_dir():
            continue
        for path in sorted(root.rglob("*")):
            if not path.is_file():
                continue
            # Skip nested tools copies under CODE/tools that are scripts only —
            # still include them as other for completeness.
            rel = str(path.relative_to(sources)).replace("\\", "/")
            if rel in seen:
                continue
            seen.add(rel)
            kind = classify(rel, path)
            size = path.stat().st_size
            digest = sha256_file(path)
            item: dict = {
                "path": rel,
                "absolute_path": str(path),
                "kind": kind,
                "size": size,
                "sha256": digest,
                "parse_status": "n/a",
            }
            if kind == "rsrc-candidate":
                parsed = parse_classic_resource_fork(path.read_bytes())
                item["parse_status"] = parsed["parse_status"]
                item["rsrc"] = {
                    k: parsed[k]
                    for k in (
                        "format",
                        "error",
                        "header",
                        "type_count",
                        "resource_count",
                        "resources",
                    )
                    if k in parsed
                }
            elif kind == "disk-image":
                item["parse_status"] = "hfs-candidate"
                item["note"] = (
                    "Classic HFS floppy image; extract with tools/mac_resources/extract.py"
                )
            elif kind == "code-bin" and size > 0:
                head = path.read_bytes()[:8]
                item["parse_status"] = "header-bytes-only"
                item["header_hex"] = head.hex()
                if len(head) >= 4:
                    item["first_u16_be"] = int.from_bytes(head[0:2], "big")
                    item["second_u16_be"] = int.from_bytes(head[2:4], "big")
            elif kind == "app-stub" and size == 0:
                item["parse_status"] = "empty"
                item["note"] = "zero-byte stub; not a usable Macintosh application image"
            entries.append(item)

    # Stable canonical form
    entries.sort(key=lambda e: e["path"])
    return {
        "schema": "hellcats-mac-resources-inventory-v1",
        "sources_root": str(sources),
        "entry_count": len(entries),
        "entries": entries,
    }


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument(
        "--sources",
        type=Path,
        default=SOURCES,
        help="Path to sources",
    )
    ap.add_argument(
        "-o",
        "--output",
        type=Path,
        required=True,
        help="Output JSON path",
    )
    args = ap.parse_args()
    if not args.sources.is_dir():
        print(f"sources not found: {args.sources}", file=sys.stderr)
        return 1
    data = inventory_sources(args.sources.resolve())
    args.output.parent.mkdir(parents=True, exist_ok=True)
    text = json.dumps(data, indent=2, sort_keys=True) + "\n"
    args.output.write_text(text, encoding="utf-8")
    print(f"wrote {args.output} ({data['entry_count']} entries)")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
