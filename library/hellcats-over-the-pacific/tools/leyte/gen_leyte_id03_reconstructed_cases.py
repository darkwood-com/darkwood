#!/usr/bin/env python3
"""Emulator cases for Leyte Gulf's reconstructed CODE 3 functions (godot/original/leyte/port/
ID03_reconstructed.gd), run on Leyte's own ID03.bin: godot/tests/leyte/port/fixtures/
id03_reconstructed_cases.json, replayed by tests/leyte/port/test_leyte_id03_reconstructed.gd
(helpers/segment_cases.gd). The runner is the CODE 2 generator's (gen_leyte_id02_reconstructed_cases.py:
the segment at CODE, A5, CODE 1's switch helpers wired, the calls leaving the routine as logging
stand-ins), with this segment's binary, routines and stand-ins. Synthetic memory only.

prepare_instrument_panel's cases take its found-entry paths (the current entry, a stored one loaded,
the cache made); its new-panel path is not written for Leyte yet.

    python3 tools/leyte/gen_leyte_id03_reconstructed_cases.py [--check]
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "leyte"))
import gen_leyte_id02_reconstructed_cases as base  # noqa: E402

FIXTURE = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "id03_reconstructed_cases.json"
A5 = base.A5
ROUTINES = {"prepare_instrument_panel": 0x5EB4, "release_panel_bitmaps": 0x5BE4,
            "store_panel_bitmaps_as_resources": 0x5CA6, "remap_panel_colours_identity": 0x741E,
            "remap_cached_panel_colours": 0x6E5E, "draw_panel_section_with_numbered_boxes": 0x4304,
            "draw_split_display_box": 0x4B32, "draw_percent_scale_gauge": 0x4790,
            "draw_six_cell_display_box": 0x4C42}
STUBS = [["jt", 0x372, [2], False, 2], ["jt", 0x262, [4], True, 4], ["jt", 0x28A, [4], True, 0], ["jt", 0x292, [4], True, 0],
         ["trap", 0xA9A0, [2, 4], True, 4], ["trap", 0xA9AB, [4, 2, 4, 4], True, 0], ["trap", 0xA8D9, [4], True, 0],
         ["trap", 0xA862, [4], True, 0], ["trap", 0xA8A2, [4], True, 0], ["trap", 0xA873, [4], True, 0],
         ["trap", 0xA9AA, [4], True, 0], ["trap", 0xA999, [2], True, 0],
         ["local", 0x5A50, [], False, 0], ["local", 0x5EA6, [4], False, 0], ["local", 0x5E26, [4, 2, 4, 4, 4], False, 4],
         ["local", 0x3832, [4, 2, 2, 2], False, 4], ["local", 0x5BE4, [], False, 0], ["local", 0x741E, [], False, 0],
         ["trap", 0xA9A3, [4], True, 0], ["jt", 0x26A, [4], True, 0], ["trap", 0xA99B, [1], True, 0],
         ["trap", 0xA9AD, [4], True, 0], ["trap", 0xA9AF, [], True, 2], ["local", 0x6E5E, [4, 4], False, 0],
         ["jt", 0x4B2, [2, 2, 2], False, 2], ["jt", 0x49A, [], False, 0],
         ["jt", 0x3B2, [2], False, 0], ["jt", 0x3A2, [2], False, 2],
         ["trap", 0xA8A9, [2, 2, 4], True, 0], ["trap", 0xA891, [2, 2], True, 0, {-1: "digest"}], ["trap", 0xA893, [2, 2], True, 0, {-1: "digest"}],
         ["trap", 0xA89B, [2, 2], True, 0], ["trap", 0xA8A7, [2, 2, 2, 2, 4], True, 0], ["trap", 0xA8A1, [4], True, 0],
         ["jt", 0x402, [2], False, 0], ["trap", 0xA8CB, [], True, 4], ["trap", 0xA8CC, [], True, 0],
         ["trap", 0xA8CD, [4], True, 0], ["trap", 0xA8C6, [4], True, 0], ["trap", 0xA8C7, [4], True, 0],
         ["trap", 0xA8C8, [4], True, 0], ["trap", 0xA8C9, [4], True, 0], ["trap", 0xA8CA, [4, 4], True, 0],
         ["jt", 0x42A, [], False, 4],
         ["jt", 0x242, [4], True, 4], ["jt", 0x24A, [4], True, 0], ["trap", 0xA86F, [4], True, 0],
         ["trap", 0xA875, [4], True, 0], ["trap", 0xA876, [2, 2], True, 0], ["trap", 0xA8DF, [4, 4], True, 0],
         ["trap", 0xA87D, [4], True, 0], ["trap", 0xA89D, [4], True, 0], ["trap", 0xA892, [2, 2], True, 0, {-1: "digest"}],
         ["trap", 0xA878, [2, 2], True, 0],
         ["jt", 0x4F2, [], False, 4], ["jt", 0x25A, [4], True, 4], ["jt", 0x282, [4, 4], True, 0], ["jt", 0x36A, [], False, 0],
         ["trap", 0xA8D8, [], True, 4], ["trap", 0xA8DA, [], True, 0], ["trap", 0xA8DB, [4], True, 0],
         ["trap", 0xA8E6, [4, 4, 4], True, 0], ["trap", 0xA8D6, [4, 4], True, 0], ["trap", 0xA8D3, [4], True, 0],
         ["trap", 0xA983, [4], True, 0],
         # Further QuickDraw calls the new panel's instruments make (signatures from quickdraw_traps.gd).
         ["trap", 0xA879, [4], True, 0, {-1: "digest"}], ["trap", 0xA87A, [4], True, 0, {-1: "digest"}],
         ["trap", 0xA884, [4], True, 0, {-1: "digest"}], ["trap", 0xA887, [2], True, 0, {-1: "digest"}],
         ["trap", 0xA888, [2], True, 0, {-1: "digest"}], ["trap", 0xA88A, [2], True, 0, {-1: "digest"}],
         ["trap", 0xA88B, [4], True, 0, {0: ["out", [0x000A0003, 0x000C0001]]}], ["trap", 0xA88C, [4], True, 2, {-1: "digest"}],
         ["trap", 0xA8A8, [2, 2, 4], True, 0, {-1: "digest"}], ["trap", 0xA8E8, [4, 4], True, 2, {-1: "digest"}],
         ["trap", 0xA8EC, [4, 2, 4, 4, 4, 4], True, 0, {-1: "digest"}], ["trap", 0xAA00, [4], True, 0, {-1: "digest"}],
         ["jt", 0x34A, [2, 4, 4, 4], False, 4], ["jt", 0x44A, [4], False, 0],
         # The overlay's point rotation (A5 - 0x10a0; C: 0, 0, angle, count, src, dst): the turn indicator's shapes.
         ["addr", 0x3200, [4, 4, 4, 4, 4, 4], False, 0, {-1: "digest"}],
         # The overlay's cosine / sine the set-up copies (A5 - 0x10a8 / - 0x10a4 into - 0x21a2 / - 0x219e):
         # C (angle long), a fixed 14-bit result.
         ["trap", 0xA8B8, [4], True, 0, {-1: "digest"}], ["trap", 0xA8B7, [4], True, 0, {-1: "digest"}],
         ["addr", 0x3000, [4], False, 4, {-1: "digest"}], ["addr", 0x3100, [4], False, 4, {-1: "digest"}]]

LEYTE_RES = ROOT / "sources" / "leyte-gulf-1-0" / "resources"
BELOW_A5 = 0x2322                 # Leyte's A5 world below A5 (CODE 0; leyte_profile.gd below_a5)


def a5_world() -> list:
    """The A5 world's initialised data as the runtime builds it (mac_machine.gd init_a5_world): DATA 0's
    words expanded from A5 - 0x2322 up to A5, each zero word followed by ZERO 0's next run of zero bytes,
    then each cell DREL 0 lists (A5 + its signed word) relocated by A5. As case writes (nonzero words)."""
    data = (LEYTE_RES / "DATA" / "0.bin").read_bytes()
    zero = (LEYTE_RES / "ZERO" / "0.bin").read_bytes()
    drel = (LEYTE_RES / "DREL" / "0.bin").read_bytes()
    world = bytearray(BELOW_A5)
    at, d, z = 0, 0, 0
    while at != BELOW_A5:
        w = int.from_bytes(data[d:d + 2], "big")
        d += 2
        world[at:at + 2] = w.to_bytes(2, "big")
        at += 2
        if w == 0:
            n = int.from_bytes(zero[z:z + 2], "big")
            z += 2
            at += n
    out = {}
    for k in range(0, len(world), 2):
        v = int.from_bytes(world[k:k + 2], "big")
        if v:
            out[A5 - BELOW_A5 + k] = v
    writes = [[a, 2, v] for a, v in sorted(out.items())]
    for k in range(0, len(drel), 2):
        cell = A5 + int.from_bytes(drel[k:k + 2], "big", signed=True)
        off = cell - (A5 - BELOW_A5)
        value = (int.from_bytes(world[off:off + 4], "big") + A5) & 0xFFFFFFFF
        writes += [[cell, 4, value]]
    return writes


CACHE_H, CACHE, PORT, PMH, PM, BITMAP_H, NEW_PORT = 0x1C000, 0x1C010, 0x1C800, 0x1C810, 0x1C820, 0x1C900, 0x1CA00
ENTRY = 0x194


def panel_cases() -> list:
    """prepare_instrument_panel's found-entry paths. The display configuration (depth, colour bits, view
    size) matches entry `match`; the case says whether it is current (A5 - 0x142c) or stored (+0xc)."""
    def case(name, match, current, stored=1, depth=8, cache=True, other_first=False):
        config = {0: depth, 2: 0x10, 4: 0x20, 6: 0x200, 8: 0x150}
        w = [[A5 - 0x13F8, 2, depth], [A5 - 0x13FC, 2, 0x10], [A5 - 0x13FA, 2, 0x20], [A5 - 0x13F0, 2, 0x200],
             [A5 - 0x13EE, 2, 0x150], [A5 - 0x10A8, 4, 0x3000], [A5 - 0x10A4, 4, 0x3100], [A5 - 0xE38, 4, 0x1CF00],
             [A5 - 0x1246, 4, 0x1CB00], [A5 - 0x1434, 4, 0x1CC00], [A5 - 0x1514, 4, PORT], [A5 - 0x142C, 2, current],
             [PORT + 2, 4, PMH], [NEW_PORT + 2, 4, PMH], [PMH, 4, PM], [A5 - 0x132A, 4, BITMAP_H], [BITMAP_H, 4, 0x50000],
             [A5 - 0x1268, 2, 4], [A5 - 0x1266, 2, 6], [A5 - 0x1264, 2, 0x40], [A5 - 0x1262, 2, 0x30],
             [A5 - 0x125E, 2, 0x60], [A5 - 0x125C, 2, 0x20], [A5 - 0x1258, 2, 8], [A5 - 0x168A, 4, 0x1CD00]]
        w += [[CACHE_H, 4, CACHE]]
        for i in range(4):
            e = CACHE + ENTRY * i
            w += [[e + k, 1, (i * 31 + k * 7) & 0xFF] for k in range(ENTRY)]
            if i == match or (other_first and i == match - 1):
                for off, v in config.items():
                    w.append([e + off, 2, v if i == match else v ^ 1])
            w += [[e + 0xC, 2, stored if i == match else 0], [e + 0xE, 4, 0x10 + i]]
        results = {("trap", 0xA9A0): [CACHE_H if cache else 0, 0x1D100, 0x1D200, 0x1D300, 0x1D400],
                   ("local", 0x5E26): [NEW_PORT, 0x1CE00], ("local", 0x3832): [0x1D500], ("jt", 0x372): [0x2A0],
                   ("jt", 0x262): [CACHE_H]}
        return {"name": "panel: " + name, "method": "prepare_instrument_panel", "args": [], "a7": base.STACK_TOP - 4,
                "writes": w, "results": results}
    return [
        case("the current entry, depth 8", 2, 2),
        case("a stored entry loaded, depth 4", 1, 3, depth=4),
        case("the current entry after a near miss, depth 1", 3, 3, depth=1, other_first=True),
        case("the cache made, its entry stored", 0, 1, cache=False),
    ]


def bitmap_cases() -> list:
    """release_panel_bitmaps (some handles held, the entry's resource flag set or not);
    store_panel_bitmaps_as_resources (old resources there or not, AddResource failing or not: ResError
    answers); remap_panel_colours_identity (its table in the frame, the remap a stand-in)."""
    cache = [[A5 - 0x1430, 4, CACHE_H], [CACHE_H, 4, CACHE]] + [[CACHE + ENTRY * i + 0xC, 2, 0x7700 + i] for i in range(4)]
    out = []
    for name, held, flag in (("all held, resources", (1, 1, 1, 1), 1), ("two held, handles", (1, 0, 1, 0), 0),
                             ("none held", (0, 0, 0, 0), 1)):
        w = cache + [[A5 - 0x21A4, 2, 2], [CACHE + ENTRY * 2 + 0xC, 2, flag]]
        w += [[A5 + slot, 4, (0x1E000 + 0x10 * k) if on else 0] for k, (slot, on) in enumerate(zip((-0x1270, -0x1256, -0x124E, -0x132A), held))]
        out.append({"name": "release bitmaps: " + name, "method": "release_panel_bitmaps", "args": [], "writes": w, "results": {}})
    for name, old, errors in (("first store", [0, 0, 0, 0], [0]), ("over old ones", [0x1E100, 0, 0x1E200, 0x1E300], [0]),
                              ("an add failing", [0, 0, 0, 0], [0, 0xFF94, 0, 0])):
        w = cache + [[A5 - 0x142C, 2, 1], [A5 - 0x1042, 2, 7], [A5 - 0x151E, 2, 0]]
        w += [[A5 - 0xE48 + 4 * k, 4, 0x1E400 + 0x20 * k] for k in range(4)]
        w += [[A5 + slot, 4, 0x1E800 + 0x10 * k] for k, slot in enumerate((-0x132A, -0x1270, -0x1256, -0x124E))]
        out.append({"name": "store bitmaps: " + name, "method": "store_panel_bitmaps_as_resources", "args": [], "writes": w,
                    "results": {("trap", 0xA9A0): old, ("trap", 0xA9AF): errors}})
    out.append({"name": "identity remap", "method": "remap_panel_colours_identity", "args": [], "a7": base.STACK_TOP - 4,
                "writes": [[A5 - 0x13D2, 4, 0x1E900]], "results": {}})
    return out


def remap_cases() -> list:
    """remap_cached_panel_colours (map, table): the current entry's configuration matching or not; depth 1
    (the refresh only), 8 and 4 (nibbles) over a 3-row panel bitmap and a 2-row gyro card; the colour JT
    ($4b2) answers a sequence; `map` and `table` are synthetic."""
    MAP, TABLE, PANEL_BITS, GYRO_BITS, BM_H, GYRO_H, PORT_P = 0x1A000, 0x1A400, 0x1A800, 0x1A900, 0x1AA00, 0x1AA10, 0x1AB00
    out = []
    for name, depth, mismatch in (("configuration differs", 8, True), ("depth 1", 1, False), ("depth 8", 8, False),
                                  ("depth 4", 4, False)):
        config = {0: depth, 2: 0x10, 4: 0x20, 6: 0x200, 8: 0x150}
        w = [[A5 - 0x1430, 4, CACHE_H], [CACHE_H, 4, CACHE], [A5 - 0x142C, 2, 1], [A5 - 0x13F8, 2, depth],
             [A5 - 0x13FC, 2, 0x10], [A5 - 0x13FA, 2, 0x20], [A5 - 0x13F0, 2, 0x200], [A5 - 0x13EE, 2, 0x150]]
        e = CACHE + ENTRY
        w += [[e + off, 2, (v ^ 1) if (mismatch and off == 6) else v] for off, v in config.items()] + [[e + 0xA, 2, 0x8008]]
        w += [[MAP + 2 * k, 2, (k * 5 + 3) & 0xFF] for k in range(0xE0)] + [[TABLE + 2 * k, 2, (k * 7 + 1) & 0xFF] for k in range(0x100)]
        w += [[A5 - 0x1514, 4, PORT_P], [PORT_P + 0x10, 2, 0], [PORT_P + 0x12, 2, 0], [PORT_P + 0x14, 2, 3], [PORT_P + 0x16, 2, 6],
              [A5 - 0x132A, 4, BM_H], [BM_H, 4, PANEL_BITS], [A5 - 0x1256, 4, GYRO_H], [GYRO_H, 4, GYRO_BITS],
              [A5 - 0x125E, 2, 4], [A5 - 0x125C, 2, 2], [A5 - 0x1258, 2, 0x8004]]
        w += [[PANEL_BITS + k, 1, (k * 13 + 5) & 0xFF] for k in range(0x20)] + [[GYRO_BITS + k, 1, (k * 11 + 9) & 0xFF] for k in range(0x10)]
        out.append({"name": "remap colours: " + name, "method": "remap_cached_panel_colours", "args": [MAP, TABLE],
                    "stack": [(4, MAP), (4, TABLE)], "a7": base.STACK_TOP - 8 - 4, "writes": w,
                    "results": {("jt", 0x4B2): [3, 7, 1, 0, 2, 5, 9, 4, 6, 8, 2, 1, 3, 0]}})
    return out


def drawer_cases() -> list:
    """Two of the instrument drawers the generated port refuses (their rects' second longs unpaired),
    run with the kept helpers they call as they are, the QuickDraw traps and the colour / rounding
    slots ($4b2, $3b2, $3a2, $372) as stand-ins answering sequences; depth 8, 4 and 1. The A5 world
    starts as the runtime builds it (a5_world): the vector digits' stroke data is there."""
    out = []
    for depth in (8, 4, 1):
        POLY_H, POLY = 0x1F000, 0x1F010          # OpenPoly's polygon: a handle to a synthetic record
        w = a5_world() + [[A5 - 0x13F8, 2, depth], [A5 - 0x151C, 4, 0x1E0], [POLY_H, 4, POLY], [POLY, 2, 0x0A]]
        rounding = {("jt", 0x372): [(k * 37) & 0x1FF for k in range(1, 40)], ("jt", 0x3A2): [(k * 41) & 0x1FF for k in range(1, 40)],
                    ("jt", 0x4B2): [3, 5, 7, 9], ("trap", 0xA8CB): [POLY_H], ("jt", 0x42A): [0x1F100]}
        out.append({"name": "numbered section: depth %d" % depth, "method": "draw_panel_section_with_numbered_boxes",
                    "args": [], "a7": base.STACK_TOP - 4, "writes": w, "results": rounding})
        # (width, height: words; an unused long; a, b: words): 8(a6), $a, $c (long), $10, $12.
        args = [0x30, 0x18, 0, 0x120, 0x80]
        out.append({"name": "split box: depth %d" % depth, "method": "draw_split_display_box", "args": args,
                    "stack": [(2, args[0]), (2, args[1]), (4, args[2]), (2, args[3]), (2, args[4])],
                    "a7": base.STACK_TOP - 12 - 4, "writes": w, "results": rounding})
        # (w, h, digits, pen, v, h0: six words at 8(a6) .. $12(a6)).
        gauge = [0x40, 0x120, 3, 1, 0x2a0, 0x1c0]
        BITS, OFFPORT = 0x1F200, 0x1F800       # NewPtr's answers: the offscreen bits, then its port record
        offscreen = dict(rounding); offscreen[("jt", 0x242)] = [BITS, OFFPORT]
        out.append({"name": "percent gauge: depth %d" % depth, "method": "draw_percent_scale_gauge", "args": gauge,
                    "stack": [(2, v) for v in gauge], "a7": base.STACK_TOP - 12 - 4, "writes": w, "results": offscreen})
        # Leyte's own six-cell box (as the panel calls it, and a narrow one for the small-step branch).
        for label, cells in (("as the panel draws it", [0x17C, 0x122, 0x7000A, 0x37F, 0xFD58]), ("narrow", [0x40, 0x30, 0, 0x100, 0x80])):
            out.append({"name": "six-cell box %s: depth %d" % (label, depth), "method": "draw_six_cell_display_box",
                        "args": cells, "stack": [(2, cells[0]), (2, cells[1]), (4, cells[2]), (2, cells[3]), (2, cells[4])],
                        "a7": base.STACK_TOP - 12 - 4, "writes": w + [[A5 - 0x1200 + k, 1, 0x5A] for k in range(0x34)],
                        "results": rounding})
    return out


def new_panel_cases() -> list:
    """prepare_instrument_panel with no cache entry for this display: the new-panel path, every
    instrument drawn as it is on both sides (the A5 world loaded), the Toolbox and the slots it reaches
    stood in for; depth 8."""
    out = []
    for name, depth in (("depth 8", 8),):
        c = panel_cases()[0]
        w = a5_world() + [x for x in c["writes"] if not (CACHE <= x[0] < CACHE + 4 * ENTRY)]
        w += [[CACHE + ENTRY * i + 0xE, 4, 0x10 + i] for i in range(4)] + [[A5 - 0x142C, 2, 0xFFFF], [A5 - 0x13F8, 2, depth]]
        w += [[0x1F000, 4, 0x1F010], [0x1F010, 2, 0xA], [A5 - 0x10A0, 4, 0x3200]]
        # The four bitmap handles NewHandle / NewHandleClear answer, each with a master pointer to its own
        # buffer (the panel's at 0x60000, the others 0x1000 apart below it).
        w += [[0x1D700, 4, 0x60000], [0x1D710, 4, 0x5D000], [0x1D720, 4, 0x5E000], [0x1D730, 4, 0x5F000]]
        results = dict(c["results"])
        results.update({("jt", 0x4F2): [0x1D600], ("jt", 0x25A): [0x1D700], ("jt", 0x262): [0x1D710, 0x1D720, 0x1D730],
                        ("trap", 0xA8D8): [0x1D800, 0x1D810], ("trap", 0xA8CB): [0x1F000], ("jt", 0x42A): [0x1F100],
                        ("jt", 0x242): [0x1F200, 0x1F800], ("jt", 0x4B2): [3, 5, 7, 9, 2, 4],
                        ("jt", 0x372): [(k * 37) & 0x1FF for k in range(1, 400)], ("jt", 0x3A2): [(k * 41) & 0x1FF for k in range(1, 400)],
                        ("addr", 0x3000): [(0x4000 - 97 * k) & 0xFFFF for k in range(200)],
                        ("addr", 0x3100): [(53 * k) & 0x3FFF for k in range(200)], ("jt", 0x34A): [0x1D900]})
        out.append({"name": "new panel: " + name, "method": "prepare_instrument_panel", "args": [], "a7": base.STACK_TOP - 4,
                    "writes": w, "results": results,
                    # labs (JT $522, CODE 2 +0x1e) and memset (JT $53a, CODE 2 +0x2b0) run as Leyte's own code:
                    # the port computes both inline.
                    "code2_slots": {0x522: 0x1E, 0x53A: 0x2B0},
                    # draw_artificial_horizon calls make_inset_face_oval_region (0x3832) directly on both
                    # sides: Leyte's own routine runs here instead of its stand-in.
                    "real": [0x3832]})
    return out


def build() -> list:
    return panel_cases() + bitmap_cases() + remap_cases() + drawer_cases() + new_panel_cases()


def _longer_calls() -> None:
    """A whole new panel (every instrument, the 3x anti-aliased labels pixel by pixel) runs past the
    emulator's default 5 000 000 steps: this generator's calls get 80 000 000."""
    from m68k_emu import Emulator
    if getattr(Emulator.call, "_leyte_id03", False):
        return
    call = Emulator.call

    def longer(self, address, args, a7, max_steps=80_000_000):
        return call(self, address, args, a7, max_steps)
    longer._leyte_id03 = True
    Emulator.call = longer


def text() -> str:
    _longer_calls()
    base.BINARY = ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "ID03.bin"
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
        print(("PASS" if ok else "FAIL") + " Leyte ID03 reconstructed cases: %s" % FIXTURE.relative_to(ROOT))
        return 0 if ok else 1
    FIXTURE.write_text(t)
    print("wrote %s (%d cases)" % (FIXTURE.relative_to(ROOT), len(json.loads(t)["cases"])))
    return 0


if __name__ == "__main__":
    sys.exit(main())
