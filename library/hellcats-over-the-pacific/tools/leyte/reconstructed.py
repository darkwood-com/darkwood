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
ID03 = "original/leyte/port/ID03_reconstructed.gd"
PORT_FILES = {2: ID02, 3: ID03, 8: ID08}
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
    (2, 0x3D38): (ID02, "start_new_mission"),
    (2, 0x45A): (ID02, "begin_overlay_session"),
    (2, 0x4A4): (ID02, "end_overlay_session"),
    (2, 0x22AC): (ID02, "load_game_sounds"),
    (2, 0x552A): (ID02, "restore_screen_globals"),
    (2, 0x54C8): (ID02, "process_pending_update_events"),
    (2, 0x54FE): (ID02, "handle_update_event"),
    (2, 0x4FC): (ID02, "run_flight_session_leyte"),
    (2, 0x34D8): (ID02, "save_changed_settings"),
    (2, 0x35E2): (ID02, "quit_application"),
    (2, 0xAB4): (ID02, "redraw_panel_readouts_locked"),
    (3, 0x5EB4): (ID03, "prepare_instrument_panel"),
    (3, 0x5BE4): (ID03, "release_panel_bitmaps"),
    (3, 0x5CA6): (ID03, "store_panel_bitmaps_as_resources"),
    (3, 0x741E): (ID03, "remap_panel_colours_identity"),
    (3, 0x6E5E): (ID03, "remap_cached_panel_colours"),
    (3, 0x4304): (ID03, "draw_panel_section_with_numbered_boxes"),
    (3, 0x4B32): (ID03, "draw_split_display_box"),
    (3, 0x4790): (ID03, "draw_percent_scale_gauge"),
    (3, 0x4C42): (ID03, "draw_six_cell_display_box"),
    (8, 0x6): (ID08, "load_overlay_from_data_fork"),
    (8, 0x350): (ID08, "alloc_and_read_resource_payload"),
}
## Evidence by method: CODE 8's two helpers are exercised inside the loader's run (its payload read
## and the error path), not by cases of their own.
COVERED_BY = {"alloc_and_read_resource_payload": "load_overlay_from_data_fork"}


def check() -> list[str]:
    bad = []
    cases = [c for f in ("id02_reconstructed_cases.json", "id03_reconstructed_cases.json")
             for c in json.loads((ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / f).read_text())["cases"]]
    tested = {c["method"] for c in cases} | {"load_overlay_from_data_fork"}
    for (seg, off), (path, method) in ROUTINES.items():
        source = (ROOT / "godot" / path).read_text()
        if not re.search(r"^func %s\(" % method, source, re.M):
            bad.append("%s: %s not defined" % (path, method))
        if COVERED_BY.get(method, method) not in tested:
            bad.append("CODE %d +%#x %s: no emulator cases" % (seg, off, method))
    return bad
