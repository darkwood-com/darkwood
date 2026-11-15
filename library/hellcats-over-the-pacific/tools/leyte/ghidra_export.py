#!/usr/bin/env python3
"""Leyte Gulf overlay -> Ghidra C (the reconstruction pipeline's first stage, as Pacific Conflict.c).

Runs Ghidra's headless analyzer (GHIDRA_INSTALL_DIR, default /Applications/Ghidra) on the verbatim
overlay (tools/leyte/extract_overlay.py): Motorola 68000 BE, raw binary at base 0 (so FUN_ names are
file offsets, as 1.0.3's), seeded with the overlay entry and the Leyte offsets of the Pacific
Conflict functions correspondence.json matched (SeedLeyteOverlay.java), auto-analysis, then every
function in the code range decompiled (ExportLeyteC.java) into
sources/leyte-gulf-1-0/CODE/Leyte Gulf overlay.c with an index of entries and sizes. The Ghidra
project is temporary. Needs Ghidra's native decompiler for the host (macOS ARM: built with
support/gradle/gradlew buildNatives).
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
OVERLAY = ROOT / "sources" / "leyte-gulf-1-0" / "Leyte Gulf overlay"
CORRESPONDENCE = ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json"
OUT_C = ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "Leyte Gulf overlay.c"
OUT_INDEX = ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "Leyte Gulf overlay.functions.json"


def main() -> int:
    if subprocess.run([sys.executable, str(ROOT / "tools" / "leyte" / "extract_overlay.py")]).returncode != 0:
        return 1
    data = OVERLAY.read_bytes()
    code, entry = struct.unpack(">I", data[4:8])[0], struct.unpack(">I", data[20:24])[0]
    functions = json.loads(CORRESPONDENCE.read_text())["functions"]
    # Exact matches only (byte for byte, or with only relocated operands moved): the "same
    # instructions" offsets come from an instruction-shape search and can land inside an instruction.
    seeds = {int(f["leyte"], 16) for f in functions
             if f["segment"].startswith("Pacific Conflict") and f["leyte"] and f["match"] in ("identical", "moved")}
    seeds.add(entry + 0x20)            # the header's entry is a runtime address (file offset - 0x20)
    OUT_C.parent.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory() as tmp:
        seed_file = Path(tmp) / "seeds.txt"
        seed_file.write_text("".join("0x%x\n" % s for s in sorted(seeds)))
        cmd = [str(GHIDRA / "support" / "analyzeHeadless"), tmp, "LeyteOverlay", "-import", str(OVERLAY),
               "-processor", "68000:BE:32:default", "-loader", "BinaryLoader", "-loader-baseAddr", "0x0",
               "-scriptPath", str(ROOT / "tools" / "leyte" / "ghidra"),
               "-preScript", "SeedLeyteOverlay.java", str(seed_file),
               "-postScript", "ExportLeyteC.java", str(OUT_C), str(OUT_INDEX), "0x%x" % (0x20 + code)]
        log = subprocess.run(cmd, capture_output=True, text=True)
    for line in (log.stdout + log.stderr).splitlines():
        if "seeded" in line or "exported" in line or "ERROR" in line:
            print(line.strip())
    index = json.loads(OUT_INDEX.read_text())
    done = sum(1 for f in index if f["decompiled"])
    print("%d functions, %d decompiled" % (len(index), done))
    return 0 if log.returncode == 0 and done == len(index) else 1


if __name__ == "__main__":
    sys.exit(main())
