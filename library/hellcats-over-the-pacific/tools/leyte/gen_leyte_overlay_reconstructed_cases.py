#!/usr/bin/env python3
"""Emulator cases for Leyte Gulf's overlay functions written from Leyte's bytes
(godot/original/leyte/port/pacific_conflict_reconstructed.gd), run on Leyte's own overlay image:
godot/tests/leyte/port/fixtures/overlay_reconstructed_cases.json, replayed by
tests/leyte/port/test_leyte_overlay_reconstructed.gd. The routines the function calls are logging
stand-ins at their addresses (tools/original/cases.py log_call: count, then each call's longs), on
both sides. Synthetic memory only.

    python3 tools/leyte/gen_leyte_overlay_reconstructed_cases.py [--check]
"""
from __future__ import annotations

import json
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

OUT = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "overlay_reconstructed_cases.json"
MEMORY_SIZE, STACK_TOP, STACK_GUARD = 0x40000, 0x3F000, [0x3F000 - 0x800, 0x3F040]
HOST = 0x2F000                      # a host world past the image
LOGS = 0x38000                      # call logs, 0x100 bytes each (past the bss and the host world)
ROUTINES = {"leyte_overlay_main_entry": 0x175A, "leyte_overlay_initialise": 0x1EF0,
            "leyte_overlay_rng_init": 0x135FE, "leyte_init_span_table_3ef2": 0x3EF2,
            "leyte_init_span_table_4094": 0x4094, "allocate_session_buffers": 0x150C,
            "unlock_session_buffers": 0x16DC, "unlock_handle_purgeable": 0x149E,
            "reset_span_row_lists": 0x7EAC,
            "update_view_clip_bounds": 0x7F12,
            "lock_session_buffers": 0x15DA, "lock_handle_nopurge": 0x1476,
            "sin_fixed14": 0x23A4, "cos_fixed14": 0x2406,
            "rotate_int3_vecs_by_3ang": 0x31CA, "build_attitude_matrix_3ang": 0x2CE2,
            "finalize_attitude_matrix_3ang": 0x2BE8, "transform_int3_vecs_by_matrix3": 0x3418,
            "begin_flight_for_rank": 0x834C, "update_bomb_indicator": 0xDDE6,
            "update_radar_range_buttons": 0x1BC0E, "update_state_lamp": 0xDE86,
            "update_view_readouts_and_overlays": 0xE3CA, "update_airspeed_needle": 0xDB2C,
            "update_direction_needle": 0xDBF6, "update_climb_needle": 0xDC68,
            "update_tachometer_needle": 0xDCE2, "update_manifold_needle": 0xDD64,
            "update_altimeter_needles": 0xD984, "update_flaps_needle": 0xDFD0, "update_gear_needles": 0xE088,
            "update_ammo_needle": 0xE1DA, "update_fuel_needles": 0xE282,
            "reset_background_rows": 0xC728, "draw_sky_and_ground": 0xC77E,
            "draw_panel_frame_lines": 0xCCF6, "update_radar_scope": 0x1BCEA,
            "copy_rect_depth8": 0xD2AE, "update_heading_tape": 0xD4B2, "redraw_view_readouts": 0xE61E,
            "retire_entity": 0x603A, "dispatch_entity_cleanup": 0x60DC,
            "apply_entity_mission_rates": 0x4FCE, "dispatch_entity_tick": 0x5152, "clear_pending_entity_slots": 0x6C6A,
            "run_simulation_tick": 0x5192, "update_object_camera_metric": 0x47B6, "alloc_object_from_desc": 0x532E,
            "atan_fixed14": 0x24EC, "acos_fixed14": 0x2468, "isqrt_floor": 0xF7C0, "attitude_from_velocity": 0x2554,
            "translate_int3_vecs": 0x2274, "matmul3x3_s16": 0x2326, "compose_template_chain": 0x36FA,
            "apply_template_chain_xform": 0x3524, "update_object_distance_band": 0x49CC,
            "update_record_distance_band": 0x4A3A, "shade_object_faces": 0x4AEC, "assign_face_colour": 0x1BE6,
            "write_face_palette_slot": 0x1B90, "build_object_poly_buffers": 0x561C,
            "approx_dist3_xyz": 0x4886, "build_land_outline_at_detail": 0x577A,
            "transform_vecs12_to_int3_by_matrix3": 0x32A0, "release_fractal_reference": 0x1746C,
            "drop_contact_records_for_object": 0x16116, "leyte_sink_body": 0x1970A,
            "sink_object_shape_at_waterline": 0x19B32, "activate_near_range_object": 0x5C98,
            "instantiate_mission_pool_slot": 0x5450, "fold_angle_pm_0x2000": 0xF85C,
            "spawn_mission_pool_object": 0x5F64, "spawn_cloud_layer": 0x728C,
            "reset_object_to_template_pose": 0x5C30, "deactivate_object": 0x6130, "copy_player_pose_to_camera": 0x83DE,
            "overlay_init_template_pointer_tables": 0x38, "copy_object_vertex_buffer": 0x221C,
            "instantiate_object_from_desc": 0x5408, "overlay_patch_template_u16_pair": 0x0,
            "configure_renderer_for_display": 0x8032, "fill_span_rows_8bit": 0x4294, "draw_line_depth8": 0xE784,
            "restore_line_depth8": 0xECE2, "plot_dot_depth8": 0x19F12, "draw_radar_blip_depth8": 0x1B992,
            "erase_radar_blip_depth8": 0x1B902, "erase_dots_depth8": 0x19E8C,
            "add_face_edges_to_rows": 0xADDC, "output_face_colour": 0xB3D0,
            "fill_span_rows_4bit_a": 0x3D9A,
            "fill_span_rows_4bit_b": 0x3F46, "fill_span_rows_4bit_c": 0x40E8, "draw_line_depth4": 0xE978,
            "restore_line_depth4": 0xEF28, "plot_dot_depth4": 0x1A104, "erase_dots_depth4": 0x19F8C,
            "draw_radar_blip_depth4": 0x1BACA, "erase_radar_blip_depth4": 0x1B9EA, "erase_radar_blip_bw": 0x1BBB6,
            "draw_radar_blip_bw": 0x1BBE2, "clear_dots_bw": 0x1A204,
            "leyte_register_ordnance_templates": 0x1EBE0, "register_shadow_templates": 0x1CA20,
            "register_parachute_template": 0x1D56E, "leyte_register_route_ship_templates": 0x1ED5C,
            "leyte_register_template_2c834": 0x14952, "leyte_register_template_2c830": 0x14974,
            "leyte_register_template_2c82c": 0x14996, "leyte_create_type10_controller": 0x13BB6,
            "init_aircraft_controller": 0xF3B8, "aircraft_create": 0xF4A8, "create_enemy_fighter": 0xF616, "spawn_object_shadow": 0x1CA84,
            "create_shadow_controller": 0x1C9DA,
            "find_anchor_point": 0x11FA, "start_mission_scene": 0x46E2,
            "create_view_attached_controller": 0x1DCE2, "create_cloud_controller": 0x1DB7A, "create_turret_controller": 0x1D21A,
            "create_flak_controller": 0x1CC72, "create_aircraft_spawner_controller": 0x1BEA6,
            "create_carrier_controller": 0x17BA2, "create_sinking_controller": 0x18544, "create_basic_ship_controller": 0x185BA,
            "leyte_create_escort_controller": 0x1862A, "create_firing_ship_controller": 0x18088,
            "leyte_create_route_ship": 0x1EC7E, "leyte_next_waypoint": 0x1EEEE, "leyte_heading_to_point": 0x18712,
            # the frame step's closure (hook 10)
            "transform_int3_vecs_asr_by_1d744": 0x311E, "transform_vecs12_by_matrix3": 0x3200, "scale_int3_vecs": 0x22CA,
            "apply_controller_velocities": 0xA5BE, "tick_flagged_object_controllers": 0xA61A,
            "age_contact_records": 0x160B0, "build_visible_object_list": 0x6754, "emit_faces_in_order": 0xB14E,
            "emit_faces_unnumbered": 0xB220}
## The host world's memory services ensure_handle_allocated calls (world +0x302 create, +0x30a
## reallocate, +0x31a make unpurgeable; 1.0.3: +0x2c6 / +0x2ce / +0x2de) as logging stand-ins at
## SERVICES; everything else runs as it is.
SERVICES = {0x302: (0x3A000, 1), 0x30A: (0x3A100, 2), 0x31A: (0x3A200, 1), 0x312: (0x3A300, 1), 0x316: (0x3A400, 1),
            0x30E: (0x3A500, 1), 0x2D6: (0x3A600, 4),   # +0x2d6: the panel lamp routine
            0x2C6: (0x3A700, 2),                        # +0x2c6: the panel rectangle routine
            0x2DA: (0x3A800, 1),                        # +0x2da: the state lamp routine
            0x2B2: (0x3A900, 6),                        # +0x2b2: the readout text routine
            0x2AA: (0x3AC00, 5),                        # +0x2aa: the host line routine
            0x2B6: (0x3AE00, 9), 0x2FE: (0x3AF00, 0),   # +0x2b6 / +0x2fe: depth-1 sky bands, cursor hook
            0x2D2: (0x3B000, 5), 0x2BA: (0x3B100, 4),   # +0x2d2: the strip copy; +0x2ba port setup,
            0x2BE: (0x3B200, 1), 0x2C2: (0x3B300, 1),
            0x2AE: (0x3B700, 4)}                        # +0x2ae: the host rectangle routine (1-bit blips)   # +0x2be port switch, +0x2c2 port release
## Code pointers the needles reach (draw_gauge_needle: depth routines at 0x23fc8 / 0x23fcc), stood in
## the same way: pointer -> (stand-in, argument count).
CODE_POINTERS = {0x23FC8: (0x3AA00, 8), 0x23FCC: (0x3AB00, 7),
                 0x26FF0: (0x3AD00, 5),                 # the span filler draw_sky_and_ground calls
                 0x23FC0: (0x3B500, 2), 0x23FC4: (0x3B600, 2)}   # the radar's erase / draw blip routines
CALL_LOGS = [[at, LOGS + 0x100 * k, n] for k, (at, n) in enumerate([*SERVICES.values(), *CODE_POINTERS.values()])]
# The readout text routine's text (in the caller's frame, inside the stack guard) is logged through
# its first argument: three longs, the 11-byte buffer and more.
for entry in CALL_LOGS:
    if entry[0] == SERVICES[0x2B2][0]:
        entry += [0, 3]
assert LOGS + 0x100 * len(CALL_LOGS) <= 0x3A000, "the call logs must stay below the stand-in addresses"


def build() -> list:
    return [
        {"name": "entry: host world, -1, ticks", "method": "leyte_overlay_main_entry", "args": [HOST, 0xFFFFFFFF, 0x55AA], "writes": []},
        {"name": "initialise: ticks 0x1234", "method": "leyte_overlay_initialise", "args": [0x1234], "writes": []},
        {"name": "initialise: over earlier state", "method": "leyte_overlay_initialise", "args": [0xFFFFFFFF],
         "writes": [[0x21020, 4, 0x99], [0x23D94, 4, 0x12345678], [0x21574, 2, 1]]},
        {"name": "rng init: seed 0", "method": "leyte_overlay_rng_init", "args": [0], "writes": []},
        {"name": "rng init: seed 0x89abcdef over an old ring", "method": "leyte_overlay_rng_init", "args": [0x89ABCDEF],
         "writes": [[0x2C5B4, 4, 0x2C5B8], [0x2C600, 4, 0x55555555]]},
        {"name": "span table 0x3ef2", "method": "leyte_init_span_table_3ef2", "args": [], "writes": [[0x3ECE, 4, 0xFFFFFFFF]]},
        {"name": "span table 0x4094", "method": "leyte_init_span_table_4094", "args": [], "writes": [[0x4070, 4, 0xFFFFFFFF]]},
        *session_buffer_cases(),
        {"name": "entry: host world over an old one", "method": "leyte_overlay_main_entry", "args": [HOST + 0x100, 0xFFFFFFFF, 7],
         "writes": [[0x207FC, 4, HOST], [HOST + 0x100 + 0x92, 2, 3], [HOST + 0x100 + 0x322, 4, 0x11111111]]},
    ]


def session_buffer_cases() -> list:
    """allocate_session_buffers over a synthetic host world: its three memory services stood in for
    (create answering a fresh handle, MADE, whose master pointer the case sets or leaves 0)."""
    MADE = 0x3B000

    def case(name, entry_flag, depth, multi, handles):
        w = [[0x207FC, 4, HOST], [HOST, 1, entry_flag], [HOST + 0xE, 2, depth], [HOST + 0xC, 2, 0x40], [HOST + 0xB4, 2, multi],
             [0x27184, 4, 0x77], [LOGS - 4, 4, MADE], [MADE, 4, 0]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        for at, h in handles.items():
            w += [[at, 4, h], [h, 4, 0x3C000 + (h & 0xFFF)]]
        return {"name": "session buffers: " + name, "method": "allocate_session_buffers", "args": [], "writes": w}
    return [
        case("first allocation, one screen", 1, 8, 0, {}),
        case("handles in place, two screens", 1, 8, 1,
             {0x20F30: 0x3B100, 0x20F38: 0x3B110, 0x20F3C: 0x3B120, 0x20F40: 0x3B130,
              0x20F48: 0x3B140, 0x20F4C: 0x3B150, 0x20F50: 0x3B160}),
        case("depth 1: the entry handle only", 1, 1, 0, {}),
        case("no entry flag, one screen", 0, 4, 0, {0x20F38: 0x3B200}),
    ] + unlock_cases() + span_reset_cases() + clip_cases() + lock_cases() + trig_cases() + rotation_cases() + flight_rank_cases() + bomb_lamp_cases() + radar_button_cases() + state_lamp_cases() + readout_cases() + needle_cases() + more_needle_cases() + sky_cases() + frame_line_cases() + radar_scope_cases() + heading_tape_cases() + hook12_cases() + entity_retire_cases() + entity_tick_cases() + simulation_tick_cases() + object_alloc_cases() + attitude_cases() + translate_cases() + chain_xform_cases() + band_cases() + shade_cases() + poly_buffer_cases() + land_outline_cases() + contact_cases() + sink_cases() + instantiate_cases() + cloud_cases() + scene_part_cases() + template_table_cases() + mission_part_cases() + hook0_cases() + depth8_cases() + erase_dot_cases() + face_colour_cases() + depth4_cases() + template_factory_cases() + aircraft_cases() + anchor_cases() + mission_factory_cases() + ship_factory_cases() + route_ship_cases() + frame_batch1_cases() + cloud_layer_cases()


def unlock_cases() -> list:
    """unlock_session_buffers (some handles held, data pointers set to markers) and
    unlock_handle_purgeable, the host's unlock / purgeable services stood in for."""
    out = []
    for name, held in (("all held", (0x20F30, 0x20F38, 0x20F3C, 0x20F40, 0x20F48, 0x20F4C, 0x20F50)),
                       ("some held", (0x20F38, 0x20F4C)), ("none held", ())):
        w = [[0x207FC, 4, HOST]] + [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        w += [[a, 4, 0x3B300 + (a & 0xFF)] for a in held]
        w += [[0x20F54 + 4 * k, 4, 0x5A5A5A00 + k] for k in range(12)]
        out.append({"name": "unlock session buffers: " + name, "method": "unlock_session_buffers", "args": [], "writes": w})
    w = [[0x207FC, 4, HOST]] + [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
    out.append({"name": "unlock a handle", "method": "unlock_handle_purgeable", "args": [0x3B400], "writes": w})
    return out


def span_reset_cases() -> list:
    """reset_span_row_lists over slots 1..3 (one missing), every slot and each one, 3 rows (and none)."""
    out = []
    for slot, rows in ((0, 3), (1, 3), (2, 3), (3, 3), (5, 3), (0, 0), (0xFFFFFFFF, 2)):
        w = [[0x20F60, 4, 0x3C000], [0x20F64, 4, 0], [0x20F68, 4, 0x3C100],
             [0x21010, 2, rows], [0x21012, 2, 0x280], [0x23FD0, 2, 0xA5]]
        out.append({"name": "reset span rows: slot %d, %d rows" % (slot, rows), "method": "reset_span_row_lists",
                    "args": [slot], "writes": w})
    return out


def clip_cases() -> list:
    """update_view_clip_bounds (hook 1) over the view modes, the inset flag, odd / negative rows and the
    mode-6 table entry."""
    out = []
    for view, inset, rows, entry in ((0, 1, 0x1E1, 0), (1, 1, 0x1E0, 0), (8, 1, 0x1E0, 0), (2, 0, 0x1E0, 0),
                                     (6, 1, 0x150, 3), (6, 0, -5, 0), (3, 1, 0x10, 0)):
        w = [[0x207FC, 4, HOST], [HOST + 0xA2, 2, inset], [0x23D88, 2, view & 0xFFFF], [0x21010, 2, rows & 0xFFFF],
             [0x21012, 2, 0x280], [0x23FD0, 2, 0x11], [0x20F60, 4, 0x3C000], [0x20F68, 4, 0], [0x20F64, 4, 0x3D000],
             [0x27020, 2, entry]]
        w += [[0x2703C + 2 * k, 2, 0x100 + k] for k in range(8)] + [[0x27024 + 2 * k, 2, 0x200 + k] for k in range(8)]
        w += [[0x23FBC, 2, 0x77], [0x23FBA, 2, 0x66], [0x27010, 2, 9], [0x2700C, 2, 9]]
        out.append({"name": "view clip bounds: mode %d, inset %d, rows %d, entry %d" % (view, inset, rows, entry),
                    "method": "update_view_clip_bounds", "args": [], "writes": w})
    return out


def lock_cases() -> list:
    """lock_session_buffers (hook 9) over the entry flag, the depth, the multi-screen flag and the 24-bit
    mask, the host's lock / no-purge services stood in for; lock_handle_nopurge alone."""
    out = []
    handles = {0x20F30: 0x3B100, 0x20F38: 0x3B110, 0x20F3C: 0x3B120, 0x20F40: 0x3B130,
               0x20F48: 0x3B140, 0x20F4C: 0x3B150, 0x20F50: 0x3B160}
    for name, flag, depth, multi, mask in (("entry, one screen", 1, 8, 0, 0), ("entry, three screens, masked", 1, 8, 1, 1),
                                           ("depth 1", 1, 1, 1, 0), ("no entry flag", 0, 4, 0, 0)):
        w = [[0x207FC, 4, HOST], [HOST, 1, flag], [HOST + 0xE, 2, depth], [HOST + 0xB4, 2, multi], [HOST + 0x76, 2, mask],
             [HOST + 0xA2, 2, 1], [0x23D88, 2, 2], [0x21010, 2, 4], [0x21012, 2, 0x280], [0x23FD0, 2, 0x11]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        for at, h in handles.items():
            w += [[at, 4, h], [h, 4, (0xC3000000 if mask else 0) + 0x3C000 + ((h & 0xFFF) << 4)]]
        out.append({"name": "lock session buffers: " + name, "method": "lock_session_buffers", "args": [], "writes": w})
    w = [[0x207FC, 4, HOST]] + [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
    out.append({"name": "lock a handle", "method": "lock_handle_nopurge", "args": [0x3B400], "writes": w})
    return out


def trig_cases() -> list:
    """sin_fixed14 / cos_fixed14 (hooks 15 / 16) on Leyte's own tables: table points, fractions, the
    wrap at the last entry and negative angles."""
    out = []
    for method in ("sin_fixed14", "cos_fixed14"):
        for angle in (0, 0x40, 0x7F, 0x1000, 0x2345, 0x3FFF, 0xFFFFFFC1, 0xFFFFFF80):
            out.append({"name": "%s 0x%x" % (method, angle), "method": method, "args": [angle], "writes": [], "d0": True})
    return out


def rotation_cases() -> list:
    """rotate_int3_vecs_by_3ang (hook 17) and its tree on Leyte's code: build_attitude_matrix_3ang, Leyte's
    inline finalize (from set sin / cos words) and the transform (count 0's frame-address D0 included)."""
    SRC, DST, MAT = 0x3C000, 0x3C100, 0x3C200
    vecs = [0x12345, -0x6789A, 0x1000, 0x7FFFFFFF, -0x80000000, 0x55, -1, 0, 0x40000]
    vw = [[SRC + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate(vecs)]
    mat = [0x1000, 0, -0x800, 0x7FF, 0xF00, -0x1000, 0x123, -0x456, 0x789]
    mw = [[MAT + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(mat)]
    out = []
    for angles in ((0, 0, 0), (0x400, 0x1000, 0x2000), (0x3FFF, 0x1234, 0x0F0F), (0xFFFFF000, 0x800, 0x3000)):
        for count in (3, 0):
            out.append({"name": "rotate by 3 angles %s, %d vectors" % ([hex(a) for a in angles], count),
                        "method": "rotate_int3_vecs_by_3ang", "args": [*angles, count, SRC, DST], "writes": vw, "d0": True})
    out.append({"name": "build matrix (0x1234, 0x2345, 0x3456)", "method": "build_attitude_matrix_3ang",
                "args": [0x1234, 0x2345, 0x3456, DST], "writes": []})
    for sc in ((0x1000, 0, 0x1000, 0, 0x1000, 0), (0xB50, -0xB50, 0x800, 0xDDB, -0x1000, 0x7FFF),
               (-0x8000, -0x8000, 0x7FFF, -0x8000, 0x7FFF, -0x8000)):
        w = [[0x23D22 + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(sc)]
        out.append({"name": "finalize from sin/cos %s" % [hex(v & 0xFFFF) for v in sc], "method": "finalize_attitude_matrix_3ang",
                    "args": [DST], "writes": w})
    for count in (3, 1, 0):
        out.append({"name": "transform %d vectors" % count, "method": "transform_int3_vecs_by_matrix3",
                    "args": [count, SRC, DST, MAT], "writes": vw + mw, "d0": True})
    return out


def flight_rank_cases() -> list:
    """begin_flight_for_rank (hook 13) over the ranks (Leyte's own table words, and set odd / negative
    ones), host word +0xa4 and the host-words flag; depth 8 (depth 1 reaches hook 3, not yet written for Leyte)."""
    PLAYER = 0x3C000
    out = []
    for rank, a4, valid, table in ((0, 0, 0, None), (3, 1, 1, None), (2, 0, 1, -5), (1, 1, 0, 7)):
        w = [[0x207FC, 4, HOST], [HOST + 0xE, 2, 8], [HOST + 0xA4, 2, a4], [HOST + 0x37A, 2, 0x1234], [HOST + 0x37C, 2, 0x5678],
             [0x20B28, 4, PLAYER], [PLAYER + 0x32, 4, 0xAAAAAAAA], [0x2B270, 2, valid],
             [0x2B268, 2, 0x11], [0x2B26A, 2, 0x22], [0x21042, 2, 0x33]]
        if table is not None:
            w.append([0x204DC + 2 * rank, 2, table & 0xFFFF])
        out.append({"name": "begin flight: rank %d, +0xa4 %d, host words %d, table %s" % (rank, a4, valid, table),
                    "method": "begin_flight_for_rank", "args": [rank], "writes": w})
    return out


def bomb_lamp_cases() -> list:
    """update_bomb_indicator (in hook 12's tree): heights either side of 0x43, bomb counts 0 / 1 / 2 / -1,
    the drawn flag with the same and changed caches, the extra byte +0x690; the lamp routine logged."""
    PLAYER, OBJ = 0x3C000, 0x3C800
    out = []
    for name, y, ground, count, drawn, cache_n, cache_h, extra in (
            ("high, 2 bombs, not drawn", 0x100, 0x10, 2, 0, 0, 0, 3),
            ("low, 1 bomb, drawn, changed", 0x50, 0x10, 1, 1, 2, 0, -1),
            ("high, 0 bombs, drawn, unchanged", 0x100, 0x10, 0, 1, 0, 1, 5),
            ("at 0x43, -1 bombs, drawn, flag changed", 0x53, 0x10, -1, 1, -1, 1, 0x7F)):
        w = [[0x207FC, 4, HOST], [HOST + 0x204, 2, 0x0C0C], [HOST + 0x200, 2, 0xF0F0]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        w += [[0x20B28, 4, PLAYER], [PLAYER, 4, OBJ], [OBJ + 8, 4, y], [PLAYER + 0x2E, 4, ground],
              [PLAYER + 0x68D, 1, count & 0xFF], [PLAYER + 0x690, 1, extra & 0xFF],
              [0x2C596, 2, drawn], [0x2C57A, 2, cache_n & 0xFFFF], [0x2C57C, 2, cache_h]]
        out.append({"name": "bomb lamp: " + name, "method": "update_bomb_indicator", "args": [], "writes": w})
    return out


def radar_button_cases() -> list:
    """update_radar_range_buttons (in hook 12's tree): each range, an off-table range, a negative width
    and radar_changed clear; the rectangle routine logged."""
    out = []
    for name, changed, rng, left, right in (("range 0xce40", 1, 0xCE40, 0x10, 0x50), ("range 0x26ac0", 1, 0x26AC0, 0x10, 0x51),
                                            ("range 0xc15c0", 1, 0xC15C0, 0x20, 0x60), ("off-table, negative width", 1, 0x12345, 0x60, 0x21),
                                            ("unchanged", 0, 0xCE40, 0x10, 0x50)):
        w = [[0x207FC, 4, HOST], [HOST + 0x1E8, 2, right], [HOST + 0x1E4, 2, left], [HOST + 0x1DE, 2, 0x0A0A],
             [HOST + 0x1E0, 2, 0xFFF0], [0x2CC22, 4, changed], [0x2CC1E, 4, rng], [0x2CC18, 2, 0x7777]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        out.append({"name": "radar buttons: " + name, "method": "update_radar_range_buttons", "args": [], "writes": w})
    return out


def state_lamp_cases() -> list:
    """Leyte's update_state_lamp (0xde86, in hook 12's tree): every switch state 0..6 with +0x68f 1 and 0,
    the drawn flag with the same state; the lamp routine logged (states above 6 pass the caller's D1)."""
    PLAYER = 0x3C000
    out = []
    cases = [(s, d, 0, 0x55) for s in range(7) for d in (1, 0)] + [(3, 1, 1, 3), (5, 0, 1, 4)]
    for state, detail, drawn, cache in cases:
        w = [[0x207FC, 4, HOST], [0x20B28, 4, PLAYER], [PLAYER + 0x68E, 1, state], [PLAYER + 0x68F, 1, detail],
             [0x2C596, 2, drawn], [0x2C57E, 2, cache]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        out.append({"name": "state lamp: state %d, detail %d, drawn %d, cache %d" % (state, detail, drawn, cache),
                    "method": "update_state_lamp", "args": [], "writes": w})
    return out


def readout_cases() -> list:
    """update_view_readouts_and_overlays, the readouts path (panels not drawn; the panels path reaches
    routines not yet written for Leyte): the inset-4 reset, plain passes at each index, view 7 (the
    designated entity, readout 3 skipped), negative values, the heading's degree sign, unchanged
    caches; the text routine logged, c_strlen run as Leyte's code."""
    PLAYER, POBJ, DESIG, DOBJ = 0x3C000, 0x3C800, 0x3D000, 0x3D800
    out = []
    for name, view, inset, index, caches, y, speed, heading, climb in (
            ("inset 4 reset, player", 2, 4, 2, None, 12345, 0x2400, 0x4000, 0x12C0),
            ("one pass, altitude", 2, 1, 0, (0, 0, 0, 0), -987, 0, 0, 0),
            ("two passes from heading, negative climb", 2, 2, 2, (1, 1, 1, 1), 50, 0x900, -0x1234, -0x5000),
            ("view 7, designated, climb skipped", 7, 4, 0, None, 30000, 0x7FFF, 0x7FFF, 0x9999),
            ("unchanged cache", 2, 1, 1, (0, 0x40, 0, 0), 0, 0x1200, 0, 0)):
        w = [[0x207FC, 4, HOST], [0x27010, 2, 0], [0x23D88, 2, view], [0x2700E, 2, inset], [0x2C58C, 2, index],
             [0x2C596, 2, 1], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [0x21070, 4, DESIG], [DESIG, 4, DOBJ]]
        for subj, obj in ((PLAYER, POBJ), (DESIG, DOBJ)):
            w += [[obj + 8, 4, y & 0xFFFFFFFF], [subj + 0x62A, 4, speed & 0xFFFFFFFF], [subj + 0x22, 2, heading & 0xFFFF]]
        w.append([0x21468, 4, climb & 0xFFFFFFFF])
        if caches is not None:
            w += [[0x2C58E + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(caches)]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        out.append({"name": "readouts: " + name, "method": "update_view_readouts_and_overlays", "args": [0], "writes": w})
    return out


def needle_cases() -> list:
    """The five simple needles over a synthetic panel (each dial's centre / length words at its Leyte
    offset): through the host line routine (+0x9e set), through the depth routines (readouts not drawn,
    drawn with a changed end, drawn with the same end), over their inputs' ranges."""
    PLAYER, POBJ = 0x3C000, 0x3C800
    DIALS = {0x104: (0x40, 0x50, 0x20), 0x154: (0x90, 0x50, 0x1C), 0x124: (0x40, 0xA0, 0x18),
             0x144: (0x90, 0xA0, 0x24), 0x134: (0xE0, 0xA0, 0x16)}
    inputs = {"update_airspeed_needle": [(0x62A, 4, v) for v in (0, 0xB40, 0x10E0, 0x2000, 0x5000)],
              "update_direction_needle": [(None, 2, v) for v in (0, 0x1000, 0xF000, 0x3FFF)],
              "update_climb_needle": [(None, 4, v) for v in (0, 0x12345, -0x40000)],
              "update_tachometer_needle": [(0x678, 4, v) for v in (0, 0x9000, 0x23A00)],
              "update_manifold_needle": [(0x680, 2, v) for v in (0, 0x1234, -0x200)]}
    globals_ = {"update_direction_needle": 0x23D3E, "update_climb_needle": 0x21468}
    out = []
    for method, rows in inputs.items():
        for n, (field, size, value) in enumerate(rows):
            for mode, line, drawn in (("line routine", 1, 0), ("depth, not drawn", 0, 0), ("depth, drawn", 0, 1)):
                if mode != "line routine" and n > 0 and mode != "depth, drawn":
                    continue
                w = [[0x207FC, 4, HOST], [HOST + 0x9E, 2, line], [HOST + 0xE8, 2, 0x33], [HOST + 6, 4, 0x11223344],
                     [HOST + 0x10, 2, 0x280], [HOST + 0xE0, 4, 0x50000], [HOST + 0xE4, 2, 0x10], [0x21564, 4, 0x100],
                     [0x2C596, 2, drawn], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ]]
                for off, (cx, cy, length) in DIALS.items():
                    w += [[HOST + off, 2, cx], [HOST + off + 2, 2, cy], [HOST + off + 4, 2, length]]
                w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
                w += [[ptr, 4, at] for ptr, (at, _n) in CODE_POINTERS.items()]
                target = globals_[method] if field is None else PLAYER + field
                w.append([target, size, value & ((1 << (8 * size)) - 1)])
                out.append({"name": "%s: %s = %#x, %s" % (method, "input", value, mode), "method": method, "args": [], "writes": w})
    return out


def more_needle_cases() -> list:
    """The altimeter, flaps, gear, ammo and fuel needles over a synthetic panel: each through the host line
    routine and through the depth routines, readouts not drawn and drawn (a changed value, and the cached
    one, which returns early), over their inputs (negative flaps / gear, the altimeter's two hands, both
    tanks against the capacity record)."""
    PLAYER, POBJ, DEF = 0x3C000, 0x3C800, 0x3CC00
    DIALS = {0xF4: (0x30, 0x30, 0x20), 0x246: (0x60, 0x30, 0x14), 0x24C: (0x90, 0x30, 0x12), 0x252: (0xB0, 0x30, 0x12),
             0x258: (0xD0, 0x30, 0x12), 0x25E: (0x60, 0x70, 0x18), 0x264: (0x90, 0x70, 0x18), 0x26A: (0xC0, 0x70, 0x16)}
    # method -> rows of [(address, size, value), ...] and the cache words a drawn, unchanged case presets
    rows = {"update_altimeter_needles": [[(0x23D64, 4, y)] for y in (0, 12345, 99999, -5000, 0x7FFFFFFF)],
            "update_flaps_needle": [[(PLAYER + 0x68C, 1, v)] for v in (0, 7, 15, -9)],
            "update_gear_needles": [[(PLAYER + 0x68A, 1, v)] for v in (0, 0x2D, -0x10, 0x7F)],
            "update_ammo_needle": [[(PLAYER + 0x696, 2, v)] for v in (0, 0x2A3, 0x100, 0xFFFF)],
            "update_fuel_needles": [[(PLAYER + 0x691, 1, a), (PLAYER + 0x692, 1, b), (DEF + 0x1C, 2, cap)]
                                    for a, b, cap in ((0, 0, 0x64), (0x30, 0x10, 0x64), (-3, 0x7F, 0xC9), (5, 5, -0x41))]}
    caches = {"update_flaps_needle": [(0x2C582, 2, 7)], "update_gear_needles": [(0x2C584, 2, 0x2D)],
              "update_ammo_needle": [(0x2C586, 2, 0x2A3)], "update_fuel_needles": [(0x2C588, 2, 0x30), (0x2C58A, 2, 0x10)]}
    out = []
    for method, inputs in rows.items():
        for n, writes in enumerate(inputs):
            modes = [("line routine", 1, 0, False), ("depth, drawn", 0, 1, False)]
            if n == 0:
                modes.append(("depth, not drawn", 0, 0, False))
            if n == 1 and method in caches:
                modes.append(("drawn, cached value", 0, 1, True))
            for mode, line, drawn, cached in modes:
                w = [[0x207FC, 4, HOST], [HOST + 0x9E, 2, line], [HOST + 0xE8, 2, 0x33], [HOST + 6, 4, 0x11223344],
                     [HOST + 0x10, 2, 0x280], [HOST + 0xE0, 4, 0x50000], [HOST + 0xE4, 2, 0x10], [0x21564, 4, 0x100],
                     [0x2C596, 2, drawn], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [PLAYER + 4, 4, DEF],
                     [0x2C572, 4, 0x1111], [0x2C576, 4, 0x2222]]
                for off, (cx, cy, length) in DIALS.items():
                    w += [[HOST + off, 2, cx], [HOST + off + 2, 2, cy], [HOST + off + 4, 2, length]]
                w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
                w += [[ptr, 4, at] for ptr, (at, _n) in CODE_POINTERS.items()]
                w += [[a, s, v & ((1 << (8 * s)) - 1)] for a, s, v in writes]
                if cached:
                    w += [[a, s, v] for a, s, v in caches[method]]
                out.append({"name": "%s: row %d, %s" % (method, n, mode), "method": method, "args": [], "writes": w})
    return out


def sky_cases() -> list:
    """reset_background_rows, and draw_sky_and_ground with its row buffers in place and the cached view
    geometry equal to the host's (no heap work): the colour path through the span filler (panels drawn
    and not), the depth-1 path through the host band routine (even and odd frames, with and without the
    cursor hook), each horizon order and the all-sky / all-ground corners."""
    NEXT, CUR, EXT = 0x3D000, 0x3D400, 0x3D800
    ROWS = 6
    # host +0x18a .. +0x19d, cached at 0x2c4cc ..: words +0x18a, left +0x18c, +0x18e, rows +0x190, ground /
    # sky colours +0x192 / +0x194, +0x196 / +0x198, then the row-extents pointer (long +0x19a -> 0x2c4dc)
    geometry = [0x0010, 0x0008, 0x00F0, ROWS, 0x5555, 0x6666, 0x0004, 0x0000, EXT >> 16, EXT & 0xFFFF]
    out = []
    for rows in (3, 0):
        w = [[0x207FC, 4, HOST], [HOST + 0x190, 2, rows], [0x27004, 4, CUR], [0x21012, 2, 0x140], [0x23FD0, 2, 0x2A]]
        out.append({"name": "reset background rows: %d" % rows, "method": "reset_background_rows", "args": [], "writes": w})
    out.append({"name": "reset background rows: no buffer", "method": "reset_background_rows", "args": [],
                "writes": [[0x207FC, 4, HOST], [HOST + 0x190, 2, 3], [0x27004, 4, 0]]})
    for name, depth, panels, frame, hide, corners, y0, y1 in (
            ("colour, falling horizon", 8, 1, 0, 0, 5, 30, 10), ("colour, rising horizon", 8, 1, 0, 0, 6, 10, 30),
            ("colour, level horizon", 8, 1, 0, 0, 9, 20, 20), ("colour, all sky", 8, 1, 0, 0, 0xF, 10, 30),
            ("colour, all ground", 8, 1, 0, 0, 0, 30, 10), ("colour, panels not drawn", 8, 0, 0, 0, 5, 30, 10),
            ("depth 1, even frame, cursor hook", 1, 1, 2, 1, 5, 30, 10), ("depth 1, odd frame", 1, 1, 3, 0, 6, 10, 30),
            ("depth 1, panels not drawn", 1, 0, 0, 0, 5, 30, 10)):
        w = [[0x207FC, 4, HOST], [0x27008, 4, NEXT], [0x27004, 4, CUR], [0x2100E, 2, depth], [0x27010, 2, panels],
             [0x2C4E0, 2, frame], [0x2C4DC, 4, EXT], [0x27052, 2, 0x50], [0x27054, 2, 0x30], [0x21010, 2, 0x60],
             [0x21012, 2, 0x140], [0x23FD0, 2, 0x2A], [0x21578, 4, corners], [0x21588, 4, (-0x20) & 0xFFFFFFFF], [0x21584, 4, y0 - 0x30 & 0xFFFFFFFF],
             [0x21580, 4, 0x18], [0x2157C, 4, y1 - 0x30 & 0xFFFFFFFF],
             [HOST + 0x10, 2, 0x50], [HOST + 6, 4, 0x60000], [HOST + 0xE0, 4, 0x70000], [HOST + 0xE4, 2, 0x28], [HOST + 0xE6, 2, 2]]
        for k, v in enumerate(geometry):
            w += [[HOST + 0x18A + 2 * k, 2, v], [0x2C4CC + 2 * k, 2, v]]
        for r in range(ROWS):
            w += [[EXT + 4 * r, 2, 0x10 + r], [EXT + 4 * r + 2, 2, 0x100 - r]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        w += [[ptr, 4, at] for ptr, (at, _n) in CODE_POINTERS.items()]
        out.append({"name": "sky and ground: " + name, "method": "draw_sky_and_ground", "args": [hide], "writes": w})
    return out


def frame_line_cases() -> list:
    """draw_panel_frame_lines: through the host line routine (and its depth-1 return), into the screen at
    depths 4 and 8 (an odd row stride, a negative stride, an odd width), and a depth that draws nothing."""
    SCREEN = 0x3D000
    out = []
    for name, line, depth, stride, right2 in (("line routine, depth 8", 1, 8, 0x50, 0x60), ("line routine, depth 1", 1, 1, 0x50, 0x60),
                                              ("screen, depth 4", 0, 4, 0x28, 0x60), ("screen, depth 8", 0, 8, 0x51, 0x60),
                                              ("screen, depth 8, negative stride, odd width", 0, 8, -0x50, 0x47),
                                              ("screen, depth 2", 0, 2, 0x50, 0x60)):
        w = [[0x207FC, 4, HOST], [HOST + 0x9E, 2, line], [0x2100E, 2, depth], [HOST + 0x10, 2, stride & 0xFFFF],
             [HOST + 0x294, 2, 0x00A7]]
        words = {0x270: 0x10, 0x272: 0x16, 0x274: 0x20, 0x276: 0x23, 0x278: 0x30, 0x27A: 0x08, 0x27C: 0x11,
                 0x27E: 0x40, 0x280: right2, 0x282: 0x50}
        w += [[HOST + off, 2, v] for off, v in words.items()]
        w += [[HOST + 0x284, 4, SCREEN + 0x100], [HOST + 0x288, 4, SCREEN + 0x400], [HOST + 0x28C, 4, SCREEN + 0x800],
              [HOST + 0x290, 4, SCREEN + 0xA00]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        out.append({"name": "frame lines: " + name, "method": "draw_panel_frame_lines", "args": [], "writes": w})
    return out


def radar_scope_cases() -> list:
    """update_radar_scope: a colour screen (last frame's drawn blips erased, the tracks drawn through the
    blip routine) and the line-routine screen, tracks inside and on each side of the scope, a negative
    offset, a turned heading, empty lists; the list swap and counts."""
    PLAYER, POBJ, CUR, PREV = 0x3C000, 0x3C800, 0x3D000, 0x3D400
    tracks = [(0x10000, 0x20000), (0x10000 + 0x8000, 0x20000), (0x10000 - 0x9000, 0x20000 + 0x3000),
              (0x10000, 0x20000 - 0x40000), (0x10000 + 0x50, 0x20000 + 0x50)]
    prev = [(0x55, 0x66, 1), (0x77, 0x88, 0), (0x99, 0xAA, 1)]
    out = []
    for name, line, heading, ntracks, nprev in (("colour, heading 0", 0, 0, 5, 3), ("colour, turned heading", 0, 0x1234, 5, 3),
                                                ("line routine", 1, 0x0800, 5, 3), ("empty lists", 0, 0, 0, 0)):
        w = [[0x207FC, 4, HOST], [HOST + 0x9E, 2, line], [HOST + 0x1EC, 2, 0x60], [HOST + 0x1EE, 2, 0x40],
             [HOST + 0x1E4, 2, 0x40], [HOST + 0x1E8, 2, 0x80], [HOST + 0x1E2, 2, 0x20], [HOST + 0x1E6, 2, 0x60],
             [HOST + 0xE8, 2, 0x37], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [POBJ + 4, 4, 0x10000], [POBJ + 0xC, 4, 0x20000],
             [0x23D3E, 2, heading], [0x2CC18, 2, 0x400], [0x20744, 4, CUR], [0x20748, 4, PREV],
             [0x2CC2A, 4, ntracks], [0x2CC26, 4, nprev]]
        for k, (x, z) in enumerate(tracks[:ntracks]):
            w += [[CUR + 0x14 * k, 4, x & 0xFFFFFFFF], [CUR + 0x14 * k + 4, 4, z & 0xFFFFFFFF], [CUR + 0x14 * k + 0x10, 4, 7]]
        for k, (x, y, d) in enumerate(prev[:nprev]):
            w += [[PREV + 0x14 * k, 4, x], [PREV + 0x14 * k + 4, 4, y], [PREV + 0x14 * k + 0x10, 4, d]]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        w += [[ptr, 4, at] for ptr, (at, _n) in CODE_POINTERS.items()]
        out.append({"name": "radar scope: " + name, "method": "update_radar_scope", "args": [], "writes": w})
    return out


def heading_tape_cases() -> list:
    """copy_rect_depth8 at each source misalignment (and a short, unaligned destination), and
    update_heading_tape: depth 8 with the panels drawn (the marked-column walk through 0xd2ae, wrapping at
    the period), depth 4 through the host copy (whole and split at the period), an unchanged position, the
    panels not drawn (the offscreen port and rect fill, with and without the cursor hook)."""
    STRIP, MARKS, SCREEN, PANEL = 0x3C000, 0x3C800, 0x3D000, 0x3D800
    out = []
    for name, dst, src, count, height in (("aligned", 0x3D000, 0x3C000, 12, 3), ("source +1", 0x3D000, 0x3C001, 13, 2),
                                          ("source +2", 0x3D000, 0x3C002, 9, 2), ("source +3", 0x3D000, 0x3C003, 16, 1),
                                          ("destination +3, short", 0x3D003, 0x3C000, 2, 2)):
        w = [[0x3C000 + k, 1, (k * 7 + 3) & 0xFF] for k in range(0x60)]
        out.append({"name": "copy_rect_depth8: " + name, "method": "copy_rect_depth8",
                    "args": [dst, src, count, height, 0x20, 0x40], "writes": w})
    PERIOD, COLS, HEIGHT = 0x40, 0x10, 3
    for name, depth, panels, heading, cached, hide, bias in (
            ("depth 8, panels drawn", 8, 1, 0x0400, 5, 0, 0), ("depth 8, wraps at the period", 8, 1, 0x3E00, 0x3A, 0, 0x30),
            ("depth 4, host copy", 4, 1, 0x0400, 5, 1, 0), ("depth 4, split at the period", 4, 1, 0x3E00, 5, 0, 0x30),
            ("unchanged position", 8, 1, 0, None, 0, 0), ("depth 8, panels not drawn, cursor hook", 8, 0, 0x0800, 0x3F, 1, 0),
            ("depth 4, panels not drawn", 4, 0, 0x0800, 5, 0, 0), ("depth 4, panels not drawn, unchanged", 4, 0, 0, None, 1, 0)):
        w = [[0x207FC, 4, HOST], [0x2100E, 2, depth], [0x27010, 2, panels], [0x23D3E, 2, heading],
             [HOST + 0x1B4, 4, STRIP], [HOST + 0x1BC, 4, MARKS], [HOST + 0x1A8, 2, PERIOD], [HOST + 0x1A2, 2, COLS],
             [HOST + 0x1A4, 2, HEIGHT], [HOST + 0x1AE, 2, 0x2040], [HOST + 0x19E, 2, 2], [HOST + 0x1A0, 2, 8],
             [HOST + 0x1AC, 2, bias], [HOST + 0x1AA, 2, HEIGHT], [HOST + 0x1C0, 4, 0x44440000],
             [HOST + 6, 4, SCREEN], [HOST + 0x10, 2, 0x50], [HOST + 0xE0, 4, PANEL], [HOST + 0xE4, 2, 0x48], [HOST + 0xE6, 2, 1],
             [HOST + 0x294, 2, 0x0033], [HOST + 0x16, 2, 0x200], [HOST + 0x18, 2, 0x100]]
        w += [[STRIP + k, 1, (k * 5 + 1) & 0xFF] for k in range(0x40 * 2)]
        w += [[MARKS + k, 1, 1 if k % 5 in (1, 2) else 0] for k in range(PERIOD)]
        if cached is not None:
            w.append([0x2C598, 2, cached])
        else:
            # the position the tape computes for heading 0: -(COLS / 2) + bias, raised by the period
            w.append([0x2C598, 2, (PERIOD - COLS // 2 + bias) % PERIOD])
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        w += [[ptr, 4, at] for ptr, (at, _n) in CODE_POINTERS.items()]
        out.append({"name": "heading tape: " + name, "method": "update_heading_tape", "args": [hide], "writes": w})
    return out


def hook12_cases() -> list:
    """Hook 12 (redraw_view_readouts) end to end: the readouts path (panels not drawn), and a whole panel
    frame through the host line routine - frame lines, the eleven needles, the radar scope, then the
    throttle, radar buttons, bomb lamp, state lamp and the overlays-drawn mark."""
    PLAYER, POBJ, DEF, CUR, PREV = 0x3C000, 0x3C800, 0x3CC00, 0x3D000, 0x3D400
    out = []
    for name, panels in (("readouts", 0), ("panels through the line routine", 1)):
        w = [[0x207FC, 4, HOST], [0x27010, 2, panels], [HOST + 0x9E, 2, 1], [0x2100E, 2, 8], [0x23D88, 2, 2],
             [0x2700E, 2, 4], [0x2C58C, 2, 0], [0x2C596, 2, 0], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ],
             [PLAYER + 4, 4, DEF], [DEF + 0x1C, 2, 0x64], [POBJ + 8, 4, 2500], [POBJ + 4, 4, 0x10000], [POBJ + 0xC, 4, 0x20000],
             [PLAYER + 0x62A, 4, 0x900], [PLAYER + 0x22, 2, 0x1000], [0x21468, 4, 0x3000], [0x23D64, 4, 2500],
             [0x23D3E, 2, 0x0400], [PLAYER + 0x678, 4, 0x9000], [PLAYER + 0x680, 2, 0x1234], [PLAYER + 0x68C, 1, 7],
             [PLAYER + 0x68A, 1, 0x20], [PLAYER + 0x696, 2, 0x100], [PLAYER + 0x691, 1, 0x30], [PLAYER + 0x692, 1, 0x20],
             [PLAYER + 0x68D, 1, 2], [PLAYER + 0x690, 1, 1], [PLAYER + 0x68E, 1, 3], [PLAYER + 0x68F, 1, 1],
             [PLAYER + 0x2E, 4, 0], [HOST + 0xE8, 2, 0x33], [HOST + 0x294, 2, 0x11],
             [0x2CC18, 2, 0x400], [0x20744, 4, CUR], [0x20748, 4, PREV], [0x2CC2A, 4, 1], [0x2CC26, 4, 0],
             [CUR, 4, 0x10100], [CUR + 4, 4, 0x20100], [HOST + 0x1EC, 2, 0x60], [HOST + 0x1EE, 2, 0x40],
             [HOST + 0x1E4, 2, 0x40], [HOST + 0x1E8, 2, 0x80], [HOST + 0x1E2, 2, 0x20], [HOST + 0x1E6, 2, 0x60],
             [0x2CC22, 4, 1], [0x2CC1E, 4, 0xCE40], [HOST + 0x1DE, 2, 1], [HOST + 0x1E0, 2, 2],
             [HOST + 0x204, 2, 0x0C0C], [HOST + 0x200, 2, 0xF0F0], [HOST + 0x202 + 0x38, 4, 0x00100010],
             [HOST + 0x206 + 0x38, 4, 0x00200040]]
        dials = {0xF4: (0x30, 0x30, 0x20), 0x104: (0x40, 0x50, 0x20), 0x154: (0x90, 0x50, 0x1C), 0x124: (0x40, 0xA0, 0x18),
                 0x144: (0x90, 0xA0, 0x24), 0x134: (0xE0, 0xA0, 0x16), 0x246: (0x60, 0x30, 0x14), 0x24C: (0x90, 0x30, 0x12),
                 0x252: (0xB0, 0x30, 0x12), 0x258: (0xD0, 0x30, 0x12), 0x25E: (0x60, 0x70, 0x18), 0x264: (0x90, 0x70, 0x18),
                 0x26A: (0xC0, 0x70, 0x16)}
        for off, (cx, cy, length) in dials.items():
            w += [[HOST + off, 2, cx], [HOST + off + 2, 2, cy], [HOST + off + 4, 2, length]]
        words = {0x270: 0x10, 0x272: 0x16, 0x274: 0x20, 0x276: 0x23, 0x278: 0x30, 0x27A: 0x08, 0x27C: 0x11,
                 0x27E: 0x40, 0x280: 0x60, 0x282: 0x50}
        w += [[HOST + off, 2, v] for off, v in words.items()]
        w += [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
        w += [[ptr, 4, at] for ptr, (at, _n) in CODE_POINTERS.items()]
        out.append({"name": "hook 12: " + name, "method": "redraw_view_readouts", "args": [], "writes": w})
    return out


def entity_retire_cases() -> list:
    """retire_entity and dispatch_entity_cleanup on Leyte's entity layout: equal / differing child indices,
    the child-table guard (entry holding the object or another), the window and the scatter-slot scrub, the
    shot controller, no controller; cleanup already retired, stamped (a shape outside the window) and
    retired (inside the window, or not a shape). D0 is recorded where the code defines it (not when it is
    the caller's)."""
    OBJ, CTL, ROOT, TABLE, OTHER = 0x3C000, 0x3C400, 0x3C800, 0x3CC00, 0x3D000
    out = []

    def world(i10, i12, ctl, flags, typ, parent, entry_holds, shot, slots):
        w = [[OBJ, 4, ctl], [OBJ + 4, 4, 0x1234], [OBJ + 0x10, 2, i10], [OBJ + 0x12, 2, i12], [OBJ + 0x14, 4, flags],
             [OBJ + 0x24, 4, parent], [OBJ + 0x28, 1, typ], [OBJ + 0x3E, 4, 0x5555], [CTL, 4, 0x7777],
             [0x23C2C, 4, ROOT], [ROOT + 0x32, 4, TABLE], [0x2107A, 2, 3], [0x21078, 2, 9], [0x21068, 4, shot],
             [0x2B22C, 4, 41], [0x21020, 4, 0xABCD]]
        w.append([TABLE + 0x14 + i12 * 0x18, 4, OBJ if entry_holds else OTHER])
        w += [[0x2144C + 4 * k, 4, OBJ if k in slots else OTHER + k] for k in range(7)]
        return w

    for name, i10, i12, ctl, entry_holds, shot, slots, defined in (
            ("equal indices", 5, 5, CTL, True, 0, (), False),
            ("indices differ, entry holds it, in window, scrub", 5, 2, CTL, True, CTL, (1, 4), True),
            ("indices differ, entry holds another", 5, 2, CTL, False, 0, (), True),
            ("indices differ, outside the window", 0x20, 1, 0, True, 0, (0,), True),
            ("indices differ, word index above 0x7fff", 0x9000, 1, CTL, True, 0, (), True)):
        out.append({"name": "retire entity: " + name, "method": "retire_entity", "args": [OBJ],
                    "writes": world(i10, i12, ctl, 0, 2, ROOT, entry_holds, shot, slots), "d0": defined})
    for name, i10, flags, typ, parent, defined in (
            ("already retired", 5, 2, 2, ROOT, False), ("shape outside the window: stamped", 0x20, 0, 2, ROOT, True),
            ("shape in the window: retired", 5, 0, 2, ROOT, True), ("not a shape, other parent: retired", 5, 0, 1, OTHER, True),
            ("shape, other parent: stamped", 5, 0, 2, OTHER, False)):
        out.append({"name": "entity cleanup: " + name, "method": "dispatch_entity_cleanup", "args": [OBJ],
                    "writes": world(i10, 1, CTL, flags, typ, parent, True, 0, (2,)), "d0": defined})
    return out


def entity_tick_cases() -> list:
    """apply_entity_mission_rates (each phase, parked, scale 0 halving, the scale bit, Leyte's 0..999999999
    hold for a negative and an over-large product, the +0x16 bit 4 floor, the shape clamp, the face pass for
    the player and for others, path 1), dispatch_entity_tick (the type-8 hide / show) and
    clear_pending_entity_slots (full and empty slots through dispatch_entity_cleanup)."""
    ENT, PARENT, LIST, BASE, FACES, CTL, CDEF = 0x3C000, 0x3C400, 0x3C800, 0x3D000, 0x3D800, 0x3CC00, 0x3CE00
    REC_OFF, IDX = 0x40, 2
    REC = BASE + REC_OFF
    out = []

    def world(phase, flags, rate, typ, rec_rate, floor_base=0, ctl=0, ctl_type=0, faces=3, b0=1, player=False):
        w = [[0x207FC, 4, HOST], [HOST + 0xA8, 2, phase & 0xFFFF], [HOST + 0x8C, 4, BASE], [HOST + 0xB0, 2, b0],
             [ENT, 4, ctl], [ENT + 0x10, 2, IDX], [ENT + 0x14, 4, flags], [ENT + 0x18, 4, rate & 0xFFFFFFFF],
             [ENT + 0x20, 4, floor_base & 0xFFFFFFFF], [ENT + 0x24, 4, PARENT], [ENT + 0x28, 1, typ],
             [ENT + 0x3C, 2, faces], [ENT + 0x4A, 4, FACES], [PARENT + 0x2A, 4, LIST],
             [LIST + 0x1A + 0x14 + 0x18 * IDX, 4, REC_OFF], [REC + 0xE, 4, rec_rate & 0xFFFFFFFF],
             [REC + 0x1A, 2, 1], [REC + 0x1E, 2, 2], [CTL + 4, 4, CDEF], [CDEF, 2, ctl_type],
             [0x20B28, 4, CTL if player else 0x3F00]]
        face = REC + 0x22 + 0xC * 3
        for k in range(faces):
            nb = k + 1
            w += [[face + 8, 1, nb], [face + 0xA, 4, (0x1000 * (k + 1) - 0x2800) & 0xFFFFFFFF]]
            face += 0xE + 2 * nb
        return w

    for name, kw, path in (
            ("phase 3, shape faces", dict(phase=3, flags=0, rate=5, typ=2, rec_rate=0x100000), 0),
            ("phase 1: halved", dict(phase=1, flags=0, rate=5, typ=2, rec_rate=-0x1235), 0),
            ("phase 0: nothing", dict(phase=0, flags=0, rate=5, typ=2, rec_rate=0x100), 0),
            ("phase 6: nothing", dict(phase=6, flags=0, rate=5, typ=2, rec_rate=0x100), 0),
            ("flagged: nothing", dict(phase=3, flags=0x00000002, rate=5, typ=2, rec_rate=0x100), 0),
            ("scale bit", dict(phase=5, flags=0x00020000, rate=5, typ=1, rec_rate=0x777), 0),
            ("parked rate", dict(phase=4, flags=0, rate=1000000000, typ=2, rec_rate=0x100), 0),
            ("negative product held", dict(phase=4, flags=0, rate=5, typ=1, rec_rate=-0x10), 0),
            ("over-large product held", dict(phase=5, flags=0, rate=5, typ=1, rec_rate=0x20000000), 0),
            ("floor raises", dict(phase=2, flags=0x00001000, rate=5, typ=2, rec_rate=0x10, floor_base=0x500000), 0),
            ("floor below the rate", dict(phase=2, flags=0x00001000, rate=5, typ=2, rec_rate=0x600000, floor_base=0x10), 0),
            ("shape clamp", dict(phase=4, flags=0, rate=5, typ=2, rec_rate=0x200000), 0),
            ("player faces", dict(phase=3, flags=0, rate=5, typ=2, rec_rate=0x10, ctl=CTL, player=True), 0),
            ("path 1: no faces", dict(phase=3, flags=0, rate=5, typ=2, rec_rate=0x10), 1)):
        out.append({"name": "mission rates: " + name, "method": "apply_entity_mission_rates", "args": [ENT, path],
                    "writes": world(**kw)})
    for name, ctl, ctl_type, b0 in (("type 8 hidden", CTL, 8, 0), ("type 8 shown", CTL, 8, 1), ("type 5 untouched", CTL, 5, 0),
                                    ("no controller", 0, 8, 0)):
        out.append({"name": "entity tick: " + name, "method": "dispatch_entity_tick", "args": [ENT, 0],
                    "writes": world(phase=3, flags=0x40, rate=5, typ=1, rec_rate=0x10, ctl=ctl, ctl_type=ctl_type, b0=b0)})
    for name, slots in (("three slots", (0, 3, 6)), ("no slots", ())):
        w = world(phase=3, flags=0, rate=5, typ=1, rec_rate=0x10)
        w += [[0x23C2C, 4, 0x3EF00], [0x2107A, 2, 0], [0x21078, 2, 9], [0x2B22C, 4, 0], [0x21020, 4, 0x99]]
        objs = []
        for k in range(7):
            obj = 0x3D200 + 0x40 * k if k in slots else 0
            w.append([0x2144C + 4 * k, 4, obj])
            if obj:
                w += [[obj + 0x10, 2, k], [obj + 0x12, 2, k], [obj + 0x28, 1, 1], [obj + 0x24, 4, 0]]
        out.append({"name": "pending slots: " + name, "method": "clear_pending_entity_slots", "args": [], "writes": w})
    return out


def simulation_tick_cases() -> list:
    """Hook 4 (run_simulation_tick): the four arrays (path 0 and path 1) through dispatch_entity_tick over
    two entities each, the pending slots cleaned without host +0xac, kept with it, phases outside 1..5."""
    BASE, PARENT, LIST, CTL, CDEF = 0x3D000, 0x3C400, 0x3C800, 0x3CC00, 0x3CE00
    out = []
    for name, phase, ac, slot in (("phase 3, slots cleaned", 3, 0, True), ("phase 5, host +0xac keeps the slots", 5, 1, True),
                                  ("phase 0", 0, 0, False), ("phase 6", 6, 0, True)):
        w = [[0x207FC, 4, HOST], [HOST + 0xA8, 2, phase], [HOST + 0xAC, 2, ac], [HOST + 0x8C, 4, BASE], [HOST + 0xB0, 2, 0],
             [PARENT + 0x2A, 4, LIST], [CTL + 4, 4, CDEF], [CDEF, 2, 8], [0x20B28, 4, 0x3F00],
             [0x23C2C, 4, 0x3EF00], [0x2107A, 2, 0], [0x21078, 2, 9], [0x2B22C, 4, 0], [0x21020, 4, 0x99]]
        arrays = ((0x20B44, 0x20F2C), (0x20804, 0x20B24), (0x233BC, 0x219C4), (0x22D2C, 0x219C0))
        n = 0
        for arr, count in arrays:
            w.append([count, 4, 2])
            for k in range(2):
                ent = 0x3E000 + 0x80 * n
                rec_off = 0x40 + 0x40 * n
                w += [[arr + 4 * k, 4, ent], [ent, 4, CTL if n % 3 == 0 else 0], [ent + 0x10, 2, n], [ent + 0x14, 4, 0],
                      [ent + 0x18, 4, 5], [ent + 0x24, 4, PARENT], [ent + 0x28, 1, 2 if n % 2 else 1], [ent + 0x3C, 2, 0],
                      [LIST + 0x1A + 0x14 + 0x18 * n, 4, rec_off], [BASE + rec_off + 0xE, 4, 0x100 * (n + 1)]]
                n += 1
        if slot:
            w += [[0x2144C, 4, 0x3EC00], [0x3EC00 + 0x10, 2, 1], [0x3EC00 + 0x12, 2, 1], [0x3EC00 + 0x28, 1, 1]]
        out.append({"name": "simulation tick: " + name, "method": "run_simulation_tick", "args": [], "writes": w})
    return out


def object_alloc_cases() -> list:
    """update_object_camera_metric (each axis dominant, negative offsets) and alloc_object_from_desc (a
    shape - with the zero word at desc +0x1c and the word-bit-13 flag - and a kind-3 object) on a fresh
    heap: the ring cursor unset, so the allocator builds its sentinel and grows the arena (host +0x384,
    break 0x27188)."""
    OBJ, DESC, ARENA = 0x3C000, 0x3C400, 0x3D000
    out = []
    for name, pos in (("x dominant", (0x50000, 0x100, -0x20)), ("y dominant", (-0x10, -0x70000, 0x30)),
                      ("z dominant", (0x1234, 0x5678, 0x7FFFF)), ("at the camera", (0, 0, 0))):
        w = [[0x23D68, 4, 0x1000], [0x23D64, 4, 0x2000], [0x23D60, 4, (-0x3000) & 0xFFFFFFFF]]
        w += [[OBJ + 4 + 4 * k, 4, (v + c) & 0xFFFFFFFF] for k, (v, c) in enumerate(zip(pos, (0x1000, 0x2000, -0x3000)))]
        out.append({"name": "camera metric: " + name, "method": "update_object_camera_metric", "args": [OBJ], "writes": w, "d0": True})
    for name, tag, flags, w1c in (("shape", 2, 0x2005, 7), ("shape, zero word +0x1c", 2, 0x0005, 0), ("kind 3", 3, 0x1234, 9)):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [0x21020, 4, 0x77],
             [DESC, 1, tag], [DESC + 2, 4, 0x11111111], [DESC + 6, 4, 0x22222222], [DESC + 0xA, 4, 0x33333333],
             [DESC + 0xE, 4, 0x44444444], [DESC + 0x12, 4, 0x55555555], [DESC + 0x16, 2, flags], [DESC + 0x18, 2, 0x6161],
             [DESC + 0x1A, 2, 0x6262], [DESC + 0x1C, 2, w1c], [DESC + 0x1E, 2, 0x6464], [DESC + 0x20, 2, 0x6565]]
        for k in range(0, 0x80, 4):
            w.append([ARENA + k, 4, 0xA5A5A5A5])
        out.append({"name": "alloc object: " + name, "method": "alloc_object_from_desc", "args": [DESC], "writes": w, "d0": True})
    return out


def attitude_cases() -> list:
    """atan_fixed14 / acos_fixed14 / isqrt_floor on Leyte's tables (D0), and hook 19 (attitude_from_velocity)
    over each heading branch, a level and a steep pitch (both signs), speed 0, vz 0."""
    OUT = 0x3C000
    out = []
    for r in (-0x80000, -0x1000, 0, 0x800, 0x1000, 0x4000, 0x7FFFFFFF):
        out.append({"name": "atan 0x%x" % (r & 0xFFFFFFFF), "method": "atan_fixed14", "args": [r & 0xFFFFFFFF], "writes": [], "d0": True})
    for x in (-0x2000, -0x1000, -0x123, 0, 0x7FF, 0x1000, 0x1800):
        out.append({"name": "acos 0x%x" % (x & 0xFFFFFFFF), "method": "acos_fixed14", "args": [x & 0xFFFFFFFF], "writes": [], "d0": True})
    for n in (0, 1, 2, 0x1000000, 0x3FFF0001, -5):
        out.append({"name": "isqrt 0x%x" % (n & 0xFFFFFFFF), "method": "isqrt_floor", "args": [n & 0xFFFFFFFF], "writes": [], "d0": True})
    for name, v, speed, sq in (("x dominant, vx > 0", (0x800, 0x100, 0x200), 0x1000, 0x1000000),
                               ("x dominant, vx < 0", (-0x800, -0x100, 0x200), 0x1000, 0x1000000),
                               ("z dominant, vz > 0", (0x100, 0x200, 0x900), 0x1000, 0x1000000),
                               ("z dominant, vz < 0", (0x100, 0x200, -0x900), 0x1000, 0x1000000),
                               ("steep up", (0x10, 0xF00, 0x20), 0x1000, 0x1000000), ("steep down", (0x10, -0xF00, 0x20), 0x1000, 0x1000000),
                               ("vz 0 and x 0", (0, 0x300, 0), 0x1000, 0x1000000), ("speed 0", (0x300, 0x100, 0x50), 0, 0)):
        w = [[OUT, 2, 0x5555], [OUT + 2, 2, 0x6666]]
        args = [x & 0xFFFFFFFF for x in v] + [speed, sq, OUT, OUT + 2]
        out.append({"name": "attitude: " + name, "method": "attitude_from_velocity", "args": args, "writes": w})
    return out


def translate_cases() -> list:
    """translate_int3_vecs: count 0, 1 and 3, in place and to another buffer, with carries across 32 bits."""
    SRC, DST = 0x3C000, 0x3C100
    vw = [[SRC + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate([1, -2, 0x7FFFFFFF, 0x10, 0x20, -0x80000000, 5, 6, 7])]
    out = []
    for name, count, dst, d in (("none", 0, DST, (1, 2, 3)), ("one", 1, DST, (0x100, -0x100, 1)),
                                ("three in place", 3, SRC, (1, -1, 0x7FFFFFFF)), ("three", 3, DST, (-5, 0, 0x10000))):
        out.append({"name": "translate: " + name, "method": "translate_int3_vecs",
                    "args": [count, SRC, dst] + [x & 0xFFFFFFFF for x in d], "writes": vw})
    return out


def chain_xform_cases() -> list:
    """The 3x3 product (0x2326), the chain composer (0x36fa) and apply_template_chain_xform (0x3524): no
    parent, a translating parent, a rotating parent, a three-level chain mixing both; the point path on
    each and the mesh path rotated and not (with the +0x36 shift)."""
    OBJ, P1, P2, P3, T1, T2, T3, OUT, VSRC, VDST, A, B = (0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C400, 0x3C500,
                                                          0x3C600, 0x3C700, 0x3C800, 0x3C900, 0x3CA00, 0x3CA40)
    out = []
    ma = [0x1000, 0x10, -0x20, 0x300, 0xF00, 0x7FF, -0x1000, 0x123, 0x456]
    mb = [0xB50, -0xB50, 0, 0xB50, 0xB50, 0x10, 0x5, -0x6, 0x1000]
    w = [[A + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(ma)] + [[B + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(mb)]
    out.append({"name": "matmul 3x3", "method": "matmul3x3_s16", "args": [A, B, OUT], "d0": True, "writes": w})

    def slot(table, idx, angles, trans):
        base = table + 0x18 * idx
        return ([[base + 2 + 2 * k, 2, a & 0xFFFF] for k, a in enumerate(angles)]
                + [[base + 8 + 4 * k, 4, t & 0xFFFFFFFF] for k, t in enumerate(trans)])

    chains = {
        "no parent": [[OBJ + 0x24, 4, 0]],
        "translating parent": [[OBJ + 0x24, 4, P1], [OBJ + 0x12, 2, 1], [P1 + 0x32, 4, T1], [P1 + 0x24, 4, 0]]
                              + slot(T1, 1, (0, 0, 0), (0x100, -0x200, 0x30000)),
        "rotating parent": [[OBJ + 0x24, 4, P1], [OBJ + 0x12, 2, 2], [P1 + 0x32, 4, T1], [P1 + 0x24, 4, 0]]
                           + slot(T1, 2, (0x400, 0x1000, 0x2345), (0x1000, 0x2000, -0x3000)),
        "three levels": [[OBJ + 0x24, 4, P1], [OBJ + 0x12, 2, 0], [P1 + 0x32, 4, T1], [P1 + 0x24, 4, P2], [P1 + 0x12, 2, 3],
                         [P2 + 0x32, 4, T2], [P2 + 0x24, 4, P3], [P2 + 0x12, 2, 1], [P3 + 0x32, 4, T3], [P3 + 0x24, 4, 0]]
                        + slot(T1, 0, (0x800, 0, 0), (0x500, 0, 0)) + slot(T2, 3, (0, 0, 0), (0x10, 0x20, 0x30))
                        + slot(T3, 1, (0x3FFF, 0x1234, 0x0F0F), (-0x7000, 0x100, 0x55))}
    pos = [[OBJ + 4, 4, 0x12345], [OBJ + 8, 4, (-0x6789) & 0xFFFFFFFF], [OBJ + 0xC, 4, 0x1000], [OBJ + 0x36, 2, 3]]
    verts = [[VSRC + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate([0x100, 0x200, 0x300, -0x400, 0x500, -0x600])]
    for name, cw in chains.items():
        out.append({"name": "compose chain: " + name, "method": "compose_template_chain", "args": [OBJ, OUT], "writes": cw + pos, "d0": True})
        out.append({"name": "chain xform, point: " + name, "method": "apply_template_chain_xform", "args": [OBJ, 1, 0, 0], "writes": cw + pos})
    for name in ("translating parent", "three levels"):
        out.append({"name": "chain xform, mesh: " + name, "method": "apply_template_chain_xform", "args": [OBJ, 2, VSRC, VDST],
                    "writes": chains[name] + pos + verts})
    return out


def band_cases() -> list:
    """The distance bands on Leyte's own band_limits table (the image's 0x203b8): the band kept, stepped up
    across several limits, stepped down from far above, for an object (+0x32) and a record (+4); D0."""
    OBJ, REC = 0x3C000, 0x3C100
    out = []
    # table (from 0x203b4): 0, 40041, 81395, 124152, 168409, 214275, 261873, 311338, ...
    for name, band, dist in (("kept", 3, 150000), ("up one", 2, 130000), ("up several", 1, 300000), ("down several", 6, 50000),
                             ("down one", 4, 150000), ("near zero", 3, 1)):
        for method, target, field in (("update_object_distance_band", OBJ, 0x32), ("update_record_distance_band", REC, 4)):
            out.append({"name": "band %s: %s" % (method.split('_')[1], name), "method": method,
                        "args": [target, dist], "writes": [[target + field, 2, band]], "d0": True})
    return out


def shade_cases() -> list:
    """shade_object_faces at depths 8 (haze on, haze masked by the flags, an owning face writing its palette
    slot), 4 and 1, the flat-white face flag, the retired skip and the snapshot; assign_face_colour at depth
    8 (re-queue, first free slot, all full); write_face_palette_slot with the multi-screen tables."""
    OBJ, PARENT, LIST, BASE, V, F, OWN, T2, T3, U1, U2, LUMA = (0x3C000, 0x3C100, 0x3C200, 0x3D000, 0x3C400, 0x3C800,
                                                                0x3DA00, 0x3DE00, 0x3E200, 0x3C300, 0x3C3D0, 0x3D400)
    REC_OFF, IDX, NV, NF = 0x40, 1, 2, 3
    REC = BASE + REC_OFF
    out = []

    def world(depth, haze, flags=0, band=5, retired=False, own_face=False, snap=False, white=False):
        w = [[0x207FC, 4, HOST], [HOST + 0xE, 2, depth], [0x2100E, 2, depth], [HOST + 0xAA, 2, haze], [HOST + 0x8C, 4, BASE],
             [HOST + 0x88, 4, LUMA], [HOST + 0xB4, 2, 0], [OBJ + 0x14, 4, flags], [OBJ + 0x17, 1, 2 if retired else 0],
             [OBJ + 0x28, 1, 2], [OBJ + 0x32, 2, band], [OBJ + 0x16, 1, 2 if snap else 0], [OBJ + 0x46, 4, V],
             [OBJ + 0x38, 2, NV], [OBJ + 0x24, 4, PARENT], [OBJ + 0x10, 2, IDX], [OBJ + 0x4A, 4, F], [OBJ + 0x3C, 2, NF],
             [PARENT + 0x2A, 4, LIST], [LIST + 0x2E + 0x18 * IDX, 4, REC_OFF], [REC + 0x1A, 2, 1], [REC + 0x1E, 2, 1],
             [0x21574, 2, 0x0800], [0x21572, 2, 0xF800], [0x21570, 2, 0x0400], [0x23FAC, 4, OWN]]
        rgb = REC + 0x22 + 0xC * 2
        for k in range(NF):
            w += [[rgb, 1, 0x40 + 0x30 * k], [rgb + 1, 1, 0x80 - 0x10 * k], [rgb + 2, 1, 0xF0 - 0x40 * k], [rgb + 8, 1, k]]
            rgb += 0xE + 2 * k
        w += [[V + 2, 2, 0x10], [V + 6, 2, 0x20], [V + 10, 2, 0x30]]
        for k in range(NF):
            n = V + 0xC * NV + 0xC * k
            w += [[n + 2, 2, 0x10 + 0x400 * (k - 1)], [n + 6, 2, (0x20 - 0x300 * k) & 0xFFFF], [n + 10, 2, 0x30 + 0x100 * k]]
            face = F + 0x20 * k
            w += [[face + 0x13, 1, 4 if (white and k == 1) else 0], [face + 0xF, 1, 5 + k]]
        if own_face:
            w.append([OWN + 4 * 6, 4, F + 0x20])
        for k in range(256):
            w.append([LUMA + 2 * k, 2, 0x100 + k])
        for k in range(0, 0x80):
            w.append([OWN + 4 * k + 0x200, 4, 0])
        return w

    for name, kw in (("depth 8, haze", dict(depth=8, haze=1)), ("depth 8, haze masked by the flags", dict(depth=8, haze=1, flags=0x200)),
                     ("depth 8, owning face", dict(depth=8, haze=0, own_face=True)), ("depth 4", dict(depth=4, haze=0)),
                     ("depth 1", dict(depth=1, haze=0)), ("flat-white face", dict(depth=8, haze=0, white=True)),
                     ("retired", dict(depth=8, haze=1, retired=True)), ("snapshot", dict(depth=8, haze=1, snap=True))):
        out.append({"name": "shade faces: " + name, "method": "shade_object_faces", "args": [OBJ], "writes": world(**kw)})
    FACE = 0x3CC00
    for name, own, used, cursor in (("re-queue its own slot", True, (), 3), ("first free slot", False, (3, 4), 3),
                                    ("all slots used", False, tuple(range(200)), 0)):
        w = [[0x207FC, 4, HOST], [HOST + 0xB4, 2, 0], [0x2100E, 2, 8], [0x23FAC, 4, OWN], [0x23D90, 4, U1], [0x23D94, 4, U2],
             [0x21568, 4, cursor], [FACE + 0xF, 1, 7], [0x23FD2, 2, 0]]
        if own:
            w.append([OWN + 4 * 7, 4, (-FACE) & 0xFFFFFFFF])
        w += [[U1 + k, 1, 1 if k in used else 0] for k in range(200)] + [[U2 + k, 1, 0] for k in range(200)]
        out.append({"name": "assign face colour: " + name, "method": "assign_face_colour", "args": [FACE], "writes": w, "d0": True})
    w = [[0x207FC, 4, HOST], [HOST + 0xB4, 2, 1], [0x23FAC, 4, OWN], [0x23FA0, 4, OWN], [0x23FA4, 4, T2], [0x23FA8, 4, 0],
         [FACE + 0xF, 1, 9]]
    out.append({"name": "palette slot, multi-screen", "method": "write_face_palette_slot", "args": [FACE], "writes": w})
    return out


def poly_buffer_cases() -> list:
    """build_object_poly_buffers on a fresh heap (the allocator grows the arena from host +0x384): a stream
    of three faces with 3 / 0 / 4 index words, with and without the trailer flag (byte +0x16 bit 1)."""
    OBJ, STREAM, ARENA = 0x3C000, 0x3C400, 0x3D000
    out = []
    for name, flag in (("faces", 0), ("faces and the vertex trailer", 2)):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [0x23FD2, 2, 0],
             [OBJ + 0x3C, 2, 3], [OBJ + 0x3A, 2, 5], [OBJ + 0x16, 1, flag]]
        at = STREAM
        for k, n in enumerate((3, 0, 4)):
            w += [[at, 4, 0x11110000 + k], [at + 4, 4, 0x20212223 + k], [at + 8, 1, n], [at + 9, 1, 0x5A + k], [at + 0xA, 4, 0x3000 + k]]
            w += [[at + 0xE + 2 * m, 2, 0x100 * k + m] for m in range(n)]
            at += 0xE + 2 * n
        for k in range(0, 0x200, 4):
            w.append([ARENA + k, 4, 0xC3C3C3C3])
        out.append({"name": "poly buffers: " + name, "method": "build_object_poly_buffers", "args": [OBJ, STREAM], "writes": w})
    return out


def land_outline_cases() -> list:
    """approx_dist3_xyz (D0), and build_land_outline_at_detail on a two-face template (no parent rotation):
    the outline thinned by the camera-distance rule, scaled about the detail reference, clipped to the
    +-limit square, the unique points rebuilt. Its frame is 0x1d38+ deep (work buffers in stack memory),
    so these cases widen their stack guard to the whole frame; the object, face and global results are
    compared. The object buffers are large heap blocks big enough not to grow."""
    OBJ, ROOT, LIST, BASE, SLOTS, FACES, IDX18, VB, VB2 = 0x3A000, 0x3A100, 0x3A200, 0x3A300, 0x3A800, 0x3A900, 0x3AA00, 0x3B008, 0x3B808
    out = []
    for name, a, b in (("x dominant", (0, 0, 0), (0x5000, -0x100, 0x30)), ("mixed", (0x100, -0x200, 0x300), (-0x700, 0x900, 0x200)),
                       ("zero", (5, 5, 5), (5, 5, 5))):
        out.append({"name": "dist3: " + name, "method": "approx_dist3_xyz", "args": [v & 0xFFFFFFFF for v in a + b],
                    "writes": [], "d0": True})
    REC_OFF = 0x40
    REC = BASE + REC_OFF
    verts = [(0x1000, 0, 0x1000), (0x9000, 0, 0x1000), (0x9000, 0, 0x9000), (0x1000, 0, 0x9000), (0x5000, 0, 0x5000)]
    faces = [(0, 1, 2, 3), (0, 4, 2)]
    for name, cam, limit, phase in (("near, wide limit", (0x5000, 0x5000), 0x7FE4, 0), ("far, clipped", (0x80000, 0x80000), 0x3000, 3)):
        w = [[0x207FC, 4, HOST], [HOST + 0x8C, 4, BASE], [HOST + 0xA8, 2, phase], [0x23D68, 4, cam[0]], [0x23D60, 4, cam[1]],
             [0x23C54, 4, 0x4000], [0x23C4C, 4, 0x4000], [0x2701A, 2, 0x1111], [0x27018, 2, 0x2222], [0x27016, 2, 0x3333],
             [0x20B2E, 2, 0x4444], [OBJ + 0x24, 4, ROOT], [OBJ + 0x10, 2, 1], [OBJ + 0x12, 2, 0], [OBJ + 0x3C, 2, 2],
             [OBJ + 0x38, 2, 5], [OBJ + 0x46, 4, VB], [OBJ + 0x4E, 4, VB2], [OBJ + 0x4A, 4, FACES], [OBJ + 0x36, 2, 0],
             [OBJ + 0x14, 4, 0x40], [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [ROOT + 0x32, 4, SLOTS],
             [LIST + 0x2E + 0x18 * 1, 4, REC_OFF], [REC + 0x1A, 2, len(verts)], [REC + 0x1E, 2, 0]]
        # large heap blocks (header byte 0xff, 0x80 units): usable 0x3f8 bytes, so nothing grows
        for blk in (VB, VB2):
            w += [[blk - 8, 4, 0x80], [blk - 4, 1, 0xFF]]
        for k in range(7):
            w += [[VB + 0xC * k + 4 * m, 4, (0x100 * k + 0x10 * m) & 0xFFFFFFFF] for m in range(3)]
        for k, v in enumerate(verts):
            w += [[REC + 0x22 + 0xC * k + 4 * m, 4, v[m]] for m in range(3)]
        at = REC + 0x22 + 0xC * len(verts)
        for k, f in enumerate(faces):
            w += [[at + 8, 1, len(f)]] + [[at + 0xE + 2 * m, 2, f[m]] for m in range(len(f))]
            at += 0xE + 2 * len(f)
            fr = FACES + 0x20 * k
            w += [[fr + 0x13, 1, 0], [fr + 0x18, 4, IDX18 + 0x80 * k], [IDX18 + 0x80 * k - 8, 4, 0x10], [IDX18 + 0x80 * k - 4, 1, 0xFF]]
        out.append({"name": "land outline: " + name, "method": "build_land_outline_at_detail", "args": [OBJ, 0xB4E1, 0, limit],
                    "writes": w, "stack_guard": [0x3F000 - 0x2000, 0x3F040]})
    return out


def contact_cases() -> list:
    """transform_vecs12_to_int3_by_matrix3 (two records, none); release_fractal_reference (user count down to
    0 and not, the owner path freeing unused blocks and the list, the static-scenery reset through the
    chain transform); drop_contact_records_for_object (records naming the object as a or b, others kept,
    the fractal release on the way). Freed blocks carry small-class heap headers (class 4)."""
    SRC, DST, M = 0x3C000, 0x3C100, 0x3C200
    out = []
    mat = [0x1000, 0x10, -0x20, 0x300, 0xF00, 0x7FF, -0x1000, 0x123, 0x456]
    w = [[M + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(mat)]
    w += [[SRC + 2 + 4 * k, 2, v & 0xFFFF] for k, v in enumerate([0x100, -0x200, 0x300, 0x7FFF, -0x8000, 0x10])]
    for n in (2, 0):
        out.append({"name": "vecs12 transform: %d" % n, "method": "transform_vecs12_to_int3_by_matrix3", "args": [n, SRC, DST, M], "writes": w})
    OBJ, OTHER, BLOCK, LIST, B1, B2, ROOT, TL, BASE = 0x3C000, 0x3C100, 0x3C400, 0x3C500, 0x3C600, 0x3C700, 0x3C800, 0x3C900, 0x3D000

    def blk(addr, cls=4):
        return [[addr - 4, 1, cls]]

    def common():
        return [[0x207FC, 4, HOST], [HOST + 0x8C, 4, BASE], [0x219A6, 2, 0], [0x20B26, 2, 0]] + \
               [[0x2719C + 4 * k, 4, 0] for k in range(8)]

    for name, users, flags15, flags16 in (("user, last", 1, 0, 0), ("user, still shared", 3, 0, 0),
                                          ("static scenery user", 2, 8, 0), ("owner, unused blocks", 0, 0, 2)):
        w = common() + [[OBJ + 0x15, 1, flags15], [OBJ + 0x16, 1, flags16], [OBJ + 0x24, 4, 0], [OBJ + 0x2A, 4, 0]]
        if flags16:
            w += [[OBJ + 0x2E, 4, LIST], [LIST, 2, 2], [LIST + 4, 4, B1], [LIST + 8, 4, B2], [B1, 2, 0], [B2, 2, 0]] + blk(LIST) + blk(B1) + blk(B2)
        else:
            w += [[OBJ + 0x2E, 4, BLOCK], [BLOCK, 2, users]]
        if flags15:
            w += [[OBJ + 0x24, 4, ROOT], [OBJ + 0x10, 2, 0], [OBJ + 0x12, 2, 0], [ROOT + 0x2A, 4, TL], [ROOT + 0x24, 4, 0],
                  [ROOT + 0x32, 4, TL + 0x80], [TL + 0x2E, 4, 0x40], [BASE + 0x40 + 2, 4, 0x1111], [BASE + 0x40 + 6, 4, 0x2222],
                  [BASE + 0x40 + 0xA, 4, 0x3333]]
        out.append({"name": "release fractal: " + name, "method": "release_fractal_reference", "args": [OBJ], "writes": w})
    R1, R2, R3 = 0x3CA00, 0x3CA20, 0x3CA40
    w = common() + [[0x215A4 + 4 * k, 4, 0] for k in range(256)]
    w += [[0x215A4 + 4 * 3, 4, R1], [0x215A4 + 4 * 9, 4, R2], [0x215A4 + 4 * 200, 4, R3]] + blk(R1) + blk(R2) + blk(R3)
    w += [[R1, 4, OBJ], [R1 + 4, 4, OTHER], [R2, 4, OTHER], [R2 + 4, 4, OBJ], [R3, 4, OTHER], [R3 + 4, 4, OTHER]]
    w += [[OBJ + 0x16, 1, 2], [OTHER + 0x16, 1, 0x20], [OTHER + 0x15, 1, 0], [OTHER + 0x2E, 4, BLOCK], [BLOCK, 2, 2],
          [OBJ + 0x2E, 4, 0], [OBJ + 0x15, 1, 0]]
    out.append({"name": "drop contacts", "method": "drop_contact_records_for_object", "args": [OBJ], "writes": w})
    return out


def sink_cases() -> list:
    """sink_object_shape_at_waterline (0x19b32 -> 0x1970a): a square face crossing the waterline (clipped) and
    a triangle wholly below it (dropped), the object tilted (sink angles) under a translating parent, with
    the re-shade flag (host +0xb2) clear, and set on a retired object (the shading returns at once). The
    frame is 0x3438+ deep, so the stack guard covers it; the data sits at 0x30000.. below the frame."""
    OBJ, ROOT, LIST, SLOTS, BASE, FACES, VB, VB2, IDX = 0x30000, 0x30100, 0x30200, 0x30300, 0x31000, 0x30400, 0x32008, 0x33008, 0x34008
    REC_OFF = 0x40
    REC = BASE + REC_OFF
    verts = [(-0x400, 0x200, -0x400), (0x400, 0x200, -0x400), (0x400, -0x300, 0x400), (-0x400, -0x300, 0x400),
             (0, -0x800, 0), (0x100, -0x900, 0x100)]
    normals = [(0, 0x1000, 0), (0, -0x1000, 0)]
    faces = [(0, (0, 1, 2, 3)), (0, (4, 5, 4))]
    out = []
    for name, shade, angles in (("tilted, no re-shade", 0, (0x100, 0x200, 0)), ("level, re-shade flag on a retired object", 1, (0, 0, 0))):
        w = [[0x207FC, 4, HOST], [HOST + 0x8C, 4, BASE], [HOST + 0xB2, 2, shade], [0x219A6, 2, 0]]
        w += [[0x215A4 + 4 * k, 4, 0] for k in range(256)]
        w += [[OBJ + 0x24, 4, ROOT], [OBJ + 0x10, 2, 1], [OBJ + 0x12, 2, 0], [OBJ + 4, 4, 0x20], [OBJ + 8, 4, 0x80],
              [OBJ + 0xC, 4, (-0x10) & 0xFFFFFFFF], [OBJ + 0x36, 2, 2], [OBJ + 0x4A, 4, FACES], [OBJ + 0x3C, 2, len(faces)],
              [OBJ + 0x46, 4, VB], [OBJ + 0x4E, 4, VB2], [OBJ + 0x14, 4, 0x40], [OBJ + 0x17, 1, 2 if shade else 0],
              [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [ROOT + 0x32, 4, SLOTS], [LIST + 0x2E + 0x18, 4, REC_OFF],
              [SLOTS + 2, 2, 0], [SLOTS + 4, 2, 0], [SLOTS + 6, 2, 0], [SLOTS + 8, 4, 0x100], [SLOTS + 0xC, 4, 0], [SLOTS + 0x10, 4, 0x80],
              [REC + 0x1A, 2, len(verts)], [REC + 0x1E, 2, len(normals)]]
        for blk in (VB, VB2):
            w += [[blk - 8, 4, 0x80], [blk - 4, 1, 0xFF]]
        for k, v in enumerate(verts + normals):
            w += [[REC + 0x22 + 0xC * k + 4 * m, 4, v[m] & 0xFFFFFFFF] for m in range(3)]
        at = REC + 0x22 + 0xC * (len(verts) + len(normals))
        for k, (fl, idx) in enumerate(faces):
            w += [[at + 7, 1, fl], [at + 8, 1, len(idx)]] + [[at + 0xE + 2 * m, 2, idx[m]] for m in range(len(idx))]
            at += 0xE + 2 * len(idx)
            fr = FACES + 0x20 * k
            w += [[fr + 0x13, 1, 2], [fr + 0x14, 2, 0x55], [fr + 0x18, 4, IDX + 0x100 * k], [IDX + 0x100 * k - 8, 4, 0x10],
                  [IDX + 0x100 * k - 4, 1, 0xFF]]
        out.append({"name": "sink at waterline: " + name, "method": "sink_object_shape_at_waterline",
                    "args": [OBJ] + [a & 0xFFFFFFFF for a in angles], "writes": w, "d0": True,
                    "stack_guard": [0x3B800, 0x3F040]})
    return out


def instantiate_cases() -> list:
    """activate_near_range_object for a kind-3 group without children and an unknown kind, and
    instantiate_mission_pool_slot end to end on a fresh heap: a shape without a class key (one face, three
    vertices) under a root with an empty chain - near the camera it activates (heap buffers, face records,
    chain transform, band, shading at depth 8), far from it it is queued on entity_array_3."""
    ROOT, LIST, SLOTS, BASE, ARENA, OBJ, OWN = 0x30000, 0x30100, 0x30200, 0x31000, 0x32000, 0x30600, 0x30800
    out = []
    for name, tag in (("group without children", 3), ("unknown kind", 7)):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [0x20F2C, 4, 0],
             [OBJ + 0x28, 1, tag], [OBJ + 0x30, 2, 0], [OBJ + 0x2A, 4, BASE], [OBJ + 4, 4, 0x100], [OBJ + 8, 4, 0x200],
             [OBJ + 0xC, 4, 0x300], [0x23D68, 4, 0], [0x23D64, 4, 0], [0x23D60, 4, 0]]
        out.append({"name": "activate: " + name, "method": "activate_near_range_object", "args": [OBJ], "writes": w})
    REC_OFF, TYPE, SLOT = 0x40, 1, 1
    REC = BASE + REC_OFF
    for name, cam in (("near: activated", (0x1000, 0x2000, 0x3000)), ("far: queued", (0x900000, 0, 0))):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [HOST + 0x8C, 4, BASE],
             [HOST + 0xA8, 2, 0], [HOST + 0xE, 2, 8], [HOST + 0xAA, 2, 0], [0x2100E, 2, 8], [0x23FAC, 4, OWN],
             [0x21574, 2, 0], [0x21572, 2, 0], [0x21570, 2, 0], [0x23FD2, 2, 0],
             [0x20F2C, 4, 0], [0x20B24, 4, 0], [0x219C4, 4, 0], [0x219B4, 4, 0],
             [0x23D68, 4, cam[0]], [0x23D64, 4, cam[1]], [0x23D60, 4, cam[2]],
             [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [ROOT + 0x32, 4, SLOTS], [ROOT + 0x2E, 2, 3], [ROOT + 0x12, 2, 0],
             [LIST + 0x1A + 0x14 + 0x18 * TYPE, 4, REC_OFF], [SLOTS + 0x18 * SLOT + 0x14, 4, 0x5555]]
        w += [[SLOTS + 0x18 * SLOT + k, 1, 0] for k in range(0x14)]
        # the descriptor / template record: tag 2, no class key, position, flags 0, shift 0, 3 vertices, 1 face
        w += [[REC, 1, 2], [REC + 1, 1, 0], [REC + 2, 4, 0x1000], [REC + 6, 4, 0x2000], [REC + 0xA, 4, 0x3000],
              [REC + 0xE, 4, 0], [REC + 0x12, 4, 0x500], [REC + 0x16, 2, 0], [REC + 0x18, 2, 0], [REC + 0x1A, 2, 3],
              [REC + 0x1C, 2, 0], [REC + 0x1E, 2, 1], [REC + 0x20, 2, 0]]
        for k, v in enumerate([(0x100, 0, 0), (0, 0x100, 0), (0, 0, 0x100), (0, 0x1000, 0)]):
            w += [[REC + 0x22 + 0xC * k + 4 * m, 4, v[m]] for m in range(3)]
        face = REC + 0x22 + 0xC * 4
        w += [[face, 4, 0x11223344], [face + 4, 4, 0x00010203], [face + 8, 1, 3], [face + 9, 1, 0x21], [face + 0xA, 4, 0x77],
              [face + 0xE, 2, 0], [face + 0x10, 2, 1], [face + 0x12, 2, 2]]
        w += [[OWN + 4 * k, 4, 0] for k in range(16)]
        out.append({"name": "instantiate slot: " + name, "method": "instantiate_mission_pool_slot", "args": [ROOT, TYPE, SLOT],
                    "writes": w, "stack_guard": [0x3E000, 0x3F040]})
    return out


def cloud_cases() -> list:
    """fold_angle_pm_0x2000 (D0); spawn_mission_pool_object with a free slot, with none (the table grows by 10)
    and with a template whose object gets no vertices (retired, 0); hook 21 (spawn_cloud_layer) without clouds:
    the type-0x2a cloud objects of entity_array_1 retire, the others stay. (With clouds it reaches the cloud
    classes' create routines, not yet written for Leyte.)"""
    out = []
    for a in (0, 0x1FFF, 0x2001, 0x3FFF, 0xE000, 0xDFFF, 0x8000):
        out.append({"name": "fold angle 0x%x" % a, "method": "fold_angle_pm_0x2000", "args": [a], "writes": [], "d0": True})
    ROOT, LIST, SLOTS, BASE, ARENA, TMPL = 0x30000, 0x30100, 0x30200, 0x31000, 0x32000, 0x30600
    REC_OFF, TYPE = 0x40, 1
    REC = BASE + REC_OFF
    for name, count, used, nverts in (("a free slot", 3, (0, 1), 3), ("no free slot: the table grows", 2, (0, 1), 3),
                                      ("no vertices: retired", 3, (), 0)):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [HOST + 0x8C, 4, BASE],
             [HOST + 0xA8, 2, 0], [HOST + 0xE, 2, 8], [HOST + 0xAA, 2, 0], [0x2100E, 2, 8], [0x23FAC, 4, 0x30800],
             [0x20F2C, 4, 0], [0x20B24, 4, 0], [0x219C4, 4, 0], [0x219B4, 4, 0], [0x2B22C, 4, 0], [0x21068, 4, 0],
             [0x2107A, 2, 0], [0x21078, 2, 9],
             [0x23D68, 4, 0x900000], [0x23D64, 4, 0], [0x23D60, 4, 0], [0x23C2C, 4, ROOT],
             [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [ROOT + 0x32, 4, SLOTS + 8], [ROOT + 0x30, 2, count], [ROOT + 0x2E, 2, 3],
             [SLOTS, 4, 0x10], [SLOTS + 4, 1, 0xFF], [LIST + 0x1A + 0x14 + 0x18 * TYPE, 4, REC_OFF], [TMPL + 0x10, 2, TYPE]]
        for k in range(count):
            w += [[SLOTS + 8 + 0x18 * k + m, 1, 0] for m in range(0x18)]
            if k in used:
                w.append([SLOTS + 8 + 0x18 * k + 0x14, 4, 0x3FF00])
        w += [[REC, 1, 2], [REC + 1, 1, 0], [REC + 0x16, 2, 0], [REC + 0x18, 2, 0], [REC + 0x1A, 2, nverts], [REC + 0x1C, 2, 0],
              [REC + 0x1E, 2, 0], [REC + 0x20, 2, 0]]
        out.append({"name": "spawn pool object: " + name, "method": "spawn_mission_pool_object",
                    "args": [TMPL, 0x100, 0x200, 0x300, 0x10, 0x20, 0x30, 0x1234], "writes": w, "d0": True,
                    "stack_guard": [0x3E000, 0x3F040]})
    E, C1, C2, D1, D2 = 0x30000, 0x30400, 0x30500, 0x30600, 0x30700
    w = [[0x207FC, 4, HOST], [HOST + 0xAE, 2, 0], [0x20F2C, 4, 3], [0x2B22C, 4, 0], [0x21068, 4, 0], [0x23C2C, 4, 0x3EF00]]
    for k, (ctl, cdef, typ) in enumerate(((C1, D1, 0x2A), (C2, D2, 0x1D), (0, 0, 0))):
        obj = E + 0x100 * k
        w += [[0x20B44 + 4 * k, 4, obj], [obj, 4, ctl], [obj + 0x10, 2, 1], [obj + 0x12, 2, 1], [obj + 0x14, 4, 0]]
        if ctl:
            w += [[ctl + 4, 4, cdef], [cdef, 2, typ]]
    out.append({"name": "cloud layer: none, clouds retired", "method": "spawn_cloud_layer", "args": [], "writes": w})
    return out


def scene_part_cases() -> list:
    """reset_object_to_template_pose (reset, skipped by byte +0x17 bit 1), copy_player_pose_to_camera, and
    deactivate_object for a shape (template pose reset, buffers and face lists freed - small-class blocks -,
    controller unlinked, cleanup for differing child indices) and for a group of two children."""
    OBJ, ROOT, LIST, BASE, CTL, TYPE = 0x30000, 0x30100, 0x30200, 0x31000, 0x30400, 0x30500
    REC = BASE + 0x40
    out = []

    def tmpl():
        return [[0x207FC, 4, HOST], [HOST + 0x8C, 4, BASE], [OBJ + 0x24, 4, ROOT], [OBJ + 0x10, 2, 1], [OBJ + 0x12, 2, 0],
                [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [LIST + 0x2E + 0x18, 4, 0x40], [REC + 2, 4, 0x1111], [REC + 6, 4, 0x2222],
                [REC + 0xA, 4, 0x3333], [REC + 0x12, 4, 0x4444], [OBJ + 0x36, 2, 0]]

    for name, fl in (("reset", 0), ("skipped by the flag", 2)):
        out.append({"name": "template pose: " + name, "method": "reset_object_to_template_pose", "args": [OBJ],
                    "writes": tmpl() + [[OBJ + 0x17, 1, fl], [OBJ + 4, 4, 9], [OBJ + 8, 4, 9], [OBJ + 0xC, 4, 9]]})
    w = [[0x20B28, 4, CTL], [CTL, 4, OBJ], [OBJ + 4, 4, 0x123], [OBJ + 8, 4, 0x456], [OBJ + 0xC, 4, 0x789],
         [CTL + 0x20, 2, 0x100], [CTL + 0x22, 2, 0x3FFF], [CTL + 0x24, 2, 0], [CTL + 4, 4, TYPE], [TYPE + 0x46, 2, 0x1000],
         [CTL + 0x626, 2, 0x2000]]
    out.append({"name": "player pose to camera", "method": "copy_player_pose_to_camera", "args": [], "writes": w})

    def blk(a):
        return [[a - 4, 1, 4]]

    FACES, VB, VB2, I0, I1 = 0x30600, 0x30700, 0x30780, 0x30800, 0x30840
    w = tmpl() + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    w += [[OBJ + 0x28, 1, 2], [OBJ + 0x14, 4, 0x80000], [OBJ + 0x16, 1, 0x10], [OBJ + 0x17, 1, 0], [OBJ + 0x4E, 4, VB2],
          [OBJ + 0x46, 4, VB], [OBJ + 0x4A, 4, FACES], [OBJ + 0x3C, 2, 2], [OBJ, 4, CTL], [CTL, 4, OBJ], [OBJ + 0x2E, 4, 0],
          [FACES + 0x18, 4, I0], [FACES + 0x20 + 0x18, 4, I1], [0x23C2C, 4, 0x3EF00], [0x2107A, 2, 0], [0x21078, 2, 9],
          [0x2B22C, 4, 0], [0x21068, 4, 0], [0x21020, 4, 0x55]]
    w += blk(VB) + blk(VB2) + blk(FACES) + blk(I0) + blk(I1)
    out.append({"name": "deactivate a shape", "method": "deactivate_object", "args": [OBJ], "writes": w})
    G, T, C1, C2, K1 = 0x30A00, 0x30B08, 0x30C00, 0x30D00, 0x30E00
    w = [[0x207FC, 4, HOST]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    w += [[G + 0x28, 1, 3], [G + 0x30, 2, 2], [G + 0x32, 4, T], [G, 4, 0], [T + 0x14, 4, C1], [T + 0x18 + 0x14, 4, C2]] + blk(T)
    w += [[C1 + 0x28, 1, 1], [C1 + 0x14, 4, 0], [C1 + 0x29, 1, 2], [C1, 4, K1], [K1, 4, C1], [C2 + 0x28, 1, 2], [C2 + 0x2E, 4, 0],
          [C2 + 0x14, 4, 0], [C2 + 0x29, 1, 1], [C2, 4, 0]]
    out.append({"name": "deactivate a group", "method": "deactivate_object", "args": [G], "writes": w})
    return out


def template_table_cases() -> list:
    """overlay_init_template_pointer_tables (0x38) on a fresh heap: the 43 definition copies (the image's own
    records at 0x1f78c / 0x1fbb0 / the fixed sources) filed into the definition table 0x2074c."""
    ARENA = 0x30000
    w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    w += [[0x2074C + 4 * k, 4, 0] for k in range(0x2C)]   # the table up to the host-world pointer 0x207fc
    return [{"name": "template pointer tables", "method": "overlay_init_template_pointer_tables", "args": [], "writes": w}]


def mission_part_cases() -> list:
    """copy_object_vertex_buffer (3 + 1 vertices, none) and instantiate_object_from_desc for a key-less shape
    descriptor on a fresh heap (the class-factory path needs Leyte's create routines, not yet written)."""
    OBJ, A, B, DESC, ARENA = 0x30000, 0x30100, 0x30200, 0x30300, 0x31000
    out = []
    for name, nv, nf in (("4 vertices", 3, 1), ("none", 0, 0)):
        w = [[OBJ + 0x38, 2, nv], [OBJ + 0x3C, 2, nf], [OBJ + 0x46, 4, A], [OBJ + 0x4E, 4, B]]
        w += [[A + 4 * k, 4, 0x1000 + k] for k in range(12)]
        out.append({"name": "copy vertex buffer: " + name, "method": "copy_object_vertex_buffer", "args": [OBJ], "writes": w})
    w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [0x21020, 4, 0x33],
         [DESC, 1, 2], [DESC + 1, 1, 0], [DESC + 2, 4, 0x111], [DESC + 6, 4, 0x222], [DESC + 0xA, 4, 0x333], [DESC + 0x16, 2, 0x2001],
         [DESC + 0x1C, 2, 0]]
    out.append({"name": "instantiate from a key-less descriptor", "method": "instantiate_object_from_desc", "args": [DESC],
                "writes": w, "d0": True})
    return out


def hook0_cases() -> list:
    """overlay_patch_template_u16_pair, and hook 0 (configure_renderer_for_display) with the mission built:
    nothing changed (geometry and clip bounds only), a depth change to 8 and to 4 (colour state, renderer
    hooks, template words, child 4's face flags), and to depth 1."""
    CHILD4, FACES, PAL = 0x30000, 0x30100, 0x30400
    out = [{"name": "patch template words", "method": "overlay_patch_template_u16_pair", "args": [0x1D5], "writes": []}]
    for name, depth, prev in (("unchanged", 8, 8), ("to depth 8", 8, 0), ("to depth 4", 4, 8), ("to depth 1", 1, 8)):
        w = [[0x207FC, 4, HOST], [HOST + 0x18, 2, 0x60], [HOST + 0x16, 2, 0x140], [HOST + 0x12, 2, 4], [HOST + 0x14, 2, 6],
             [HOST + 0xE, 2, depth], [HOST + 0xB4, 2, 0], [HOST + 0x34, 4, PAL], [HOST + 0x38, 2, 0x10], [HOST + 0x2AE, 4, 0x3A123],
             [HOST + 0xA2, 2, 0], [0x2100E, 2, prev], [0x2100C, 2, 0], [0x2159C, 4, 0x3EF00], [0x2158C, 4, CHILD4],
             [CHILD4 + 0x4A, 4, FACES], [0x23D88, 2, 2], [0x27020, 2, 0], [0x21010, 2, 0x60], [0x21012, 2, 0x140],
             [0x23FD0, 2, 0], [0x20F60, 4, 0], [0x20F64, 4, 0], [0x20F68, 4, 0],
             [HOST + 0x384, 4, 0x31000], [0x27188, 4, 0], [0x27198, 4, 0], [0x23FA0, 4, 0], [0x23FA4, 4, 0], [0x23FA8, 4, 0]]
        w += [[0x2719C + 4 * k, 4, 0] for k in range(8)]   # a fresh heap for the palette owner tables
        w += [[FACES + 0x20 * k + 0x13, 1, 0x40 + k] for k in range(5)]
        w += [[PAL + k, 1, (k * 13) & 0xFF] for k in range(0x200)]
        out.append({"name": "hook 0: " + name, "method": "configure_renderer_for_display", "args": [], "writes": w})
    return out


def depth8_cases() -> list:
    """Hook 0's depth-8 renderer routines: the dot plotter (sizes 1..4), the radar blips drawn and erased, lines
    drawn and restored (shallow, steep, reversed) and the span filler (rows whose spans differ and
    match, an odd screen start, a long run through the long-word writer; both writer tables in place)."""
    SCREEN, PANEL, REC = 0x30000, 0x34000, 0x3C000
    out = []
    host = [[0x207FC, 4, HOST], [HOST + 6, 4, SCREEN], [HOST + 0x10, 2, 0x140], [HOST + 0xE0, 4, PANEL], [HOST + 0xE4, 2, 0x140],
            [0x21564, 4, 0x100], [HOST + 0x1EB, 1, 0x77], [0x23FB8, 2, 0x0055], [0x27052, 2, 0x80], [0x27054, 2, 0x40]]
    pattern = [[SCREEN + 0x140 * r + c, 4, (0x01020304 * (r + 1) + c) & 0xFFFFFFFF] for r in range(0x60, 0x68) for c in range(0x40, 0x60, 4)]
    for size in (1, 2, 3, 4):
        out.append({"name": "dot depth 8: size %d" % size, "method": "plot_dot_depth8", "args": [REC, 0x10, (-3) & 0xFFFFFFFF, size],
                    "writes": host + pattern})
    for method in ("draw_radar_blip_depth8", "erase_radar_blip_depth8"):
        w = host + [[PANEL + 0x140 * r + c - 0x100, 4, 0xA0A0A0A0 + r] for r in range(8, 0x10) for c in range(0x20, 0x30, 4)]
        out.append({"name": method.replace('_', ' '), "method": method, "args": [0x22, 0xA], "writes": w})
    for name, x0, y0, x1, y1 in (("shallow", 4, 4, 0x30, 0xA), ("steep", 6, 2, 0xA, 0x28), ("reversed", 0x30, 0x20, 2, 3)):
        # (a zero-length line starts the dbra counter at -1 and runs 65536 steps, in the original as in the port)
        out.append({"name": "line depth 8: draw " + name, "method": "draw_line_depth8", "args": [x0, y0, x1, y1, 0x33, SCREEN, 0x140],
                    "writes": host + pattern})
        out.append({"name": "line depth 8: restore " + name, "method": "restore_line_depth8",
                    "args": [x0, y0, x1, y1, SCREEN, 0x140, PANEL, 0x140],
                    "writes": host + pattern + [[PANEL + 0x140 * r + c, 4, (0x10101010 * (r & 0xF) + 0x01020304 + c) & 0xFFFFFFFF]
                                                for r in range(0, 0x2A) for c in range(0, 0x40, 4)]})
    even = [0x4316, 0x4322, 0x4330, 0x4320, 0x432E, 0x431E]
    odd = [0x4316, 0x4322, 0x4362, 0x436C, 0x437C, 0x4388, 0x4378]
    tables = [[0x27224 + 4 * k, 4, even[k] if k < len(even) else 0x4338] for k in range(0x800)]
    tables += [[0x29224 + 4 * k, 4, odd[k] if k < len(odd) else 0x4396] for k in range(0x800)]
    NEW, OLD, PN, PO = 0x3D000, 0x3D400, 0x3DC00, 0x3DC04

    def row(spans):
        # header = the span count (as _bg_row writes it: record longs - 1), then (x << 16 | colour) per span
        words = [len(spans)] + [(x << 16) | c for x, c in spans]
        return words

    rows_new = [row([(0x10, 5), (0x40, 9), (0x100, 3)]), row([(0x7, 1), (0x13, 2), (0x100, 3)])]
    rows_old = [row([(0x10, 5), (0x48, 9), (0x100, 4)]), row([(0x9, 1), (0x13, 2), (0x100, 3)])]
    for name, screen in (("aligned screen", SCREEN), ("odd screen start", SCREEN + 1)):
        w = host + tables
        at = NEW
        for r in rows_new:
            w += [[at + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate(r)]
            at += 4 * len(r)
        at = OLD
        for r in rows_old:
            w += [[at + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate(r)]
            at += 4 * len(r)
        w += [[PN, 4, NEW], [PO, 4, OLD]]
        out.append({"name": "span rows depth 8: " + name, "method": "fill_span_rows_8bit", "args": [PN, PO, 2, screen, 0x40], "writes": w})
    return out


def erase_dot_cases() -> list:
    """erase_dots_depth8: three dot records (sizes 1, 3, 4; one pixel recoloured since, so it is left alone)
    and an empty list."""
    SCREEN = 0x30000
    out = []
    for name, n in (("three dots", 3), ("none", 0)):
        w = [[0x207FC, 4, HOST], [HOST + 0x10, 2, 0x140], [0x23FBC, 2, n]]
        w += [[SCREEN + k, 4, 0x55555555] for k in range(0, 0x600, 4)]
        for k, (size, x) in enumerate(((1, 0x10), (3, 0x20), (4, 0x30))):
            rec = 0x2C83C + 0xE * k
            w += [[rec, 4, SCREEN + 0x140 + x], [rec + 6, 2, size], [rec + 8, 1, 0x55], [rec + 9, 1, 0x11 + k],
                  [rec + 0xA, 1, 0x21 + k], [rec + 0xB, 1, 0x31 + k], [rec + 0xC, 1, 0x41 + k]]
        w.append([SCREEN + 0x140 + 0x20 + 1, 1, 0x99])
        out.append({"name": "erase dots depth 8: " + name, "method": "erase_dots_depth8", "args": [], "writes": w})
    return out


def face_colour_cases() -> list:
    """output_face_colour (and add_face_edges_to_rows through it): a triangle inside the view (no palette slot),
    a quad clipped by the right and bottom edges whose face still owns its slot, a polygon wholly outside, an edge
    equal to one already in its row's bucket (merged as its second face) beside one sorted after it, and the
    ground face (horizon tracking against the alternate bottom row)."""
    FACE, GROUND, POOL, OWNERS, USAGE, OLD = 0x3C000, 0x3C100, 0x3D000, 0x3E000, 0x3E400, 0x3D800
    base = [[0x2701A, 2, (-0x40) & 0xFFFF], [0x27018, 2, 0x3F], [0x27016, 2, (-0x30) & 0xFFFF], [0x20B2E, 2, 0x2F],
            [0x20B30, 2, 0x28], [0x27052, 2, 0x40], [0x27054, 2, 0x30], [0x2701C, 4, 0x00408000],
            [0x20F54, 4, POOL], [0x26FE0, 4, 0], [0x21590, 4, GROUND], [0x23FAC, 4, OWNERS], [0x23D94, 4, USAGE],
            [0x21578, 4, 0]]
    base += [[0x24FDC + 4 * r, 4, 0] for r in range(0x62)] + [[0x23FD4 + 2 * r, 2, 0] for r in range(0x62)]

    def poly(points):
        w = [[0x2C0B0, 2, len(points)]]
        for k, (x, y) in enumerate(points):
            w += [[0x2B338 + 8 * k, 4, x & 0xFFFFFFFF], [0x2B338 + 8 * k + 4, 4, y & 0xFFFFFFFF]]
        return w

    def face(at, slot):
        return [[at, 2, 0x1234], [at + 4, 4, 0x55], [at + 0xF, 1, slot]]

    out = []
    for name, f, slot, pts, extra in (
            ("triangle inside", FACE, 0xFF, [(-0x10, -0x10), (0x20, 0x8), (-0x8, 0x18)], []),
            ("quad clipped, slot owned", FACE, 7, [(0x10, 0x10), (0x60, 0x10), (0x60, 0x50), (0x10, 0x50)],
             [[OWNERS + 4 * 7, 4, FACE], [USAGE + 7, 1, 0]]),
            ("wholly outside", FACE, 0xFF, [(0x50, 0x0), (0x70, 0x0), (0x60, 0x10)], []),
            ("ground face", GROUND, 0xFF, [(-0x60, 0x0), (0x60, 0x0), (0x60, 0x40), (-0x60, 0x40)], [])):
        out.append({"name": "face colour: " + name, "method": "output_face_colour", "args": [f], "d0": True,
                    "writes": base + extra + face(f, slot) + poly(pts)})
    # an equal edge already in row 0x20 (no second face) and one with a larger x: the new left edge merges
    # into the first, the right edge (x 0x48) is inserted before the second (x 0x50)
    left_x = ((-0x10) << 16) + 0x8000 + (0x40 << 16)
    old = [[OLD, 4, 0x3C800], [OLD + 4, 4, 0], [OLD + 0xC, 4, left_x & 0xFFFFFFFF], [OLD + 0x10, 4, 0],
           [OLD + 0x16, 2, 0x40], [OLD + 0x18, 4, OLD + 0x20],
           [OLD + 0x20, 4, 0x3C800], [OLD + 0x24, 4, 0], [OLD + 0x2C, 4, (0x50 << 16) & 0xFFFFFFFF],
           [OLD + 0x30, 4, 0], [OLD + 0x36, 2, 0x40], [OLD + 0x38, 4, 0]]
    out.append({"name": "face colour: edge merged into its row", "method": "output_face_colour", "args": [FACE],
                "d0": True, "writes": base + [[0x24FDC + 4 * 0x20, 4, OLD]] + old + face(FACE, 0xFF)
                + poly([(-0x10, -0x10), (0x8, -0x10), (0x8, 0x10), (-0x10, 0x10)])})
    return out


def depth4_cases() -> list:
    """Hook 0's depth-4 renderer routines and the 1-bit ones (the run-writer table fillers have their cases above): the three span
    fillers (rows whose spans differ and match, an odd pixel start, a long run; A by handles with its alternate-row
    colour byte), lines drawn and restored, dots of each size at both nibble phases and erased (one pixel
    recoloured since), blips at both phases drawn and erased, the 1-bit blips (host rectangle calls) and the
    1-bit dot eraser."""
    SCREEN, PANEL, REC = 0x30000, 0x34000, 0x3C000
    out = []
    host = [[0x207FC, 4, HOST], [HOST + 6, 4, SCREEN], [HOST + 0x10, 2, 0x140], [HOST + 0xE0, 4, PANEL], [HOST + 0xE4, 2, 0x140],
            [0x21564, 4, 0x100], [HOST + 0x1EB, 1, 0x7C], [0x23FB8, 2, 0x00A5], [0x27052, 2, 0x80], [0x27054, 2, 0x40]]
    pattern = [[SCREEN + 0x140 * r + c, 4, (0x01020304 * (r + 1) + c) & 0xFFFFFFFF] for r in range(0x60, 0x68) for c in range(0x40, 0x60, 4)]
    for size in (1, 2, 3, 4):
        for sx in (0x10, 0x11):
            out.append({"name": "dot depth 4: size %d phase %d" % (size, sx & 1), "method": "plot_dot_depth4",
                        "args": [REC, sx, (-3) & 0xFFFFFFFF, size], "writes": host + pattern})
    w = host + [[SCREEN + k, 4, 0xA5A5A5A5] for k in range(0, 0x600, 4)] + [[0x23FBC, 2, 4]]
    for k, (phase, size, x) in enumerate(((0, 2, 0x10), (0, 4, 0x20), (1, 3, 0x30), (1, 4, 0x40))):
        rec = 0x2C83C + 0xE * k
        w += [[rec, 4, SCREEN + 0x140 + x], [rec + 4, 2, phase], [rec + 6, 2, size], [rec + 8, 1, 0xA5],
              [rec + 9, 1, 0x11 + k], [rec + 0xA, 1, 0x21 + k], [rec + 0xB, 1, 0x31 + k], [rec + 0xC, 1, 0x41 + k]]
    w.append([SCREEN + 0x140 + 0x20, 1, 0x95])
    out.append({"name": "erase dots depth 4", "method": "erase_dots_depth4", "args": [], "writes": w})
    for method in ("draw_radar_blip_depth4", "erase_radar_blip_depth4"):
        for x in (0x22, 0x23):
            w = host + [[PANEL + 0x140 * r + c - 0x100, 4, 0xA0A0A0A0 + r] for r in range(8, 0x10) for c in range(0x10, 0x20, 4)]
            w += [[SCREEN + 0x140 * r + c - 0x100, 4, 0x36363636] for r in range(8, 0x10) for c in range(0x10, 0x20, 4)]
            out.append({"name": method.replace('_', ' ') + " phase %d" % (x & 1), "method": method, "args": [x, 0xA], "writes": w})
    for method in ("draw_radar_blip_bw", "erase_radar_blip_bw"):
        out.append({"name": method.replace('_', ' '), "method": method, "args": [0x22, 0xA],
                    "writes": host + [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]})
    out.append({"name": "clear dots bw", "method": "clear_dots_bw", "args": [], "writes": [[0x23FBC, 2, 7]]})
    # the saved panel the restores copy from: a distinct byte per row and column pair
    panel = [[PANEL + 0x140 * r + c, 4, (0x10101010 * (r & 0xF) + 0x01020304 + c) & 0xFFFFFFFF] for r in range(0, 0x2A) for c in range(0, 0x20, 4)]
    for name, x0, y0, x1, y1 in (("shallow", 4, 4, 0x30, 0xA), ("steep", 7, 2, 0xA, 0x28), ("reversed", 0x31, 0x20, 2, 3)):
        out.append({"name": "line depth 4: draw " + name, "method": "draw_line_depth4", "args": [x0, y0, x1, y1, 0x5C, SCREEN, 0x140],
                    "writes": host + pattern})
        out.append({"name": "line depth 4: restore " + name, "method": "restore_line_depth4",
                    "args": [x0, y0, x1, y1, SCREEN, 0x140, PANEL, 0x140], "writes": host + pattern + panel})
    NEW, OLD, PN, PO = 0x3D000, 0x3D400, 0x3DC00, 0x3DC04

    def row(spans):
        return [len(spans)] + [(x << 16) | c for x, c in spans]

    rows_new = [row([(0x10, 0x55), (0x41, 0x99), (0x100, 0x33)]), row([(0x7, 0x11), (0x13, 0x22), (0x100, 0x33)])]
    rows_old = [row([(0x10, 0x55), (0x48, 0x99), (0x100, 0x44)]), row([(0x9, 0x11), (0x13, 0x22), (0x100, 0x33)])]
    for method, table, l0 in (("fill_span_rows_4bit_a", 0x3D22, 0x3E34), ("fill_span_rows_4bit_b", 0x3ECE, 0x3FD6),
                              ("fill_span_rows_4bit_c", 0x4070, 0x4178)):
        tab = [[table + 4 * k, 4, l0 + off] for k, off in enumerate((0, 6, 0x18, 0x20, 0x24, 0x50, 0x50, 0x50, 0x50))]
        w = host + tab + [[SCREEN + k, 4, 0x77777777] for k in range(0, 0x200, 4)]
        at = NEW
        for r in rows_new:
            w += [[at + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate(r)]
            at += 4 * len(r)
        at = OLD
        for r in rows_old:
            w += [[at + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate(r)]
            at += 4 * len(r)
        handles = method.endswith("_a")
        w += [[PN, 4, NEW], [PO, 4, OLD]]
        args = [PN, PO, 2, SCREEN, 0x20] if handles else [NEW, OLD, 2, SCREEN, 0x20]
        out.append({"name": "span rows depth 4: " + method[-1].upper(), "method": method, "args": args, "writes": w})
    return out


def template_factory_cases() -> list:
    """The template-registration class factories hook 0's mission build reaches: each slot table empty, at its
    redirect threshold and full (ordnance 9 slots, redirect only at index 8; shadow 6, past 4; cloud 2, any index
    but 0, both cloud definitions), the single-template ones, and the type-0x10 controller (fresh heap)."""
    TMPL, KEY = 0x3C000, 0x0F
    DEFS = 0x3B800                       # a definition record per key (only +6 is read or written)
    base = [[0x2074C + 4 * k, 4, DEFS + 0x20 * k] for k in range(0x2C)]
    base += [[DEFS + 0x20 * k + 6, 4, 0xAAAA0000 + k] for k in range(0x2C)]

    def table(at, n, full):
        return [[at + 4 * k, 4, (0x3C100 + 0x10 * k) if k < full else 0] for k in range(n)]

    out = []
    for name, method, at, n, fulls in (("ordnance", "leyte_register_ordnance_templates", 0x2CDD4, 9, (0, 7, 8, 9)),
                                       ("shadow", "register_shadow_templates", 0x2CD70, 6, (0, 4, 5, 6)),
                                       ("cloud", "leyte_register_route_ship_templates", 0x2CE04, 2, (0, 1, 2))):
        for full in fulls:
            out.append({"name": "template factory: %s, %d of %d full" % (name, full, n), "method": method,
                        "args": [TMPL, KEY], "d0": True, "writes": base + table(at, n, full)})
    for method in ("register_parachute_template", "leyte_register_template_2c834", "leyte_register_template_2c830",
                   "leyte_register_template_2c82c"):
        out.append({"name": "template factory: " + method.replace('_', ' '), "method": method, "args": [TMPL, KEY],
                    "d0": True, "writes": base})
    ARENA = 0x30000
    heap = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    out.append({"name": "template factory: type 0x10 controller", "method": "leyte_create_type10_controller",
                "args": [TMPL, 0x10], "d0": True, "writes": base + heap + [[TMPL + 0x14, 4, 0x01020304]]})
    return out


def aircraft_cases() -> list:
    """The enemy-fighter class factory and its chain: init_aircraft_controller (fresh heap; negative odd fuel),
    create_shadow_controller, create_enemy_fighter capturing the template and building a fighter, and
    spawn_object_shadow through the real pool spawn and shadow factory (the template's descriptor key names a
    definition whose +6 is 0x1c9da; the camera beside the spawn point): owned by the player (0x20b28) and hidden
    (host +0xb0 0), with no player pointer, and out of range (not activated, so no vertex buffer: retired, 0). The
    fighter's own shadow is spawned at its position, out of range, and retired.)"""
    ROOT, LIST, SLOTS, BASE, ARENA, TMPL = 0x30000, 0x30100, 0x30200, 0x31000, 0x32000, 0x30600
    OBJ, DEF, SDEF, PLAYER = 0x3C000, 0x3C800, 0x3C900, 0x3CA00
    REC_OFF, TYPE, SKEY = 0x40, 1, 5
    REC = BASE + REC_OFF

    def pool(nverts, b0):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [HOST + 0x8C, 4, BASE],
             [HOST + 0xA8, 2, 0], [HOST + 0xE, 2, 8], [HOST + 0xAA, 2, 0], [HOST + 0xB0, 2, b0], [0x2100E, 2, 8],
             [0x23FAC, 4, 0x30800], [0x20F2C, 4, 0], [0x20B24, 4, 0], [0x219C4, 4, 0], [0x219B4, 4, 0], [0x2B22C, 4, 0],
             [0x21068, 4, 0], [0x2107A, 2, 0], [0x21078, 2, 9],
             [0x23D68, 4, 0x100], [0x23D64, 4, 0], [0x23D60, 4, 0x300], [0x23C2C, 4, ROOT],
             [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [ROOT + 0x32, 4, SLOTS + 8], [ROOT + 0x30, 2, 3], [ROOT + 0x2E, 2, 3],
             [SLOTS, 4, 0x10], [SLOTS + 4, 1, 0xFF], [LIST + 0x1A + 0x14 + 0x18 * TYPE, 4, REC_OFF], [TMPL + 0x10, 2, TYPE]]
        w += [[0x2719C + 4 * k, 4, 0] for k in range(8)]
        for k in range(3):
            w += [[SLOTS + 8 + 0x18 * k + m, 1, 0] for m in range(0x18)]
        w += [[REC, 1, 2], [REC + 1, 1, SKEY], [REC + 0x16, 2, 0], [REC + 0x18, 2, 0], [REC + 0x1A, 2, nverts], [REC + 0x1C, 2, 0],
              [REC + 0x1E, 2, 0], [REC + 0x20, 2, 0]]
        w += [[0x2074C + 4 * SKEY, 4, SDEF], [SDEF + 6, 4, 0x1C9DA], [0x2CD6C + 4 * 2, 4, TMPL]]
        w += [[0x23C40 + 4 * k, 4, 0x11111111] for k in range(6)]
        return w

    fighter = [[0x2074C + 4 * 7, 4, DEF], [DEF + 0x1A, 2, 0x100], [DEF + 0x1C, 2, (-0x33) & 0xFFFF], [DEF + 0x42, 2, 0x18],
               [DEF + 0x44, 2, 0x4567], [OBJ + 4, 4, 0x123400], [OBJ + 8, 4, 0x2000], [OBJ + 0xC, 4, 0x56780],
               [OBJ + 0x14, 4, 0x01000000], [OBJ + 0x29, 1, 3]]
    heap = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    out = [{"name": "aircraft: init controller", "method": "init_aircraft_controller", "args": [OBJ, 7, 0x2B], "d0": True,
            "writes": heap + fighter},
           {"name": "aircraft: shadow controller", "method": "create_shadow_controller", "args": [OBJ, 7], "d0": True,
            "writes": heap + fighter + [[0x23C40 + 4 * k, 4, 0x11111111] for k in range(6)]},
           {"name": "aircraft: enemy fighter captured as template", "method": "create_enemy_fighter", "args": [OBJ, 7, 1],
            "d0": True, "writes": heap + fighter + [[0x2C5AE, 4, 0]]}]
    for name, x, player in (("player's, hidden", 0x100, OBJ), ("no player object", 0x100, 0), ("out of range: retired, 0", 0x900000, OBJ)):
        w = pool(3, 0) + fighter + [[0x20B28, 4, PLAYER if player else 0], [PLAYER, 4, player]]
        out.append({"name": "aircraft: shadow " + name, "method": "spawn_object_shadow", "args": [x, 0, 0x300, 2, OBJ],
                    "d0": True, "writes": w, "stack_guard": [0x3E000, 0x3F040]})
    w = pool(3, 1) + fighter + [[0x20B28, 4, 0], [0x2C5AE, 4, 0x3FF00]]
    out.append({"name": "aircraft: enemy fighter with its shadow", "method": "create_enemy_fighter", "args": [OBJ, 7, 1],
                "d0": True, "writes": w, "stack_guard": [0x3E000, 0x3F040]})
    # aircraft_create (0xf4a8): the player (first key 1) with its stores from the host, the player with both tanks
    # scaled to empty, key 1 with a player already, keys 2 and 3 (0x21074 free and claimed), and a key without a
    # shadow; shadow templates for kinds 1, 5 and 6 as well
    host = [[HOST + 0xB9, 1, 3], [HOST + 0xBB, 1, 2], [HOST + 0xBD, 1, 9], [HOST + 0xB6, 2, 0x3C]]
    shadows = [[0x2CD6C + 4 * k, 4, TMPL] for k in (1, 5, 6)]
    cases = (("the player, stores loaded", 1, 0, [], 0x3C),
             ("the player, tanks scaled empty", 1, 0, [], 0),
             ("key 1 with a player already", 1, PLAYER, [], 0x3C),
             ("key 2", 2, PLAYER, [], 0x3C),
             ("key 3, 0x21074 free", 3, PLAYER, [[0x21074, 4, 0]], 0x3C),
             ("key 3, 0x21074 claimed", 3, PLAYER, [[0x21074, 4, 0x3EE00]], 0x3C),
             ("key 4, no shadow", 4, PLAYER, [], 0x3C))
    for name, key, player, extra, pct in cases:
        w = pool(3, 1) + [[0x2074C + 4 * key, 4, DEF]] + fighter[1:] + host + shadows + extra
        w += [[0x20B28, 4, player], [HOST + 0xB6, 2, pct]]
        out.append({"name": "aircraft create: " + name, "method": "aircraft_create", "args": [OBJ, key, 0x2B], "d0": True,
                    "writes": w, "stack_guard": [0x3E000, 0x3F040]})
    return out


def anchor_cases() -> list:
    """find_anchor_point (0x11fa) through Leyte's scene walker (0x82a), the verified traversal and the extended visitor
    (0x108e). The scene: a group holding runway leaves R1 (near the reference) and R2 (far), then a sub-group with
    node N1 beside R1, node N2 beside R2 and a kind-1 leaf (placed and turned). Each runway leaf also has a face
    lying above ground (refused) and a plain face. Mode 0: the kind-1 leaf with its record's attitude. Mode 1: N1
    of kind 0x19 (R1 taken); N1 0x18 (R1 vetoed, R2 taken through N2's 0x19); N1 0x18 with N2 out of range (R2 keeps
    the vetoing D5: refused too); no node in range with the caller's D5 0 (R1 taken) and 0x18 (none)."""
    BASE, GROUP, SUB, R1, R2, N1, N2, K1, ANCHOR = 0x30000, 0x30100, 0x30200, 0x30300, 0x30400, 0x30500, 0x30600, 0x30700, 0x30F00

    def leaf(w, addr, kind, rng, verts, faces):
        w += [[addr, 1, 2], [addr + 1, 1, kind], [addr + 0xE, 4, rng], [addr + 0x18, 2, 0], [addr + 0x1A, 2, len(verts)],
              [addr + 0x1E, 2, len(faces)]]
        for k, (x, y, z) in enumerate(verts):
            w += [[addr + 0x22 + 12 * k + 4 * m, 4, v & 0xFFFFFFFF] for m, v in enumerate((x, y, z))]
        face = addr + 0x22 + 12 * len(verts) + 12 * len(faces)
        for ftype, idx in faces:
            w += [[face + 8, 1, len(idx)], [face + 9, 1, ftype]] + [[face + 0xE + 2 * i, 2, v] for i, v in enumerate(idx)]
            face += 0xE + 2 * len(idx)

    def record(w, at, child, att, pos):
        w.append([at + 0x14, 4, child - BASE])
        w += [[at + 2 + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(att)]
        w += [[at + 8 + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate(pos)]

    def scene(k1, r1, r2):
        w = [[a, 4, 0] for a in range(BASE, 0x31000, 4)]
        w += [[0x207FC, 4, HOST], [HOST + 0x8C, 4, BASE], [HOST + 0x90, 2, 1], [BASE + 0x18 + 0x8E, 4, GROUP - BASE],
              [GROUP, 1, 3], [GROUP + 0x18, 2, 3], [SUB, 1, 3], [SUB + 0x18, 2, 3]]
        record(w, GROUP + 0x1A, R1, (0, 0, 0), (100, 0, 50))
        record(w, GROUP + 0x1A + 0x18, R2, (0, 0, 0), (3000, 0, 3000))
        record(w, GROUP + 0x1A + 0x30, SUB, (0, 0, 0), (0, 0, 0))
        record(w, SUB + 0x1A, N1, (0, 0, 0), (40, 0, 10))
        record(w, SUB + 0x1A + 0x18, N2, (0, 0, 0), (2900, 0, 3000))
        record(w, SUB + 0x1A + 0x30, K1, (0x100, (-0x80), 0x40), (-200, 0, 70))
        runway = [(0, -5, 0), (30, -5, 0), (0, 30, 30)]
        for at in (R1, R2):
            leaf(w, at, 0, 0, runway, [(0x70, [0, 1, 2]), (0x70, [2, 0, 1]), (0x20, [0, 1, 2])])
        leaf(w, N1, k1, r1, [(0, 0, 0), (10, 0, 0), (0, 0, 10)], [(0x20, [0, 1, 2])])
        leaf(w, N2, 0x19, r2, [(0, 0, 0), (10, 0, 0), (0, 0, 10)], [(0x20, [0, 1, 2])])
        leaf(w, K1, 1, 0, [(5, 0, 5), (50, 0, 0), (0, 0, 50)], [(0x30, [0, 1, 2])])
        w += [[ANCHOR + 4 * k, 4, 0] for k in range(6)] + [[ANCHOR + 0x18, 2, 0]]
        return w

    out = []
    for name, mode, k1, r1, r2, d5 in (("mode 0, the kind-1 leaf", 0, 0x19, 0x200, 0x200, -1),
                                       ("mode 1, N1 0x19: R1 taken", 1, 0x19, 0x200, 0x200, -1),
                                       ("mode 1, N1 0x18 vetoes R1, R2 taken", 1, 0x18, 0x200, 0x200, -1),
                                       ("mode 1, N1 0x18, N2 out of range: the veto carries to R2", 1, 0x18, 0x200, 1, -1),
                                       ("mode 1, no node in range, caller's D5 0", 1, 0x19, 1, 1, 0),
                                       ("mode 1, no node in range, caller's D5 0x18", 1, 0x19, 1, 1, 0x18)):
        case = {"name": "anchor: " + name, "method": "find_anchor_point", "args": [ANCHOR, mode, d5],
                "writes": scene(k1, r1, r2), "stack_guard": [0x3B000, 0x3F040]}
        if d5 >= 0:
            case["d5_in"] = d5
        out.append(case)
    # hook 2 (start_mission_scene) on the same scene: no previous scene object; scene record 1's object (+0x74) a
    # group without children; the player controller (*0x20b28) with its object and type record; no clouds, no
    # sound host (+0x296 0); the frame counter 0x21020
    CTL, TYPE, POBJ, ROOT_S, TABLE, SOBJ = 0x31000, 0x31800, 0x31900, 0x31A00, 0x31B00, 0x31C00
    w = scene(0x19, 0x200, 0x200)
    w += [[0x23C30, 4, 0], [0x23C2C, 4, ROOT_S], [ROOT_S + 0x32, 4, TABLE], [TABLE + 0x18 + 0x74, 4, SOBJ],
          [SOBJ + 0x28, 1, 3], [SOBJ + 0x30, 2, 0], [SOBJ + 0x2A, 4, BASE], [SOBJ + 4, 4, 0x100], [SOBJ + 8, 4, 0x200], [SOBJ + 0xC, 4, 0x300],
          [0x20B28, 4, CTL], [CTL, 4, POBJ], [POBJ + 4, 4, 0x123], [POBJ + 8, 4, 0x456], [POBJ + 0xC, 4, 0x789],
          [CTL + 0x20, 2, 0x100], [CTL + 0x22, 2, 0x3FFF], [CTL + 0x24, 2, 0x40], [CTL + 4, 4, TYPE], [TYPE + 0x46, 2, 0x1000],
          [CTL + 0x626, 2, 0x2000], [HOST + 0xAE, 2, 0], [HOST + 0x296, 4, 0], [0x20F2C, 4, 0], [0x2B22C, 4, 0], [0x21068, 4, 0],
          [0x21020, 4, 0x100], [0x2B250, 4, 7]]
    w += [[0x23C40 + 4 * k, 4, 0x11111111] for k in range(6)]
    out.append({"name": "hook 2: start mission scene 1", "method": "start_mission_scene", "args": [1], "writes": w,
                "stack_guard": [0x3B000, 0x3F040]})
    return out


def mission_factory_cases() -> list:
    """The mission objects' class factories hook 2 reaches (fresh heap each): the 0x32- and 0x40-byte flagged
    controllers, the turret (object y 0 and not), the flak (word +0x3c 4 and 5), and the aircraft spawner over each
    range (0x80e80, 0xc15c0, 0x40740, another) with key 0x18 and another, the rng ring primed with distinct longs."""
    OBJ, DEF, ARENA = 0x3C000, 0x3C800, 0x32000
    heap = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    defs = [[0x2074C + 4 * k, 4, DEF + 0x10 * k] for k in range(0x2C)]
    obj = [[OBJ + 0x14, 4, 0x01000000]]
    ring = [[0x2C5B8 + 4 * k, 4, (0x9E3779B9 * (k + 1)) & 0xFFFFFFFF] for k in range(0x37)]
    out = []
    for method in ("create_view_attached_controller", "create_cloud_controller"):
        out.append({"name": "mission factory: " + method.replace('_', ' '), "method": method, "args": [OBJ, 0x2B], "d0": True,
                    "writes": heap + defs + obj})
    for y in (0, 0x40):
        out.append({"name": "mission factory: turret, y 0x%x" % y, "method": "create_turret_controller", "args": [OBJ, 0x24],
                    "d0": True, "writes": heap + defs + obj + [[OBJ + 8, 4, y]]})
    for n in (4, 5):
        out.append({"name": "mission factory: flak, word +0x3c %d" % n, "method": "create_flak_controller", "args": [OBJ, 0x23],
                    "d0": True, "writes": heap + defs + obj + [[OBJ + 0x3C, 2, n]]})
    for k, (rng, key) in enumerate(((0x80E80, 0x18), (0xC15C0, 0x19), (0x40740, 0x18), (0x1234, 0x19))):
        out.append({"name": "mission factory: spawner, range 0x%x key 0x%x" % (rng, key), "method": "create_aircraft_spawner_controller",
                    "args": [OBJ, key], "d0": True,
                    "writes": heap + defs + obj + ring + [[OBJ + 0x18, 4, rng], [0x2C5B4, 4, 0x2C5BC + 4 * (k * 5)]]})
    return out


def ship_factory_cases() -> list:
    """Leyte's ship classes (fresh heap each; definition word +0x1a 0x321): the carrier with the anchor mode word 0,
    with the anchor within the object's range (it becomes the first carrier, mode 2), beyond it, and with a first
    carrier already set; each with kind 0 or 1; the sinking, basic, escort and firing ships with kind 0 and 1."""
    OBJ, DEF, ARENA = 0x3C000, 0x3C800, 0x32000
    heap = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    defs = [[0x2074C + 4 * k, 4, DEF + 0x40 * k] for k in range(0x2C)] + [[DEF + 0x40 * k + 0x1A, 2, 0x321] for k in range(0x2C)]
    obj = [[OBJ + 4, 4, 0x1000], [OBJ + 8, 4, 0], [OBJ + 0xC, 4, 0x2000], [OBJ + 0x14, 4, 0x01000000], [OBJ + 0x16, 2, 0x0012],
           [OBJ + 0x18, 4, 0x77]]
    anchor = [[0x23D6C, 4, 0x1100], [0x23D70, 4, 0], [0x23D74, 4, 0x2100]]
    out = []
    for name, mode, rng, first, kind in (("no anchor mode", 0, 0x10000, 0, 0), ("anchor in range", 1, 0x10000, 0, 1),
                                         ("anchor beyond range", 1, 0x10, 0, 0), ("a first carrier already", 1, 0x10000, 0x3EEE0, 1)):
        out.append({"name": "ship factory: carrier, " + name, "method": "create_carrier_controller", "args": [OBJ, 0x1B, kind],
                    "d0": True, "writes": heap + defs + obj + anchor + [[0x23D84, 2, mode], [0x2104C, 4, first], [OBJ + 0x20, 4, rng]],
                    "stack_guard": [0x3E000, 0x3F040]})
    for method in ("create_sinking_controller", "create_basic_ship_controller", "leyte_create_escort_controller"):
        for kind in (0, 1):
            out.append({"name": "ship factory: %s, kind %d" % (method.replace('_', ' '), kind), "method": method,
                        "args": [OBJ, 0x1C, kind], "d0": True, "writes": heap + defs + obj})
    out.append({"name": "ship factory: firing ship", "method": "create_firing_ship_controller", "args": [OBJ, 0x22], "d0": True,
                "writes": heap + defs + obj})
    return out


def route_ship_cases() -> list:
    """The kind-99 class 0x1ec7e and its helpers on a fresh heap. The owner group holds a non-shape child and a route
    shape (kind 2, word +0x3c 1, no controller, face record flagged; four vertices, shift 2), the ship placed within
    0xa9 of the second: kind 99 builds the route (the nearest point 1), steps past it to the third and turns toward
    it; kind 99 under a group without such a shape clears the kind; kind 5 builds nothing. The waypoint step on its
    static point (+0x622 / +0x626) near and far, and the heading helper with deltas past 15 bits."""
    OBJ, DEF, ARENA, GROUP, TABLE, OTHER, SHAPE, FACES, IDX, VERTS = (0x3C000, 0x3C800, 0x32000, 0x3CA00, 0x3CB00,
                                                                     0x3CC00, 0x3CD00, 0x3CE00, 0x3CF00, 0x3D000)
    heap = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    defs = [[0x2074C + 4 * k, 4, DEF] for k in range(0x2C)]
    verts = [(0x400, 0, 0x400), (0x1000, 0x20, 0x800), (0x4000, 0, 0x4000), (0x800, 0, 0x6000)]

    def scene(with_shape):
        w = [[OBJ + 4, 4, 0x1000 // 4 + 0x40], [OBJ + 8, 4, 0], [OBJ + 0xC, 4, 0x800 // 4 - 0x30], [OBJ + 0x14, 4, 0x01000000],
             [OBJ + 0x24, 4, GROUP], [GROUP + 0x24, 4, 0], [GROUP + 0x30, 2, 2], [GROUP + 0x32, 4, TABLE],
             [TABLE + 0x14, 4, OTHER], [TABLE + 0x18 + 0x14, 4, SHAPE], [OTHER + 0x28, 1, 3], [OTHER, 4, 0]]
        w += [[SHAPE + 0x28, 1, 2 if with_shape else 4], [SHAPE + 0x3C, 2, 1], [SHAPE, 4, 0], [SHAPE + 0x4A, 4, FACES],
              [SHAPE + 0x36, 2, 2], [SHAPE + 0x46, 4, VERTS], [FACES + 0x13, 1, 2], [FACES + 0x14, 2, 4], [FACES + 0x18, 4, IDX]]
        w += [[IDX + 2 * k, 2, 3 - k] for k in range(4)]
        for k, (x, y, z) in enumerate(verts):
            w += [[VERTS + 12 * (3 - k) + 4 * m, 4, v] for m, v in enumerate((x, y, z))]
        return w

    out = []
    for name, kind, shape in (("kind 99, a route", 99, True), ("kind 99, no route shape", 99, False), ("kind 5", 5, True)):
        out.append({"name": "route ship: " + name, "method": "leyte_create_route_ship", "args": [OBJ, 0x27, kind], "d0": True,
                    "writes": heap + defs + scene(shape), "stack_guard": [0x3E000, 0x3F040]})
    CTL = 0x3D400
    for name, px in (("static point near", 0x1000 + 0x50), ("static point far", 0x1000 + 0x400)):
        w = [[CTL, 4, OBJ], [CTL + 0x612, 4, 0], [CTL + 0x62D, 1, 0], [CTL + 0x622, 4, px], [CTL + 0x626, 4, 0x2000],
             [OBJ + 4, 4, 0x1000], [OBJ + 0xC, 4, 0x2000], [0x3D800, 4, 0], [0x3D804, 4, 0]]
        out.append({"name": "route ship: waypoint, " + name, "method": "leyte_next_waypoint", "args": [CTL, 0x3D800, 0x3D804],
                    "d0": True, "writes": w})
    for name, x, z in (("small", 0x1100, 0x2300), ("past 15 bits", 0x91000, (-0x52000) & 0xFFFFFFFF)):
        out.append({"name": "route ship: heading, " + name, "method": "leyte_heading_to_point", "args": [OBJ, x, 0, z], "d0": True,
                    "writes": [[OBJ + 4, 4, 0x1000], [OBJ + 0xC, 4, 0x2000]]})
    return out


def frame_batch1_cases() -> list:
    """The first routines of the frame step's closure. The vector routines over no, three and in-place vectors
    (camera matrix 0x23c74; a matrix argument; component scales). apply_controller_velocities: a moving object, one
    whose object lacks flag bit 5, a controller without an object; and no controller. The flagged tick: ticks
    through the definition's +2 (a stand-in that logs), an object without the flag, a definition without a tick, a
    dropped controller, and a count long above its low word (the move-down path, nothing to move). Contact ageing: a
    touched record, a plain one freed, one whose object a gives up its fractal reference first. The visible-object
    cull over both passes: no view point (the point cursor stays), camera inside, in front inside and outside the
    cone on each axis, behind. The face-emit walks: two roots with chains, a far face, a flagged one, back-facing
    one- and two-sided faces, in and out of near mode."""
    out = []
    MAT = [0x1000, 0x10, -0x20, 0x300, 0xF00, 0x7FF, -0x1000, 0x123, 0x456]
    SRC, DST, M = 0x3C000, 0x3C400, 0x3C800
    vecs = [(0x1234, -0x5678, 0x9ABC), (-0x100, 0x200, -0x300), (0x7FFF0, -0x80000, 0x12)]
    cam = [[0x23C74 + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(MAT)]

    def int3(at, vs):
        return [[at + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for k, v in enumerate(vs) for m, c in enumerate(v)]
    for name, count, shift, dst in (("none", 0, 0, DST), ("three", 3, 0, DST), ("three, shifted", 3, 4, DST), ("in place", 2, 1, SRC)):
        out.append({"name": "camera transform: " + name, "method": "transform_int3_vecs_asr_by_1d744",
                    "args": [count, SRC, dst, shift], "writes": cam + int3(SRC, vecs)})
    recs = [[SRC + 12 * k + 2 + 4 * m, 2, c & 0xFFFF] for k, v in enumerate(vecs) for m, c in enumerate(v)]
    mat = [[M + 2 * k, 2, v & 0xFFFF] for k, v in enumerate(MAT)]
    for name, count in (("none", 0), ("three", 3)):
        out.append({"name": "vecs12 by matrix: " + name, "method": "transform_vecs12_by_matrix3", "args": [count, SRC, DST, M],
                    "writes": mat + recs})
    for name, count, scale, dst in (("none", 0, (2, 3, 4), DST), ("three", 3, (3, -2, 0x10000), DST), ("in place", 2, (-1, 1, 7), SRC)):
        out.append({"name": "scale int3: " + name, "method": "scale_int3_vecs",
                    "args": [count, SRC, dst] + [x & 0xFFFFFFFF for x in scale], "writes": int3(SRC, vecs)})
    # controllers
    LIST = 0x21BD4
    ctl = [0x3C000, 0x3C100, 0x3C200, 0x3C300]
    obj = [0x3C400, 0x3C500, 0, 0x3C600]
    w = []
    for k in range(4):
        w += [[LIST + 4 * k, 4, ctl[k]], [ctl[k], 4, obj[k]], [ctl[k] + 8, 4, 0x10 * (k + 1)], [ctl[k] + 0xC, 4, (-0x20) & 0xFFFFFFFF],
              [ctl[k] + 0x10, 4, 0x30], [ctl[k] + 0x14, 4, 0xAAAA], [ctl[k] + 0x2E, 4, 0x1234]]
        if obj[k]:
            w += [[obj[k] + 4, 4, 0x1000 + k], [obj[k] + 8, 4, 0x2000], [obj[k] + 0xC, 4, 0x3000],
                  [obj[k] + 0x14, 4, 0x0000DF00 if k == 1 else 0x00002000]]      # flags byte +0x16: bit 5 moves it
    for name, count in (("four controllers", 4), ("none", 0)):
        out.append({"name": "controller velocities: " + name, "method": "apply_controller_velocities", "args": [],
                    "writes": w + [[0x219B2, 2, count]]})
    DEFS, TICK = 0x3C800, SERVICES[0x302][0]
    for name, count_long in (("ticks and a dropped controller", 4), ("count long above its low word", 0x10004), ("none", 0)):
        w = [[0x219B4, 4, count_long]]
        for k in range(4):
            w += [[LIST + 4 * k, 4, ctl[k]], [ctl[k], 4, obj[k]], [ctl[k] + 4, 4, DEFS + 0x10 * k], [DEFS + 0x10 * k + 2, 4, 0 if k == 3 else TICK]]
            if obj[k]:
                w += [[obj[k] + 0x14, 4, 0x00FB0000 if k == 1 else 0x00040000]]
        out.append({"name": "flagged tick: " + name, "method": "tick_flagged_object_controllers", "args": [], "writes": w})
    # contact records
    SLOTS, RA, RB, RC, OA, OB, OT, BLOCK = 0x215A4, 0x3C008, 0x3C048, 0x3C088, 0x3C400, 0x3C500, 0x3C600, 0x3C700
    w = [[0x2719C + 4 * k, 4, 0] for k in range(8)] + [[0x207FC, 4, HOST], [0x219A6, 2, 0]]
    w += [[SLOTS + 4 * 3, 4, RA], [RA, 4, OA], [RA + 4, 4, OB], [RA + 8, 2, 3], [RA - 4, 1, 4],
          [SLOTS + 4 * 0x40, 4, RB], [RB, 4, OA], [RB + 4, 4, OB], [RB + 8, 2, 0], [RB - 4, 1, 4],
          [SLOTS + 4 * 0xFF, 4, RC], [RC, 4, OA], [RC + 4, 4, OT], [RC + 8, 2, 0], [RC - 4, 1, 4],
          [OA + 0x14, 4, 0x00002000], [OA + 0x2E, 4, BLOCK], [BLOCK, 2, 2], [OB + 0x14, 4, 0], [OT + 0x14, 4, 0x00000200]]
    out.append({"name": "contact ageing: touched, freed, fractal released", "method": "age_contact_records", "args": [], "writes": w})
    out.append({"name": "contact ageing: empty table", "method": "age_contact_records", "args": [], "writes": []})
    # the visible-object cull
    VIS, PTS = 0x22994, 0x21F44
    identity = [[0x23C74 + 2 * k, 2, 0x1000 if k in (0, 4, 8) else 0] for k in range(9)]
    #          flags +0x17, metric, radius, shift, point (x, y, z) or None when it has no view point
    objects = [(0x04, 0x900, 0x100, 0, None),                       # no view point: kept, the cursor stays
               (0x00, 0x100, 0x200, 0, (5, 5, -9)),                 # camera inside: kept, flags |= 8
               (0x00, 0x4000, 0x100, 0, (0x20, 0x10, 0x200)),       # in front, x dominant, inside the cone
               (0x00, 0x4000, 0x100, 0, (0x900, 0x10, 0x200)),      # x dominant, outside
               (0x00, 0x4000, 0x400, 2, (-0x10, -0x150, 0x200)),    # y dominant, inside (radius shifted)
               (0x00, 0x4000, 0x100, 0, (0x10, 0x900, 0x200)),      # y dominant, outside
               (0x00, 0x4000, 0x100, 0, (0x10, 0x10, 0)),           # not in front
               (0x00, 0x4000, 0x100, 0, (0x10, 0x10, -0x40))]       # behind
    w = list(identity)
    points = [o[4] for o in objects if o[4] is not None]
    w += int3(PTS, points) + [[0x219B8, 4, len(points)]]
    for k, (flags, metric, radius, shift, _pt) in enumerate(objects):
        o = 0x3C000 + 0x100 * k
        w += [[VIS + 4 * k, 4, o], [o + 0x14, 4, flags], [o + 0x1C, 4, metric], [o + 0x20, 4, radius], [o + 0x34, 2, shift]]
    for name, first in (("three near, five far", 3), ("all in the far pass", 0), ("all in the near pass", len(objects))):
        out.append({"name": "visible cull: " + name, "method": "build_visible_object_list", "args": [],
                    "writes": w + [[0x219AA, 2, first], [0x219BC, 4, len(objects)]], "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "visible cull: nothing listed", "method": "build_visible_object_list", "args": [],
                "writes": identity + [[0x219AA, 2, 0], [0x219BC, 4, 0], [0x219B8, 4, 0]], "stack_guard": [0x3E000, 0x3F040]})
    # the face-emit walks
    OBJ, FACES, QUEUE, BACKQ, NORMALS, VERTS, IDX = 0x3C000, 0x3C100, 0x3C400, 0x3C500, 0x3C600, 0x3C700, 0x3C800
    #        distance, flags +0x13, next +0x12, normal (x, y, z)
    faces = [(0x500, 0x00, 1, (0, 0, 0x100)),        # root 0: one-sided, facing the eye
             (0x500, 0x01, 2, (0, 0, -0x100)),       # two-sided, facing away: flagged in near mode
             (0x500, 0x00, 3, (0, 0, -0x100)),       # one-sided, facing away: ends the chain (queued behind in near mode)
             (0x500, 0x00, 0xFF, (0, 0, 0x100)),     # never reached
             (0x500, 0x10, 5, (0, 0x100, 0)),        # root 1 (index 4): flag 0x10 cleared when it faces the eye
             (0x100, 0x00, 0xFF, (0, 0x100, 0)),     # far: ends the chain
             (0x500, 0x02, 0xFF, (0, 0, 0x100)),     # root 2 (index 6): +0x13 bit 1, next root
             (0x500, 0x01, 0xFF, (0x100, 0, 0))]     # root 3 (index 7): last of its chain
    w = [[0x2C0BA, 4, OBJ], [OBJ + 0x1C, 4, 0x300], [OBJ + 0x46, 4, VERTS], [0x2C4C6, 4, FACES], [0x2C2C2, 4, QUEUE],
         [0x2C0BE, 4, BACKQ], [0x219A8, 2, 0x20], [0x2B286, 4, NORMALS], [0x2B296, 2, 0], [0x2B29C, 2, 0], [0x2B29A, 2, 0x10],
         [0x2B298, 2, 0x40]]
    for k, (dist, flags, nxt, normal) in enumerate(faces):
        f = FACES + 0x20 * k
        w += [[f + 0x1C, 4, dist], [f + 0x13, 1, flags], [f + 0x12, 1, nxt], [f + 2, 2, 0xEEEE], [f + 0x18, 4, IDX + 2 * k], [IDX + 2 * k, 2, 0]]
        w += [[NORMALS + 12 * k + 2 + 4 * m, 2, c & 0xFFFF] for m, c in enumerate(normal)]
    for method in ("emit_faces_in_order", "emit_faces_unnumbered"):
        for name, near in (("far mode", 0), ("near mode", 1)):
            out.append({"name": "%s: %s" % (method.replace('_', ' '), name), "method": method, "args": [],
                        "writes": w + [[0x2C0AE, 2, 8], [0x2C0AC, 2, near]], "stack_guard": [0x3E000, 0x3F040]})
        out.append({"name": "%s: no roots" % method.replace('_', ' '), "method": method, "args": [],
                    "writes": w + [[0x2C0AE, 2, 0], [0x2C0AC, 2, 0]]})
    return out


def cloud_layer_cases() -> list:
    """Hook 21 with clouds on (host word +0xae): ten clouds through the real pool spawn, the cloud class
    (register_cloud_templates' definition, +6 = create_cloud_controller 0x1db7a) and the camera-relative spawn, the
    rng ring primed two ways (both class 4, the high cloud, and the others; bearings re-drawn when too close). The
    cloud template's range reaches every spawn point, so each cloud is activated; the slot table grows on the way."""
    ROOT, LIST, SLOTS, BASE, ARENA, TMPL, CDEF = 0x30000, 0x30100, 0x30200, 0x31000, 0x32000, 0x30600, 0x30700
    REC_OFF, TYPE, CKEY = 0x40, 1, 0x2A
    REC = BASE + REC_OFF
    out = []
    for name, seed, cursor in (("ring A", 0x9E3779B9, 0x2C5BC), ("ring B", 0x7F4A7C15, 0x2C5E0)):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [HOST + 0x8C, 4, BASE],
             [HOST + 0xA8, 2, 0], [HOST + 0xE, 2, 8], [HOST + 0xAA, 2, 0], [HOST + 0xAE, 2, 1], [HOST + 0xB0, 2, 1], [0x2100E, 2, 8],
             [0x23FAC, 4, 0x30800], [0x20F2C, 4, 0], [0x20B24, 4, 0], [0x219C4, 4, 0], [0x219B4, 4, 0], [0x2B22C, 4, 0],
             [0x21068, 4, 0], [0x2107A, 2, 0], [0x21078, 2, 9],
             [0x23D68, 4, 0x1000], [0x23D64, 4, 0x2000], [0x23D60, 4, 0x3000], [0x23C2C, 4, ROOT],
             [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [ROOT + 0x32, 4, SLOTS + 8], [ROOT + 0x30, 2, 3], [ROOT + 0x2E, 2, 3],
             [SLOTS, 4, 0x10], [SLOTS + 4, 1, 0xFF], [LIST + 0x1A + 0x14 + 0x18 * TYPE, 4, REC_OFF], [TMPL + 0x10, 2, TYPE]]
        w += [[0x2719C + 4 * k, 4, 0] for k in range(8)]
        for k in range(3):
            w += [[SLOTS + 8 + 0x18 * k + m, 1, 0] for m in range(0x18)]
        w += [[REC, 1, 2], [REC + 1, 1, CKEY], [REC + 0x12, 4, 0x7FFFFFFF], [REC + 0x16, 2, 0], [REC + 0x18, 2, 0], [REC + 0x1A, 2, 3],
              [REC + 0x1C, 2, 0], [REC + 0x1E, 2, 0], [REC + 0x20, 2, 0]]
        w += [[0x2074C + 4 * CKEY, 4, CDEF], [CDEF, 2, CKEY], [CDEF + 6, 4, 0x1DB7A]] + [[0x2CD90 + 4 * k, 4, TMPL] for k in range(5)]
        w += [[0x2C5B8 + 4 * k, 4, (seed * (k + 1) + (k << 13)) & 0xFFFFFFFF] for k in range(0x37)] + [[0x2C5B4, 4, cursor]]
        w += [[0x23C40 + 4 * k, 4, 0x11111111] for k in range(6)]
        out.append({"name": "cloud layer: ten clouds, " + name, "method": "spawn_cloud_layer", "args": [], "writes": w,
                    "stack_guard": [0x3D000, 0x3F040]})
    return out


def text() -> str:
    fork = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data
    text, data = struct.unpack(">II", fork[4:12])
    image = fork[0x20:0x20 + text + data]       # as the loader leaves it: the bss zero
    cases = []
    for c in build():
        emu = Emulator(MEMORY_SIZE)
        # Emulator() starts from 1.0.3's overlay image: all of it replaced by Leyte's, the bss zero.
        emu.mem[0:MEMORY_SIZE] = bytes(MEMORY_SIZE)
        emu.mem[0:len(image)] = image
        for a, s, v in c["writes"]:
            emu.write(a, s, v)
        for address, base, nargs, *deref in CALL_LOGS:
            emu.stubs[address] = lambda e, b=base, n=nargs, d=tuple(deref): log_call(e, b, n, *d)
        # a routine that reads a register its caller left (find_anchor_point's D5) gets it here, and the port the
        # same value as an extra argument (pushed but unread by the original)
        emu.d[5] = c.get("d5_in", 0) & 0xFFFFFFFF
        before = bytes(emu.mem)
        d0 = emu.call(ROUTINES[c["method"]], c["args"], STACK_TOP)
        guard = c.get("stack_guard", STACK_GUARD)
        changes = [[a, emu.mem[a]] for a in range(MEMORY_SIZE) if not (guard[0] <= a < guard[1]) and emu.mem[a] != before[a]]
        cases.append({"name": c["name"], "method": c["method"], "args": c["args"], "zero": [], "writes": c["writes"],
                      "call_logs": CALL_LOGS, "changes": changes})
        if "stack_guard" in c:
            cases[-1]["stack_guard"] = c["stack_guard"]
        if c.get("d0"):
            cases[-1]["d0"] = d0 & 0xFFFFFFFF
        if "d5_in" in c:
            cases[-1]["d5_in"] = c["d5_in"]          # what the emulator's D5 held at entry (overlay_case_coverage.py reruns it)
    doc = {"schema": 1, "generated_by": "tools/leyte/gen_leyte_overlay_reconstructed_cases.py", "memory_size": MEMORY_SIZE,
           "stack_top": STACK_TOP, "stack_guard": STACK_GUARD, "cases": cases}
    return json.dumps(doc, separators=(",", ":")) + "\n"


def main() -> int:
    t = text()
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == t
        print(("PASS" if ok else "FAIL") + " Leyte overlay reconstructed cases: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(t)
    print("wrote %s (%d cases)" % (OUT.relative_to(ROOT), len(json.loads(t)["cases"])))
    return 0


if __name__ == "__main__":
    sys.exit(main())
