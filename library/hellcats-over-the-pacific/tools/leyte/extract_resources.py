#!/usr/bin/env python3
"""Leyte Gulf's resource fork, extracted verbatim: sources/leyte-gulf-1-0/resources/<TYPE>/<id>.bin and
CODE/ID<nn>.bin (as sources/resedit-1-0-3/ lays out 1.0.3's), with resources.json (type, id, name,
attributes, size, SHA-256 of each). Read from sources/mac-os-9/LeyteGulf.dsk ("Leyte Gulf:Leyte
Gulf"), which is never written. The first stage of the Leyte title profile: the runtime's resources
and the host CODE the ports are checked against; and the Godot mirror the runtime reads
(godot/assets/leyte-gulf-1.0/, the title "leyte-gulf" in assets/ui/resource_files.gd). --check compares.

    python3 tools/leyte/extract_resources.py [--check]
"""
from __future__ import annotations

import hashlib
import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
import hfs  # noqa: E402
from tools import resedit  # noqa: E402

BASE = ROOT / "sources" / "leyte-gulf-1-0"
DISK = ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk"
## The Godot mirror the runtime reads with the title "leyte-gulf" (assets/ui/resource_files.gd): the
## payloads under the folder names the 1.0.3 mirror uses (lower-cased; resource_manager.gd FOLDERS).
MIRROR = ROOT / "godot" / "assets" / "leyte-gulf-1.0"
FOLDERS = {"snd ": "snds", "icl4": "icI4", "icl8": "icI8"}


def _folder(type_name: str) -> str:
    """A type as a folder name (no path separators: 'snd ' keeps its space, as resedit-1-0-3 does)."""
    return type_name.replace("/", "_")


def build() -> dict:
    app = hfs.find(DISK, "Leyte Gulf:Leyte Gulf")
    files, rows = {}, []
    for e in sorted(resedit.parse_fork(app.rsrc), key=lambda e: (e["type"], e["id"])):
        payload = bytes(e["payload"])
        if e["type"] == "CODE":
            path = BASE / "CODE" / ("ID%02d.bin" % e["id"])
        else:
            path = BASE / "resources" / _folder(e["type"]) / ("%d.bin" % e["id"])
        files[path] = payload
        if e["type"] != "CODE":
            files[MIRROR / FOLDERS.get(e["type"], e["type"]).lower() / ("%d.bin" % e["id"])] = payload
        rows.append({"type": e["type"], "id": e["id"], "name": e["name"], "attributes": e["attributes"],
                     "size": len(payload), "sha256": hashlib.sha256(payload).hexdigest(),
                     "file": path.relative_to(BASE).as_posix()})
    manifest = {"schema": 1, "generated_by": "tools/leyte/extract_resources.py",
                "source": "sources/mac-os-9/LeyteGulf.dsk Leyte Gulf:Leyte Gulf (resource fork)",
                "count": len(rows), "resources": rows}
    files[BASE / "resources.json"] = (json.dumps(manifest, indent=1) + "\n").encode()
    mirror = {"schema": 1, "generated_by": "tools/leyte/extract_resources.py",
              "resources": [{"folder": FOLDERS.get(r["type"], r["type"]).lower(), "type": r["type"], "id": r["id"], "sha256": r["sha256"]}
                            for r in rows if r["type"] != "CODE"]}
    files[MIRROR / "resources.json"] = (json.dumps(mirror, indent=1) + "\n").encode()
    return files


def main() -> int:
    files = build()
    if "--check" in sys.argv:
        bad = [p for p, b in files.items() if not p.is_file() or p.read_bytes() != b]
        listed = {p for p in files}
        extra = [p for p in (BASE / "resources").rglob("*.bin") if p not in listed] if (BASE / "resources").is_dir() else []
        extra += [p for p in (BASE / "CODE").glob("ID*.bin") if p not in listed]
        # data_fork.bin is the data fork's mirror, written and checked by extract_overlay.py.
        extra += [p for p in MIRROR.rglob("*.bin") if p not in listed and p.name != "data_fork.bin"] if MIRROR.is_dir() else []
        for p in bad + extra:
            print("FAIL Leyte resources: %s" % p.relative_to(ROOT))
        if not bad and not extra:
            print("PASS Leyte resources: %d files" % len(files))
        return 1 if bad or extra else 0
    for p, b in files.items():
        p.parent.mkdir(parents=True, exist_ok=True)
        p.write_bytes(b)
    print("wrote %d files under %s" % (len(files), BASE.relative_to(ROOT)))
    return 0


if __name__ == "__main__":
    sys.exit(main())
