#!/usr/bin/env python3
"""Leyte Gulf's jump table for the runtime: godot/original/leyte/data/jump_table.json, from Leyte's own
CODE 0 (sources/leyte-gulf-1-0/CODE/ID00.bin, tools/leyte/extract_resources.py), in the format of
1.0.3's (tools/runtime/build_runtime_data.py): one entry per slot (A5 offset, segment, target = routine
offset + 4), named only where a Leyte host port proves the routine (sources/leyte-gulf-1-0/
host_ports.json: the port and its functions, placed at Leyte's offsets by correspondence.json). The
title profile's linkage step. --check compares.

    python3 tools/leyte/build_jump_table.py [--check]
"""
from __future__ import annotations

import json
import struct
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

ROOT = Path(__file__).resolve().parents[2]
BASE = ROOT / "sources" / "leyte-gulf-1-0"
OUT = ROOT / "godot" / "original" / "leyte" / "data" / "jump_table.json"


def build() -> str:
    code0 = (BASE / "CODE" / "ID00.bin").read_bytes()
    above, below, size, offset = struct.unpack(">IIII", code0[:16])
    corr = {(r["segment"], r["name"]): r for r in json.loads((BASE / "correspondence.json").read_text())["functions"]}
    ported = {}
    for seg, row in json.loads((BASE / "host_ports.json").read_text())["segments"].items():
        for name in row["functions"]:
            # A function gen_host_ports.py relocated (its Leyte routine found by proof, the
            # correspondence's placement wrong) is at that offset.
            at = row.get("relocated", {}).get(name) or corr[(seg, name)]["leyte"]
            ported[(int(seg[2:]), int(at, 16))] = (row["port"], name)
    # Routines written from Leyte's bytes (tools/leyte/reconstructed.py, each with its evidence), and
    # every port of a segment that has them is that segment's reconstructed file (it extends the
    # generated one).
    import reconstructed
    bad = reconstructed.check()
    assert not bad, "reconstructed routines: %s" % bad
    for (seg, off), (path, method) in reconstructed.ROUTINES.items():
        ported[(seg, off)] = (path, method)
    for key, (path, method) in list(ported.items()):
        if key[0] in reconstructed.PORT_FILES:
            ported[key] = (reconstructed.PORT_FILES[key[0]], method)
    entries = []
    for k in range(size // 8):
        o = 16 + 8 * k
        stored, = struct.unpack(">H", code0[o:o + 2])
        segment, = struct.unpack(">H", code0[o + 4:o + 6])
        target = stored + 4
        port, method = ported.get((segment, target), (None, None))
        entries.append({"a5_offset": offset + 8 * k + 2, "segment": segment, "target": target,
                        "port": port, "method": method, "pascal": segment == 9})
    doc = {"source": "sources/leyte-gulf-1-0/CODE/ID00.bin", "above_a5": above, "below_a5": below,
           "jump_table_offset": offset, "named": sum(1 for e in entries if e["method"]), "entries": entries}
    return json.dumps(doc, indent=1) + "\n"


def main() -> int:
    text = build()
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte jump table: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(text)
    d = json.loads(text)
    print("wrote %s: %d slots, %d named, A5 world %#x below / %#x above" % (OUT.relative_to(ROOT), len(d["entries"]), d["named"], d["below_a5"], d["above_a5"]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
