#!/usr/bin/env python3
"""Emulator cases for Leyte Gulf's overlay functions written from Leyte's bytes
(godot/original/leyte/port/pacific_conflict_reconstructed.gd), run on Leyte's own overlay image:
godot/tests/leyte/port/fixtures/overlay_reconstructed_cases.json, replayed by
tests/leyte/port/test_leyte_overlay_reconstructed.gd. The routines the function calls are logging
stand-ins at their addresses (tools/original/cases.py log_call: count, then each call's longs), on
both sides. Synthetic memory only.

    python3 tools/leyte/gen_leyte_overlay_reconstructed_cases.py [--check]
"""
from __future__ import annotations

import json
import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "original"))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
import hfs  # noqa: E402
from m68k_emu import Emulator  # noqa: E402
from cases import log_call  # noqa: E402

OUT = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "overlay_reconstructed_cases.json"
MEMORY_SIZE, STACK_TOP, STACK_GUARD = 0x40000, 0x3F000, [0x3F000 - 0x800, 0x3F040]
HOST = 0x2F000                      # a host world past the image
LOGS = 0x3E000
ROUTINES = {"leyte_overlay_main_entry": 0x175A, "leyte_overlay_initialise": 0x1EF0,
            "leyte_overlay_rng_init": 0x135FE, "leyte_init_span_table_3ef2": 0x3EF2,
            "leyte_init_span_table_4094": 0x4094}
## Every callee of the initialiser is written now, so nothing is stood in: all of them run as they are.
CALL_LOGS = []


def build() -> list:
    return [
        {"name": "entry: host world, -1, ticks", "method": "leyte_overlay_main_entry", "args": [HOST, 0xFFFFFFFF, 0x55AA], "writes": []},
        {"name": "initialise: ticks 0x1234", "method": "leyte_overlay_initialise", "args": [0x1234], "writes": []},
        {"name": "initialise: over earlier state", "method": "leyte_overlay_initialise", "args": [0xFFFFFFFF],
         "writes": [[0x21020, 4, 0x99], [0x23D94, 4, 0x12345678], [0x21574, 2, 1]]},
        {"name": "rng init: seed 0", "method": "leyte_overlay_rng_init", "args": [0], "writes": []},
        {"name": "rng init: seed 0x89abcdef over an old ring", "method": "leyte_overlay_rng_init", "args": [0x89ABCDEF],
         "writes": [[0x2C5B4, 4, 0x2C5B8], [0x2C600, 4, 0x55555555]]},
        {"name": "span table 0x3ef2", "method": "leyte_init_span_table_3ef2", "args": [], "writes": [[0x3ECE, 4, 0xFFFFFFFF]]},
        {"name": "span table 0x4094", "method": "leyte_init_span_table_4094", "args": [], "writes": [[0x4070, 4, 0xFFFFFFFF]]},
        {"name": "entry: host world over an old one", "method": "leyte_overlay_main_entry", "args": [HOST + 0x100, 0xFFFFFFFF, 7],
         "writes": [[0x207FC, 4, HOST], [HOST + 0x100 + 0x92, 2, 3], [HOST + 0x100 + 0x322, 4, 0x11111111]]},
    ]


def text() -> str:
    fork = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data
    text, data = struct.unpack(">II", fork[4:12])
    image = fork[0x20:0x20 + text + data]       # as the loader leaves it: the bss zero
    cases = []
    for c in build():
        emu = Emulator(MEMORY_SIZE)
        # Emulator() starts from 1.0.3's overlay image: all of it replaced by Leyte's, the bss zero.
        emu.mem[0:MEMORY_SIZE] = bytes(MEMORY_SIZE)
        emu.mem[0:len(image)] = image
        for a, s, v in c["writes"]:
            emu.write(a, s, v)
        for address, base, nargs in CALL_LOGS:
            emu.stubs[address] = lambda e, b=base, n=nargs: log_call(e, b, n)
        before = bytes(emu.mem)
        emu.call(ROUTINES[c["method"]], c["args"], STACK_TOP)
        changes = [[a, emu.mem[a]] for a in range(MEMORY_SIZE) if not (STACK_GUARD[0] <= a < STACK_GUARD[1]) and emu.mem[a] != before[a]]
        cases.append({"name": c["name"], "method": c["method"], "args": c["args"], "zero": [], "writes": c["writes"],
                      "call_logs": CALL_LOGS, "changes": changes})
    doc = {"schema": 1, "generated_by": "tools/leyte/gen_leyte_overlay_reconstructed_cases.py", "memory_size": MEMORY_SIZE,
           "stack_top": STACK_TOP, "stack_guard": STACK_GUARD, "cases": cases}
    return json.dumps(doc, separators=(",", ":")) + "\n"


def main() -> int:
    t = text()
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == t
        print(("PASS" if ok else "FAIL") + " Leyte overlay reconstructed cases: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(t)
    print("wrote %s (%d cases)" % (OUT.relative_to(ROOT), len(json.loads(t)["cases"])))
    return 0


if __name__ == "__main__":
    sys.exit(main())
