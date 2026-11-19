#!/usr/bin/env python3
"""Which instructions of Leyte's overlay routines the emulator cases execute: a check on the cases, not
on the port. The two fixtures (godot/tests/leyte/port/fixtures/overlay_cases.json, 1.0.3's cases
translated, and overlay_reconstructed_cases.json, the hand-written ones) are run again on the 68000
emulator from Leyte's image, exactly as their generators run them, with every executed address noted.
For each routine with an address (the generated port's functions, the reconstruction's case table),
the instructions of its own body (from its entry to its natural end: the first rts / jmp / bra no
earlier forward branch passes) are counted as executed or not by ANY case - one that calls the routine
through another compares its effect just as well.

A routine written by hand in pacific_conflict_reconstructed.gd rests on its cases: an instruction no
case executes is a branch nothing has compared with Leyte's code. (A generated function is proven
instruction by instruction; its cases are a second check.) Read-only: prints, writes nothing.

    python3 tools/leyte/overlay_case_coverage.py              reconstructed routines, least covered first
    python3 tools/leyte/overlay_case_coverage.py --all        generated functions too
    python3 tools/leyte/overlay_case_coverage.py NAME ...     those routines, with the instructions not executed
Needs capstone (shell.nix) and the disk image (sources/mac-os-9).
"""
from __future__ import annotations

import json
import re
import struct
import sys
from pathlib import Path

import capstone

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "original"))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
sys.path.insert(0, str(ROOT / "tools" / "leyte"))
import hfs  # noqa: E402
from m68k_emu import Emulator  # noqa: E402
from cases import log_call  # noqa: E402

HEADER = 0x20
FIXTURES = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures"
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True
NOT_BRANCH = ("bset", "bclr", "btst", "bchg", "bsr", "bf", "bkpt")


def body(mem: bytes, text: int, at: int) -> list:
    """The routine's instructions, to its natural end."""
    out, far = [], at
    for a in MD.disasm(mem[at:min(text, at + 0x4000)], at):
        out.append(a)
        m = a.mnemonic
        if m.startswith(("b", "db")) and not m.startswith(NOT_BRANCH):
            t = re.search(r"\$([0-9a-f]+)$", a.op_str)
            if t:
                far = max(far, int(t.group(1), 16))
        if (m in ("rts", "rte", "rtd", "rtr") or m.startswith(("jmp", "bra"))) and a.address + a.size > far:
            break
    return out


def main() -> int:
    import gen_leyte_overlay_reconstructed_cases as reconstructed_cases
    fork = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data
    text, data = struct.unpack(">II", fork[4:12])
    image = fork[HEADER:HEADER + text + data]
    manifest = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_port.json").read_text())
    address = {n: int(a, 16) - HEADER for n, a in manifest["functions"].items()}
    address.update(reconstructed_cases.ROUTINES)
    written = set(re.findall(r"^(?:static )?func (\w+)\(", (
        ROOT / "godot" / "original" / "leyte" / "port" / "pacific_conflict_reconstructed.gd").read_text(), re.M))
    executed: set = set()
    count: dict = {}
    for name in ("overlay_cases.json", "overlay_reconstructed_cases.json"):
        doc = json.loads((FIXTURES / name).read_text())
        size = int(doc["memory_size"])
        for c in doc["cases"]:
            emu = Emulator(size)
            emu.mem[0:size] = bytes(size)
            emu.mem[0:len(image)] = image
            for lo, hi in c.get("zero", []):
                emu.mem[lo:hi] = bytes(hi - lo)
            for a, s, v in c["writes"]:
                emu.write(a, s, v)
            for at, base, nargs, *deref in c.get("call_logs", []):
                emu.stubs[at] = lambda e, b=base, n=nargs, d=tuple(deref): log_call(e, b, n, *d)
            emu.d[5] = c.get("d5_in", 0) & 0xFFFFFFFF
            emu.a[5] = c.get("a5_in", 0) & 0xFFFFFFFF
            count[c["method"]] = count.get(c["method"], 0) + 1
            step = emu.step

            def traced(_e=emu, _s=step):
                executed.add(_e.pc)
                return _s()
            emu.step = traced
            # the port's extra arguments (a register the caller left) are pushed too, as the generators push them
            emu.call(address[c["method"]], list(c["args"]), int(doc["stack_top"]))
    names = [a for a in sys.argv[1:] if not a.startswith("--")]
    rows = []
    for method in sorted(address):
        if names and method not in names:
            continue
        if not names and "--all" not in sys.argv and method not in written:
            continue
        ins = body(image, text, address[method])
        missed = [i for i in ins if i.address not in executed]
        rows.append((len(missed), method, len(ins), missed))
    rows.sort(key=lambda r: (-r[0], r[1]))
    whole = sum(1 for r in rows if r[0] == 0)
    print("%d routines, %d fully executed; instructions not executed by any case:" % (len(rows), whole))
    for n, method, total, missed in rows:
        if n == 0 and not names:
            continue
        print("  %-40s %4d of %4d not executed, %3d cases of its own, routine 0x%x" % (
            method, n, total, count.get(method, 0), address[method]))
        if names:
            for i in missed:
                print("        0x%x %s %s" % (i.address, i.mnemonic, i.op_str))
    return 0


if __name__ == "__main__":
    sys.exit(main())
