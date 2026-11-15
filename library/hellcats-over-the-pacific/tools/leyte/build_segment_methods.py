#!/usr/bin/env python3
"""Leyte Gulf's segment methods (godot/original/leyte/data/segment_methods.json): per segment, the
routines its ports call by offset (`_local`) -> the port method, as godot/original/mac/data/
segment_methods.json is for 1.0.3, keyed by Leyte's offsets only: every verified function at its
Leyte offset (correspondence.json, or host_ports.json "relocated"), and the routines written from
Leyte's bytes (tools/leyte/reconstructed.py), which call one another by Leyte's offsets. No offset
may be claimed for two methods.

1.0.3's offsets cannot share the map: they collide (CODE 2 0xad0 is paint_view_horizon_bands_a5safe
in 1.0.3, draw_thick_line_pixel_index_a5safe in Leyte). The generated ports' verified code reaches one
`_local` call, run_protection_dialog's 0x3ba (CODE 5): Leyte's run_protection_dialog makes the same
JSR at the same place to the same 0x3ba, which the runtime supplies itself (unexported.gd), as for
1.0.3.

    python3 tools/leyte/build_segment_methods.py [--check]
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
import reconstructed  # noqa: E402

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "godot" / "original" / "leyte" / "data" / "segment_methods.json"


def build() -> str:
    ports = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "host_ports.json").read_text())["segments"]
    corr = {(r["segment"], r["name"]): r for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]}
    bad = reconstructed.check()
    assert not bad, "reconstructed routines: %s" % bad
    out: dict = {}
    claims: dict = {}

    def claim(seg: int, off: int, method: str, why: str) -> None:
        key = (seg, off)
        if key in claims and claims[key][0] != method:
            raise AssertionError("CODE %d +%#x: %s (%s) and %s (%s)" % (seg, off, claims[key][0], claims[key][1], method, why))
        claims[key] = (method, why)
        out.setdefault(str(seg), {})["0x%x" % off] = method

    for seg_key, row in ports.items():
        seg = int(seg_key[2:])
        verified = set(row["functions"])
        for method in sorted(verified):
            at = row.get("relocated", {}).get(method) or (corr.get((seg_key, method)) or {}).get("leyte")
            if at:
                claim(seg, int(at, 16), method, "Leyte offset")
    for (seg, off), (_path, method) in reconstructed.ROUTINES.items():
        claim(seg, off, method, "reconstructed")
    doc = {"schema": 1, "generated_by": "tools/leyte/build_segment_methods.py",
           "segments": {k: dict(sorted(v.items(), key=lambda kv: int(kv[0], 16))) for k, v in sorted(out.items(), key=lambda kv: int(kv[0]))}}
    return json.dumps(doc, indent=1) + "\n"


def main() -> int:
    text = build()
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte segment methods: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(text)
    d = json.loads(text)["segments"]
    print("wrote %s: %s" % (OUT.relative_to(ROOT), {k: len(v) for k, v in d.items()}))
    return 0


if __name__ == "__main__":
    sys.exit(main())
