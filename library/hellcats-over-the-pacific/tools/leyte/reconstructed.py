#!/usr/bin/env python3
"""Leyte Gulf's routines written from its own bytes (not generated from a 1.0.3 port): segment and
Leyte offset -> (port file under godot/, method). The CODE 2 ones live in
original/leyte/port/ID02_reconstructed.gd (which extends the generated partial port), the CODE 8
loader in original/leyte/port/ID08.gd. check() proves each entry: the method is defined in its file,
and it has emulator cases on Leyte's own code (CODE 2: tests/leyte/port/fixtures/
id02_reconstructed_cases.json; CODE 8: fixtures/loader_image.json covers load_overlay_from_data_fork).
Used by build_jump_table.py and build_segment_methods.py.
"""
from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
ID02 = "original/leyte/port/ID02_reconstructed.gd"
ID08 = "original/leyte/port/ID08.gd"
## segment -> the port file the runtime instantiates for it when it has reconstructed routines
PORT_FILES = {2: ID02, 8: ID08}
ROUTINES = {
    (2, 0x245A): (ID02, "reset_overlay_state"),
    (2, 0x54B8): (ID02, "apply_display_palettes"),
    (2, 0x2DB2): (ID02, "apply_settings_to_live_options"),
    (2, 0x37D2): (ID02, "main"),
    (2, 0x32D8): (ID02, "initialize_application"),
    (2, 0x1E58): (ID02, "install_overlay_service_table"),
    (2, 0x3082): (ID02, "import_settings_from_resource_file"),
    (2, 0x31D8): (ID02, "convert_imported_pilots"),
    (2, 0x2F42): (ID02, "open_preferences_file"),
    (2, 0x24C8): (ID02, "scan_display_devices"),
    (2, 0x2B48): (ID02, "configure_display_from_main_device"),
    (2, 0x2676): (ID02, "layout_game_window_and_view"),
    (2, 0x370C): (ID02, "poll_and_dispatch_event"),
    (2, 0x5544): (ID02, "redraw_main_window"),
    (2, 0x4E32): (ID02, "rebuild_all_palette_index_maps"),
    (2, 0x502C): (ID02, "build_palette_index_map"),
    (2, 0x53AC): (ID02, "check_device_color_state_changed"),
    (2, 0x2D60): (ID02, "refresh_display_if_changed"),
    (2, 0x38B2): (ID02, "update_menu_items"),
    (8, 0x6): (ID08, "load_overlay_from_data_fork"),
    (8, 0x350): (ID08, "alloc_and_read_resource_payload"),
}
## Evidence by method: CODE 8's two helpers are exercised inside the loader's run (its payload read
## and the error path), not by cases of their own.
COVERED_BY = {"alloc_and_read_resource_payload": "load_overlay_from_data_fork"}


def check() -> list[str]:
    bad = []
    cases = json.loads((ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "id02_reconstructed_cases.json").read_text())["cases"]
    tested = {c["method"] for c in cases} | {"load_overlay_from_data_fork"}
    for (seg, off), (path, method) in ROUTINES.items():
        source = (ROOT / "godot" / path).read_text()
        if not re.search(r"^func %s\(" % method, source, re.M):
            bad.append("%s: %s not defined" % (path, method))
        if COVERED_BY.get(method, method) not in tested:
            bad.append("CODE %d +%#x %s: no emulator cases" % (seg, off, method))
    return bad
