#!/usr/bin/env python3
"""Comparison cases for Leyte Gulf's ID07 (the STR# 129 string table) from Leyte's own bytes:
godot/tests/leyte/port/fixtures/id07_cases.json, replayed against the generated Leyte port
(godot/original/leyte/port/ID07.gd) by tests/leyte/port/test_leyte_id07.gd.

The harness is tools/original/gen_id07_09_cases.py's (the same emulator, stand-ins and cases), with
what the binaries say differs: Leyte's CODE 7 (and its CODE 1 / CODE 2 for the switch helper, JT $3a,
and memmove, here JT $542: both byte-identical to 1.0.3's), HLock through JT $28a, and the A5 world
cleared down to Leyte's size (0x2322). Needs the disk image only (Pillow / capstone not used).

Usage: python3 tools/leyte/gen_leyte_id07_cases.py
"""
from __future__ import annotations

import json
import sys
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
sys.path.insert(0, str(ROOT / "tools" / "original"))
import hfs  # noqa: E402
from tools import resedit  # noqa: E402
import gen_id07_09_cases as H  # noqa: E402
from m68k_emu import Emulator  # noqa: E402
from segment_stubs import install  # noqa: E402

OUT = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "id07_cases.json"
JT_HLOCK, JT_SWITCH, JT_MEMMOVE = 0x28A, 0x3A, 0x542
SWITCH_AT, MEMMOVE_AT = 0xBE, 0x24A          # in Leyte's CODE 1 / CODE 2 (as in 1.0.3's)


def main() -> None:
    ley = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf")
    code = {e["id"]: e["payload"] for e in resedit.parse_fork(ley.rsrc) if e["type"] == "CODE"}
    spec = dict(H.SEGMENTS["ID07"])
    spec["logs"] = [[e[0], JT_HLOCK if e[0] == "jt" else e[1]] + e[2:] for e in spec["logs"]]
    base = [[a, 4, 0] for start, end in [(H.DATA, H.DATA + 0x2000), (H.HANDLE, H.HANDLE + 0x10), (H.LOGS, H.LOGS + 0x600),
                                         (H.A5 - 0x2400, H.A5)] for a in range(start, end, 4)] + spec["common"]
    base += [[H.A5 + JT_SWITCH, 2, 0x4EF9], [H.A5 + JT_SWITCH + 2, 4, H.ID01_AT + SWITCH_AT],
             [H.A5 + JT_MEMMOVE, 2, 0x4EF9], [H.A5 + JT_MEMMOVE + 2, 4, H.ID02_AT + MEMMOVE_AT]]
    out = {"memory_size": H.MEMORY_SIZE, "a5": H.A5, "stack_top": H.STACK_TOP, "stack_guard": list(H.STACK_GUARD),
           "common_writes": base, "call_logs": spec["logs"], "cases": []}
    with tempfile.TemporaryDirectory() as tmp:
        seg7 = Path(tmp) / "ID07.bin"
        seg7.write_bytes(code[7])
        for c in spec["cases"]:
            emu = Emulator(H.MEMORY_SIZE, str(seg7), file_delta=0x7FFFFFFF)
            emu.mem[H.CODE:H.CODE + len(code[7])] = code[7]
            emu.mem[H.ID01_AT:H.ID01_AT + 0x22E] = code[1][:0x22E]
            emu.mem[H.ID02_AT:H.ID02_AT + len(code[2])] = code[2]
            for addr, size, value in base + c["writes"]:
                emu.write(addr, size, value)
            emu.a[5] = H.A5
            install(emu, H.A5, [[e[0], e[1], e[2], e[3], e[4], e[5], {int(k): v for k, v in e[6].items()}] + e[7:] for e in spec["logs"]])
            a7 = H.STACK_TOP
            for size, value in reversed(c["stack"]):
                a7 -= size
                emu.write(a7, size, value)
            before = bytes(emu.mem)
            emu.call(H.CODE + spec["routines"][c["method"]], [], a7)
            changes = [[a, emu.mem[a]] for a in range(H.MEMORY_SIZE)
                       if not (H.STACK_GUARD[0] <= a < H.STACK_GUARD[1]) and emu.mem[a] != before[a]]
            out["cases"].append({"name": c["name"], "method": c["method"], "args": c["args"], "writes": c["writes"], "changes": changes})
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(out, separators=(",", ":")) + "\n")
    print("ID07 (Leyte): %d cases -> %s" % (len(out["cases"]), OUT.relative_to(ROOT)))


if __name__ == "__main__":
    main()
