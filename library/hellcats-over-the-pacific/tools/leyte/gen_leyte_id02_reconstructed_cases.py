#!/usr/bin/env python3
"""Emulator cases for Leyte Gulf's reconstructed CODE 2 functions (godot/original/leyte/port/
ID02_reconstructed.gd), run on Leyte's own ID02.bin: godot/tests/leyte/port/fixtures/
id02_reconstructed_cases.json, replayed by tests/leyte/port/test_leyte_id02_reconstructed.gd
(helpers/segment_cases.gd).

The layout: Leyte's ID02.bin at CODE = 0x30000 (file offsets), A5 = 0x20000, the calls leaving the
routine as logging stand-ins (tools/original/segment_stubs.py): jump-table slots, traps, code
addresses (the overlay's entry), and the segment's own routines by offset ("local" here, "addr" with
the offset in the fixture, as gen_id02_session_cases.py does). Synthetic memory only.

    python3 tools/leyte/gen_leyte_id02_reconstructed_cases.py [--check]
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "original"))
from m68k_emu import Emulator  # noqa: E402
from segment_stubs import install  # noqa: E402

BINARY = ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "ID02.bin"
FIXTURE = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "id02_reconstructed_cases.json"
MEMORY_SIZE, CODE, A5 = 0x80000, 0x30000, 0x20000
STACK_TOP, STACK_GUARD = 0x2F000, (0x2E000, 0x2F040)
LOGS = 0x40000                   # 0x400 per stand-in, clear of the code and the data below
## CODE 1 (the C runtime's switch helpers, behind slots $2a / $32 / $3a: CODE 1 +0x96 / +0xaa / +0xbe, JMP abs.l)
ID01_AT = 0x7E000
CODE2_AT = 0x10000               # Leyte's CODE 2, for another segment's cases that call its library routines
ID01 = ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "ID01.bin"
IMAGE, BLOCK, ENTRY_OFF = 0x15000, 0x16000, 0x100
ROUTINES = {"labs_jt": 0x1E, "strlen_jt": 0x3E8, "reset_overlay_state": 0x245A, "apply_settings_to_live_options": 0x2DB2, "apply_display_palettes": 0x54B8,
            "main": 0x37D2, "initialize_application": 0x32D8, "install_overlay_service_table": 0x1E58,
            "import_settings_from_resource_file": 0x3082, "convert_imported_pilots": 0x31D8,
            "open_preferences_file": 0x2F42, "scan_display_devices": 0x24C8,
            "configure_display_from_main_device": 0x2B48, "layout_game_window_and_view": 0x2676,
            "poll_and_dispatch_event": 0x370C, "redraw_main_window": 0x5544,
            "rebuild_all_palette_index_maps": 0x4E32, "build_palette_index_map": 0x502C,
            "check_device_color_state_changed": 0x53AC, "refresh_display_if_changed": 0x2D60,
            "update_menu_items": 0x38B2, "start_new_mission": 0x3D38,
            "begin_overlay_session": 0x45A, "end_overlay_session": 0x4A4, "load_game_sounds": 0x22AC,
            "restore_screen_globals": 0x552A, "process_pending_update_events": 0x54C8, "handle_update_event": 0x54FE,
            "run_flight_session_leyte": 0x4FC, "save_changed_settings": 0x34D8, "quit_application": 0x35E2,
            "redraw_panel_readouts_locked": 0xAB4, "draw_notched_frame_pair_marked_a5safe": 0x127A,
            "draw_six_segment_indicator_a5safe": 0x1516}
## Routines a method calls as written methods of the port (not by offset): Leyte's own code runs for
## them in its cases, under the same stand-ins, instead of a local stand-in.
REAL = {"configure_display_from_main_device": {0x24C8}, "rebuild_all_palette_index_maps": {0x502C},
        "check_device_color_state_changed": {0x502C}, "update_menu_items": {0x3CE4},
        "quit_application": {0x34D8}}
WORD_RESULT = {"import_settings_from_resource_file", "build_palette_index_map", "labs_jt", "strlen_jt"}     # cases compare D0's low word
H127, REC127, H128 = 0x19A40, 0x19B00, 0x19A50
HEL1, MENU = 0x48454C31, 0x4D454E55
REC227, REC228, H227, H228, CTAB = 0x19000, 0x19200, 0x19800, 0x19810, 0x19900
POLL = 0x370C                    # main's event loop: the emulator run stops at its second pass (the port: event_loop_passes 1)
## The overlay's hooks (A5 slot -> stand-in address) and the settings record (its handle at A5 - 0x142a).
READOUTS_HOOK = 0x17F00         # A5 - 0x10b4, the overlay's readouts hook (redraw_panel_readouts_locked)
## The overlay's flight hooks (A5 slot -> stand-in address): the pilot's, the key hook, the ask hook,
## the step hook, and the two after a concluded mission.
FLIGHT_HOOKS = {-0x10B0: 0x17900, -0x10AC: 0x17A00, -0x10C8: 0x17B00, -0x10BC: 0x17C00, -0x10D0: 0x17D00, -0x10CC: 0x17E00}
## The overlay's session hooks (A5 slot -> stand-in address): prepare, start, finish.
SESSION_HOOKS = {-0x10C4: 0x17600, -0x10C0: 0x17700, -0x10B8: 0x17800}
MISSION_HOOK = 0x17500          # A5 - 0x10dc, the overlay's mission hook (start_new_mission)
HOOKS = {-0x10D4: 0x17000, -0x1090: 0x17100, -0x10D8: 0x17200, -0x10E4: 0x17300}
SETTINGS_HANDLE, SETTINGS = 0x18000, 0x18010
STUBS = [["jt", 0x252, [4], True, 4], ["trap", 0xA975, [], True, 4], ["addr", IMAGE + ENTRY_OFF, [4, 4, 4], False, 0],
         ["local", 0x54B8, [], False, 0], ["local", 0x2DB2, [], False, 0], ["local", 0x5544, [], False, 0],
         ["local", 0x4DAA, [], False, 0], ["local", 0x4E32, [], False, 0]] + [["addr", at, [], False, 0] for at in HOOKS.values()] + [
         ["trap", 0xA9F1, [4], True, 0], ["jt", 0x22A, [4], True, 4], ["jt", 0x1FA, [], False, 2],
         ["local", 0x32D8, [], False, 0], ["local", 0x1E58, [], False, 0], ["local", 0x3838, [], False, 0],
         ["local", 0x3D38, [], False, 0], ["local", POLL, [], False, 0],
         ["jt", 0x232, [], True, 0], ["jt", 0x2AA, [], True, 0], ["trap", 0xA86E, [4], True, 0], ["trap", 0xA8FE, [], True, 0],
         ["jt", 0x212, [2, 2], True, 0], ["trap", 0xA912, [], True, 0], ["trap", 0xA930, [], True, 0], ["trap", 0xA9CC, [], True, 0],
         ["trap", 0xA97B, [4], True, 0], ["trap", 0xA850, [], True, 0], ["jt", 0x20A, [2], True, 0], ["jt", 0x5D2, [], False, 0],
         ["jt", 0xC2, [], False, 2], ["trap", 0xA9F4, [], True, 0], ["local", 0x2F42, [], False, 0],
         ["jt", 0x202, [4, 2], True, 2], ["local", 0x24C8, [], False, 0], ["jt", 0x242, [4], True, 4], ["trap", 0xAA28, [], True, 4],
         ["trap", 0xA9A0, [2, 4], True, 4], ["trap", 0xA9C8, [2], True, 0], ["local", 0x356E, [], False, 0],
         ["local", 0x35E2, [], False, 0], ["jt", 0x262, [4], True, 4], ["trap", 0xA9AB, [4, 2, 4, 4], True, 0, {0: "pstr"}],
         ["local", 0x3082, [], False, 2], ["local", 0x361C, [4, 4], False, 0], ["trap", 0xA9AA, [4], True, 0],
         ["trap", 0xA9B0, [4], True, 0], ["trap", 0xA9A2, [4], True, 0], ["local", 0x31D8, [], False, 0],
         ["local", 0x2B48, [], False, 0], ["trap", 0xA873, [4], True, 0], ["jt", 0x222, [], True, 4],
         ["trap", 0xA997, [4], True, 2, {0: "pstr"}], ["trap", 0xA992, [4], True, 0], ["trap", 0xA99A, [2], True, 0],
         ["trap", 0xA998, [2], True, 0], ["jt", 0x272, [4], True, 4], ["jt", 0x2B2, [4, 4, 4], True, 0],
         ["jt", 0x26A, [4], True, 0], ["jt", 0x2DA, [4, 4], True, 2], ["jt", 0x302, [2, 4], True, 2],
         ["jt", 0x30A, [2, 4], True, 2], ["jt", 0x2FA, [2, 4], True, 2], ["jt", 0x2E2, [2, 4], True, 2],
         ["trap", 0xA994, [], True, 2], ["trap", 0xA9B1, [4], True, 0, {0: "pstr"}], ["trap", 0xA9AF, [], True, 2],
         ["local", 0x2066, [4, 2], False, 0], ["trap", 0xAA2A, [], True, 4], ["trap", 0xAA29, [], True, 4],
         ["trap", 0xAA2B, [4], True, 4], ["jt", 0x31A, [], True, 2], ["trap", 0xA916, [4], True, 0],
         ["trap", 0xA915, [4], True, 0], ["local", 0x2676, [2, 2, 2, 2], False, 0],
         ["trap", 0xAA45, [4, 1, 4, 2, 1, 4, 4, 4], True, 4, {5: "pstr", 6: 2}], ["trap", 0xAA91, [2, 2, 4, 2], True, 4],
         ["trap", 0xA91B, [1, 2, 2, 4], True, 0], ["trap", 0xA91D, [1, 2, 2, 4], True, 0], ["trap", 0xA878, [2, 2], True, 0],
         ["local", 0x4EB6, [2], False, 0], ["local", 0xDB2, [4], False, 0], ["local", 0x1CF8, [], False, 0],
         ["jt", 0x1DA, [], False, 0], ["jt", 0x1D2, [4, 2, 2, 2, 2], False, 2], ["jt", 0x1EA, [], False, 4],
         ["trap", 0xA938, [2], True, 0], ["trap", 0xA9B4, [], True, 0], ["jt", 0xAA, [], False, 0],
         ["trap", 0xA970, [4, 2], True, 2], ["trap", 0xA93E, [2], True, 4], ["trap", 0xA922, [4], True, 0],
         ["trap", 0xA923, [4], True, 0], ["local", 0x38B2, [], False, 0], ["local", 0x2D60, [], False, 0],
         ["local", 0x3656, [4], False, 0], ["local", 0x3DFC, [4], False, 0], ["local", 0x36D8, [4, 4, 4, 4], False, 0],
         ["trap", 0xA852, [], True, 0], ["trap", 0xA853, [], True, 0], ["local", 0x53AC, [], False, 0],
         ["local", 0x9BE, [4, 4], False, 0], ["local", 0x552A, [], False, 0], ["local", 0xAB4, [], False, 0],
         ["addr", 0x17400, [], False, 0], ["trap", 0xAA31, [4], True, 0],
         ["trap", 0xA924, [], True, 4], ["trap", 0xAA32, [], True, 4], ["trap", 0xAA97, [2], True, 0],
         ["local", 0x52F6, [], False, 0], ["local", 0xDB2, [4], False, 0], ["local", 0x502C, [2, 4, 4, 4], False, 2],
         ["jt", 0x1A2, [4, 4], False, 0], ["local", 0x3CE4, [4, 2, 2], False, 0],
         ["trap", 0xA939, [2, 4], True, 0], ["trap", 0xA93A, [2, 4], True, 0], ["trap", 0xA937, [], True, 0],
         ["trap", 0xA945, [1, 2, 4], True, 0],
         ["jt", 0x15A, [], False, 2], ["jt", 0x92, [], False, 2], ["local", 0x4FC, [], False, 2],
         ["addr", MISSION_HOOK, [4], False, 0], ["trap", 0xA928, [4], True, 0],
         ["local", 0x4C6, [], False, 0], ["local", 0x22AC, [], False, 0], ["local", 0x9E8, [], False, 0],
         ["local", 0x23EE, [], False, 0], ["local", 0x4A96, [], False, 0], ["local", 0x2186, [], False, 0],
         ["local", 0xA88, [], False, 0], ["local", 0x21FA, [4, 2], False, 2], ["local", 0x20F2, [], False, 0],
         ["trap", 0xA91F, [4], True, 0], ["trap", 0xA972, [4], True, 0], ["trap", 0xA974, [], True, 2],
         ["trap", 0xA999, [2], True, 0], ["local", 0x1C92, [], False, 0], ["local", 0x54C8, [], False, 0],
         ["local", 0x45A, [], False, 0], ["local", 0x4A4, [], False, 0], ["local", 0x54FE, [4], False, 0],
         ["jt", 0x10A, [2, 4], False, 2], ["jt", 0x16A, [], False, 0], ["jt", 0x8A, [], False, 0],
         ["addr", FLIGHT_HOOKS[-0x10B0], [4], False, 0], ["addr", FLIGHT_HOOKS[-0x10AC], [4, 4], False, 2],
         ["addr", FLIGHT_HOOKS[-0x10C8], [], False, 4], ["addr", FLIGHT_HOOKS[-0x10BC], [4, 4], False, 0],
         ["addr", FLIGHT_HOOKS[-0x10D0], [], False, 0], ["addr", FLIGHT_HOOKS[-0x10CC], [], False, 0],
         ["jt", 0x9A, [], False, 2], ["jt", 0x132, [], False, 2], ["local", 0x34D8, [], False, 0],
         ["addr", READOUTS_HOOK, [], False, 0],
         # the overlay's drawing services ($3e2 / $432): QuickDraw (Pascal traps) and the colour pattern slot
         ["trap", 0xA8CB, [], True, 4], ["trap", 0xA8CC, [], True, 0], ["trap", 0xA893, [2, 2], True, 0],
         ["trap", 0xA891, [2, 2], True, 0], ["trap", 0xA892, [2, 2], True, 0], ["trap", 0xA89B, [2, 2], True, 0], ["trap", 0xA862, [4], True, 0],
         ["trap", 0xA863, [4], True, 0], ["trap", 0xA8C7, [4], True, 0], ["trap", 0xA8CA, [4, 4], True, 0],
         ["trap", 0xA8C6, [4], True, 0], ["trap", 0xA8A5, [4, 4], True, 0], ["trap", 0xA8CE, [2, 2, 4], True, 0],
         ["trap", 0xA8A8, [2, 2, 4], True, 0], ["trap", 0xA8CD, [4], True, 0], ["trap", 0xA89D, [4], True, 0],
         ["jt", 0x1E2, [2, 2, 2], False, 4]] + [
         ["addr", at, [], False, 0] for at in SESSION_HOOKS.values()]


## Each stand-in logs into its own 0x400 bytes from LOGS: the table must end below CODE 1.
assert LOGS + 0x400 * len(STUBS) <= ID01_AT, "the call logs reach CODE 1 (%d stand-ins)" % len(STUBS)


class Stop(Exception):
    pass


def logs(results: dict | None = None) -> list:
    out = []
    for i, (kind, key, sizes, pascal, result, *rest) in enumerate(STUBS):
        e = [kind, key, LOGS + 0x400 * i + 4, sizes, pascal, result, dict((results or {}).get(("derefs", kind, key), rest[0] if rest else {}))]
        if results and (kind, key) in results:
            e.append(results[(kind, key)])
        out.append(e)
    return out


def overlay(text: int, data: int, bss: int, block: int) -> list:
    """The loader's state after relocation: header sizes, image base, entry offset, the block A5 -
    0x1082; the image's bss and the block filled with markers (what the routine must clear)."""
    w = [[A5 - 0x1048, 4, IMAGE], [A5 - 0x1064, 4, text], [A5 - 0x1060, 4, data], [A5 - 0x105C, 4, bss],
         [A5 - 0x1054, 4, ENTRY_OFF], [A5 - 0x1082, 4, BLOCK]]
    w += [[IMAGE + k, 1, 0x11] for k in range(text + data)] + [[IMAGE + text + data + k, 1, 0x55] for k in range(bss + 4)]
    w += [[BLOCK + k, 1, 0x66] for k in range(block + 4)]
    return w


def settings(fields: dict, live: dict, depth: int, image: bool) -> list:
    """A settings record (words at +0..+0x2a, `fields` over a ramp) and the live options before."""
    w = [[A5 - 0x142A, 4, SETTINGS_HANDLE], [SETTINGS_HANDLE, 4, SETTINGS], [A5 - 0x13F8, 2, depth],
         [A5 - 0x1048, 4, IMAGE if image else 0]] + [[A5 + k, 4, v] for k, v in HOOKS.items()]
    w += [[SETTINGS + off, 2, fields.get(off, 0x100 + off)] for off in range(0, 0x2C, 2)]
    w += [[A5 + off, 2, v] for off, v in live.items()]
    return w


SAME = {-0x135E: 0x110, -0x1356: 0x118, -0x1364: 0x108, -0x135A: 0x114, -0x1358: 0x116, -0x135C: 0x112}


def environs(color: int, cpu: int) -> list:
    """SysEnvirons' record as three longs from +0: +6 processor, +9 hasColorQD."""
    return ["out", [0x00020001, (0x0700 << 16) | cpu, (0 << 24) | (color << 16) | 3]]


def init_cases() -> list:
    i = "initialize_application"
    base = [[A5 - 0x1548, 4, 0x13000], [H227, 4, REC227], [H228, 4, REC228]] + [[REC227 + k, 1, 0x77] for k in range(0x30)]
    base += [[A5 - 0xC72 + k, 1, b] for k, b in enumerate(b"\x05Pilot")] + [[A5 - 0xC8A + k, 1, b] for k, b in enumerate(b"\x03Opt")]
    base += [[A5 - 0xC66 + k, 1, b] for k, b in enumerate(b"\x04Logs")]
    def case(name, color, cpu, check, menu, r227, r228, defaults):
        return {"name": "init: " + name, "method": i, "args": [], "a7": STACK_TOP - 4, "writes": base,
                "results": {("derefs", "jt", 0x202): {0: environs(color, cpu)}, ("jt", 0xC2): [check],
                            ("trap", 0xA9A0): [menu, r227, r228], ("jt", 0x262): [H227, H228] if not r227 else [H228],
                            ("jt", 0x242): [CTAB], ("trap", 0xAA28): [0xC0FFEE], ("local", 0x3082): [defaults]}}
    return [
        case("colour 68020, everything present", 1, 2, 1, 0x19A00, H227, H228, 0),
        case("no colour, 68040, settings made with defaults", 0, 4, 1, 0x19A00, 0, 0, 0),
        case("colour 68030, settings made without defaults, 228 present", 1, 3, 1, 0x19A00, 0, H228, 1),
        case("no MENU 2, system check fails", 0, 1, 0, 0, H227, 0, 0),
    ]


def import_cases() -> list:
    m = "import_settings_from_resource_file"
    base = [[A5 - 0x142A, 4, H227], [H227, 4, REC227], [H127, 4, REC127], [A5 - 0x1042, 2, 0x22]]
    base += [[REC227 + k, 1, 0x77] for k in range(0x30)] + [[REC127 + k, 1, 0x30 + k] for k in range(0x30)]
    base += [[A5 - 0xC94 + k, 1, b] for k, b in enumerate(b"\x0eHellcats Prefs")]
    return [
        {"name": "import: no such file", "method": m, "args": [], "writes": base, "results": {("trap", 0xA997): [0xFFFF]}},
        {"name": "import: 'HEL1' 127 and 128", "method": m, "args": [], "writes": base,
         "results": {("trap", 0xA997): [9], ("trap", 0xA9A0): [H127, H128]}},
        {"name": "import: no 'HEL1' 127", "method": m, "args": [], "writes": base,
         "results": {("trap", 0xA997): [9], ("trap", 0xA9A0): [0, H128]}},
        {"name": "import: neither", "method": m, "args": [], "writes": base, "results": {("trap", 0xA997): [9], ("trap", 0xA9A0): [0, 0]}},
    ]


def pilot_cases() -> list:
    m = "convert_imported_pilots"
    table, imported = 0x1A000, 0x1A600
    base = [[A5 - 0x1426, 4, H228], [H228, 4, table], [H128, 4, imported]]
    base += [[table + i * 64 + k, 1, (i * 7 + k * 3) & 0xFF] for i in range(20) for k in range(64)]
    return [
        {"name": "pilots: nothing imported", "method": m, "args": [], "writes": base, "results": {}},
        {"name": "pilots: 20 records converted", "method": m, "args": [], "writes": base + [[A5 - 0xD40, 4, H128]],
         "results": {("jt", 0x272): [0x500]}},
    ]


def prefs_cases() -> list:
    m = "open_preferences_file"
    base = [[A5 - 0xDA0, 4, 0x1B000]] + [[A5 - 0xCB6 + k, 1, b] for k, b in enumerate(b"\x0bLeyte Prefs")]
    base += [[A5 - 0xCA0 + k, 1, b] for k, b in enumerate(b"\x0bPreferences")]

    def pb(words: dict) -> list:
        """A parameter block's first 0x34 bytes as longs, the given words (offset -> value) set."""
        raw = bytearray(0x34)
        for off, v in words.items():
            raw[off:off + 2] = (v & 0xFFFF).to_bytes(2, "big")
        return [int.from_bytes(raw[k:k + 4], "big") for k in range(0, 0x34, 4)]
    env = ["out", [0x00020001, 0x07000002, 0x00010003, 0x0000FFFE]]           # sysVRefNum (+0xe) = -2
    wd = ["out", pb({0x20: 0xFFF0, 0x30: 0x0000, 0x32: 0x0010})]              # ioWDVRefNum, ioWDDirID 0x10
    cat = ["out", pb({0x30: 0x0000, 0x32: 0x0123})]                            # the folder's ioDirID 0x123
    opened = ["out", pb({0x16: 0x8001})]                                       # the new working directory
    def case(name, wd_err, cat_err, opens, res_errors):
        return {"name": "prefs: " + name, "method": m, "args": [], "a7": STACK_TOP - 4, "writes": base,
                "results": {("trap", 0xA994): [3, 9], ("derefs", "jt", 0x2DA): {0: ["out", 0xFFFF, 2]},
                            ("derefs", "jt", 0x202): {0: env}, ("derefs", "jt", 0x302): {1: wd}, ("jt", 0x302): [wd_err],
                            ("derefs", "jt", 0x30A): {1: cat}, ("jt", 0x30A): [cat_err], ("derefs", "jt", 0x2FA): {1: opened},
                            ("trap", 0xA997): opens, ("trap", 0xA9AF): res_errors}}
    return [
        case("folder found, file present", 0, 0, [7], [0]),
        case("no folder, file created", 0, 0xFFD7, [0xFFFF, 7], [0]),
        case("no WD info, creation fails once", 0xFFC9, 0, [0xFFFF, 0xFFFF, 7], [0xFFD8, 0]),
    ]


def dev(i, top, left, bottom, right, pixel=8, kind=1, flags=1):
    """GDevice i: handle at 0x1C000 + 0x100 i -> record +0x10, its pixmap handle +0x40 -> pixmap +0x50."""
    h = 0x1C000 + 0x100 * i
    rec, ph, pm = h + 0x10, h + 0x40, h + 0x50
    w = [[h, 4, rec], [rec + 4, 2, kind], [rec + 0x14, 1, flags], [rec + 0x16, 4, ph], [ph, 4, pm],
         [pm, 4, 0x40000000 + 0x100000 * i], [pm + 4, 2, 0x8280 + i], [pm + 6, 2, top], [pm + 8, 2, left],
         [pm + 0xA, 2, bottom], [pm + 0xC, 2, right], [pm + 0x20, 2, pixel]]
    return h, w


def event_record(what: int, message: int = 0, when: int = 0x1234, where: int = 0x00500060, modifiers: int = 0) -> list:
    """An EventRecord (what, message, when, where, modifiers: 16 bytes) as the four longs "out" writes."""
    return [((what & 0xFFFF) << 16) | (message >> 16), ((message & 0xFFFF) << 16) | (when >> 16),
            ((when & 0xFFFF) << 16) | (where >> 16), ((where & 0xFFFF) << 16) | (modifiers & 0xFFFF)]


def redraw_cases() -> list:
    m = "redraw_main_window"
    def case(name, up=1, image=True, running=1, drawn=0, two=0, multi=0, sides=(), offscreen=0):
        w = [[A5 - 0x1544, 2, up], [A5 - 0x1048, 4, IMAGE if image else 0], [A5 - 0x1376, 2, running],
             [A5 - 0x1368, 2, drawn], [A5 - 0x1352, 2, two], [A5 - 0x1074, 2, multi], [A5 - 0x10E0, 4, 0x17400],
             [A5 - 0x107C, 4, offscreen]]
        for k, off in enumerate([-0x13D6] + list(sides)):
            w += [[A5 + off, 4, 0x1C000 + 0x100 * k], [A5 + off + 0xE, 2, 0x280 + k], [A5 + off + 0x10, 2, 1],
                  [A5 + off + 0x12, 4, 0x40000000 + 0x100000 * k]]
        return {"name": "redraw: " + name, "method": m, "args": [], "a7": STACK_TOP - 4, "writes": w, "results": {}}
    return [
        case("window not up", up=0),
        case("no overlay image", image=False),
        case("one screen, first drawing"),
        case("one screen, already drawn", drawn=1),
        case("two screens, left and right, offscreen port", two=1, multi=1, sides=(-0x13C0, -0x13AA), offscreen=0x1DC00),
        case("two-screen option without the devices", two=1, multi=0),
    ]


def poll_cases() -> list:
    m = "poll_and_dispatch_event"
    window = 0x1E000
    base = [[A5 - 0x1548, 4, window], [A5 - 0x1544, 2, 1]]
    def case(name, record, got=1):
        res = {("trap", 0xA970): [0x100 if got else 0], ("trap", 0xA93E): [0x00810003]}
        if record is not None:
            res[("derefs", "trap", 0xA970)] = {0: ["out", record]}
        return {"name": "poll: " + name, "method": m, "args": [], "a7": STACK_TOP - 4, "writes": base, "results": res}
    return [
        case("no event", event_record(0), got=0),
        case("mouse-down", event_record(1, 0, where=0x00400030)),
        case("command key", event_record(3, 0x0000_0071, modifiers=0x0100)),
        case("key without the command bit", event_record(3, 0x0000_0071)),
        case("update of the game window", event_record(6, window)),
        case("update of another window", event_record(6, 0x1E800)),
        case("activate", event_record(8, window, modifiers=1)),
    ]


def layout_cases() -> list:
    m = "layout_game_window_and_view"
    window = 0x1E000
    def case(name, rect, depth=8, option=1, two=0, multi=0, width=640, height=480, win=False, visible=0,
             offscreen=0, hook=False, skip=0, sides=(), results=None):
        w = [[0xBAA, 2, 20], [A5 - 0x142A, 4, H227], [H227, 4, REC227], [REC227 + 0xE, 2, option], [REC227 + 0x1C, 2, two],
             [A5 - 0x1074, 2, multi], [A5 - 0x13FC, 2, width], [A5 - 0x13FA, 2, height], [A5 - 0x13F8, 2, depth],
             [A5 - 0x106A, 2, skip], [A5 - 0x107C, 4, offscreen], [A5 - 0x1070, 2, 1 if hook else 0],
             [A5 - 0x10E4, 4, HOOKS[-0x10E4] if hook else 0], [A5 - 0x1548, 4, window if win else 0], [window + 0x6E, 1, visible],
             [A5 - 0x13C4, 4, 0x1D800], [A5 - 0x13C8, 2, 0x50]]
        w += [[A5 - 0xCB8 + k, 1, b] for k, b in enumerate(b"\x0aLeyte Gulf")]
        for k, rec_off in enumerate([-0x13D6] + list(sides)):
            w += [[A5 + rec_off, 4, 0x1C000 + 0x100 * k], [A5 + rec_off + 0xA, 4, 0x40000000 + 0x100000 * k],
                  [A5 + rec_off + 0xE, 2, 0x280 >> (3 - min(depth, 8).bit_length() + 1) if depth < 8 else 0x280]]
        res = {("trap", 0xAA45): [window], ("trap", 0xAA91): [0x1D900, 0x1DA00], ("jt", 0x1D2): [1], ("jt", 0x1EA): [0x1DB00]}
        res.update(results or {})
        # C words, pushed right to left (MPW C): the stack holds them above the return address.
        return {"name": "layout: " + name, "method": m, "args": list(rect), "stack": [(2, v & 0xFFFF) for v in rect],
                "a7": STACK_TOP - 2 * len(rect) - 4, "writes": w, "results": res}
    return [
        case("first window, 8 bits", (20, 0, 480, 640)),
        case("visible window, 4 bits, half width, offscreen port, resize hook", (20, 0, 480, 640), depth=4, option=2, win=True,
             visible=1, offscreen=0x1DC00, hook=True),
        case("1 bit, the buffer fails", (20, 0, 342, 512), depth=1, width=512, height=342, results={("jt", 0x1D2): [0]}),
        case("two screens, left and right", (20, 0, 480, 640), two=1, multi=1, sides=(-0x13C0, -0x13AA)),
        case("512 x 342, the menu bar skipped", (20, 0, 342, 512), width=512, height=322),
        case("menu bar already skipped, shrink to fit", (0, 0, 300, 640), skip=1, width=640, height=480),
    ]


def configure_cases() -> list:
    m = "configure_display_from_main_device"
    window = 0x1E000
    base = [[0xBAA, 2, 20], [A5 - 0x142A, 4, H227], [H227, 4, REC227], [REC227 + 0x1C, 2, 0x0102],
            [A5 - 0x1548, 4, window], [window + 0x6E, 1, 1], [A5 - 0x1376, 2, 1], [A5 - 0xDB4, 4, 0x1B100]]
    def case(name, devs, mains, nexts, mmu=0):
        writes = list(base)
        hs = []
        for i, d in enumerate(devs):
            h, w = dev(i, *d)
            hs.append(h)
            writes += w
        return {"name": "configure: " + name, "method": m, "args": [], "writes": writes,
                "results": {("trap", 0xAA2A): [hs[k] for k in mains], ("trap", 0xAA29): [hs[0]],
                            ("trap", 0xAA2B): [hs[k] if k is not None else 0 for k in nexts], ("jt", 0x31A): [mmu << 8],
                            ("jt", 0x242): [0x1D000, 0x1D200, 0x1D400, 0x1D600]}}
    return [
        case("8 bits, one screen", [(0, 0, 480, 640)], [0], [None]),
        case("16 bits, then 8 after the rescan", [(0, 0, 480, 640, 16), (0, 0, 480, 640)], [0, 1], [1, None, 1, None]),
        case("2 bits stays unsupported", [(0, 0, 480, 640, 2)], [0, 0], [None]),
        case("two screens, MMU32Bit set", [(0, 0, 480, 640), (0, 640, 480, 1280)], [0], [1, None], mmu=1),
    ]


def scan_cases() -> list:
    m = "scan_display_devices"
    def case(name, devs, main_index, mmu=0, maps=()):
        hs, writes = [], [[A5 - 0x13D6 + 4, 4, 0], [A5 - 0x1074, 2, 0x55]]
        for i, d in enumerate(devs):
            h, w = dev(i, *d)
            hs.append(h)
            writes += w
        for off, at in maps:
            writes.append([A5 + off + 4, 4, at])
        return {"name": "scan: " + name, "method": m, "args": [], "writes": writes,
                "results": {("trap", 0xAA2A): [hs[main_index]], ("trap", 0xAA29): [hs[0]], ("trap", 0xAA2B): hs[1:] + [0],
                            ("jt", 0x31A): [mmu << 8], ("jt", 0x242): [0x1D000, 0x1D200, 0x1D400]}}
    main = (0, 0, 480, 640)
    return [
        case("one screen", [main], 0),
        case("main, left and right", [main, (0, -640, 480, 0), (0, 640, 480, 1280)], 0),
        case("sides that do not match", [main, (0, -640, 480, 0, 4), (0, 640, 400, 1280)], 0),
        case("main second, MMU32Bit set", [(0, -640, 480, 0, 8, 0, 0), main], 1, mmu=1),
        case("index maps already there", [main, (0, 640, 480, 1280)], 0, maps=[(-0x13D6, 0x1D800), (-0x13AA, 0x1DA00)]),
    ]


PM_PORT, MAP1, MAP2, REVERSE = 0x1C000, 0x1C100, 0x1C400, 0x1C800


def palette_map_cases() -> list:
    """build_palette_index_map and rebuild_all_palette_index_maps over synthetic device records
    (A5 - 0x13ec + 0x16 * i): PMForeColor is a stand-in, so the port's colour word (A5 - 0x168a +0x52)
    stays as written and every index after the first hits it again, which exercises the duplicate
    flag's condition (A5 - 0x1352 and A5 - 0x1074, or device 1)."""
    def world(depth, colour, devices=(1,), w1352=0, w1074=0, offscreen=0, flag=0x7777, mode=8):
        w = [[A5 - 0x2CC, 2, depth], [A5 - 0x168A, 4, PM_PORT], [PM_PORT + 0x52, 2, colour & 0xFFFF],
             [A5 - 0x1352, 2, w1352], [A5 - 0x1074, 2, w1074], [A5 - 0x1548, 4, 0x1D000], [A5 - 0x106C, 2, flag],
             [A5 - 0x107C, 4, offscreen], [A5 - 0x13F8, 2, mode], [A5 - 0x1168, 4, 0xDEADBEEF]]
        for i in devices:
            rec = A5 - 0x13EC + 0x16 * i
            w += [[rec, 4, 0x1E000 + 0x10 * i], [rec + 4, 4, MAP1 if i == 1 else MAP2]]
        w += [[REVERSE + 2 * k, 2, 0x5A5A] for k in range(0x100)]
        return w

    b, r = "build_palette_index_map", "rebuild_all_palette_index_maps"
    gd = {("trap", 0xA924): [0x1D000, 0x1D000, 0x1D000], ("trap", 0xAA32): [0x1F000, 0x1F000, 0x1F000]}

    def bcase(name, device, writes, results=None):
        args = [device, REVERSE - 4, MAP1 if device == 1 else MAP2, REVERSE]
        return {"name": "palette map: " + name, "method": b, "args": args,
                "stack": [(2, args[0]), (4, args[1]), (4, args[2]), (4, args[3])],
                "a7": STACK_TOP - 14 - 4, "writes": writes, "results": results or gd}

    def rcase(name, writes, results=None):
        return {"name": "rebuild maps: " + name, "method": r, "args": [], "a7": STACK_TOP - 4,
                "writes": writes, "results": results or gd}
    return [
        bcase("no device", 2, world(8, 5)),
        bcase("depth 4", 1, world(4, 3)),
        bcase("depth 8, device 1 (flag set)", 1, world(8, 7)),
        bcase("depth 8, device 2, flags clear (no flag)", 2, world(8, 7, devices=(2,))),
        bcase("depth 8, device 2, both flags (flag set)", 2, world(8, 7, devices=(2,), w1352=1, w1074=1)),
        bcase("depth 8, device 3, one flag only", 3, world(8, 9, devices=(3,), w1352=1)),
        bcase("depth 8, front window other", 1, world(8, 2), {("trap", 0xA924): [0x1D100], ("trap", 0xAA32): [0x1F000]}),
        rcase("depth 1: standard colours", world(8, 1, mode=1)),
        rcase("no palette depth", world(0, 1)),
        rcase("devices 1 and 3, offscreen port", world(8, 4, devices=(1, 3), offscreen=0x1D800)),
        rcase("depth 4, device 2", world(4, 6, devices=(2,))),
    ]


def colour_check_cases() -> list:
    """check_device_color_state_changed: the fresh maps are all one colour (PMForeColor a stand-in, the
    port's +0x52 as written), so a record map of that colour is unchanged and one with another entry
    changed. refresh_display_if_changed over a synthetic main device chain."""
    def world(colour, maps, depth=8, mode=8, image=True, hook=True):
        w = [[A5 - 0x2CC, 2, depth], [A5 - 0x168A, 4, PM_PORT], [PM_PORT + 0x52, 2, colour],
             [A5 - 0x1352, 2, 0], [A5 - 0x1074, 2, 0], [A5 - 0x1548, 4, 0x1D000], [A5 - 0x106C, 2, 0x7777],
             [A5 - 0x13F8, 2, mode], [A5 - 0x13D2, 4, 0x1E010], [A5 - 0x1048, 4, IMAGE if image else 0],
             [A5 - 0x10D8, 4, HOOKS[-0x10D8] if hook else 0]]
        for i, entries in maps.items():
            rec, at = A5 - 0x13EC + 0x16 * i, (MAP1 if i == 1 else MAP2)
            w += [[rec, 4, 0x1E000 + 0x10 * i], [rec + 4, 4, at]] + [[at + 2 * k, 2, entries.get(k, colour)] for k in range(0xD9)]
        return w
    gd = {("trap", 0xA924): [0x1D000] * 4, ("trap", 0xAA32): [0x1F000] * 4}
    c = "check_device_color_state_changed"

    def ccase(name, writes):
        return {"name": "colour check: " + name, "method": c, "args": [], "a7": STACK_TOP - 4, "writes": writes, "results": gd}
    GDH, GD, PMH, PM = 0x1D200, 0x1D210, 0x1D300, 0x1D310

    def refresh(name, main=GDH, pixel=8, mode=8, flag=1, image=True, live=1):
        w = [[A5 - 0x1078, 4, GDH], [GDH, 4, GD], [GD + 0x16, 4, PMH], [PMH, 4, PM], [PM + 0x20, 2, pixel],
             [A5 - 0x13F8, 2, mode], [A5 - 0x1070, 2, flag], [A5 - 0x1048, 4, IMAGE if image else 0],
             [A5 - 0x1544, 2, live], [A5 - 0x10D8, 4, HOOKS[-0x10D8]]]
        return {"name": "refresh: " + name, "method": "refresh_display_if_changed", "args": [], "a7": STACK_TOP - 4,
                "writes": w, "results": {("trap", 0xAA2A): [main]}}
    return [
        ccase("depth 1", world(5, {1: {}}, mode=1)),
        ccase("unchanged", world(5, {1: {}})),
        ccase("device 1 changed", world(5, {1: {7: 9}})),
        ccase("device 2 changed, no image", world(5, {2: {0: 1}}, image=False)),
        ccase("devices 1 and 3, 3 changed", world(5, {1: {}, 3: {0xD8: 2}})),
        refresh("nothing changed"),
        refresh("another main device", main=0x1D400),
        refresh("depth changed, hook", pixel=4),
        refresh("depth changed, no flag", pixel=4, flag=0),
        refresh("depth changed, no image", pixel=4, image=False),
        refresh("depth changed, not live", pixel=4, live=0),
    ]


def menu_cases() -> list:
    """update_menu_items over synthetic menus and a settings record (**(A5 - 0x142a)) whose bytes and
    words are a ramp with the chosen fields; the front window (a desk accessory when its kind is
    negative) from FrontWindow."""
    SH, SR, WIN = 0x1D500, 0x1D510, 0x1D600

    def case(name, front=WIN, kind=8, playing=1, state=3, image=True, w136e=0, w1542=0, w132e=0, w1074=1,
             w1352=0, sound=1, depth=8, shown=0, fields=None):
        w = [[A5 - 0x142A, 4, SH], [SH, 4, SR], [WIN + 0x6C, 2, kind & 0xFFFF], [A5 - 0x1070, 2, playing],
             [A5 - 0x1376, 2, state & 0xFFFF], [A5 - 0x1048, 4, IMAGE if image else 0], [A5 - 0x136E, 2, w136e],
             [A5 - 0x1542, 2, w1542], [A5 - 0x132E, 2, w132e], [A5 - 0x1074, 2, w1074], [A5 - 0x1352, 2, w1352],
             [A5 - 0x1071, 1, sound], [A5 - 0x13F8, 2, depth], [A5 - 0xC3A, 2, shown],
             [A5 - 0xC4A, 4, 0x1D700], [A5 - 0xC46, 4, 0x1D710], [A5 - 0xC42, 4, 0x1D720], [A5 - 0xC3E, 4, 0x1D730]]
        w += [[SR + k, 1, (k * 7 + 1) & 1] for k in range(0x20)]
        for off, v in (fields or {0xE: 2, 0x10: 4}).items():
            w.append([SR + off, 2, v])
        return {"name": "menus: " + name, "method": "update_menu_items", "args": [], "a7": STACK_TOP - 4,
                "writes": w, "results": {("trap", 0xA924): [front]}}
    return [
        case("playing, state 3"),
        case("no front window", front=0),
        case("desk accessory in front, shown before", kind=-2, shown=0),
        case("desk accessory, already shown", kind=-2, shown=1),
        case("not playing, state 0, depth 4", playing=0, state=0, depth=4),
        case("state 0 with an image", state=0),
        case("state 1, 0x136e and 0x1542 set", state=1, w136e=1, w1542=1, w132e=1),
        case("no image, busy, depth 1", image=False, w1352=1, depth=1, fields={0xE: 3, 0x10: 1}),
        case("state -1, 0x1074 clear", state=-1, w1074=0, sound=0, fields={0xE: 1, 0x10: 5}),
    ]


def mission_cases() -> list:
    """start_new_mission: the two checks (JT $15a, $92) and the session (0x4fc) as stand-ins with chosen
    results, the counters and flags as markers to be cleared."""
    WIN = 0x1D800

    def case(name, ready, check, session, up=1, playing=1, image=True, depth=1, state=0xFFFE):
        w = [[A5 - 0x10DC, 4, MISSION_HOOK], [A5 - 0x10D8, 4, HOOKS[-0x10D8]], [A5 - 0x1376, 2, state],
             [A5 - 0x1544, 2, up], [A5 - 0x1548, 4, WIN], [A5 - 0x168A, 4, 0x1D900], [A5 - 0x1070, 2, playing],
             [A5 - 0x1048, 4, IMAGE if image else 0], [A5 - 0x13F8, 2, depth]]
        w += [[A5 + off, 2, 0x1111] for off in (-0x136E, -0x132E, -0x1330, -0x132C, -0x1542, -0x1540, -0x153E)]
        w += [[A5 - 0x1420 + 2 * i, 2, 0x2200 + i] for i in range(0xC)] + [[A5 - 0x1348 + 2 * i, 2, 0x3300 + i] for i in range(0xC)]
        return {"name": "mission: " + name, "method": "start_new_mission", "args": [], "a7": STACK_TOP - 4, "writes": w,
                "results": {("jt", 0x15A): ready, ("jt", 0x92): check, ("local", 0x4FC): session}}
    return [
        case("not ready", [0], [1], [0]),
        case("check refuses", [1], [0], [0]),
        case("one mission, window up, 1-bit hook", [1], [1], [0]),
        case("one mission, window down", [1], [1], [0], up=0),
        case("depth 8: no 1-bit hook", [1], [1], [0], depth=8),
        case("two missions, then not ready", [1, 1, 0], [1, 1], [1, 0]),
        case("session again, then the check refuses", [1, 1], [1, 0], [1], playing=0),
    ]


def session_cases() -> list:
    """begin_overlay_session (FreeMem below / above 100 000 bytes: MaxMem or not) and
    end_overlay_session (sound on / off, the menu bar hidden or not); every callee a stand-in."""
    hooks = [[A5 + k, 4, v] for k, v in SESSION_HOOKS.items()]
    out = []
    for free in (50000, 0x186A0, 0x7FFFFFFF):
        out.append({"name": "begin session: FreeMem %d" % free, "method": "begin_overlay_session", "args": [],
                    "a7": STACK_TOP - 4, "writes": hooks, "results": {("jt", 0x222): [free]}})
    for sound in (0, 1):
        for hidden in (0, 1):
            out.append({"name": "end session: sound %d, bar hidden %d" % (sound, hidden), "method": "end_overlay_session",
                        "args": [], "a7": STACK_TOP - 4, "results": {},
                        "writes": hooks + [[A5 - 0x136C, 2, sound], [A5 - 0x106A, 2, hidden]]})
    return out


def sound_cases() -> list:
    """load_game_sounds: the loader (0x21fa) answers each load from the case (0 loaded, nonzero not),
    FreeMem above or below 0x2e400 picks the engine sound; slot 8's fallback when its first is missing."""
    m = "load_game_sounds"
    def case(name, free, loads):
        return {"name": "sounds: " + name, "method": m, "args": [], "a7": STACK_TOP - 4, "writes": [[A5 - 0x1170, 4, 0]],
                "results": {("jt", 0x222): [free], ("local", 0x21FA): loads}}
    return [
        case("all loaded, plenty free", 0x40000, [0]),
        case("all loaded, little free", 0x2E400, [0]),
        case("one missing", 0x40000, [0, 0, 0, 1] + [0] * 10),
        case("slot 8 missing, its fallback loads", 0x10000, [0] * 12 + [1, 0]),
        case("slot 8 and its fallback missing", 0x10000, [0] * 12 + [1, 1]),
    ]


def update_cases() -> list:
    """restore_screen_globals; process_pending_update_events over a sequence of update events
    (GetNextEvent answering true for each, then false; the records written into the frame) for the game
    window A5 - 0x1548 or another; handle_update_event with the window up or down, for it or another."""
    win, other = 0x1D800, 0x1DC00
    base = [[A5 - 0x1548, 4, win], [A5 - 0x1544, 2, 1]]
    out = [{"name": "screen globals restored", "method": "restore_screen_globals", "args": [], "results": {},
            "writes": [[A5 - 0x1078, 4, 0x1E200], [A5 - 0x13C4, 4, 0x12345678], [A5 - 0x13C8, 2, 0x2A0],
                       [A5 - 0x13C6, 2, 1], [A5 - 0x1400, 4, 0], [A5 - 0x13F6, 2, 0], [A5 - 0x1390, 2, 0]]}]
    for name, windows in (("none", []), ("one for the game window", [win]), ("one for another window", [other]),
                          ("two for the game window, then another", [win, win, other])):
        records = [event_record(6, w) for w in windows] or [event_record(0)]
        out.append({"name": "pending updates: " + name, "method": "process_pending_update_events", "args": [],
                    "a7": STACK_TOP - 4, "writes": base,
                    "results": {("trap", 0xA970): [0x100] * len(windows) + [0],
                                ("derefs", "trap", 0xA970): {0: ["outseq", records]}}})
    EV = 0x1D400
    for name, up, w in (("game window", 1, win), ("window down", 0, win), ("another window", 1, other)):
        out.append({"name": "update event: " + name, "method": "handle_update_event", "args": [EV],
                    "stack": [(4, EV)], "a7": STACK_TOP - 4 - 4, "results": {},
                    "writes": [[A5 - 0x1548, 4, win], [A5 - 0x1544, 2, up], [EV, 2, 6], [EV + 2, 4, w]]})
    return out


def flight_cases() -> list:
    """run_flight_session_leyte (0x4fc) over whole sessions: every callee a stand-in. Each frame calls
    GetNextEvent once for the keys, then for the other events until it answers false; `frames` gives,
    per frame, the key event (or None) and the other events. TickCount climbs by 4 a call, so each
    frame's four-tick wait passes at once; the key hook (A5 - 0x10ac) answers from `hooks`."""
    ROSTER_H, RECORDS = 0x1D000, 0x1D100
    devices = A5 - 0x13D6

    def case(name, frames, hooks, conclude=(1,), up=1, two=False, status=0, ask=0):
        calls, records = [], []
        for key, others in frames:
            calls.append(0x100 if key else 0)
            records.append(key or event_record(0))
            for e in others:
                calls.append(0x100)
                records.append(e)
            calls.append(0)
            records.append(event_record(0))
        w = [[A5 + k, 4, v] for k, v in FLIGHT_HOOKS.items()]
        w += [[A5 - 0x1426, 4, ROSTER_H], [ROSTER_H, 4, RECORDS], [A5 - 0x1422, 2, 1], [RECORDS + 0x40 + 0x22, 2, 3],
              [RECORDS + 0x40 + 0x20, 2, 0], [A5 - 0x132E, 2, status], [A5 - 0x1544, 2, up], [A5 - 0x1548, 4, 0x1D800],
              [A5 - 0x1352, 2, 1 if two else 0], [A5 - 0x1074, 2, 1 if two else 0], [A5 - 0x1072, 2, ask],
              [A5 - 0x1086, 4, 0], [A5 - 0x13F0, 2, 640], [A5 - 0x13EE, 2, 480], [A5 - 0x1576, 4, 0x00100020],
              [0xBAA, 2, 20], [A5 - 0x107E, 2, 0], [A5 - 0x1368, 2, 1], [A5 - 0x142A, 4, SETTINGS_HANDLE],
              [SETTINGS_HANDLE, 4, SETTINGS], [SETTINGS + 0x10, 2, 2], [A5 - 0x1071, 1, 0], [A5 - 0xE24, 4, 0x1D900]]
        if two:
            w += [[devices, 4, 0x1E000], [devices + 0x12, 4, 0x500000], [devices + 0xE, 2, 0x280], [devices + 0x10, 2, 1],
                  [devices + 0x16, 4, 0], [devices + 0x2C, 4, 0x1E100], [devices + 0x2C + 0x12, 4, 0x600000]]
        return {"name": "flight: " + name, "method": "run_flight_session_leyte", "args": [], "a7": STACK_TOP - 4,
                "writes": w, "results": {("trap", 0xA970): calls, ("derefs", "trap", 0xA970): {0: ["outseq", records]},
                                         ("trap", 0xA975): [4 * k for k in range(1, 600)],
                                         ("addr", FLIGHT_HOOKS[-0x10AC]): hooks, ("jt", 0x10A): list(conclude),
                                         ("addr", FLIGHT_HOOKS[-0x10C8]): [ask]}}
    CMD = 0x100

    def key(c, what=4, mods=CMD):
        return event_record(what, c, modifiers=mods)
    return [
        case("command-Q quits", [(key(0x51), [])], [0]),
        case("hook 2: next mission, then 5", [(None, []), (None, []), (None, [])], [0, 2, 5]),
        case("concluded, answer 1", [(None, [])], [1], conclude=(1,)),
        case("concluded, answer 3: again, then quit", [(None, []), (None, [])], [1, 5], conclude=(3,)),
        case("plain key, two devices: paused, then quit", [(key(0x61, 3, 0), []), (None, [])], [4, 5], two=True),
        case("an update mid-flight, then quit", [(None, [event_record(6, 0x1D800)]), (None, [])], [0, 5]),
        case("first show, T and 3 keys, status written", [(key(0x54), []), (key(0x33), []), (key(0x71), [])], [0, 0, 0],
             up=0, status=2),
        case("the ask hook, an activate, then quit", [(None, [event_record(8, 0x1D800, modifiers=1)]), (None, [])], [0, 0, 5],
             ask=1),
    ]


def quit_cases() -> list:
    """save_changed_settings (changed or not); quit_application after 1.0.3's cases (the confirm JT $9a
    answering 0 or 2; with changed settings the ask JT $132 answering 1 save, 2 cancel, 3 go on; the
    window shown or not; ExitToShell a stand-in that returns); redraw_panel_readouts_locked with both,
    one or neither session handle."""
    out = []
    for changed in (0, 1):
        out.append({"name": "save settings: changed %d" % changed, "method": "save_changed_settings", "args": [],
                    "results": {}, "writes": [[A5 - 0x1408, 2, changed], [A5 - 0x142A, 4, SETTINGS_HANDLE]]})
    for confirm, changed, answer, shown in ((0, 1, 1, 1), (2, 0, 0, 1), (2, 1, 1, 1), (2, 1, 2, 1), (2, 1, 3, 0)):
        out.append({"name": "quit: confirm %d changed %d answer %d shown %d" % (confirm, changed, answer, shown),
                    "method": "quit_application", "args": [], "a7": STACK_TOP - 4,
                    "writes": [[A5 - 0x1408, 2, changed], [A5 - 0x1544, 2, shown], [A5 - 0x1548, 4, 0x1D800],
                               [A5 - 0x142A, 4, SETTINGS_HANDLE]],
                    "results": {("jt", 0x9A): [confirm], ("jt", 0x132): [answer]}})
    for first, second in ((1, 1), (1, 0), (0, 1)):
        out.append({"name": "panel readouts: handles %d %d" % (first, second), "method": "redraw_panel_readouts_locked",
                    "args": [], "a7": STACK_TOP - 4, "results": {},
                    "writes": [[A5 - 0x1270, 4, 0x1D300 if first else 0], [A5 - 0x1256, 4, 0x1D310 if second else 0],
                               [A5 - 0x10B4, 4, READOUTS_HOOK]]})
    return out


def build() -> list:
    a, d = "apply_settings_to_live_options", "apply_display_palettes"
    return [
        {"name": "settings: nothing changed, depth 8", "method": a, "args": [], "writes": settings({}, SAME, 8, True), "results": {}},
        {"name": "settings: the view fields changed", "method": a, "args": [], "writes": settings({0x10: 7}, SAME, 8, True), "results": {}},
        {"name": "settings: +0x16 changed", "method": a, "args": [], "writes": settings({0x16: 3}, SAME, 8, True), "results": {}},
        {"name": "settings: +0x12 changed at depth 8", "method": a, "args": [], "writes": settings({0x12: 9}, SAME, 8, True), "results": {}},
        {"name": "settings: depth 4 clears the depth-8 options", "method": a, "args": [], "writes": settings({0x12: 9}, SAME, 4, True), "results": {}},
        {"name": "settings: everything changed, no overlay image", "method": a, "args": [],
         "writes": settings({0x10: 1, 0x16: 2, 0x12: 3}, SAME, 8, False), "results": {}},
        {"name": "settings: all changed with an image", "method": a, "args": [],
         "writes": settings({0x10: 1, 0x18: 5, 0x16: 2, 0x12: 3, 0x28: 0xABCD, 0x26: 0x1234}, {}, 8, True), "results": {}},
        {"name": "palettes", "method": d, "args": [], "writes": [[A5 - 0x10E4, 4, HOOKS[-0x10E4]]], "results": {}},
        *init_cases(),
        *import_cases(),
        *pilot_cases(),
        *prefs_cases(),
        *scan_cases(),
        *configure_cases(),
        *layout_cases(),
        *palette_map_cases(),
        *colour_check_cases(),
        *menu_cases(),
        *mission_cases(),
        *session_cases(),
        *sound_cases(),
        *update_cases(),
        *flight_cases(),
        *quit_cases(),
        *poll_cases(),
        *redraw_cases(),
        *draw_service_cases(),
        *labs_cases(),
        *strlen_cases(),
        *[{"name": "service table, FreeMem %#x" % free, "method": "install_overlay_service_table", "args": [], "writes": [],
           "results": {("jt", 0x222): [free], ("jt", 0x242): [0x1A000]}} for free in (0x400000, 0x1C2000, 0x1C2001, 0x80000)],
        {"name": "main: overlay loaded, new mission at once", "method": "main", "args": [], "a7": STACK_TOP - 4,
         "writes": [[A5 - 0x1070, 2, 1], [A5 - 0x1048, 4, IMAGE]], "results": {("jt", 0x1FA): [1], ("jt", 0x22A): [0x123456]}},
        {"name": "main: loader returned 0", "method": "main", "args": [], "a7": STACK_TOP - 4,
         "writes": [[A5 - 0x1070, 2, 1], [A5 - 0x1048, 4, IMAGE]], "results": {("jt", 0x1FA): [0], ("jt", 0x22A): [0x1000]}},
        {"name": "main: loaded but no overlay image", "method": "main", "args": [], "a7": STACK_TOP - 4,
         "writes": [[A5 - 0x1070, 2, 1]], "results": {("jt", 0x1FA): [1], ("jt", 0x22A): [0x1000]}},
        {"name": "reset: bss 0x20, block 0x10", "method": "reset_overlay_state", "args": [],
         "writes": overlay(0x10, 8, 0x20, 0x10), "results": {("jt", 0x252): [0x10], ("trap", 0xA975): [0x55AA]}},
        {"name": "reset: no bss, empty block", "method": "reset_overlay_state", "args": [],
         "writes": overlay(0x20, 0, 0, 0), "results": {("jt", 0x252): [0], ("trap", 0xA975): [1]}},
        {"name": "reset: odd sizes", "method": "reset_overlay_state", "args": [],
         "writes": overlay(0x12, 6, 0x13, 0x7), "results": {("jt", 0x252): [7], ("trap", 0xA975): [0x7FFFFFFF]}},
    ]


def labs_cases() -> list:
    """labs (CODE 2 +0x1e, JT $522; C: long): the absolute value (0x80000000 stays itself); D0's low word compared."""
    return [{"name": "labs: %#x" % v, "method": "labs_jt", "args": [v], "stack": [(4, v)], "a7": STACK_TOP - 4 - 4,
             "writes": [], "results": {}} for v in (0, 5, 0xFFFFFFFB, 0x12345, 0xFFFEDCBB, 0x80000000, 0x7FFFFFFF)]


def strlen_cases() -> list:
    """strlen (CODE 2 +0x3e8, JT $56a; C: string): the bytes before the zero; D0's low word compared."""
    out = []
    for text in (b"", b"Mission", b"Lt. Smith was shot down over Leyte Gulf.", bytes(range(1, 200))):
        at = 0x1D000
        w = [[at + k, 1, b] for k, b in enumerate(text)] + [[at + len(text), 1, 0]]
        out.append({"name": "strlen: %d bytes" % len(text), "method": "strlen_jt", "args": [at], "stack": [(4, at)],
                    "a7": STACK_TOP - 4 - 4, "writes": w, "results": {}})
    return out


def draw_service_cases() -> list:
    """The overlay's two drawing services ($3e2 / $432), A5-safe (CurrentA5 0x904 = A5), with the colour helpers
    (set_draw_color_index / set_back_color_index), the notched frame builder (+0x115c) and the beveled box builder
    (+0x1490) running as Leyte's code: the notched frame pair painted / filled, with and without its marker rects (a
    frame wide enough for each divide), at depth 8 and at depth 1 (the pattern slot $1e2); the six-segment indicator
    over a few bit patterns (the rects stepping right and down)."""
    PORT = 0x1D800
    POLYS = {("trap", 0xA8CB): [0x1DA00, 0x1DB00], ("jt", 0x1E2): [0x1DC00] * 12}
    base = [[0x904, 4, A5], [A5 - 0x168A, 4, PORT], [PORT + 0x34, 2, 3], [PORT + 0x36, 2, 5], [A5 - 0x13F8, 2, 8],
            [A5 - 0x1206, 2, 2], [A5 - 0x1204, 2, 4], [A5 - 0x1202, 2, 6], [A5 - 0x120C, 2, 0x150], [A5 - 0x1214, 2, 0x20]]
    frame = [[A5 - 0x1216, 2, 0x30], [A5 - 0x1214, 2, 0x20], [A5 - 0x1212, 2, 0x70], [A5 - 0x1210, 2, 0xB7]]
    colours = [[A5 - 0x1438, 2, 0], [A5 - 0x1436, 2, 0], [A5 - 0x1434, 4, 0x1D900]] + [[0x1D900 + 6 * k + j, 2, 0x1111 * (k + 1) + j] for k in range(8) for j in (0, 2, 4)]
    out = []
    for name, args, extra in (("painted, no marker", (1, 3, 5, 0), []), ("filled, marker", (0, 3, 5, 1), []),
                              ("painted, marker, a narrow frame", (1, 7, 1, 1), [[A5 - 0x1210, 2, 0x2B]]),
                              ("filled, marker, depth 1", (0, 2, 4, 1), [[A5 - 0x13F8, 2, 1]])):
        out.append({"name": "notched frame pair: " + name, "method": "draw_notched_frame_pair_marked_a5safe", "args": list(args),
                    "stack": [(4, v) for v in args], "a7": STACK_TOP - 16 - 4, "writes": base + frame + colours + extra,
                    "results": POLYS})
    box = [[A5 - 0x11D8, 2, 0x40], [A5 - 0x11D6, 2, 0x10], [A5 - 0x11D4, 2, 0x48], [A5 - 0x11D2, 2, 0x20]]
    box += [[A5 - 0x1200 + 8 * k + j, 2, v] for k in range(6) for j, v in ((0, 0x40 + 2 * k), (2, 0x10 + 0xC * k), (4, 0x48 + 2 * k), (6, 0x20 + 0xC * k))]
    for name, bits in (("all clear", 0), ("all set", 0x3F), ("alternating", 0x15), ("a negative argument", -2)):
        out.append({"name": "six-segment indicator: " + name, "method": "draw_six_segment_indicator_a5safe", "args": [bits & 0xFFFFFFFF],
                    "stack": [(4, bits & 0xFFFFFFFF)], "a7": STACK_TOP - 4 - 4, "writes": base + box + colours, "results": POLYS})
    out.append({"name": "six-segment indicator: depth 1", "method": "draw_six_segment_indicator_a5safe", "args": [0x2A],
                "stack": [(4, 0x2A)], "a7": STACK_TOP - 4 - 4, "writes": base + box + colours + [[A5 - 0x13F8, 2, 1]], "results": POLYS})
    return out


def run(c: dict) -> list:
    emu = Emulator(MEMORY_SIZE, str(BINARY), file_delta=0x7FFFFFFF)
    code = BINARY.read_bytes()
    emu.mem[CODE:CODE + len(code)] = code
    id01 = ID01.read_bytes()
    emu.mem[ID01_AT:ID01_AT + len(id01)] = id01
    # CODE 1's three switch helpers behind slots $2a / $32 / $3a (+0x96 / +0xaa / +0xbe), as the loaded
    # jump table has them: JMP abs.l.
    for slot, target in ((0x2A, 0x96), (0x32, 0xAA), (0x3A, 0xBE)):
        emu.write(A5 + slot, 2, 0x4EF9)
        emu.write(A5 + slot + 2, 4, ID01_AT + target)
    # A case of another segment may ask for CODE 2's own routines behind some slots (C library helpers the
    # port computes inline: labs, memset): Leyte's CODE 2 loaded at CODE2_AT, each slot a JMP abs.l there.
    if c.get("code2_slots"):
        code2 = (ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "ID02.bin").read_bytes()
        emu.mem[CODE2_AT:CODE2_AT + len(code2)] = code2
        for slot, target in c["code2_slots"].items():
            emu.write(A5 + int(slot), 2, 0x4EF9)
            emu.write(A5 + int(slot) + 2, 4, CODE2_AT + int(target))
    for a, s, v in c["writes"]:
        emu.write(a, s, v)
    emu.a[5] = A5
    own = {ROUTINES[c["method"]]} | REAL.get(c["method"], set()) | set(c.get("real", []))   # these run: no stand-in at their entry
    install(emu, A5, [["addr", CODE + e[1]] + e[2:] if e[0] == "local" else e for e in logs(c["results"])
                      if not (e[0] == "local" and e[1] in own)])
    poll = emu.stubs.get(CODE + POLL)
    if poll is not None:
        passes = {"n": 0}

        def once(e, _p=poll):
            passes["n"] += 1
            if passes["n"] > 1:
                raise Stop()
            return _p(e)
        emu.stubs[CODE + POLL] = once
    before = bytes(emu.mem)
    c["d0"] = None
    a7 = STACK_TOP
    for size, value in reversed(c.get("stack", [])):
        a7 -= size
        emu.write(a7, size, value)
    try:
        c["d0"] = emu.call(CODE + ROUTINES[c["method"]], [], a7) & 0xFFFF
    except Stop:
        pass
    return [[a, emu.mem[a]] for a in range(MEMORY_SIZE) if not (STACK_GUARD[0] <= a < STACK_GUARD[1]) and emu.mem[a] != before[a]]


def text() -> str:
    cases = []
    for c in build():
        entry = {"name": c["name"], "method": c["method"], "args": c["args"], "writes": c["writes"], "changes": run(c),
                 "call_logs": [["addr" if e[0] == "local" else e[0]] + e[1:6] + [{str(k): v for k, v in e[6].items()}] + e[7:]
                               for e in logs(c["results"])]}
        if "a7" in c:
            entry["a7"] = c["a7"]
        if c["method"] in WORD_RESULT:
            entry["d0"], entry["d0_mask"] = c["d0"], 0xFFFF
        cases.append(entry)
    doc = {"memory_size": MEMORY_SIZE, "a5": A5, "stack_top": STACK_TOP, "stack_guard": list(STACK_GUARD),
           "port_vars": {"event_loop_passes": 1}, "common_writes": [], "cases": cases}
    return json.dumps(doc, separators=(",", ":")) + "\n"


def main() -> int:
    t = text()
    if "--check" in sys.argv:
        ok = FIXTURE.is_file() and FIXTURE.read_text() == t
        print(("PASS" if ok else "FAIL") + " Leyte ID02 reconstructed cases: %s" % FIXTURE.relative_to(ROOT))
        return 0 if ok else 1
    FIXTURE.write_text(t)
    print("wrote %s (%d cases)" % (FIXTURE.relative_to(ROOT), len(json.loads(t)["cases"])))
    return 0


if __name__ == "__main__":
    sys.exit(main())
