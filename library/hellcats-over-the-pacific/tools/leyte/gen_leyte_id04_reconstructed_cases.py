#!/usr/bin/env python3
"""Emulator cases for Leyte Gulf's reconstructed CODE 4 functions (godot/original/leyte/port/
ID04_reconstructed.gd), run on Leyte's own ID04.bin: godot/tests/leyte/port/fixtures/
id04_reconstructed_cases.json, replayed by tests/leyte/port/test_leyte_id04_reconstructed.gd
(helpers/segment_cases.gd). The runner is the CODE 2 generator's (gen_leyte_id02_reconstructed_cases.py:
the segment at CODE, A5, CODE 1's switch helpers wired, the calls leaving the routine as logging
stand-ins), with this segment's binary, routines and stand-ins. Synthetic memory only.

The mission's end: conclude_mission (JT $10a) with the summary dialog stood in; the summary dialog with its
helpers, the Toolbox and ModalDialog stood in (ModalDialog writes a fixed item); the list scroll bars.

    python3 tools/leyte/gen_leyte_id04_reconstructed_cases.py [--check]
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "leyte"))
import gen_leyte_id02_reconstructed_cases as base  # noqa: E402
import gen_leyte_id03_reconstructed_cases as id03  # noqa: E402  (a5_world: the A5 world as the runtime builds it)

FIXTURE = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "id04_reconstructed_cases.json"
A5 = base.A5
ROUTINES = {"conclude_mission": 0x3A2E, "show_mission_summary_dialog": 0x3638, "attach_list_scroll_bar": 0x27E2,
            "dispose_list_scroll_bar": 0x28CC, "advance_mission_summary_text": 0x29E8,
            "summary_dialog_filter_proc": 0x28F0, "leyte_list_set_scroll_max": 0x2436, "leyte_list_scroll": 0x24B8,
            "leyte_list_scroll_to_end": 0x262E, "leyte_list_redraw": 0x23B2, "leyte_fill_medals_list": 0x1FF8,
            "leyte_fill_kill_markers_list": 0x21C6, "leyte_draw_target_row": 0x1E28,
            "leyte_target_table_item_proc": 0x1FB8, "leyte_medals_item_proc": 0x23FE,
            "leyte_kill_markers_item_proc": 0x241A, "leyte_rank_insignia_item_proc": 0x1D9E}
FILL = 0x17500                                  # a list's fill procedure (its record +4), stood in
STUBS = [["local", 0x39A8, [], False, 0], ["local", 0x39FC, [], False, 0], ["local", 0x3638, [2, 2, 4], False, 2],
         ["trap", 0xA9AA, [4], True, 0], ["trap", 0xA999, [2], True, 0],
         ["trap", 0xA97C, [4, 4, 2], True, 4], ["jt", 0x4AA, [4], False, 0],
         ["local", 0x13C, [4, 2, 4], False, 0], ["local", 0x6, [4, 2], False, 0], ["local", 0x1A52, [4, 2], False, 0],
         ["local", 0x27E2, [4, 2, 4], False, 0], ["local", 0x114, [4, 2, 4], False, 0], ["local", 0x54, [4, 2], False, 0],
         ["local", 0x78, [4, 2], False, 0], ["local", 0x208, [4, 2, 2], False, 0], ["local", 0x35D8, [4, 2], False, 0],
         ["local", 0x29E8, [4, 2, 2], False, 2], ["local", 0x1B8E, [4, 2], False, 0], ["local", 0x566, [], False, 2],
         ["local", 0x1B08, [4, 2], False, 0], ["local", 0x28CC, [4, 2], False, 0], ["jt", 0x532, [4, 4, 4], False, 0],
         ["trap", 0xA915, [4], True, 0], ["trap", 0xA975, [], True, 4], ["trap", 0xA991, [4, 4], True, 0],
         ["trap", 0xA983, [4], True, 0], ["trap", 0xA916, [4], True, 0],
         ["trap", 0xA98D, [4, 4, 4, 2, 4], True, 0], ["trap", 0xA8A9, [2, 2, 4], True, 0], ["trap", 0xA8A8, [2, 2, 4], True, 0],
         ["trap", 0xA954, [4, 2, 2, 2, 2, 1, 4, 4, 4], True, 4, {7: 2}], ["trap", 0xA955, [4], True, 0],
         ["local", 0x1B1C, [4, 2, 4], False, 0], ["local", 0x1B4C, [], False, 2], ["local", 0x5D8, [2], False, 4],
         ["local", 0x1E28, [4, 2, 2], False, 0], ["local", 0x23B2, [4, 2], False, 0], ["local", 0x262E, [2], False, 0],
         ["jt", 0x512, [4, 4], False, 4], ["jt", 0x33A, [4], False, 0], ["jt", 0x55A, [4, 4], False, 0],
         ["jt", 0x572, [4, 4], False, 0], ["jt", 0x342, [4], False, 0], ["trap", 0xA873, [4], True, 0],
         ["trap", 0xA8A3, [4], True, 0], ["trap", 0xA928, [4], True, 0],
         ["local", 0x19BE, [4, 4], False, 2], ["local", 0x2732, [4, 4], False, 2], ["jt", 0x4A2, [4], False, 0],
         ["jt", 0x522, [4], False, 2],
         # the summary's item drawers and lists (Leyte only)
         ["trap", 0xA960, [4], True, 2], ["trap", 0xA962, [4], True, 2], ["trap", 0xA963, [2, 4], True, 0],
         ["trap", 0xA965, [2, 4], True, 0], ["trap", 0xA87B, [4], True, 0], ["trap", 0xAA1E, [2], True, 4],
         ["trap", 0xAA1F, [4, 4], True, 0], ["trap", 0xAA25, [4], True, 0], ["trap", 0xA8A6, [4, 4], True, 1],
         ["trap", 0xA8AA, [4, 4, 4], True, 1], ["trap", 0xA8EC, [4, 2, 4, 4, 4, 4], True, 0],
         ["trap", 0xA887, [2], True, 0], ["trap", 0xA88B, [4], True, 0], ["trap", 0xA893, [2, 2], True, 0],
         ["trap", 0xA884, [4], True, 0], ["trap", 0xA88C, [4], True, 2],
         ["local", 0x170, [4, 2], False, 0], ["local", 0x2436, [2], False, 0], ["local", 0x24B8, [2], False, 0],
         ["addr", FILL, [4, 4], False, 0]]

ROSTER_H, ROSTER, SCEN_H, SCEN = 0x1C000, 0x1C010, 0x1C800, 0x1C810
DIALOG, WINDOW = 0x1D000, 0x1D800


def world(status=0, pilot=2, first=0, sound=1, record_status=None) -> list:
    """The roster (handle A5 - 0x1426, 64-byte records, pilot A5 - 0x1422), the scenario record (A5 - 0x142a, word
    +0x20 its pilot), the status / flag words, the tallies and kill counts."""
    w = [[A5 - 0x1426, 4, ROSTER_H], [ROSTER_H, 4, ROSTER], [A5 - 0x1422, 2, pilot], [A5 - 0x142A, 4, SCEN_H],
         [SCEN_H, 4, SCEN], [SCEN + 0x20, 2, 3], [A5 - 0x132E, 2, status], [A5 - 0x1540, 2, first], [A5 - 0x1542, 2, 0],
         [A5 - 0x136C, 2, sound], [A5 - 0x1042, 2, 7], [A5 - 0x2322, 2, 9], [A5 - 0x1548, 4, WINDOW], [A5 - 0x1376, 2, 4],
         [A5 - 0x1544, 2, 1], [A5 - 0x153E, 2, 0]]
    for p in range(6):
        rec = ROSTER + 0x40 * p
        w += [[rec + 0x20, 2, status if record_status is None else record_status], [rec + 0x22, 2, p]]
        w += [[rec + 0x28 + 2 * k, 2, (p * 3 + k) & 0xF] for k in range(12)]
    w += [[A5 - 0x1420 + 2 * k, 2, (k * 5 + 1) & 0xF] for k in range(12)]
    w += [[A5 - 0x1348 + 2 * k, 2, (k + 2) & 7] for k in range(12)]
    return w


def conclude_cases() -> list:
    out = []
    for name, which, kw, answer in (("a crash, first death, sound on", 1, dict(status=1), 3),
                                    ("a crash, first death, sound off", 1, dict(status=1, sound=0), 1),
                                    ("a crash, already dead", 1, dict(status=1, first=1), 1),
                                    ("result 3", 3, dict(status=1), 3), ("a status 2, result 6", 6, dict(status=2), 1),
                                    ("status 0, result 7", 7, dict(status=0), 1),
                                    ("the roster's status already the same", 8, dict(status=2, record_status=2), 1),
                                    ("the roster's status differing", 9, dict(status=1, record_status=0), 3)):
        args = [which, 0x1E000]
        out.append({"name": "conclude: " + name, "method": "conclude_mission", "args": args,
                    "stack": [(2, which), (4, args[1])], "a7": base.STACK_TOP - 6 - 4, "writes": world(**kw),
                    "results": {("local", 0x3638): [answer]}})
    return out


def summary_cases() -> list:
    out = []
    for name, pilot, phase, kw, item, confirm, first_step in (
            ("the mission, item 3", 0xFFFF, 1, dict(status=0), 3, [2], 0),
            ("the mission, item 1 confirmed", 0xFFFF, 1, dict(status=0), 1, [2], 1),
            ("the mission, item 1 asked twice", 0xFFFF, 0x0106, dict(status=2), 1, [0, 2], 0),
            ("the mission, phase 3", 0xFFFF, 3, dict(status=1), 3, [2], 1),
            ("the mission, flagged, phase 7", 0xFFFF, 7, dict(status=1), 3, [2], 0),
            ("a pilot, item 1", 2, 1, dict(status=0), 1, [2], 0),
            ("a pilot, item 3, phase 3", 4, 3, dict(status=1), 3, [2], 1)):
        w = world(**kw)
        if name.endswith("flagged, phase 7"):
            w.append([A5 - 0x1542, 2, 1])
        args = [pilot, phase, 0x1E100]
        out.append({"name": "summary: " + name, "method": "show_mission_summary_dialog", "args": args,
                    "stack": [(2, pilot), (2, phase), (4, args[2])], "a7": base.STACK_TOP - 8 - 4, "writes": w,
                    "results": {("trap", 0xA97C): [DIALOG], ("trap", 0xA975): [0x12345],
                                ("derefs", "trap", 0xA991): {0: ["out", item, 2]},
                                ("local", 0x566): confirm, ("local", 0x29E8): [first_step]}})
    return out


def scroll_cases() -> list:
    out = []
    box = ["out", [0x00100020, 0x00800120]]          # GetDItem's box: top 0x10, left 0x20, bottom 0x80, right 0x120
    for item in (6, 7):
        args = [DIALOG, item, 0x1E200 + item]
        out.append({"name": "list scroll bar: item %d attached" % item, "method": "attach_list_scroll_bar", "args": args,
                    "stack": [(4, DIALOG), (2, item), (4, args[2])], "a7": base.STACK_TOP - 10 - 4, "writes": [],
                    "results": {("derefs", "trap", 0xA98D): {0: box}, ("trap", 0xA954): [0x1E400 + item]}})
        out.append({"name": "list scroll bar: item %d disposed" % item, "method": "dispose_list_scroll_bar", "args": [DIALOG, item],
                    "stack": [(4, DIALOG), (2, item)], "a7": base.STACK_TOP - 6 - 4,
                    "writes": [[A5 - 0x22D6 + 0x1A * (item - 6), 4, 0x1E400 + item]], "results": {}})
    return out


def text_cases() -> list:
    """advance_mission_summary_text through its stages on the A5 world as the runtime builds it (the message
    pointers are Leyte's own): stage 0 by the phase's low byte, high bits, status, promotion counter and kill
    report word; the message, medal, kill and rank stages."""
    TALLY = 0x1D400
    common = id03.a5_world() + [[A5 - 0x22DA, 4, TALLY]] + [[TALLY + 2 * k, 2, k] for k in range(12)]
    for k in range(10):
        common.append([A5 - 0x1014 + 4 * k, 4, 0x40 * k * k])
    out = []

    def case(name, stage, phase, kw=None, extra=None, results=None, pilot=0xFFFF):
        w = common + world(**(kw or {})) + [[A5 - 0x22A2, 2, stage], [A5 - 0x22DC, 2, pilot]] + (extra or [])
        out.append({"name": "summary text: " + name, "method": "advance_mission_summary_text", "args": [DIALOG, 8, phase],
                    "stack": [(4, DIALOG), (2, 8), (2, phase)], "a7": base.STACK_TOP - 8 - 4, "writes": w,
                    "results": results or {}})
    for t in range(4):
        case("collect, a crash, tick %d" % t, 0, 1, dict(status=1), results={("trap", 0xA975): [4 * t + 2]})
    case("collect, a crash replayed", 0, 1, dict(status=1), [[A5 - 0x132C, 2, 1]])
    case("collect, phase 1 status 0", 0, 1, dict(status=0))
    for low in (6, 7, 8):
        for high in (0, 1, 2):
            for st in (0, 1, 3, 4, 5):
                case("collect, phase %d high %d status %d" % (low, high, st), 0, (high << 8) | low, dict(status=st))
    for high in (0, 1):
        for st in (0, 3):
            case("collect, phase 9 high %d status %d" % (high, st), 0, (high << 8) | 9, dict(status=st))
    case("collect, phase 10", 0, 10, dict(status=0))
    case("collect, phase 2 (none)", 0, 2, dict(status=0))
    for report in (0, 1, 2):
        case("collect, phase 3, report %d" % report, 0, 3, dict(status=0), results={("local", 0x1B4C): [report]})
    case("collect, phase 3, status 1", 0, 3, dict(status=1), results={("local", 0x1B4C): [1]})
    for state in (2, 3, 5, 6, 7, 8):
        case("collect, phase 3, unreported, state %d" % state, 0, 3, dict(status=0),
             [[A5 - 0x1334, 2, 0xFFFF], [A5 - 0x1376, 2, state]])
    case("collect, phase 3, report word -2", 0, 3, dict(status=0), [[A5 - 0x1334, 2, 0xFFFE]])
    case("collect, phase 3, early death", 0, 3, dict(status=0), [[A5 - 0x1334, 2, 0xFFFF], [A5 - 0x153E, 2, 1]])
    for count, record in ((1, 0), (2, 1), (3, 2), (5, 0)):
        case("collect, promotion %d, record %d" % (count, record), 0, 6, dict(status=0),
             [[A5 - 0x1330, 2, count], [ROSTER + 0x40 * 2 + 0x24, 2, record]])
    for word in (3, 0, 0xFFFF):
        case("collect, phase 6, kill word %x" % word, 0, 6, dict(status=0), [[A5 - 0x1334, 2, word]])
    case("messages, two left", 1, 6, None, [[A5 - 0x21F6, 2, 2], [A5 - 0x224A, 4, A5 - 0x2246], [A5 - 0x2246, 4, 0x1E500]])
    case("messages, done, the mission", 1, 6, None, [[A5 - 0x21F6, 2, 0]])
    case("messages, done, a pilot", 1, 6, None, [[A5 - 0x21F6, 2, 0]], pilot=2)
    kills = [[A5 - 0x1348 + 2 * k, 2, 0] for k in range(12)]
    for state in (3, 5, 9, 10):
        case("medal, state %d, kills" % state, 2, 1, dict(status=0), [[A5 - 0x1376, 2, state]])
    case("medal skipped, status 1", 2, 1, dict(status=1))
    case("no kills, nothing to report", 2, 6, dict(status=0), kills, {("local", 0x1B4C): [0]})
    case("no kills, a report, ranked up", 2, 6, dict(status=0), kills, {("local", 0x1B4C): [2], ("local", 0x5D8): [0x2000]})
    case("no kills, a report, not ranked", 2, 6, dict(status=0), kills, {("local", 0x1B4C): [1], ("local", 0x5D8): [0x10]})
    for start, counts in ((0, None), (3, None), (4, [[A5 - 0x1348 + 2 * k, 2, 1 if k == 7 else 0] for k in range(12)])):
        case("kills from %d" % start, 3, 6, dict(status=0), [[A5 - 0x21F6, 2, start]] + (counts or []),
             {("jt", 0x512): [0x1E600]})
    case("kills done, ranked up", 3, 6, dict(status=1), [[A5 - 0x21F6, 2, 0xA]], {("local", 0x5D8): [0x2000]})
    case("kills done, not ranked", 3, 6, dict(status=0), [[A5 - 0x21F6, 2, 0xA]], {("local", 0x5D8): [0x10]})
    case("rank, status 1, ranked", 4, 6, dict(status=1), None, {("local", 0x5D8): [0x2000]})
    case("rank line, messages left", 5, 6, dict(status=0), [[A5 - 0x21F6, 2, 1], [A5 - 0x224A, 4, A5 - 0x2246], [A5 - 0x2246, 4, 0x1E700]])
    box = ["out", [0x00100020, 0x00800120]]
    for st in (0, 1):
        case("rank line, status %d" % st, 5, 6, dict(status=st), [[A5 - 0x21F6, 2, 0]], {("derefs", "trap", 0xA98D): {0: box}})
    case("stage 7", 7, 6, dict(status=0))
    return out


def filter_cases() -> list:
    """summary_dialog_filter_proc (Pascal: dialog, event, VAR item; the Boolean in the result word above them): keys
    (Return, Enter, others; the text done or not), clicks (on the text's bar, a list's bar, neither), an update, a null
    event with the time not yet up and up (the text step ending or not)."""
    EVENT, ITEM = 0x1D600, 0x1D700
    out = []

    def case(name, what, message=0, extra=None, results=None):
        w = [[EVENT, 2, what], [EVENT + 2, 4, message], [ITEM, 2, 0], [A5 - 0x2322, 2, 2], [A5 - 0x2318, 4, 0],
             [A5 - 0x2314, 4, 0x1000], [A5 - 0x22A0, 2, 6]] + (extra or [])
        out.append({"name": "summary filter: " + name, "method": "summary_dialog_filter_proc", "args": [DIALOG, EVENT, ITEM],
                    "stack": [(4, ITEM), (4, EVENT), (4, DIALOG), (2, 0)], "a7": base.STACK_TOP - 14 - 4, "writes": w,
                    "results": results or {}})
    for name, c, waiting in (("Return, text done", 0xD, 0), ("Enter, text done", 3, 0), ("Return, text running", 0xD, 0x2D),
                             ("a letter", 0x41, 0)):
        case("key " + name, 3, 0x2400 | c, [[A5 - 0x2318, 4, waiting]], {("jt", 0x522): [0]})
    case("click on the text's bar", 1, 0, None, {("local", 0x19BE): [1]})
    case("click on a list's bar", 1, 0, None, {("local", 0x19BE): [0], ("local", 0x2732): [7]})
    case("click elsewhere", 1, 0, None, {("local", 0x19BE): [0], ("local", 0x2732): [0], ("jt", 0x522): [0]})
    case("update", 6, 0x1D000, None, {("jt", 0x522): [0]})
    case("idle, waiting", 0, 0, [[A5 - 0x2318, 4, 0x2D]], {("jt", 0x522): [3], ("trap", 0xA975): [0x1010]})
    case("idle, a step", 0, 0, [[A5 - 0x2318, 4, 1]], {("jt", 0x522): [5], ("trap", 0xA975): [0x1010, 0x1020],
                                                       ("local", 0x29E8): [0]})
    case("idle, the last step", 0, 0, [[A5 - 0x2318, 4, 1]], {("jt", 0x522): [5], ("trap", 0xA975): [0x1010, 0x1020],
                                                              ("local", 0x29E8): [1]})
    return out


PORT, PORT2, RECT, ICON_H, ICON = 0x1DA00, 0x1DB00, 0x1DC00, 0x1DD00, 0x1DD10
TALLIES, NAMES, SEP, LINE_OUT = 0x1DE00, 0x1DE40, 0x1DEC0, 0x1DF00


def drawers_world(medals=0x8F05, view=(0x10, 0x20, 0x50, 0x120), content=(-0x10, 0x20, 0x80, 0x120)) -> list:
    """The pilot's world, thePort (A5 - 0x168a: its portBits +2, portRect +0x10, txFont +0x44), the two list records
    (A5 - 0x22d6: control, the fill procedure FILL, view and content rects), a colour icon (handle -> its bounds +6
    0..0x20 square), the targets' tallies, names, values and separator."""
    w = world() + [[A5 - 0x168A, 4, PORT], [PORT + 0x10, 4, 0x00000000], [PORT + 0x14, 4, 0x01E00280], [PORT + 0x44, 2, 3],
                   [PORT + 2, 4, 0x40000], [ROSTER + 0x40 * 2 + 0x26, 2, medals], [ICON_H, 4, ICON],
                   [ICON + 6, 4, 0x00000000], [ICON + 10, 4, 0x00200018]]
    for i in range(2):
        rec = A5 - 0x22D6 + 0x1A * i
        w += [[rec, 4, 0x1E400 + i], [rec + 4, 4, FILL], [rec + 8, 2, view[0]], [rec + 10, 2, view[1]],
              [rec + 12, 2, view[2]], [rec + 14, 2, view[3]], [rec + 0x10, 2, content[0] & 0xFFFF],
              [rec + 0x12, 2, content[1]], [rec + 0x14, 2, content[2]], [rec + 0x16, 2, content[3]]]
    w += [[A5 - 0x22DA, 4, TALLIES]] + [[TALLIES + 2 * k, 2, (0, 3, 1, 0, 7, 2, 0, 1, 0xFFFF, 4)[k]] for k in range(10)]
    w += [[A5 - 0xFC4 + 4 * k, 4, NAMES + 8 * k] for k in range(10)] + [[A5 - 0xF9C + 2 * k, 2, (k + 1) * 50] for k in range(10)]
    w += [[A5 - 0x21BE, 4, 0x20782000]]
    return w


def drawer_cases() -> list:
    """The summary's lists (scroll maximum, scrolling by a bar's value up / down / not at all, to the end, redraw), the
    medals and kill-marker fills (the content rect changed or not), a targets-destroyed row (a tally, none, a negative
    one), and the four user item procedures (Pascal: dialog, item)."""
    out = []
    box = ["out", [0x00100020, 0x00C00120]]
    font = ["out", [0x000A0003, 0x000C0002]]

    # the rects the drawers hand on are logged by content (their frames are outside the compared memory)
    rects = {("derefs", "trap", 0xA8A3): {0: 2}, ("derefs", "trap", 0xA87B): {0: 2}, ("derefs", "trap", 0xA8A9): {2: 2},
             ("derefs", "trap", 0xA8A8): {2: 2}, ("derefs", "trap", 0xA8AA): {0: 2, 1: 2, 2: 2},
             ("derefs", "trap", 0xA8EC): {2: 2, 3: 2}, ("derefs", "trap", 0xAA1F): {1: 2}, ("derefs", "addr", FILL): {1: 2}}

    def case(name, method, args, stack, writes=None, results=None):
        out.append({"name": "summary drawers: " + name, "method": method, "args": args, "stack": stack,
                    "a7": base.STACK_TOP - sum(s for s, _ in stack) - 4, "writes": drawers_world() + (writes or []),
                    "results": {**rects, **(results or {})}})
    tall = [[A5 - 0x22D6 + 0x1A + 0x14, 2, 0x30]]                     # list 1's content shorter than its view
    for i in (0, 1):
        case("scroll maximum, list %d" % i, "leyte_list_set_scroll_max", [i], [(2, i)], tall)
    for name, value in (("unmoved", 0x20), ("down", 0x10), ("up", 0x30), ("far up", 0x7F)):
        case("list scrolled, " + name, "leyte_list_scroll", [0], [(2, 0)], None,
             {("trap", 0xA960): [value], ("trap", 0xA8AA): [1, 1, 0]})
    for item, (value, top) in ((6, (5, 5)), (7, (5, 9)), (6, (0, 0x40))):
        case("list %d to its end (%d of %d)" % (item, value, top), "leyte_list_scroll_to_end", [item], [(2, item)], None,
             {("trap", 0xA960): [value], ("trap", 0xA962): [top, top]})
    for item in (6, 7):
        case("list item %d redrawn" % item, "leyte_list_redraw", [DIALOG, item], [(4, DIALOG), (2, item)])
    icons = {("trap", 0xAA1E): [ICON_H] * 16}
    for medals in (0x8F05, 0x0000, 0xFFFF):
        for same in (1, 0):
            case("medals 0x%04x, content %s" % (medals, "kept" if same else "changed"), "leyte_fill_medals_list", [PORT2, RECT],
                 [(4, PORT2), (4, RECT)], [[ROSTER + 0x40 * 2 + 0x26, 2, medals]], {**icons, ("trap", 0xA8A6): [same]})
    for tallies in ((6, 7, 8), (0, 0, 0), (2, 0, 3)):
        for same in (1, 0):
            w = [[ROSTER + 0x40 * 2 + 0x28 + 2 * k, 2, tallies[k]] for k in range(3)]
            case("kill markers %s, content %s" % (tallies, "kept" if same else "changed"), "leyte_fill_kill_markers_list",
                 [PORT2, RECT], [(4, PORT2), (4, RECT)], w,
                 {("trap", 0xAA1E): [ICON_H, ICON_H + 0x100, ICON_H + 0x200], ("trap", 0xA8A6): [same]})
    for kind in (0, 1, 4, 8, 9):
        case("target row %d" % kind, "leyte_draw_target_row", [DIALOG, 5, kind], [(4, DIALOG), (2, 5), (2, kind)], None,
             {("derefs", "trap", 0xA98D): {0: box}, ("derefs", "trap", 0xA88B): {0: font},
              ("jt", 0x512): [LINE_OUT, LINE_OUT + 0x40], ("jt", 0x33A): [LINE_OUT], ("trap", 0xA88C): [0x2C]})
    case("target table", "leyte_target_table_item_proc", [DIALOG, 5], [(2, 5), (4, DIALOG)])
    case("medals item", "leyte_medals_item_proc", [DIALOG, 6], [(2, 6), (4, DIALOG)])
    case("kill markers item", "leyte_kill_markers_item_proc", [DIALOG, 7], [(2, 7), (4, DIALOG)])
    for rank in (0, 3, 7):
        case("rank insignia %d" % rank, "leyte_rank_insignia_item_proc", [DIALOG, 4], [(2, 4), (4, DIALOG)],
             [[ROSTER + 0x40 * 2 + 0x22, 2, rank]], {("derefs", "trap", 0xA98D): {0: box}, ("trap", 0xAA1E): [ICON_H]})
    return out


def build() -> list:
    return conclude_cases() + summary_cases() + scroll_cases() + text_cases() + filter_cases() + drawer_cases()


def text() -> str:
    base.BINARY = ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "ID04.bin"
    base.ROUTINES = ROUTINES
    base.STUBS = STUBS
    base.REAL = {}
    base.WORD_RESULT = set()
    assert base.LOGS + 0x400 * len(STUBS) <= base.ID01_AT
    cases = []
    for c in build():
        entry = {"name": c["name"], "method": c["method"], "args": c["args"], "writes": c["writes"], "changes": base.run(c),
                 "call_logs": [["addr" if e[0] == "local" else e[0]] + e[1:6] + [{str(k): v for k, v in e[6].items()}] + e[7:]
                               for e in base.logs(c["results"])]}
        if "a7" in c:
            entry["a7"] = c["a7"]
        cases.append(entry)
    doc = {"memory_size": base.MEMORY_SIZE, "a5": A5, "stack_top": base.STACK_TOP, "stack_guard": list(base.STACK_GUARD),
           "port_vars": {}, "common_writes": [], "cases": cases}
    return json.dumps(doc, separators=(",", ":")) + "\n"


def main() -> int:
    t = text()
    if "--check" in sys.argv:
        ok = FIXTURE.is_file() and FIXTURE.read_text() == t
        print(("PASS" if ok else "FAIL") + " Leyte ID04 reconstructed cases: %s" % FIXTURE.relative_to(ROOT))
        return 0 if ok else 1
    FIXTURE.write_text(t)
    print("wrote %s (%d cases)" % (FIXTURE.relative_to(ROOT), len(json.loads(t)["cases"])))
    return 0


if __name__ == "__main__":
    sys.exit(main())
