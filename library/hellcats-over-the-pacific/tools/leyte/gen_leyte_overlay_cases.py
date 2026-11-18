#!/usr/bin/env python3
"""Emulator cases for Leyte Gulf's overlay port (godot/original/leyte/port/pacific_conflict.gd, as the game
runs it: extended by pacific_conflict_reconstructed.gd), from 1.0.3's overlay fixtures
(godot/tests/original/port/fixtures/*.json, the tools/original/gen_*_cases.py families), run on
Leyte's own overlay image: godot/tests/leyte/port/fixtures/overlay_cases.json, replayed by
tests/leyte/port/test_leyte_overlay.gd.

For each case of a function the Leyte port keeps (sources/leyte-gulf-1-0/overlay_port.json) or
pacific_conflict_reconstructed.gd writes under its 1.0.3 name (any input is a fair test there too: the
comparison is Leyte's code against Leyte's port, whatever the case's memory means to it):
- the routine is the function's Leyte address (the manifest's, or the one the reconstruction's own cases
  run it at);
- every address the case writes, and every long value it writes, that lies in 1.0.3's image is
  translated through overlay_map.json: an address the map names moves to its Leyte address, one within
  0x40 above a mapped one moves with it (a field of it), any other goes to scratch space past Leyte's
  image (SCRATCH, page by page; safe because no kept function names an unmapped image address).
  Addresses outside the image (the stack, the call logs' stand-ins) stay;
- both sides start from the plain Leyte image: 1.0.3's cases seed the overlay RNG with its
  overlay_rng_init(1), which Leyte has no counterpart of, so neither side seeds here;
- a case whose fixture stubs functions by name is left out (that stubbing is 1.0.3's port's);
- the generated port keeps a verified function even when something it calls refuses (a stub that stops
  the run), so a case is left out when the port could not follow Leyte's run: the run enters a routine
  that is neither kept nor written for Leyte under its 1.0.3 name in pacific_conflict_reconstructed.gd
  (the routines its own generator, gen_leyte_overlay_reconstructed_cases.py, runs at their Leyte
  addresses), or the function is made of a private helper that refuses and the reconstruction does not
  define (the manifest's open_calls); and one whose Leyte run calls a null code pointer (a host
  service the 1.0.3 case gives at an offset the translation does not move to Leyte's);
- a host-world offset moves through the function's own record-field pairs first (the manifest's
  record_fields: displacements its proof settled for itself), then the overlay-wide ones;
Leyte's code runs on tools/original/m68k_emu.py; its changes are the case's. The comparison is Leyte's
code against Leyte's port on the same inputs.

    python3 tools/leyte/gen_leyte_overlay_cases.py [--check]
"""
from __future__ import annotations

import glob
import json
import re
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

OUT = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "overlay_cases.json"
HEADER = 0x20


## Scratch space past Leyte's image and below the stack guard: where an image address the map does not
## reach goes, page by page (0x100 bytes), addresses and pointer values alike. Safe for the kept
## functions only: none of them names an unmapped image address (gen_overlay_port.py refuses those),
## so such memory is reached through what the case passes, wherever it is.
SCRATCH, SCRATCH_END = 0x2E000, 0x3E800


## What a reconstructed function takes beyond its routine's stack arguments: a register its caller left,
## which the port takes explicitly. The emulator starts a call with its data registers 0.
PORT_ARGUMENTS = {"find_anchor_point": [0]}      # entry D5 (read when no list-B node is in range)


HOST_WORLD = 0x1B5A0           # the overlay's pointer to the host's A5 world (1.0.3; its Leyte address by the map)


def translator(omap: dict, end: int, leyte_end: int = 0, host: int | None = None, layout: dict | None = None):
    """A 1.0.3 address as the case's Leyte one. host: the host world's base the case writes at
    HOST_WORLD; addresses in its first 0x600 bytes move with it, their offset through the record-layout
    pairs (the overlay's instructions reach the host world at positive offsets, through an address
    register)."""
    import bisect
    bases = sorted(omap)
    pages: dict = {}

    def move(a: int):
        if host is not None and host < a < host + 0x600:
            off = a - host
            return move_plain(host) + (layout or {}).get(off, off)
        return move_plain(a)

    def move_plain(a: int):
        if a >= max(end, leyte_end):
            return a
        if a >= end:                         # past 1.0.3's image, inside Leyte's: scratch too
            page = a & ~0xFF
            if page not in pages:
                at = SCRATCH + 0x100 * len(pages)
                if at >= SCRATCH_END:
                    return None
                pages[page] = at
            return pages[page] + (a - page)
        if a in omap:
            return omap[a]
        i = bisect.bisect_right(bases, a) - 1
        if i >= 0 and a - bases[i] < 0x40:
            return omap[bases[i]] + (a - bases[i])
        page = a & ~0xFF
        if page not in pages:
            at = SCRATCH + 0x100 * len(pages)
            if at >= SCRATCH_END:
                return None
            pages[page] = at
        return pages[page] + (a - page)
    return move


def build() -> str:
    port = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_port.json").read_text())
    kept = port["functions"]
    m = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_map.json").read_text())
    omap = {int(x["hellcats_1_0_3"], 16): int(x["leyte"], 16) for x in m["mapped"]}
    end = sum(m["images"]["hellcats_1_0_3"].values())
    leyte_end = sum(m["images"]["leyte"].values())
    layout = {(-int(k[3:], 16) if k.startswith("-") else int(k, 16)): (-int(v[3:], 16) if v.startswith("-") else int(v, 16))
              for k, v in port.get("record_layout", {}).items()}
    # Every function entry Ghidra finds in Leyte's overlay: a kept function's by its name, any other by its
    # entry (a run reaching one the port does not keep is left out).
    names_at = {int(a, 16) - HEADER: n for n, a in kept.items()}
    # What pacific_conflict_reconstructed.gd writes for Leyte: a routine under the 1.0.3 name the kept code
    # calls it by, at the Leyte address its own cases run it at; and every name it defines (helpers too).
    sys.path.insert(0, str(ROOT / "tools" / "leyte"))
    import gen_leyte_overlay_reconstructed_cases as reconstructed_cases
    function = re.compile(r"^(?:static )?func (\w+)\(", re.M)
    written = set(function.findall((ROOT / "godot" / "original" / "leyte" / "port" / "pacific_conflict_reconstructed.gd").read_text()))
    port_names = set(function.findall((ROOT / "godot" / "original" / "port" / "pacific_conflict.gd").read_text()))
    written_at = {a: n for n, a in reconstructed_cases.ROUTINES.items() if n in written and n in port_names}
    address_of = {n: int(a, 16) - HEADER for n, a in kept.items()}
    address_of.update({n: a for a, n in written_at.items() if n not in kept})
    open_calls = port.get("open_calls", {})

    def signed(text: str) -> int:
        return -int(text[3:], 16) if text.startswith("-") else int(text, 16)
    own_fields = {n: {signed(k): signed(x) for k, x in pairs.items()} for n, pairs in port.get("record_fields", {}).items()}
    starts = {int(f["entry"], 16) - HEADER: names_at.get(int(f["entry"], 16) - HEADER, f["name"])
              for f in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "Leyte Gulf overlay.functions.json").read_text())}
    starts[0] = starts.get(0, "image start")
    image = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data
    out_cases, left_out = [], {}
    memory_size, stack_top, stack_guard = 0x40000, 0x3F000, [0x3F000 - 0x800, 0x3F040]
    for path in sorted(glob.glob(str(ROOT / "godot" / "tests" / "original" / "port" / "fixtures" / "*.json"))):
        doc = json.loads(Path(path).read_text())
        # The overlay's families only (the host segments' fixtures, id0*, are gen_leyte_host_cases.py's).
        if Path(path).name.startswith("id0") or not isinstance(doc, dict) or "stack_top" not in doc or doc.get("stubs"):
            continue
        for c in doc.get("cases", []):
            name = c.get("method")
            if name not in address_of:
                continue
            label = "%s: %s" % (Path(path).name, c["name"])
            pieces_missing = sorted(h for h in open_calls.get(name, []) if h.startswith("_") and h not in written)
            if pieces_missing:
                left_out[label] = "made of private helpers neither kept nor written for Leyte: %s" % ", ".join(pieces_missing[:4])
                continue
            host = next((v for a, sz, v in doc.get("common_writes", []) + c["writes"] if a == HOST_WORLD and sz == 4), None)
            move = translator(omap, end, leyte_end, host, {**layout, **own_fields.get(name, {})})     # scratch pages per case
            why = None
            writes = []
            for a, s, v in doc.get("common_writes", []) + c["writes"]:
                a2 = move(a)
                v2 = move(v) if s == 4 and v < end and v >= 0x1000 else v
                if a2 is None or v2 is None:
                    why = "write %#x <- %#x: not reached by the map" % (a, v)
                    break
                writes.append([a2, s, v2])
            zero = []
            for lo, hi in c.get("zero", []):
                # Byte by byte, merged where the translation stays contiguous (pages and fields move apart).
                for addr in range(lo, hi):
                    t = move(addr)
                    if t is None:
                        why = why or "zero range %#x..%#x: no room in the scratch space" % (lo, hi)
                        break
                    if zero and zero[-1][1] == t:
                        zero[-1][1] = t + 1
                    else:
                        zero.append([t, t + 1])
                if why:
                    break
            if why:
                left_out[label] = why
                continue
            emu = Emulator(memory_size)
            # The image as the loader leaves it: text and data, the bss (and everything past it) zero.
            text, data = struct.unpack(">II", image[4:12])
            emu.mem[0:memory_size] = bytes(memory_size)          # Emulator() starts from 1.0.3's image
            emu.mem[0:text + data] = image[HEADER:HEADER + text + data]
            for lo, hi in zero:
                emu.mem[lo:hi] = bytes(hi - lo)
            for a, s, v in writes:
                emu.write(a, s, v)
            for address, log_base, nargs, *deref in doc.get("call_logs", []):
                emu.stubs[address] = lambda e, b=log_base, n=nargs, d=tuple(deref): log_call(e, b, n, *d)
            before = bytes(emu.mem)
            entered: set = set()
            step = emu.step
            previous, null_call = [None], [False]

            def traced(_e=emu, _s=step):
                if _e.pc in starts:
                    # Entered only when a call or a jump brings the run here (bsr, jsr, jmp, or the first
                    # instruction): Ghidra also lists entries inside routines (a loop head of
                    # matmul3x3_s16), which a branch or the instruction before reaches.
                    op = struct.unpack(">H", bytes(_e.mem[previous[0]:previous[0] + 2]))[0] if previous[0] is not None else 0x4E80
                    if (op & 0xFF00) == 0x6100 or (op & 0xFF80) == 0x4E80:
                        entered.add(_e.pc)
                        # jsr / jmp through a register or memory operand (not an absolute address) landing
                        # on 0: a null code pointer, which the port refuses where Leyte's code runs on.
                        if _e.pc == 0 and previous[0] is not None and (op & 0x3F) not in (0x38, 0x39):
                            null_call[0] = True
                previous[0] = _e.pc
                return _s()
            emu.step = traced
            try:
                d0 = emu.call(address_of[name], list(c["args"]), stack_top)
            except Exception as err:            # Leyte's code needs what the case does not give
                left_out[label] = "Leyte's run stopped: %s" % err
                continue
            if null_call[0]:
                left_out[label] = "Leyte's run calls a null code pointer (a service the case gives at 1.0.3's offset)"
                continue
            # A case driving the function into a routine the port has not got (called, or through a code
            # pointer the case writes) cannot be followed: left out until that routine is kept or written.
            foreign = sorted(starts[a] for a in entered
                             if starts[a] not in kept and a not in written_at and a != address_of[name])
            if foreign:
                left_out[label] = "reaches routines the port has not got: %s" % ", ".join(foreign[:4])
                continue
            changes = [[a, emu.mem[a]] for a in range(memory_size)
                       if not (stack_guard[0] <= a < stack_guard[1]) and emu.mem[a] != before[a]]
            entry = {"name": label, "method": name, "args": list(c["args"]) + PORT_ARGUMENTS.get(name, []), "zero": zero, "writes": writes,
                     "call_logs": doc.get("call_logs", []), "changes": changes}
            if "d0" in c:
                entry["d0"] = d0 & 0xFFFFFFFF
            out_cases.append(entry)
    doc = {"schema": 1, "generated_by": "tools/leyte/gen_leyte_overlay_cases.py", "memory_size": memory_size,
           "stack_top": stack_top, "stack_guard": stack_guard, "cases": out_cases, "left_out": dict(sorted(left_out.items()))}
    return json.dumps(doc, separators=(",", ":")) + "\n"


def main() -> int:
    text = build()
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte overlay cases: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(text)
    d = json.loads(text)
    print("wrote %s: %d cases (%d functions), %d left out" % (OUT.relative_to(ROOT), len(d["cases"]),
          len({c["method"] for c in d["cases"]}), len(d["left_out"])))
    return 0


if __name__ == "__main__":
    sys.exit(main())
