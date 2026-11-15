#!/usr/bin/env python3
"""Leyte Gulf's CODE 9 Toolbox glue against 1.0.3's: godot/original/leyte/data/glue_slots.json.

The runtime answers 1.0.3's glue slots with its own stand-ins (the Toolbox managers' install(),
keyed by 1.0.3's jump-table offsets). A Leyte slot whose glue is byte-identical to exactly one
1.0.3 slot's (from its entry through its first RTS, JMP (an) or JMP d16(PC), that one's
displacement left out, within 0x80 bytes) is the same call: "same" maps it to
that 1.0.3 offset, so the Leyte profile can re-key the stand-ins. Any other is "new", with the trap
word(s) its bytes hold (and $A260's selector, the moveq before it), for a stand-in of its own.

    python3 tools/leyte/build_glue_slots.py [--check]
"""
from __future__ import annotations

import json
import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "godot" / "original" / "leyte" / "data" / "glue_slots.json"


def _table(code0: bytes) -> dict:
    size, off = struct.unpack(">II", code0[8:16])
    out = {}
    for k in range(size // 8):
        e = code0[16 + 8 * k:24 + 8 * k]
        if struct.unpack(">H", e[4:6])[0] == 9:
            out[off + 8 * k + 2] = struct.unpack(">H", e[0:2])[0] + 4
    return out


def _glue(code: bytes, at: int):
    # Up to 0x80 bytes: FSRead / FSWrite's shared entry runs 72 (gen_host_ports.py stops at 64).
    for end in range(at, min(at + 0x80, len(code) - 1), 2):
        w = int.from_bytes(code[end:end + 2], "big")
        if w == 0x4E75 or (w & 0xFFF8) == 0x4ED0:
            return code[at:end + 2]
        if w == 0x4EFA:
            return code[at:end + 2]                          # its displacement left out
    return None


def _traps(glue: bytes) -> list:
    out = []
    for k in range(0, len(glue) - 1, 2):
        w = int.from_bytes(glue[k:k + 2], "big")
        if w & 0xF000 == 0xA000:
            item = {"trap": "%04x" % w}
            if w == 0xA260 and k >= 2 and glue[k - 2] == 0x70:
                item["selector"] = glue[k - 1]
            out.append(item)
    return out


def build() -> str:
    h0 = (ROOT / "sources" / "resedit-1-0-3" / "CODE" / "ID00.bin").read_bytes()
    l0 = (ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "ID00.bin").read_bytes()
    h9 = (ROOT / "sources" / "resedit-1-0-3" / "CODE" / "ID09.bin").read_bytes()
    l9 = (ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "ID09.bin").read_bytes()
    by_glue: dict = {}
    for off, target in _table(h0).items():
        by_glue.setdefault(_glue(h9, target), []).append(off)
    same, new = {}, {}
    for off, target in sorted(_table(l0).items()):
        g = _glue(l9, target)
        hits = by_glue.get(g, [])
        if g is not None and len(hits) == 1:
            same["0x%x" % off] = "0x%x" % hits[0]
        else:
            new["0x%x" % off] = {"target": "0x%x" % target, "traps": _traps(g or b""),
                                 "why": "no 1.0.3 slot with these bytes" if not hits else "several 1.0.3 slots: %s" % hits}
    doc = {"schema": 1, "generated_by": "tools/leyte/build_glue_slots.py", "same": same, "new": new}
    return json.dumps(doc, indent=1) + "\n"


def main() -> int:
    text = build()
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte glue slots: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(text)
    d = json.loads(text)
    print("wrote %s: %d same, %d new %s" % (OUT.relative_to(ROOT), len(d["same"]), len(d["new"]),
                                          {k: v["traps"] for k, v in d["new"].items()}))
    return 0


if __name__ == "__main__":
    sys.exit(main())
