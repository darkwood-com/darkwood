#!/usr/bin/env python3
"""The Leyte Gulf overlay's semantic C, first stage: CODE/Leyte Gulf overlay.decomp.c.

For each Leyte function named by binary evidence (CODE/Leyte Gulf overlay.names.json "named": the
same bytes as a Hellcats 1.0.3 function, or the same once relocated longs and PC-relative
displacements are masked), its Leyte Ghidra C (CODE/Leyte Gulf overlay.c, unchanged) under the
Hellcats name, the Ghidra name kept as `/* Original: FUN_... */`, and the globals' readable names in
the bodies (their Ghidra symbols in the #defines), as Pacific Conflict.decomp.c does.

Globals: the two functions of a pair have their relocated longs at the same offsets (the masked
match compares everything else), so each such long pairs a Leyte address with a Hellcats one. A
Leyte address paired with one Hellcats address only, which Pacific Conflict.decomp.c names, gets
that name as a `#define` with the pairs it rests on; one paired with several is listed as a
conflict and named nowhere. Ambiguous offsets (names.json "ambiguous"), candidates and Leyte's own
functions are not written yet. Nothing here is reviewed C: the Hellcats review applies to the
Hellcats code, and the names to what the bytes prove (same code, same relative layout).

    python3 tools/leyte/semantic_overlay.py [--check]
"""
from __future__ import annotations

import json
import re
import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
BASE = ROOT / "sources" / "leyte-gulf-1-0"
LEYTE_C = BASE / "CODE" / "Leyte Gulf overlay.c"
NAMES = BASE / "CODE" / "Leyte Gulf overlay.names.json"
OUT = BASE / "CODE" / "Leyte Gulf overlay.decomp.c"
HELLCATS = ROOT / "sources" / "mac-os-9" / "Pacific Conflict"
HELLCATS_DECOMP = ROOT / "sources" / "resedit-1-0-3" / "CODE" / "Pacific Conflict.decomp.c"
HEADER = 0x20


def overlay(img: bytes) -> tuple[bytes, set]:
    """The overlay's code (after the 0x20-byte header) and its relocated longs' code offsets."""
    code_size, data_size = struct.unpack(">II", img[4:12])
    first = struct.unpack(">I", img[24:28])[0] // 8
    at = HEADER + code_size + data_size
    starts = set()
    for i in range(first):
        off, flags = struct.unpack(">II", img[at + 8 * i:at + 8 * i + 8])
        if not (flags >> 7) & 1:
            starts.add(off)
    return img[HEADER:HEADER + code_size], starts


def function_blocks(text: str) -> dict:
    """Ghidra name -> its C block (from its `// FUN_x @ 0x..` line to the next)."""
    marks = [(m.start(), m.group(1)) for m in re.finditer(r"^// (FUN_[0-9a-f]{8}) @ 0x[0-9a-f]+$", text, re.M)]
    blocks = {}
    for k, (at, name) in enumerate(marks):
        end = marks[k + 1][0] if k + 1 < len(marks) else len(text)
        blocks[name] = text[at:end].rstrip() + "\n"
    return blocks


def hellcats_globals() -> dict:
    """Hellcats address -> readable name, from Pacific Conflict.decomp.c's #defines."""
    out = {}
    for m in re.finditer(r"^#define (\w+) _?DAT_([0-9a-f]{8})\b", HELLCATS_DECOMP.read_text(), re.M):
        out.setdefault(int(m.group(2), 16), m.group(1))
    return out


def build() -> str:
    names = json.loads(NAMES.read_text())
    named = names["named"]
    hcode, hrel = overlay(HELLCATS.read_bytes())
    lcode, lrel = overlay((BASE / "Leyte Gulf overlay").read_bytes())
    index = {f["name"]: f for f in json.loads((BASE / "CODE" / "Leyte Gulf overlay.functions.json").read_text())}
    blocks = function_blocks(LEYTE_C.read_text())
    gnames = hellcats_globals()

    pairs = {}        # Leyte address -> {Hellcats address: [function names]}
    for f in named:
        if f["evidence"].startswith("layout"):
            continue        # byte offsets may shift: no relocation pairing (names_from_correspondence.py step 3)
        size = index[f["ghidra"]]["size"]
        h0 = int(f["hellcats_1_0_3"], 16) - HEADER
        l0 = int(f["leyte"], 16) - HEADER
        hs = sorted(o - h0 for o in hrel if h0 <= o < h0 + size)
        ls = sorted(o - l0 for o in lrel if l0 <= o < l0 + size)
        assert hs == ls, "%s: relocations at different offsets" % f["name"]
        for o in ls:
            hv = struct.unpack(">I", hcode[h0 + o:h0 + o + 4])[0]
            lv = struct.unpack(">I", lcode[l0 + o:l0 + o + 4])[0]
            pairs.setdefault(lv, {}).setdefault(hv, []).append(f["name"])

    defines, conflicts, unnamed, gmap = [], [], 0, {}
    for lv in sorted(pairs):
        hv = pairs[lv]
        if len(hv) > 1:
            conflicts.append("/*   0x%08x <- %s */" % (lv, ", ".join("0x%x (%s)" % (h, ", ".join(sorted(set(n)))) for h, n in sorted(hv.items()))))
            continue
        (h, fns), = hv.items()
        if h in gnames:
            gmap[lv] = gnames[h]
            defines.append("#define %s DAT_%08x /* Hellcats DAT_%08x; paired in %s */" % (gnames[h], lv, h, ", ".join(sorted(set(fns)))))
        else:
            unnamed += 1

    # Calls take the names of named functions and of "called" ones (their role proven at the call site).
    fn_names = {f["ghidra"]: f["name"] for f in named + names.get("called", [])}
    out = ["// Leyte Gulf overlay, semantic C (first stage), GENERATED by tools/leyte/semantic_overlay.py from",
           "// Leyte Gulf overlay.c (Ghidra, unchanged) and Leyte Gulf overlay.names.json: do not edit.",
           "// %d functions named by binary evidence (Hellcats 1.0.3 functions with the same code, or the same" % len(named),
           "// instructions with structure offsets moved: \"layout\"); globals named where a relocated long of a",
           "// same-code pair ties the Leyte address to one named Hellcats global. Not reviewed: the names say",
           "// what the bytes prove, nothing more.",
           "",
           "/* Globals: %d named, %d paired with an unnamed Hellcats address, %d conflicts (one Leyte" % (len(defines), unnamed, len(conflicts)),
           " * address, several Hellcats ones: named nowhere). */"]
    out += defines + [""] + (["/* Conflicts: */"] + conflicts + [""] if conflicts else [])
    for f in sorted(named, key=lambda f: int(f["leyte"], 16)):
        block = blocks[f["ghidra"]]
        body = block.split("\n", 1)[1]
        # The function's own name and its calls to other named functions; the Ghidra name stays.
        body = re.sub(r"\b(FUN_[0-9a-f]{8})\b(?=\s*\()",
                      lambda m: fn_names.get(m.group(1), m.group(1)) + (" /* Original: %s */ " % m.group(1) if m.group(1) == f["ghidra"] else ""),
                      body)
        body = re.sub(r"\b_?DAT_([0-9a-f]{8})\b", lambda m: gmap.get(int(m.group(1), 16), m.group(0)), body)
        out += ["",
                "/* Original: %s; source: Leyte Gulf overlay.c. Name from Hellcats 1.0.3: %s (%s at 0x%x, evidence: %s)." % (
                    f["ghidra"], f["name"], "Pacific Conflict", int(f["hellcats_1_0_3"], 16), f["evidence"]),
                " * Leyte offset %s. Ghidra C as exported, names applied; not reviewed. */" % f["leyte"],
                body.strip("\n")]
    return "\n".join(out) + "\n"


def main() -> int:
    text = build()
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte semantic C: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(text)
    print("wrote %s" % OUT.relative_to(ROOT))
    return 0


if __name__ == "__main__":
    sys.exit(main())
