#!/usr/bin/env python3
"""Emulator cases for the routines the runtime writes for Leyte Gulf (godot/original/leyte/machine/
leyte_runtime.gd), as tools/runtime/gen_runtime_cases.py makes 1.0.3's for unexported.gd: each runs on
Leyte's own CODE bytes (sources/leyte-gulf-1-0/CODE) on the m68k interpreter, with that generator's
runner (run(): the segment at its file offsets, A5 the runtime's, the calls it makes stubbed and
logged), and records the D0 and the memory it leaves; tests/leyte/test_leyte_runtime_routines.gd
replays them on a machine built by the Leyte profile, on temporary storage.

The cases are 1.0.3's with Leyte's operands (read from Leyte's bytes; the jump-table slots and globals
through sources/leyte-gulf-1-0/a5_map.json and the ports' own remapping): CODE 4 0x482
report_panel_store_failure, CODE 5 0x8b2 arm_restore_gate and 0x3ba build_protection_code_strings,
CODE 6 0x858 release_render_buffers, CODE 2 0x434 strncmp, CODE 2 0x9e8 / 0xa88 lock / unlock_session_handles, CODE 4 0x694 run_duty_roster_dialog (the Active Duty Roster), CODE 4 0xc52
run_scenario_selection_dialog (Select a Mission; with 0x566 confirm_end_mission and 0x1b4c, run as
they are).
Synthetic memory only.

    python3 tools/leyte/gen_leyte_runtime_cases.py [--check]
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "runtime"))
import gen_runtime_cases as gr  # noqa: E402

gr.CODE = ROOT / "sources" / "leyte-gulf-1-0" / "CODE"
FIXTURE = ROOT / "godot" / "tests" / "leyte" / "fixtures" / "runtime_cases.json"
g = gr.g


def store_failure_cases() -> list[dict]:
    """CODE 4 0x482 (1.0.3 0x432): with the flag A5 - 0x151e set, JT $4ba then JT $4da (the caution
    alert, the message at *(A5 - 0xf80) and 0), the flag cleared; both calls stubbed alike."""
    return [{"name": f"report_panel_store_failure flag {f}", "routine": "id04_0482", "segment": "ID04", "address": 0x482,
             "args": [], "writes": [[g(-0x151E), 2, f], [g(-0xF80), 4, 0x2B000]], "check": [[g(-0x151E), 2]], "void": True,
             "c_stubs": {"0x4ba": [[], 0], "0x4da": [[4, 2], 0]}, "log_calls": True} for f in (0, 1, 0x8000)]


def gate_cases() -> list[dict]:
    """CODE 5 0x8b2 arm_restore_gate: A5 - 0xbc counts up (a word)."""
    return [{"name": f"arm_restore_gate from {v}", "routine": "id05_08b2", "segment": "ID05", "address": 0x8B2,
             "args": [], "writes": [[g(-0xBC), 2, v]], "check": [[g(-0xBC), 2]], "void": True} for v in (0, 1, 0x7FFF, 0xFFFF)]


## Leyte's: the pattern tables, the edge pool, the row table (its blocks at +4 / +0x1c / +0x20), the
## raster target (1.0.3: -0x1eda, -0x1ede, -0x1cd6, -0x1cd2, -0x1cce).
RENDER_SLOTS = [-0x1F60, -0x1F64, -0x1D5C, -0x1D58, -0x1D54]


def render_buffers_cases() -> list[dict]:
    """CODE 6 0x858 release_render_buffers, some pointers null: each aligned block's allocation
    (*(block - 8)) given to DisposPtr (JT $24a, Pascal, a logging stub; 1.0.3 $23a), each cleared."""
    out = []
    for present in ((1, 1, 1, 1, 1), (0, 1, 0, 1, 0), (1, 0, 1, 0, 1), (0, 0, 0, 0, 0)):
        for rows in ((1, 1, 1), (1, 0, 1)):
            writes = []
            for k, (slot, on) in enumerate(zip(RENDER_SLOTS, present)):
                block = 0x2D000 + 0x100 * k
                writes += [[g(slot), 4, block if on else 0], [block - 8, 4, 0x2E000 + 0x10 * k]]
            for j, (off, on) in enumerate(zip((4, 0x1C, 0x20), rows)):
                block = 0x2D800 + 0x100 * j
                writes += [[0x2D300 + off, 4, block if on else 0], [block - 8, 4, 0x2E800 + 0x10 * j]]
            out.append({"name": f"release_render_buffers {''.join(map(str, present))} rows {''.join(map(str, rows))}",
                        "routine": "id06_0858", "segment": "ID06", "address": 0x858, "args": [], "writes": writes,
                        "check": [[g(slot), 4] for slot in RENDER_SLOTS], "void": True,
                        "pascal": {"0x24a": [4, None]}, "log_calls": True})
    return out


def protection_code_cases() -> list[dict]:
    """CODE 5 0x3ba build_protection_code_strings, as 1.0.3's cases with Leyte's operands: SysEnvirons
    JT $202, HLock / HUnlock / HNoPurge / HPurge glue $2da / $2a2 / $29a, the string glue $33a, strcmp
    $562, the copy $572; the screen bounds at A5 - 0x16fe..- 0x16f8; the equal case's string at
    A5 - 0xda4. The seed A5 - 0x6c, the buffers - 0x68 / - 0x54 / - 0x40 and the volume name buffer
    - 0x2c8 are where 1.0.3 has them."""
    out = []
    code, code_text, other, other_text, pair_h, pair = 0x2C400, 0x2C410, 0x2C440, 0x2C450, 0x2C500, 0x2C600
    for name, first, second, floppy in (("codes differ", 0x1234567, 0x0765432, 1), ("equal elsewhere", 5, 5, 1),
                                        ("equal on the floppy", 5, 5, 0)):
        writes = [[pair_h, 4, pair], [pair, 4, first], [pair + 4, 4, second], [code, 4, 0], [other, 4, 0],
                  [g(-0x6C), 4, 0x13579BDF], [g(-0xDA4), 4, 0x2C700],
                  [g(-0x16FE), 2, 0], [g(-0x16FC), 2, 0], [g(-0x16FA), 2, 480], [g(-0x16F8), 2, 640]]
        writes += [[g(-0x2C8) + 4 * k, 4, 0x4C455900 + k] for k in range(0x42)]
        writes += [[g(b) + 4 * k, 4, 0] for b in (-0x68, -0x54, -0x40) for k in range(3)]
        specs = {"0xa9a0": {"sizes": [2, 4], "log": [0, 1], "result": [4, [pair_h]]},
                 "0xa9a3": {"sizes": [4], "log": [0]},
                 "0xa9aa": {"sizes": [4], "log": [0]}, "0xa9b0": {"sizes": [4], "log": [0]}}
        out.append({"name": f"build_protection_code_strings {name}", "routine": "id05_03ba", "segment": "ID05",
                    "address": 0x3BA, "args": [code, code_text, other, other_text], "void": True, "log_calls": True,
                    "zero_stack": True, "writes": writes, "trap_specs": specs,
                    "check": [[code, 4], [other, 4], [pair, 4], [pair + 4, 4], [g(-0x6C), 4]]
                    + [[g(b) + 4 * k, 4] for b in (-0x68, -0x54, -0x40) for k in range(2)],
                    "pascal_stubs": {"0x202": [6, 2, 0], "0x2da": [8, 2, 0], "0x2a2": [4, 0, 0], "0x29a": [4, 0, 0]},
                    "c_stubs": {"0x33a": [[], 0x2CB00], "0x562": [[4, 4], floppy], "0x572": [[4, 4], 0]}})
    return out


def duty_roster_cases() -> list[dict]:
    """CODE 4 0x694 run_duty_roster_dialog, as 1.0.3's cases with Leyte's operands: the roster
    **(A5 - 0x1426), the settings **(A5 - 0x142a) and the current pilot A5 - 0x1422, the rank names
    A5 - 0x103c, the tallies' weights A5 - 0xf9c; JT $4aa (centre), $512 (the score text), $33a / $342
    (C strings), $54a strncmp (answered by the case), $552 strncpy, HLock / HUnlock $28a / $292 and the
    name order $32a (never asked: no two pilots tie). The original's own rank / prune / score routines
    run on the case's roster; the Dialog Manager traps are stub layouts (ModalDialog's item hits from
    the case). Item 4 (the record) is not hit: its routine (0x3638) is not ported for Leyte."""
    out = []
    dialog = gr.DIALOG
    roster_h, records, settings_h, settings, ranks = 0x2C200, 0x2D000, 0x2C500, 0x2C600, 0x2C300
    for name, hits, differ, retired, current, edit in (
            ("cancel", [3], 0, (2, 7), 3, 15),
            ("OK unchanged", [1], 0, (0, 5, 11), 4, 16),
            ("OK renamed, ten shown", [1], 1, (), 9, 18),
            ("OK after other items", [2, 39, 1], 0, (1,), 0, 15)):
        writes = [[g(-0x1426), 4, roster_h], [roster_h, 4, records], [g(-0x142A), 4, settings_h],
                  [settings_h, 4, settings], [settings + 0x20, 2, current], [g(-0x1422), 2, 0],
                  [dialog + 0xA4, 2, edit]]
        writes += [[g(-0x103C) + 4 * r, 4, ranks + 0x10 * r] for r in range(8)]
        writes += [[g(-0xF9C) + 2 * k, 2, 5 * k + 3] for k in range(12)]
        for p in range(20):
            rec = records + 64 * p
            writes += [[rec, 4, 0x50000000 | (0x41 + p) << 16], [rec + 0x20, 2, 1 if p in retired else 0],
                       [rec + 0x22, 2, p % 6]]
            writes += [[rec + 0x24 + 2 * k, 2, (p * 37 + k * 11) % 23] for k in range(14)]
        specs = {"0xa97c": {"sizes": [4, 4, 2], "log": [2], "result": [4, [dialog]]},
                 "0xa915": {"sizes": [4], "log": [0]},
                 "0xa991": {"sizes": [4, 4], "log": [1], "outs": {"0": [2, hits]}},
                 "0xa983": {"sizes": [4], "log": [0]},
                 "0xa98d": dict(gr.GET_DITEM, outs={"2": [2, [0x0010]], "1": [4, [0x3000 + 4 * k for k in range(80)]],
                                                    "0": [8, [[0x00100010, 0x00200020]]]}),
                 "0xa98e": gr.SET_DITEM,
                 "0xa98f": {"sizes": [4, 4], "log": [1]},
                 "0xa990": {"sizes": [4, 4], "log": [1]},
                 "0xa97e": {"sizes": [2, 2, 2, 4], "log": [0, 1, 2, 3]},
                 "0xa9aa": {"sizes": [4], "log": [0]}, "0xa9b0": {"sizes": [4], "log": [0]}}
        out.append({"name": f"run_duty_roster_dialog {name}", "routine": "id04_0694", "segment": "ID04",
                    "address": 0x694, "args": [], "log_calls": True, "writes": writes, "id01": True,
                    "check": [[g(-0x1422), 2]] + [[records + 64 * p + k, 4] for p in range(20) for k in (0, 0x20, 0x24)],
                    "c_stubs": {"0x4aa": [[4], 0], "0x512": [[4], 0x2CA00], "0x33a": [[], 0], "0x342": [[], 0],
                                "0x54a": [[], differ], "0x552": [[], 0]},
                    "pascal": {"0x28a": [4, None], "0x292": [4, None], "0x32a": [8, None]},
                    "trap_specs": specs})
    return out


def scenario_dialog_cases() -> list[dict]:
    """CODE 4 0xc52 run_scenario_selection_dialog, after 1.0.3's cases with Leyte's operands: settings
    **(A5 - 0x142a), fuel A5 - 0x1350 into - 0x2310, the pilot - 0x1422, the changed flag - 0x1408, the
    game state - 0x1376 and - 0x1542; the radios: 8 missions at A5 - 0x230a, 3 bombs at - 0x22fa and
    Leyte's 2 at - 0x22f4 (from A5 - 0x134a), and its checkbox 25 (from A5 - 0x134c). JT $4aa, $4fa,
    $51a and $4c2 are stubs; GetCtlValue answers from the case (the get_radio_group_selection calls,
    then the checkbox). confirm_end_mission (0x566) and its kill report (0x1b4c) run as they are: the
    last case is a running mission with a kill, whose alert (CautionAlert 0x103) answers 1, then 2."""
    out = []
    dialog = gr.DIALOG
    settings_h, settings = 0x2C500, 0x2C600
    missions, bombs, second = g(-0x230A), g(-0x22FA), g(-0x22F4)
    for name, hits, on, scenario, fuel, pilot, flags, state, alert in (
            ("cancel", [3], [0], 3, 60, 1, (0, 0), 1, [2]),
            ("OK unset", [1], [0, 0, 1, 1, 0, 0], 0, 0, 1, (0, 0), 1, [2]),
            ("change all, box ticked", [11, 7, 0x18, 0x19, 1], [0, 1, 0, 0, 1, 1, 0, 0, 0, 0, 1], 4, 75, 2, (0, 0), 1, [2]),
            ("no radio on, set flags", [9, 0x19, 1], [0] * 14, 2, 40, 1, (1, 1), 0, [2]),
            ("running mission: alert 1 then 2", [1, 1], [1, 0, 0, 1, 0, 1, 0, 0, 0, 0], 5, 50, 3, (0, 1), 3, [1, 2])):
        writes = [[g(-0x142A), 4, settings_h], [settings_h, 4, settings], [settings + 0x1E, 2, scenario],
                  [settings + 0x20, 2, 1], [settings + 0x22, 2, 60], [settings + 0x24, 2, 2], [settings + 0x26, 2, 0],
                  [settings + 0x28, 2, 0], [g(-0x1350), 2, fuel], [g(-0x1422), 2, pilot], [g(-0x1408), 2, 0],
                  [g(-0x1376), 2, state], [g(-0x1542), 2, 0], [g(-0x2310), 2, 0], [g(-0x134A), 2, flags[0]],
                  [g(-0x134C), 2, flags[1]], [g(-0x153E), 2, 1], [g(-0x1334), 2, 0]]
        writes += [[g(-0x1348) + 2 * k, 2, 1 if (state == 3 and k == 4) else 0] for k in range(10)]
        writes += [[missions + 2 * k, 2, 9 + k] for k in range(8)] + [[bombs + 2 * k, 2, 6 + k] for k in range(3)]
        writes += [[second + 2 * k, 2, 0x17 + k] for k in range(2)]
        specs = {"0xa97c": {"sizes": [4, 4, 2], "log": [2], "result": [4, [dialog]]},
                 "0xa915": {"sizes": [4], "log": [0]},
                 "0xa991": {"sizes": [4, 4], "log": [1], "outs": {"0": [2, hits]}},
                 "0xa983": {"sizes": [4], "log": [0]},
                 "0xa98d": dict(gr.GET_DITEM, outs={"2": [2, [0x0004]], "1": [4, [0x1000, 0x2000, 0x3000]],
                                                    "0": [8, [[0x00100010, 0x00200020]]]}),
                 "0xa98e": gr.SET_DITEM, "0xa963": gr.SET_CTL_VALUE,
                 "0xa960": {"sizes": [4], "log": [0], "result": [2, on]},
                 "0xa988": {"sizes": [4, 2], "log": [1], "result": [2, alert]}}
        out.append({"name": f"run_scenario_selection_dialog {name}", "routine": "id04_0c52", "segment": "ID04",
                    "address": 0xC52, "args": [], "d0_size": 2, "log_calls": True, "writes": writes,
                    "check": [[settings + 0x1E, 2], [settings + 0x20, 2], [settings + 0x22, 2], [settings + 0x24, 2],
                              [settings + 0x26, 2], [settings + 0x28, 2], [g(-0x1408), 2], [g(-0x1376), 2],
                              [g(-0x1350), 2], [g(-0x2310), 2], [g(-0x1542), 2]],
                    "c_stubs": {"0x4aa": [[4], 0], "0x4fa": [[], 0], "0x51a": [[], 0], "0x4c2": [[2], 0]},
                    "trap_specs": specs})
    return out


def session_lock_cases() -> list[dict]:
    """CODE 2 0x9e8 / 0xa88 lock / unlock_session_handles, as 1.0.3's cases with Leyte's operands: the
    counter A5 - 0x1572, the four handles and their kept master pointers, the 24-bit flag A5 - 0x13c6;
    HLock / HUnlock (JT $28a / $292, Pascal) logging stubs."""
    handles = [-0x1270, -0x1256, -0x124E, -0x132A]
    kept = [-0x126C, -0x1252, -0x124A, -0x1326]
    out = []
    for depth in (0, 1, 2):
        for flag in (0, 1):
            writes = [[g(-0x1572), 2, depth], [g(-0x13C6), 2, flag]]
            for k, h in enumerate(handles):
                master = 0x30000 + 0x10 * k
                writes += [[g(h), 4, master], [master, 4, 0xFF000000 | (0x123450 + 0x100 * k)], [g(kept[k]), 4, 0xDEADBEEF]]
            out.append({"name": f"lock_session_handles depth {depth} flag {flag}", "routine": "id02_09e8", "segment": "ID02",
                        "address": 0x9E8, "args": [], "writes": writes, "check": [[g(-0x1572), 2]] + [[g(a), 4] for a in kept],
                        "void": True, "pascal": {"0x28a": [4, None], "0x292": [4, None]}, "log_calls": True})
            if depth > 0 and flag == 0:
                out.append({"name": f"unlock_session_handles depth {depth}", "routine": "id02_0a88", "segment": "ID02",
                            "address": 0xA88, "args": [], "writes": writes, "check": [[g(-0x1572), 2]], "void": True,
                            "pascal": {"0x28a": [4, None], "0x292": [4, None]}, "log_calls": True})
    return out


def build() -> dict:
    # CODE 2 0x434 strncmp: Leyte's bytes are 1.0.3's at the same offset, so 1.0.3's cases (their strings
    # in synthetic memory) apply as they are, run here on Leyte's CODE 2.
    cases = (gr.strncmp_cases() + store_failure_cases() + gate_cases() + render_buffers_cases() + protection_code_cases()
             + duty_roster_cases() + scenario_dialog_cases() + session_lock_cases())
    for c in cases:
        gr.run(c)
    return {"source": "tools/leyte/gen_leyte_runtime_cases.py", "cases": cases}


def main() -> int:
    text = json.dumps(build(), indent=1) + "\n"
    if "--check" in sys.argv:
        ok = FIXTURE.exists() and FIXTURE.read_text(encoding="utf-8") == text
        print(("PASS" if ok else "FAIL") + " Leyte runtime routine cases: %s" % FIXTURE.relative_to(ROOT))
        return 0 if ok else 1
    FIXTURE.parent.mkdir(parents=True, exist_ok=True)
    FIXTURE.write_text(text, encoding="utf-8")
    print("wrote %s: %d cases" % (FIXTURE.relative_to(ROOT), text.count('"routine"')))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
