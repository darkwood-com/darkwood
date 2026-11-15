#!/usr/bin/env python3
"""Leyte Gulf's host CODE segments -> Ghidra C: sources/leyte-gulf-1-0/CODE/IDnn.c and IDnn.functions.json
(as 1.0.3's sources/resedit-1-0-3/CODE/IDnn.c), the first stage of the host's reconstruction where the
1.0.3 ports cannot be generated (Leyte's own code: CODE 8's data-fork loader, CODE 2's new functions).

Ghidra's headless analyzer (GHIDRA_INSTALL_DIR, default /Applications/Ghidra) on the verbatim segment
(tools/leyte/extract_resources.py): Motorola 68000 BE, raw binary at base 0 (FUN_ names are the
segment's offsets, as 1.0.3's: the 4-byte header first), seeded with the segment's jump-table targets
(Leyte's CODE 0) and the Leyte offsets of the 1.0.3 functions the correspondence found there exactly
(identical / moved), auto-analysis, every function from offset 4 decompiled. The project is temporary.

    python3 tools/leyte/ghidra_host.py 8 [2 ...]
"""
import json
import os
import struct
import subprocess
import sys
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
GHIDRA = Path(os.environ.get("GHIDRA_INSTALL_DIR", "/Applications/Ghidra"))
BASE = ROOT / "sources" / "leyte-gulf-1-0"


def export(n: int) -> bool:
    seg = "ID%02d" % n
    binary = BASE / "CODE" / ("%s.bin" % seg)
    code0 = (BASE / "CODE" / "ID00.bin").read_bytes()
    seeds = set()
    for k in range(struct.unpack(">I", code0[8:12])[0] // 8):
        e = code0[16 + 8 * k:24 + 8 * k]
        if struct.unpack(">H", e[4:6])[0] == n:
            seeds.add(struct.unpack(">H", e[0:2])[0] + 4)
    for f in json.loads((BASE / "correspondence.json").read_text())["functions"]:
        if f["segment"] == seg and f["leyte"] and f["match"] in ("identical", "moved"):
            seeds.add(int(f["leyte"], 16))
    out_c, out_index = BASE / "CODE" / ("%s.c" % seg), BASE / "CODE" / ("%s.functions.json" % seg)
    with tempfile.TemporaryDirectory() as tmp:
        seed_file = Path(tmp) / "seeds.txt"
        seed_file.write_text("".join("0x%x\n" % s for s in sorted(seeds)))
        cmd = [str(GHIDRA / "support" / "analyzeHeadless"), tmp, "Leyte" + seg, "-import", str(binary),
               "-processor", "68000:BE:32:default", "-loader", "BinaryLoader", "-loader-baseAddr", "0x0",
               "-scriptPath", str(ROOT / "tools" / "leyte" / "ghidra"),
               "-preScript", "SeedLeyteOverlay.java", str(seed_file),
               "-postScript", "ExportLeyteC.java", str(out_c), str(out_index), "0x%x" % binary.stat().st_size, "0x4",
               "Leyte Gulf CODE %d (Leyte Gulf:Leyte Gulf resource fork)" % n]
        log = subprocess.run(cmd, capture_output=True, text=True)
    index = json.loads(out_index.read_text())
    done = sum(1 for f in index if f["decompiled"])
    print("%s: %d seeds, %d functions, %d decompiled" % (seg, len(seeds), len(index), done))
    return log.returncode == 0 and done == len(index)


if __name__ == "__main__":
    sys.exit(0 if all(export(int(a)) for a in sys.argv[1:]) else 1)
