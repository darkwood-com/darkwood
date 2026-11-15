#!/usr/bin/env python3
"""Emulator cases for Leyte Gulf's generated host ports (ID05 so far) from Leyte's own bytes, reusing 1.0.3's
case generators (tools/original/gen_id05_*_cases.py) unchanged:
godot/tests/leyte/port/fixtures/<name>.json, replayed against godot/original/leyte/port/ by
tests/leyte/port/test_leyte_host_ports.gd.

What is swapped, below the generators: the segment's bytes (Leyte's CODE n), the routines' offsets
(Leyte's, from correspondence.json), and every address in the A5 world (inputs written with the
emulator's write, stand-ins installed at jump-table slots) translated through the port's verified
remap (sources/leyte-gulf-1-0/host_ports.json: "a5"; only before the code runs, its own stores
untouched): an offset the remap names moves to its Leyte
offset, one within 0x40 above a remapped global moves with it (a field of it), any other stays. The
fixture's recorded inputs are translated the same way, so the port replays exactly what Leyte's code
ran on. The comparison is Leyte's code against Leyte's port on the same inputs, whatever they are.

Usage: python3 tools/leyte/gen_leyte_host_cases.py [IDnn ...]   (the segments to build; all by default)
"""
from __future__ import annotations

import importlib
import json
import sys
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "original"))
OUT = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures"
## ID06's generators build their scenes by running routines and record the memory they leave as the
## cases' inputs: with Leyte's code those are already in Leyte's layout, and translating them again
## would move them twice. They need their own handling (not done yet).
GENERATORS = {"ID05": ["gen_id05_protection_cases", "gen_id05_dialog_cases"],
              ## ID02's port is "partial" (host_ports.json): the generators run as they are, a routine
              ## Leyte lacks left at 1.0.3's offset, and only the cases of the verified functions are
              ## kept (the others' would replay a refusal).
              "ID02": ["gen_id02_trapfree_cases", "gen_id02_crt_cases", "gen_id02_glue_cases",
                       "gen_id02_memory_cases", "gen_id02_draw_cases",
                       "gen_id02_palette_cases", "gen_id02_toolbox_cases", "gen_id02_dialog_cases"],
              ## ID03's generators run through gen_id03_helpers_cases.run (CODE_DIR: Leyte's CODE here);
              ## ID02's own code runs behind two of their slots (abs_long, memset: the same in Leyte).
              ## (prepare and remap left out: none of their functions is verified for Leyte yet)
              "ID03": ["gen_id03_helpers_cases", "gen_id03_boxes_cases", "gen_id03_downsample_cases",
                       "gen_id03_gauges_cases", "gen_id03_glyph_cases", "gen_id03_panel_cases",
                       "gen_id03_supersample_cases"],
              ## gen_id04_summary_cases is left out: its cases carry no "method" to keep the verified ones by.
              "ID04": ["gen_id04_about_cases", "gen_id04_dialog_cases", "gen_id04_draw_cases",
                       "gen_id04_filter_cases", "gen_id04_scroll_cases"]}
A5 = 0x20000
## Globals a generator writes past 0x40 bytes (a table, not a record): its extent, so every byte of it
## moves with it (otherwise the bytes past 0x40 stay at 1.0.3's offsets, over Leyte's other globals).
EXTENTS = {"gen_id02_crt_cases": {-0x1608: 0x100}}      # the ctype table (TABLE, 256 bytes)


def _int(s: str) -> int:
    return -int(s[3:], 16) if s.startswith("-") else int(s, 16)


def translator(remap: dict, a5: int = A5, extents: dict | None = None):
    """The remap as an offset translation and an address translation in the A5 world at a5 (each
    generator's own: most put A5 at 0x20000, gen_id02_crt_cases at 0x9000)."""
    globals_ = sorted(k for k in remap if k < 0)

    def offset(off: int) -> int:
        if off in remap:
            return remap[off]
        if off < 0:
            bases = [k for k in globals_ if k <= off < k + (extents or {}).get(k, 0x40)]
            if bases:
                k = bases[-1]
                return off + remap[k] - k
        return off

    def address(addr: int) -> int:
        if a5 - 0x2400 <= addr < a5 + 0x600:
            return a5 + offset(addr - a5)
        return addr
    return offset, address


def main() -> None:
    manifest = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "host_ports.json").read_text())["segments"]
    corr = {(r["segment"], r["name"]): r for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]}
    import m68k_emu
    import segment_stubs
    original_write, original_install, original_call = m68k_emu.Emulator.write, segment_stubs.install, m68k_emu.Emulator.call
    running = {"on": False}

    def call(self, *args, **kwargs):
        # The program's own stores go through write too: no translation while it runs.
        running["on"] = True
        try:
            return original_call(self, *args, **kwargs)
        finally:
            running["on"] = False
    m68k_emu.Emulator.call = call
    OUT.mkdir(parents=True, exist_ok=True)
    with tempfile.TemporaryDirectory() as tmp:
        only = [a for a in sys.argv[1:] if a.startswith("ID")]
        for seg, names in GENERATORS.items():
            if only and seg not in only:
                continue
            verified = set(manifest[seg]["functions"]) if manifest[seg].get("mode") == "partial" else None
            remap = {_int(k): _int(v) for k, v in manifest[seg]["a5"].items()}
            offset, address = translator(remap)
            leyte_bin = Path(tmp) / ("%s.bin" % seg)
            leyte_bin.write_bytes((ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / ("%s.bin" % seg)).read_bytes())
            code_dir = Path(tmp) / "CODE"                     # all of Leyte's CODE, for generators reading a directory
            code_dir.mkdir(exist_ok=True)
            for b in (ROOT / "sources" / "leyte-gulf-1-0" / "CODE").glob("ID*.bin"):
                (code_dir / b.name).write_bytes(b.read_bytes())
            current = {"failed": None}                        # the generator now running: a stop is charged to it
            m68k_emu.Emulator.write = lambda self, addr, size, value, _w=original_write, _a=address: \
                _w(self, addr if running["on"] else _a(addr), size, value)

            def install(emu, a5, logs, *rest, _i=original_install):
                moved = [[e[0], translator(remap)[0](e[1]) if e[0] == "jt" else e[1]] + list(e[2:]) for e in logs]
                return _i(emu, a5, moved, *rest)
            segment_stubs.install = install
            for name in names:
                sys.modules.pop(name, None)
            for name in names:
                mod = importlib.import_module(name)
                offset, address = translator(remap, getattr(mod, "A5", A5), EXTENTS.get(name))
                m68k_emu.Emulator.write = lambda self, addr, size, value, _w=original_write, _a=address: \
                    _w(self, addr if running["on"] else _a(addr), size, value)
                mod.install = install
                skipped = []
                if verified is not None and hasattr(mod, "build"):
                    # The unverified functions' cases are not run: their Leyte code may call slots or
                    # globals the generator's stand-ins do not cover (they are left out anyway).
                    def build(*a, _b=mod.build, **k):
                        cases = _b(*a, **k)
                        skipped.extend(c for c in cases if c["method"] not in verified)
                        return [c for c in cases if c["method"] in verified]
                    mod.build = build
                failed = {}
                current["failed"] = failed
                if verified is not None and hasattr(mod, "run") and not getattr(mod.run, "_leyte_wrapped", False):
                    # A verified function's case whose Leyte run stops on what the stand-ins do not
                    # cover (it reached an unverified routine of the segment: its own slots and globals)
                    # is left out with the error, not given a result: recorded in the fixture's "not_run".
                    shape = str(getattr(mod.run, "__annotations__", {}).get("return", ""))
                    def run(c, *a, _r=mod.run, _shape=shape, **k):
                        try:
                            return _r(c, *a, **k)
                        except Exception as err:          # any stop of the emulated run (capstone on data too)
                            # charged to the generator running now (another may call this module's run),
                            # whose fixture then leaves the case out: the placeholder is never kept
                            current["failed"][c["name"]] = "%s: %s" % (type(err).__name__, err)
                            return ([], 0) if _shape.startswith("tuple") else []
                    run._leyte_wrapped = True
                    mod.run = run
                mod.BINARY = str(leyte_bin)
                if hasattr(mod, "CODE_DIR"):
                    mod.CODE_DIR = str(code_dir)
                if hasattr(mod, "ROUTINES"):
                    moved = manifest[seg].get("relocated", {})       # placements gen_host_ports.py proved
                    mod.ROUTINES = {fn: int(moved[fn], 16) if fn in moved else
                                    int(corr[(seg, fn)]["leyte"], 16) if corr.get((seg, fn), {}).get("leyte") else off
                                    for fn, off in mod.ROUTINES.items()}
                fixture = OUT / ("%s.json" % name.replace("gen_", "").replace("_cases", "_cases"))
                mod.FIXTURE = fixture
                cwd = Path.cwd()
                try:
                    import os
                    os.chdir(ROOT)
                    mod.main()
                finally:
                    os.chdir(cwd)
                doc = json.loads(fixture.read_text())
                dropped = 0
                if verified is not None:
                    kept = [c for c in doc["cases"] if c["method"] in verified and c["name"] not in failed]
                    dropped = len(doc["cases"]) - len(kept) - len(failed) + len(skipped)
                    doc["cases"] = kept
                    doc["not_run"] = dict(sorted(failed.items()))
                doc["common_writes"] = [[address(a), s, v] for a, s, v in doc["common_writes"]]
                for c in doc["cases"]:
                    c["writes"] = [[address(a), s, v] for a, s, v in c["writes"]]
                    if "call_logs" in c:
                        c["call_logs"] = [[e[0], offset(e[1]) if e[0] == "jt" else e[1]] + e[2:] for e in c["call_logs"]]
                if "call_logs" in doc:
                    doc["call_logs"] = [[e[0], offset(e[1]) if e[0] == "jt" else e[1]] + e[2:] for e in doc["call_logs"]]
                fixture.write_text(json.dumps(doc, separators=(",", ":")) + "\n")
                print("%s (Leyte): %d cases (%d of unverified functions left out, %d not run) -> %s" % (
                    name, len(doc["cases"]), dropped, len(doc.get("not_run", {})), fixture.relative_to(ROOT)))
    m68k_emu.Emulator.write, segment_stubs.install, m68k_emu.Emulator.call = original_write, original_install, original_call


if __name__ == "__main__":
    main()
