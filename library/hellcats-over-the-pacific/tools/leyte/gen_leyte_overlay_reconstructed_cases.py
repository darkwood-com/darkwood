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
MEMORY_SIZE, STACK_TOP, STACK_GUARD = 0x44000, 0x3F000, [0x3F000 - 0x800, 0x3F040]
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
            "emit_faces_unnumbered": 0xB220,
            "project_prepared_object": 0xB902, "transform_face_vertices": 0xB796, "project_face_vertices_xy": 0x15A06,
            "project_face_vertices_xz": 0x15A6C, "project_face_vertices_yz": 0x15AD2, "emit_faces_bsp": 0xAFFC,
            "collect_near_objects": 0x6250,
            "lock_overlay_buffers": 0x14C6, "dispatch_collision": 0x16C8A, "dispatch_overlap_collisions": 0x66A6,
            "point_in_polygon_xy": 0x1581A, "point_in_polygon_xz": 0x158BE, "point_in_polygon_yz": 0x15962, "surface_type_at_point": 0x15C2C,
            "invoke_tagged_attitude_compose_neg_sins4": 0x30B8, "submit_object_faces": 0xB554, "transform_and_project_object": 0xB9F8, "sort_group_parts_by_distance": 0x16D7A, "render_visible_objects": 0xBF1E, "collect_far_objects": 0x63BA,
            "update_altitude_detail_bands": 0x6882, "leyte_order_tracked_controllers": 0x525C, "leyte_move_type0f_shapes_last": 0x52B6,
            "update_ground_scatter_objects": 0x6C9A, "draw_distant_object_dots": 0x1A296, "overlay_frame_step": 0x741E,
            "test_proximity_collision": 0x16784, "test_player_vs_type0e_proximity": 0x167E4, "turret_pre_render": 0x1D4F6,
            "find_contact_record": 0x15EC0, "create_shape_contact_record": 0x15F0E, "handle_player_contact": 0x16BE2,
            "create_fractal_contact_record": 0x1617E, "fractal_height_at": 0x162B8, "handle_terrain_contact": 0x16AE6,
            "point_in_face_by_axis": 0x15D8C, "test_object_collision": 0x16812,
            "segment_point_min_distance": 0x48E8, "bullet_hits_shape": 0x1A64A,
            "lift_object_onto_terrain": 0x171BA, "test_terrain_collision": 0x1661A,
            "leyte_sink_with_cached_chain": 0x177D0, "leyte_apply_pending_pose": 0x17D8C, "leyte_pending_pose_pre_render": 0x17DFA,
            "point_over_face_xz": 0x15BAE, "leyte_ship_collision_hook": 0x17E0E, "leyte_carrier_deck_hook": 0x17E54,
            "spawn_effect": 0x13D60, "spawn_debris_fragment": 0x1B582,
            "any_mission_kills_recorded": 0x3E8, "build_attitude_matrix_3ang_alt": 0x2B52, "finalize_attitude_matrix_3ang_alt": 0x2A5A,
            "update_tracking_camera": 0x9D52, "begin_replay_view": 0xA116, "probe_nearest_facility_kind": 0x40C,
            "leyte_type80_centroid_visitor": 0xCE0, "leyte_object_type80_centroid": 0xE52, "point_near_camera_or_type80_object": 0xF7C,
            "enqueue_control_inputs_avg4": 0x123BE, "advance_frame_and_tick_controllers": 0xA406,
            "play_game_sound": 0xA700, "update_game_sounds": 0xA8EA, "find_nearest_runway_approach_ends": 0x1DDBE, "try_begin_landing_approach": 0x1E132, "build_relative_steer_sample": 0x121C4,
            "process_flight_input_and_outcome": 0x844E, "update_bailout_view": 0x9A94,
            "tick_shadow": 0x1CB04, "tick_view_attached_object": 0x1DD1C,
            "tick_turret": 0x1D2E2, "spawn_ordnance_scattered": 0x19BCE,
            "tick_aircraft_spawner": 0x1BF64, "spawn_enemy_fighter": 0xF692,
            "shadow_pre_render": 0x1CB66,
            "tally_destroyed_entity": 0x5E2, "apply_random_aircraft_damage": 0x1A70C,
            "spawn_destruction_effect": 0x13EC0,
            "apply_hit_to_sinkable_object": 0x17A2A,
            "apply_blast_damage": 0x15192, "leyte_destroy_entity": 0x14FE6,
            "tick_flak_gun": 0x1CDCE,
            "leyte_ship_tick": 0x18F94, "leyte_reshape_ship": 0x18784,
            "build_attitude_matrix": 0x2EC4, "finalize_attitude_matrix_4ang": 0x2D08, "leyte_carry_attached_objects": 0x1756A,
            "step_sinking_object": 0x17850, "overlay_printf": 0x189C, "leyte_attach_to_ship": 0x17CEE,
            "leyte_ship_first_move": 0x18D54, "leyte_ship_mover": 0x18E20, "leyte_landing_ship_mover": 0x18376,
            "leyte_steer_ship": 0x18B34, "leyte_turn_ship": 0x18908, "leyte_next_route_point": 0x1869C,
            "leyte_move_ship_attachments": 0x17D2A, "leyte_make_route": 0x1A8A2, "leyte_route_from_scene": 0x1A936,
            "leyte_ship_kind1": 0x18D1C, "apply_template_chain_xform_always": 0x3604}
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
            0x2AE: (0x3B700, 4),                        # +0x2ae: the host rectangle routine (1-bit blips)
            0x29A: (0x3B800, 1)}                        # +0x29a: the host printf (overlay_printf); only the format is
                                                        # logged: leyte_attach_to_ship pushes nothing else, so the other
                                                        # five longs are its caller's saved registers on the stack                        # +0x2ae: the host rectangle routine (1-bit blips)   # +0x2be port switch, +0x2c2 port release
## Code pointers the needles reach (draw_gauge_needle: depth routines at 0x23fc8 / 0x23fcc), stood in
## the same way: pointer -> (stand-in, argument count).
CODE_POINTERS = {0x23FC8: (0x3AA00, 8), 0x23FCC: (0x3AB00, 7),
                 0x26FF0: (0x3AD00, 5),                 # the span filler draw_sky_and_ground calls
                 0x23FC0: (0x3B500, 2), 0x23FC4: (0x3B600, 2),   # the radar's erase / draw blip routines
                 0x205EC: (0x3BF00, 3),
                 0x26FE4: (0x3BE00, 1)}                 # the face depth routine submit_object_faces calls                 # collision handlers (definition +0x12, the pair table 0x205ec)
# routines reached through pointers a case lays out itself (a definition's pre-render hook +0xe)
STAND_INS = {"pre-render hook": (0x3BD00, 2),
             # the broad-phase tests collect_far_objects reaches (through code_table in the port), stood in where they are
             "test_proximity_collision": (0x16784, 2), "test_player_vs_type0e_proximity": (0x167E4, 2),
             "test_object_collision": (0x16812, 2), "handle_player_contact": (0x16BE2, 2),
             "destroy hook": (0x3BC00, 1), "dot erase": (0x3BB00, 0), "dot plot": (0x3BA00, 4),
             "shape collision hook": (0x3B900, 3), "effect initialiser": (0x3B480, 1), "controller tick": (0x3B400, 1),
             "sound play": (0x3B380, 6), "sound rate": (0x3B300 + 0x40, 1), "sound status": (0x3B280, 1), "sound stop": (0x3B200 + 0x40, 1)}
SOUND_HOST = tuple(STAND_INS[k][0] for k in ("sound play", "sound rate", "sound status", "sound stop"))
LOGS_HIGH = 0x40000                 # call logs past the 32nd: above the stack, in the extra 0x4000 of the case memory
CALL_LOGS = [[at, (LOGS + 0x100 * k) if k < 32 else (LOGS_HIGH + 0x100 * (k - 32)), n]
             for k, (at, n) in enumerate([*SERVICES.values(), *CODE_POINTERS.values(), *STAND_INS.values()])]
# The readout text routine's text (in the caller's frame, inside the stack guard) is logged through
# its first argument: three longs, the 11-byte buffer and more.
for entry in CALL_LOGS:
    if entry[0] == SERVICES[0x2B2][0]:
        entry += [0, 3]
assert LOGS + 0x100 * min(len(CALL_LOGS), 32) <= 0x3A000 and LOGS_HIGH + 0x100 * max(len(CALL_LOGS) - 32, 0) <= MEMORY_SIZE, \
    "the call logs must stay below the stand-in addresses and inside the case memory"


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
    ] + unlock_cases() + span_reset_cases() + clip_cases() + lock_cases() + trig_cases() + rotation_cases() + flight_rank_cases() + bomb_lamp_cases() + radar_button_cases() + state_lamp_cases() + readout_cases() + needle_cases() + more_needle_cases() + sky_cases() + frame_line_cases() + radar_scope_cases() + heading_tape_cases() + hook12_cases() + entity_retire_cases() + entity_tick_cases() + simulation_tick_cases() + object_alloc_cases() + attitude_cases() + translate_cases() + chain_xform_cases() + band_cases() + shade_cases() + poly_buffer_cases() + land_outline_cases() + contact_cases() + terrain_cases() + object_collision_cases() + bullet_cases() + pending_pose_cases() + carrier_cases() + effect_cases() + small_flight_cases() + tracking_camera_cases() + replay_view_cases() + facility_cases() + type80_point_cases() + control_input_cases() + frame_advance_cases() + sound_cases() + runway_cases() + steer_sample_cases() + flight_input_cases() + bailout_view_cases() + small_tick_cases() + turret_cases() + spawner_cases() + shadow_render_cases() + damage_small_cases() + destruction_effect_cases() + sinkable_hit_cases() + blast_cases() + flak_cases() + ship_tick_cases() + sinking_step_cases() + ship_move_cases() + sink_cases() + instantiate_cases() + cloud_cases() + scene_part_cases() + template_table_cases() + mission_part_cases() + hook0_cases() + depth8_cases() + erase_dot_cases() + face_colour_cases() + depth4_cases() + template_factory_cases() + aircraft_cases() + anchor_cases() + mission_factory_cases() + ship_factory_cases() + route_ship_cases() + frame_batch1_cases() + cloud_layer_cases() + frame_batch2_cases() + frame_batch3_cases() + polygon_cases() + surface_cases() + submit_cases() + transform_object_cases() + sort_parts_cases() + render_objects_cases() + far_objects_cases() + altitude_cases() + scatter_cases() + dot_cases() + frame_step_cases() + collision_test_cases() + contact_cases()


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


def frame_batch2_cases() -> list:
    """Batch 2 of the frame step's closure. The face projections (xy / xz / yz) over three vertices and none, the
    shift word above 63 (mod 64). project_prepared_object over view vertices in front, on the near plane and behind,
    and with none. transform_face_vertices with a vertex already done this frame and one ending behind the near
    plane. emit_faces_bsp over a small tree (front and back subtrees, a chain) in and out of near mode. The near-list
    sort: a visible shape (its detail shift halved twice, a view point), one without a view point (flags bit 2), one
    kept off the visible list (flags bit 6), a non-shape, a hidden group and a far group (both deactivated to
    entity_array_3), one beyond its range but within 0x19c7f, a far shape seen recently."""
    out = []
    MODEL, FACE, IDX, VB, OUT = 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C600
    verts = [(0x12345, -0x2340, 0x56780), (-0x400, 0x800, -0xC00), (0x7FFF0, 0x10, -0x20)]
    w = [[MODEL + 0x46, 4, VB], [FACE + 0x18, 4, IDX]] + [[IDX + 2 * k, 2, v] for k, v in enumerate((2, 0, 1))]
    w += [[VB + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for k, v in enumerate(verts) for m, c in enumerate(v)]
    for method in ("project_face_vertices_xy", "project_face_vertices_xz", "project_face_vertices_yz"):
        for name, count, shift in (("three vertices", 3, 3), ("shift word mod 64", 3, 0x43), ("none", 0, 3)):
            out.append({"name": "%s: %s" % (method.replace('_', ' '), name), "method": method,
                        "args": [MODEL, shift, FACE, 0x123, (-0x45) & 0xFFFFFFFF, OUT], "writes": w + [[FACE + 0x14, 2, count]]})
    # project_prepared_object
    OBJ, VIEW, FACES, VB2 = 0x3C000, 0x3C100, 0x3C400, 0x3C800
    w = [[0x2C0BA, 4, OBJ], [OBJ + 0x4E, 4, VIEW], [OBJ + 0x4A, 4, FACES], [OBJ + 0x3A, 2, 2], [OBJ + 0x34, 2, 3],
         [OBJ + 0x46, 4, VB2], [0x27050, 2, 0x140], [0x2704E, 2, 0x120]]
    w += [[VB2 + 4 * k, 4, v] for k, v in enumerate((0x1111, 0x2222, 0x3333, 0x4444))]
    for k, (x, y, z) in enumerate(((0x40, -0x30, 0x100), (0x10, 0x10, 4), (0x22, 0x33, (-5) & 0xFFFF))):
        w += [[VIEW + 12 * k + 2, 2, x & 0xFFFF], [VIEW + 12 * k + 6, 2, y & 0xFFFF], [VIEW + 12 * k + 0xA, 2, z]]
    w += [[0x2BBF8 + 8 * k + m, 4, 0x77777777] for k in range(3) for m in (0, 4)]
    w += [[FACES + 0x20 * k + 0x13, 1, (0x01, 0x80, 0x00, 0x1F)[k]] for k in range(4)]
    for name, nv, nf in (("three view vertices, four faces", 3, 4), ("none", 0, 0)):
        out.append({"name": "prepare object: " + name, "method": "project_prepared_object", "args": [],
                    "writes": w + [[OBJ + 0x38, 2, nv], [OBJ + 0x3C, 2, nf]]})
    # transform_face_vertices
    FACE2, IDX2, VB3, VIEW2, MAT = 0x3C100, 0x3C140, 0x3C200, 0x3C400, 0x3C600
    w = [[0x2C0BA, 4, OBJ], [OBJ + 0x46, 4, VB3], [0x2C0B6, 4, VIEW2], [FACE2 + 0x14, 2, 3], [FACE2 + 0x18, 4, IDX2],
         [0x2B29C, 2, 0x10], [0x2B29A, 2, (-0x20) & 0xFFFF], [0x2B298, 2, 0x30], [0x2B296, 2, 1], [0x2B29E, 4, MAT],
         [0x27050, 2, 0x140], [0x2704E, 2, 0x120], [0x2B2A2 + 2, 1, 1]]
    w += [[IDX2 + 2 * k, 2, v] for k, v in enumerate((0, 2, 1))]
    w += [[MAT + 2 * k, 2, v & 0xFFFF] for k, v in enumerate((0x1000, 0x10, -0x20, 0x300, 0xF00, 0x7FF, -0x1000, 0x123, 0x456))]
    for k, v in enumerate(((0x2000, 0x400, 0x3000), (0x100, 0x200, 0x300), (0x40, -0x60, -0x2000))):
        w += [[VB3 + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for m, c in enumerate(v)]
    out.append({"name": "transform face vertices: one done, one behind", "method": "transform_face_vertices", "args": [FACE2], "writes": w})
    # emit_faces_bsp: the root walks' face set (batch 1) with subtrees
    OBJ3, FACES3, QUEUE, BACKQ, NORMALS, VERTS, IDX3 = 0x3C000, 0x3C100, 0x3C400, 0x3C500, 0x3C600, 0x3C700, 0x3C800
    #        distance, flags +0x13, front +0x10, back +0x11, chain +0x12, normal
    faces = [(0x500, 0x00, 1, 4, 3, (0, 0, 0x100)),          # emitted: its chain (3) follows
             (0x500, 0x01, 5, 2, 3, (0, 0, -0x100)),          # back-facing: its front subtree (5) first
             (0x500, 0x00, 0xFF, 0xFF, 0xFF, (0, 0, -0x100)),
             (0x500, 0x00, 0xFF, 0xFF, 0xFF, (0, 0x100, 0)),
             (0x100, 0x00, 5, 0xFF, 6, (0, 0x100, 0)),
             (0x500, 0x02, 0xFF, 0xFF, 0xFF, (0, 0, 0x100)),
             (0x500, 0x01, 0xFF, 0xFF, 0xFF, (0x100, 0, 0))]
    w = [[0x2C0BA, 4, OBJ3], [OBJ3 + 0x1C, 4, 0x300], [OBJ3 + 0x46, 4, VERTS], [0x2C4C6, 4, FACES3], [0x2C2C2, 4, QUEUE],
         [0x2C0BE, 4, BACKQ], [0x219A8, 2, 0x20], [0x2B286, 4, NORMALS], [0x2B296, 2, 0], [0x2B29C, 2, 0], [0x2B29A, 2, 0x10],
         [0x2B298, 2, 0x40]]
    for k, (dist, flags, front, back, chain, normal) in enumerate(faces):
        f = FACES3 + 0x20 * k
        w += [[f + 0x1C, 4, dist], [f + 0x13, 1, flags], [f + 0x10, 1, front], [f + 0x11, 1, back], [f + 0x12, 1, chain],
              [f + 2, 2, 0xEEEE], [f + 0x18, 4, IDX3 + 2 * k], [IDX3 + 2 * k, 2, 0]]
        w += [[NORMALS + 12 * k + 2 + 4 * m, 2, c & 0xFFFF] for m, c in enumerate(normal)]
    for name, near in (("far mode", 0), ("near mode", 1)):
        out.append({"name": "bsp faces: " + name, "method": "emit_faces_bsp", "args": [0], "writes": w + [[0x2C0AC, 2, near]],
                    "stack_guard": [0x3D000, 0x3F040]})
    # collect_near_objects
    LIST, COUNT, T = 0x30000, 0x30080, 0x30F08
    # the flags long +0x14: its low byte +0x17 holds bit 1 (hidden), bit 2 (no view point) and bit 6 (not listed visible)
    objs = [  # type, flags long +0x14, metric, range, radius, scale +0x36, last seen +0x3e
        (2, 0x00, 0x100, 0x1000, 0x80, 8, 0),
        (2, 0x04, 0x100, 0x1000, 0x80, 0, 0),
        (2, 0x40, 0x100, 0x1000, 0x80, 0, 0),
        (1, 0x00, 0x100, 0x1000, 0x80, 0, 0),
        (3, 0x02, 0x100, 0x1000, 0x80, 0, 0),
        (2, 0x00, 0x2000, 0x1000, 0x80, 0, 0),
        (2, 0x00, 0x40000, 0x1000, 0x80, 0, 0xFE0),
        (3, 0x00, 0x40000, 0x1000, 0x80, 0, 0)]
    w = [[0x207FC, 4, HOST], [COUNT, 4, len(objs)], [0x219B8, 4, 1], [0x219BC, 4, 0], [0x219C4, 4, 0], [0x21020, 4, 0x1000],
         [0x23D68, 4, 0x5000], [0x23D64, 4, 0x100], [0x23D60, 4, (-0x3000) & 0xFFFFFFFF]] + [[0x2719C + 4 * k, 4, 0] for k in range(8)]
    for k, (typ, flags, metric, rng, radius, scale, seen) in enumerate(objs):
        o = 0x30100 + 0x100 * k
        w += [[LIST + 4 * k, 4, o], [o + 0x28, 1, typ], [o + 0x14, 4, flags], [o + 0x1C, 4, metric],
              [o + 0x18, 4, rng], [o + 0x20, 4, radius], [o + 0x36, 2, scale], [o + 0x3E, 4, seen], [o + 4, 4, 0x5100 + k],
              [o + 8, 4, 0x180], [o + 0xC, 4, (-0x2F00) & 0xFFFFFFFF], [o, 4, 0]]
        if typ == 3:
            w += [[o + 0x30, 2, 0], [o + 0x32, 4, T + 0x10 * k], [T + 0x10 * k - 4, 1, 4]]
    out.append({"name": "near list: every path but a retirement", "method": "collect_near_objects", "args": [LIST, COUNT], "writes": w,
                "stack_guard": [0x3D000, 0x3F040]})
    out.append({"name": "near list: empty", "method": "collect_near_objects", "args": [LIST, COUNT],
                "writes": [[COUNT, 4, 0x10000], [0x219B8, 4, 0]]})
    return out


def frame_batch3_cases() -> list:
    """Batch 3 of the frame step's closure. lock_overlay_buffers through the host's lock routine. dispatch_collision:
    view mode 8, a flagged object, neither / one / both handlers, the pair table matched in order, reversed and not at
    all (a's flag byte +0x16 bit 5 picks a's handler, else b's). dispatch_overlap_collisions: four objects sorted by
    metric, two overlapping pairs (notice handlers on one side, both, none) and one far enough to end the back scan."""
    out = []
    w = [[0x207FC, 4, HOST]] + [[HOST + off, 4, at] for off, (at, _n) in SERVICES.items()]
    w += [[HOST + off, 4, 0x11110000 + off] for off in (0x196, 0x1B0, 0x1B8, 0xDC)]
    out.append({"name": "lock overlay buffers", "method": "lock_overlay_buffers", "args": [], "writes": w})
    A, B, CA, CB, DA, DB, TABLE = 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C400, 0x3C500, 0x205EC
    HANDLER = CODE_POINTERS[0x205EC][0]
    base = [[A, 4, CA], [B, 4, CB], [CA + 4, 4, DA], [CB + 4, 4, DB], [DA, 2, 0x11], [DB, 2, 0x22], [0x23D88, 2, 1]]
    table = []
    for k in range(0x11):
        table += [[TABLE + 8 * k, 2, 0x90 + k], [TABLE + 8 * k + 2, 2, 0xA0 + k], [TABLE + 8 * k + 4, 4, HANDLER]]
    for name, extra in (("view mode 8", [[0x23D88, 2, 8], [DA + 0x12, 4, HANDLER]]),
                        ("a flagged", [[A + 0x15, 1, 1], [DA + 0x12, 4, HANDLER]]),
                        ("no handler", []),
                        ("a's handler only", [[DA + 0x12, 4, HANDLER]]),
                        ("b's handler only", [[DB + 0x12, 4, HANDLER]]),
                        ("both, pair in order", [[DA + 0x12, 4, HANDLER], [DB + 0x12, 4, HANDLER], [TABLE + 8 * 16, 2, 0x11], [TABLE + 8 * 16 + 2, 2, 0x22]]),
                        ("both, pair reversed", [[DA + 0x12, 4, HANDLER], [DB + 0x12, 4, HANDLER], [TABLE + 8 * 3, 2, 0x22], [TABLE + 8 * 3 + 2, 2, 0x11]]),
                        ("both, no pair, a flagged bit 5", [[DA + 0x12, 4, HANDLER], [DB + 0x12, 4, HANDLER], [A + 0x16, 1, 0x20]]),
                        ("both, no pair", [[DA + 0x12, 4, HANDLER], [DB + 0x12, 4, HANDLER]])):
        out.append({"name": "collision: " + name, "method": "dispatch_collision", "args": [A, B], "writes": base + table + extra})
    LIST, HEADER, NOTICE = 0x3C800, 0x3C900, SERVICES[0x30A][0]
    #           metric, radius, controller, notice handler
    # the last one, behind the others, reaches back only past objects that do not reach it (their running minimum decides)
    objs = [(0x100, 0x100, True, True), (0x180, 0x40, True, False), (0x400, 0x10, False, False), (0x420, 0x30, True, True),
            (0x50, 0x10, False, False)]
    w = [[HEADER + 2, 2, len(objs)], [0x21058, 2, 0], [0x21070, 4, 0x5555]]
    for k, (metric, radius, has_ctl, has_notice) in enumerate(objs):
        o, c, d = 0x3C000 + 0x100 * k, 0x3D000 + 0x40 * k, 0x3D200 + 0x40 * k
        w += [[LIST + 4 * k, 4, o], [o + 0x1C, 4, metric], [o + 0x18, 4, radius], [o + 0x2A, 4, 0x7777]]
        if has_ctl:
            w += [[o, 4, c], [c + 4, 4, d], [d + 0x16, 4, NOTICE if has_notice else 0]]
    out.append({"name": "overlap sweep: pairs, a scan ended", "method": "dispatch_overlap_collisions", "args": [LIST, HEADER], "writes": w})
    out.append({"name": "overlap sweep: empty", "method": "dispatch_overlap_collisions", "args": [LIST, HEADER], "writes": [[HEADER + 2, 2, 0]]})
    return out


def polygon_cases() -> list:
    """The point-in-polygon tests over a concave pentagon (indices in rotated order): points inside, outside to either
    side, on a vertex's row, past the edges both ways, and with no vertices; each pair of coordinates in turn (D0)."""
    VERTS, IDX = 0x3C000, 0x3C100
    poly = [(-0x40, 0x10, -0x30), (0x50, -0x20, -0x40), (0x30, 0x60, 0x10), (0, 0x20, 0x08), (-0x50, 0x40, 0x50)]
    w = [[VERTS + 12 * k + 2 + 4 * m, 2, c & 0xFFFF] for k, v in enumerate(poly) for m, c in enumerate(v)]
    w += [[IDX + 2 * k, 2, v] for k, v in enumerate((2, 3, 4, 0, 1))]
    out = []
    for method in ("point_in_polygon_xy", "point_in_polygon_xz", "point_in_polygon_yz"):
        for a, b in ((0, 0), (0x10, 0x5), (-0x60, 0), (0x70, 0), (0x30, 0x60), (0, 0x20), (-0x10, -0x38), (0x20, 0x45), (0x20, 0), (0x30, 0x20)):
            out.append({"name": "%s: (0x%x, 0x%x)" % (method.replace('_', ' '), a & 0xFFFF, b & 0xFFFF), "method": method,
                        "args": [a & 0xFFFFFFFF, b & 0xFFFFFFFF, VERTS, 5, IDX], "writes": w, "d0": True})
        out.append({"name": "%s: no vertices" % method.replace('_', ' '), "method": method, "args": [0, 0, VERTS, 0, IDX],
                    "writes": w, "d0": True})
    return out


def surface_cases() -> list:
    """surface_type_at_point over four objects walked from the last: one out of metric range, one flagged, one not a
    shape, one beyond its approximate distance, and shapes whose faces (no surface byte, a miss, a hit) are projected at
    scale shifts left, zero and right; the default face when nothing holds the point."""
    DEFAULT, VB, IDX = 0x3E000, 0x3E100, 0x3E200
    # a square around the origin in x / z, at vertex buffer scale 1 (the projection shifts it by detail + 1)
    verts = [(-0x400, 0, -0x400), (0x400, 0, -0x400), (0x400, 0, 0x400), (-0x400, 0, 0x400), (0x4000, 0, 0x4000), (0x5000, 0, 0x4000), (0x5000, 0, 0x5000)]
    w = [[0x21590, 4, DEFAULT], [DEFAULT + 0x16, 1, 0xF3]]
    w += [[VB + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for k, v in enumerate(verts) for m, c in enumerate(v)]
    w += [[IDX + 2 * k, 2, v] for k, v in enumerate((0, 1, 2, 3, 4, 5, 6))]
    #  metric, range, flags, type, position, scale +0x36, detail +0x34, faces (surface byte, first index, count)
    def obj(k, metric, rng, flags, typ, pos, scale, detail, faces):
        o, fr = 0x3C000 + 0x200 * k, 0x3D000 + 0x200 * k
        out = [[0x20B44 + 4 * k, 4, o], [o + 0x1C, 4, metric], [o + 0x20, 4, rng], [o + 0x14, 4, flags], [o + 0x28, 1, typ],
               [o + 4, 4, pos[0] & 0xFFFFFFFF], [o + 8, 4, pos[1] & 0xFFFFFFFF], [o + 0xC, 4, pos[2] & 0xFFFFFFFF],
               [o + 0x36, 2, scale & 0xFFFF], [o + 0x34, 2, detail & 0xFFFF], [o + 0x3C, 2, len(faces)], [o + 0x4A, 4, fr], [o + 0x46, 4, VB]]
        for j, (surface, first, count) in enumerate(faces):
            out += [[fr + 0x20 * j + 0x16, 1, surface], [fr + 0x20 * j + 0x14, 2, count], [fr + 0x20 * j + 0x18, 4, IDX + 2 * first]]
        return out
    hit = [(0x07, 0, 4), (0x00, 0, 4), (0x05, 4, 3)]      # from the last: a miss (the far triangle), none, a hit
    out = []
    for name, shift in (("scaled left", (3, 1)), ("unscaled", (1, 0)), ("scaled right", (0, 1)), ("scaled right twice", ((-1) & 0xFFFF, 0))):
        scale, detail = shift
        objs = (obj(0, 0x100, 0x10000, 0, 2, (0x10, 0, 0x20), scale, detail, hit)
                + obj(1, 0x100, 0x10000, 0, 2, (0x10, 0, 0x20), 0, 0, [])
                + obj(2, 0x100, 0x10000, 0x2000, 2, (0, 0, 0), 0, 0, hit)
                + obj(3, 0x100, 0x10000, 0x40, 2, (0, 0, 0), 0, 0, hit)
                + obj(4, 0x100, 0x10000, 0x2, 2, (0, 0, 0), 0, 0, hit)
                + obj(5, 0x100, 0x10000, 0, 3, (0, 0, 0), 0, 0, hit)
                + obj(6, 0x100, 0x200, 0, 2, (0x7000, 0, 0x7000), 0, 0, hit)
                + obj(7, 0x7FFFFF, 0x40, 0, 2, (0, 0, 0), 0, 0, hit))
        for px, pz in ((0x30, -0x30), (0x4800, 0x4900), (0x2600, 0x2100)):
            out.append({"name": "surface at (0x%x, 0x%x): %s" % (px & 0xFFFF, pz & 0xFFFF, name), "method": "surface_type_at_point",
                        "args": [px & 0xFFFFFFFF, 0x80, pz & 0xFFFFFFFF], "writes": w + objs + [[0x20F2C, 4, 8]], "d0": True})
    out.append({"name": "surface: no objects", "method": "surface_type_at_point", "args": [0, 0, 0],
                "writes": w + [[0x20F2C, 4, 0]], "d0": True})
    return out


def submit_cases() -> list:
    """The transpose at 0x30b8; submit_object_faces over a queue of faces: transformed or not, a point behind the near
    plane (clipped), forced clipping, no vertices, near modes 0 / 1 / 2 with the back-facing and inside flags differing or
    not and face bit 1 set or not (a penetration hitting or missing), the depth routine drawing or not, and the back
    queue's faces (inside, bit 1, transformed, hitting)."""
    M = 0x3C000
    out = [{"name": "transpose a word matrix", "method": "invoke_tagged_attitude_compose_neg_sins4", "args": [M],
            "writes": [[M + 2 * k, 2, 0x1111 * (k + 1)] for k in range(9)]}]
    OBJ, VIEW, IDX, FACES, VB, MAT, PLAYER, POBJ = 0x3C000, 0x3C200, 0x3C400, 0x3C500, 0x3C800, 0x3CA00, 0x3CB00, 0x3CC00
    DRAW = next(e[1] for e in CALL_LOGS if e[0] == CODE_POINTERS[0x26FE4][0])
    # a square around the view axis (the penetration point (0, 0) inside), and one beside it
    view = [(-0x10, -0x10), (0x10, -0x10), (0x10, 0x10), (-0x10, 0x10), (0x40, 0x40), (0x60, 0x40), (0x60, 0x60)]
    w = [[0x2C0BA, 4, OBJ], [OBJ + 0x14, 4, 0x0C], [OBJ + 0x46, 4, VB], [0x2C0B6, 4, VIEW], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ],
         [0x2B29E, 4, MAT], [0x27050, 2, 0x140], [0x2704E, 2, 0x120], [0x26FE4, 4, CODE_POINTERS[0x26FE4][0]]]
    w += [[MAT + 2 * k, 2, v] for k, v in enumerate((0x4000, 0, 0, 0, 0x4000, 0, 0, 0, 0x4000))]
    w += [[VB + 12 * k + 8, 4, 0x100] for k in range(7)]
    for k, (x, y) in enumerate(view):
        w += [[VIEW + 12 * k + 2, 2, x & 0xFFFF], [VIEW + 12 * k + 6, 2, y & 0xFFFF], [VIEW + 12 * k + 0xA, 2, 0x100],
              [0x2BBF8 + 8 * k, 4, 0x100 + x], [0x2BBF8 + 8 * k + 4, 4, 0x80 + y]]
    w += [[IDX + 2 * k, 2, v] for k, v in enumerate((0, 1, 2, 3, 4, 5, 6))]
    #        flags +0x13, first vertex, count
    queue = [(0x60, 0, 4), (0x70, 0, 4), (0x68, 4, 3), (0x00, 4, 3), (0x72, 0, 4), (0x60, 0, 0), (0x78, 4, 3), (0x68, 0, 4)]
    backq = [(0x60, 0, 4), (0x68, 0, 4), (0x62, 0, 4), (0x00, 4, 3)]
    for k, (flags, first, count) in enumerate(queue + backq):
        f = FACES + 0x20 * k
        w += [[f + 0x13, 1, flags], [f + 0x14, 2, count], [f + 0x18, 4, IDX + 2 * first]]
        slot = 0x2C2C6 + 4 * k if k < len(queue) else 0x2C0C2 + 4 * (k - len(queue))
        w += [[slot, 4, f]]
    w += [[0x2C2C2, 4, 0x2C2C6 + 4 * len(queue)], [0x2C0BE, 4, 0x2C0C2 + 4 * len(backq)]]
    for near in (0, 1, 2):
        for name, extra in (("drawn", [[DRAW - 4, 4, 1]]), ("none drawn", []),
                            ("forced clip", [[0x2C0AA, 2, 1]]), ("a point behind", [[0x2BBF8 + 8 * 2, 4, 0x80000000]]),
                            ("a vertex behind the near plane", [[0x2BBF8 + 8 * 2, 4, 0x80000000], [VIEW + 12 * 2 + 0xA, 2, 0]])):
            out.append({"name": "submit faces, near mode %d: %s" % (near, name), "method": "submit_object_faces", "args": [],
                        "writes": w + extra + [[0x2C0AC, 2, near]], "stack_guard": [0x3D000, 0x3F040]})
    out.append({"name": "submit faces: empty queues", "method": "submit_object_faces", "args": [],
                "writes": [[0x2C0BA, 4, OBJ], [OBJ + 0x14, 4, 0xFF], [0x2C2C2, 4, 0x2C2C6], [0x2C0BE, 4, 0x2C0C2], [0x2C0AC, 2, 1],
                           [0x2C0A8, 2, 1]]})
    return out


def transform_object_cases() -> list:
    """transform_and_project_object: no controller (its band and shade, or skipped by +0x16 bit 1), an unlinked controller,
    no pre-render hook, the hook returning 4, an oriented object (its attitude built, or kept by the hook's bit 0; lit,
    turning the light vector, or not; the hook called again on bit 1), the eye shifted left or right; faces marking their
    vertices (0x20 not 0x40) or not, a vertex behind the near plane. Shading meets a retired object (+0x17 bit 1) and stops."""
    OBJ, CTL, DEF, VB, VIEW, FACES, IDX = 0x3C000, 0x3C100, 0x3C180, 0x3C200, 0x3C300, 0x3C400, 0x3C500
    HOOK = STAND_INS["pre-render hook"][0]
    RET = next(e[1] for e in CALL_LOGS if e[0] == HOOK) - 4
    verts = [(0x100, 0x80, 0x2000), (-0x200, 0x40, 0x1800), (0x300, -0x100, 0x2800), (0x10, 0x10, 0x10), (0x50, 0x60, 0x3000)]
    w = [[0x207FC, 4, HOST], [0x2C0BA, 4, OBJ], [OBJ + 0x34, 2, 1], [OBJ + 4, 4, 0x1000], [OBJ + 8, 4, (-0x800) & 0xFFFFFFFF],
         [OBJ + 0xC, 4, 0x3000], [OBJ + 0x1C, 4, 0x20000], [OBJ + 0x32, 2, 3], [OBJ + 0x17, 1, 2], [OBJ + 0x28, 1, 2],
         [OBJ + 0x4E, 4, VIEW], [OBJ + 0x4A, 4, FACES], [OBJ + 0x3A, 2, 2], [OBJ + 0x38, 2, len(verts)], [OBJ + 0x46, 4, VB],
         [OBJ + 0x3C, 2, 4], [0x23D68, 4, 0x2400], [0x23D64, 4, 0x300], [0x23D60, 4, (-0x5000) & 0xFFFFFFFF],
         [0x27050, 2, 0x140], [0x2704E, 2, 0x120], [0x21574, 2, 0x0800], [0x21572, 2, 0xF800], [0x21570, 2, 0x0400],
         [CTL, 4, 0x3F00], [CTL + 4, 4, DEF], [CTL + 0x20, 2, 0x100], [CTL + 0x22, 2, 0x2000], [CTL + 0x24, 2, 0xF000]]
    w += [[0x23C74 + 2 * k, 2, v & 0xFFFF] for k, v in enumerate((0xF00, 0x100, -0x200, -0x100, 0xF00, 0x80, 0x200, -0x80, 0xF00))]
    w += [[VB + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for k, v in enumerate(verts) for m, c in enumerate(v)]
    w += [[0x2B2A2 + k, 1, 0xEE] for k in range(8)]
    #        flags +0x13, vertices
    faces = [(0x20, (0, 1, 2)), (0x60, (3,)), (0x00, (4,)), (0x21, (2, 3))]
    for k, (flags, idx) in enumerate(faces):
        f = FACES + 0x20 * k
        w += [[f + 0x13, 1, flags], [f + 0x14, 2, len(idx)], [f + 0x18, 4, IDX + 0x10 * k]]
        w += [[IDX + 0x10 * k + 2 * j, 2, v] for j, v in enumerate(idx)]
    out = []
    ctl = [[OBJ, 4, CTL]]
    hook = [[DEF + 0xE, 4, HOOK]]
    for name, extra in (("no controller", []),
                        ("no controller, hazy", [[HOST + 0xAA, 2, 1]]),
                        ("no controller, +0x16 bit 1", [[HOST + 0xAA, 2, 1], [OBJ + 0x16, 1, 2]]),
                        ("unlinked controller", ctl + [[CTL, 4, 0]]),
                        ("no hook, not oriented, hazy", ctl + [[HOST + 0xAA, 2, 1]]),
                        ("no controller, hazy, band changed", [[HOST + 0xAA, 2, 1], [OBJ + 0x1C, 4, 0x80000]]),
                        ("oriented, hazy, band changed", ctl + hook + [[OBJ + 0x15, 1, 0x10], [HOST + 0xAA, 2, 1], [OBJ + 0x1C, 4, 0x80000]]),
                        ("oriented, hazy, band kept", ctl + hook + [[OBJ + 0x15, 1, 0x10], [HOST + 0xAA, 2, 1]]),
                        ("hook says 4", ctl + hook + [[RET, 4, 4]]),
                        ("oriented", ctl + hook + [[OBJ + 0x15, 1, 0x10]]),
                        ("oriented, eye shifted right", ctl + hook + [[OBJ + 0x15, 1, 0x10], [OBJ + 0x36, 2, 0xFFFF]]),
                        ("oriented, eye shifted left", ctl + hook + [[OBJ + 0x15, 1, 0x10], [OBJ + 0x36, 2, 3]]),
                        ("oriented, attitude kept, called again", ctl + hook + [[OBJ + 0x15, 1, 0x10], [RET, 4, 3]]),
                        ("oriented, lit, hazy", ctl + hook + [[OBJ + 0x15, 1, 0x10], [OBJ + 0x16, 1, 1], [HOST + 0xB2, 2, 1], [HOST + 0xAA, 2, 1]]),
                        ("oriented, lit flag but no light", ctl + hook + [[OBJ + 0x15, 1, 0x10], [OBJ + 0x16, 1, 1]])):
        out.append({"name": "transform object: " + name, "method": "transform_and_project_object", "args": [], "writes": w + extra,
                    "d0": True})
    out.append({"name": "transform object: no vertices, no faces", "method": "transform_and_project_object", "args": [],
                "writes": w + [[OBJ + 0x38, 2, 0], [OBJ + 0x3C, 2, 0]], "d0": True})
    return out


def sort_parts_cases() -> list:
    """sort_group_parts_by_distance on Leyte's layout (1.0.3's cases, tools/original/gen_sort_parts_cases.py, laid out
    again): seeded part centres, unlit and lit (bands changed, faces recoloured, owned palette slots rewritten, others
    dropped), no group list, groups before / between / after the parts (every merge exit), deferred frees of word-0
    entries (small heap blocks, class 4)."""
    import random
    OBJ, VERTS, FACES, GROUPS, OWNERS, ENTRIES = 0x3C000, 0x3C100, 0x3C400, 0x3C800, 0x3C900, 0x3CA00
    NF = 6
    out = []

    def case(name, seed, parts=5, lit=0, groups=None, deferred=0, owned=(), scale=1):
        rnd = random.Random(seed)
        nverts = 3 + parts
        w = [[0x207FC, 4, HOST], [HOST + 0xAA, 2, lit], [HOST + 0xB4, 2, 0], [0x23FAC, 4, OWNERS], [0x219A8, 2, 10],
             [0x219A6, 2, deferred], [0x23D68, 4, 4000], [0x23D64, 4, 777], [0x23D60, 4, (-2500) & 0xFFFFFFFF],
             [OBJ + 0x36, 2, scale], [OBJ + 0x38, 2, nverts], [OBJ + 0x3A, 2, parts], [OBJ + 0x3C, 2, NF],
             [OBJ + 0x46, 4, VERTS], [OBJ + 0x4A, 4, FACES]]
        for i in range(nverts):
            w += [[VERTS + 12 * i + 4 * c, 4, rnd.randint(-6000, 6000) & 0xFFFFFFFF] for c in range(3)]
        lst = FACES + 32 * NF
        order = list(range(parts))
        rnd.shuffle(order)
        w += [[lst + 2 * k, 2, p] for k, p in enumerate(order)]
        colours = lst + 2 * parts
        for p in range(parts):
            w += [[colours + 6 * p + c, 1, rnd.randint(0, 255)] for c in range(3)]
            w += [[colours + 6 * p + 4, 2, rnd.choice([0, 3, 9])], [FACES + 32 * p + 0xF, 1, 7 + p], [FACES + 32 * p + 2, 2, 0xEEEE]]
            if p in owned:
                w.append([OWNERS + 4 * (7 + p), 4, FACES + 32 * p])
        if groups is not None:
            w += [[OBJ + 0x2E, 4, GROUPS], [GROUPS, 2, len(groups)]]
            for k, (count, gx, gz) in enumerate(groups):
                e = ENTRIES + 0x40 * k
                w += [[e - 4, 1, 4], [e, 2, count], [e + 4, 4, gx & 0xFFFFFFFF], [e + 8, 4, gz & 0xFFFFFFFF], [GROUPS + 4 + 4 * k, 4, e]]
        out.append({"name": "sort parts: " + name, "method": "sort_group_parts_by_distance", "args": [OBJ], "writes": w,
                    "stack_guard": [0x3E000, 0x3F040]})

    case("unlit, no groups: parts numbered far to near", 1)
    case("lit: bands, colours and owned slots", 2, lit=1, owned=(0, 2, 4))
    case("lit, nothing owned: slots dropped", 3, lit=1)
    case("groups before, between and after the parts", 4, groups=[(2, 8000, 0), (1, 1000, 500), (3, -100, 50)])
    case("all groups farther than every part (early exit)", 5, groups=[(1, 40000, 0), (2, 30000, 9000)])
    case("all groups nearer than every part", 6, groups=[(1, 10, 0), (4, 0, 20)])
    case("deferred free: word-0 entries freed", 7, groups=[(0, 5000, 0), (2, 3000, 0), (0, 10, 10)], deferred=1)
    case("deferred free: every entry freed", 8, groups=[(0, 5000, 0)], deferred=1)
    case("one part, lit, scale 3", 9, parts=1, lit=1, owned=(0,), scale=3)
    case("no parts", 10, parts=0)
    case("groups at the (scaled) camera: after every part", 11, groups=[(1, 8000, -5000), (2, 8010, -4990), (1, 7990, -5010)])
    return out


def render_objects_cases() -> list:
    """render_visible_objects over light objects (no vertices, two faces, no root chains): a pre-transformed object (its
    faces through the BSP walk), then a far one, a group whose parts are sorted, one deferred under a parent, an unlinked
    controller, near on first entry and again, far once more; a deferred object whose controller is unlinked; views 1
    (the last object drawn) and 0, the view flag set, and an empty list."""
    LIST, PARENT, CTL, DEAD = 0x22994, 0x3C080, 0x3C0C0, 0x3C0E0
    w = [[0x207FC, 4, HOST], [0x26FE4, 4, CODE_POINTERS[0x26FE4][0]], [DEAD, 4, 0], [PARENT + 2, 2, 0x40]]

    def obj(k, near=None, first=True, bsp=False, sort=False, parent=0, ctl=0):
        o, faces = 0x3C200 + 0x100 * k, 0x3D000 + 0x80 * k
        r = [[o + 0x4A, 4, faces], [o + 0x4E, 4, 0x3CF00], [o + 0x46, 4, 0x3CF80], [o + 0x3C, 2, 2], [o + 0x1C, 4, 0x100],
             [o + 0x20, 4, 0x200 if near else 0x80], [o + 0x15, 1, 0 if first else 0x20], [o + 0x16, 1, 0x0B if sort else 0x08],
             [o + 0x17, 1, 0x28 if bsp else 0x08], [o + 0x2E, 4, parent], [o, 4, ctl]]
        for j in range(2):
            r += [[faces + 0x20 * j + 0x13, 1, 0x68], [faces + 0x20 * j + 0x10, 2, 0xFFFF], [faces + 0x20 * j + 0x12, 1, 0xFF],
                  [faces + 0x20 * j + 0x18, 4, 0x3CFF0]]
        return o, r

    kinds = [dict(bsp=True), dict(), dict(sort=True), dict(parent=PARENT), dict(ctl=DEAD), dict(near=True),
             dict(near=True, first=False), dict(bsp=True), dict(parent=PARENT, ctl=DEAD), dict(parent=PARENT, near=True, bsp=True)]
    for k, kw in enumerate(kinds):
        o, r = obj(k, **kw)
        w += r + [[LIST + 4 * k, 4, o]]
    last, r = obj(len(kinds), bsp=True)
    w += r + [[0x2158C, 4, last]]
    base = w + [[0x215A0, 4, 1], [0x219BC, 4, len(kinds)], [0x219AA, 2, len(kinds) - 5]]
    out = []
    for name, extra in (("view 1", [[0x23D88, 2, 1]]), ("view 0", [[0x23D88, 2, 0]]), ("view 1, view flag", [[0x23D88, 2, 1], [0x23D86, 2, 1]]),
                        ("view 1, near decided for all", [[0x23D88, 2, 1], [0x219AA, 2, 0]]),
                        ("view 1, two pre-transformed, the last in order", [[0x23D88, 2, 1], [0x215A0, 4, 2], [last + 0x17, 1, 0x08]])):
        out.append({"name": "render objects: " + name, "method": "render_visible_objects", "args": [], "writes": base + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "render objects: none, view 0", "method": "render_visible_objects", "args": [],
                "writes": [[0x215A0, 4, 0], [0x219BC, 4, 0], [0x219AA, 2, 3]]})
    return out


def far_objects_cases() -> list:
    """collect_far_objects over a far list in metric order: an overlap / proximity object scaled to a detail shift, a
    hidden recent one (skipped by the scans), active ones (player and not) testing the kept objects behind them, a
    passive one without a view point testing the actives, one out of range but within 0x19c7f, far ones kept for being
    seen recently, deactivated (never seen) or retired, then a very low start lowering the running minimum so a passive
    scan and an active scan each stop on an object's minimum (+0x2a). Every collision test is a logging stand-in."""
    LIST, COUNT = 0x30000, 0x30080
    #        metric, range, radius, flags +0x14, last seen +0x3e, scale +0x36, +0x2a
    objs = [(0x100, 0x10000, 0x40, 0x108000, 0, 8, 0),
            (0x120, 0x10000, 0x40, 0x000002, 0xFF0, 0, 0),
            (0x130, 0x10000, 0x40, 0x000000, 0, 0, 0),
            (0x150, 0x10000, 0x80, 0x402040, 0, 0, 0),
            (0x180, 0x10000, 0x40, 0x000800, 0, 0, 0),
            (0x1A0, 0x10000, 0x40, 0x100004, 0, 0, 0),
            (0x20000, 0x10000, 0x40, 0x008000, 0, 0, 0),
            (0x40000, 0x100, 0x40, 0, 0, 0, 0),
            (0x40000, 0x100, 0x40, 0, 0xFF0, 0, 0x7FFFFFFF),
            (0x40000, 0x100, 0x40, 0, 0x100, 0, 0),
            (0x10, 0x10000, 0x200, 0, 0, 0, 0),
            (0x60, 0x10000, 0x20, 0x000004, 0, 0, 0),
            (0x70, 0x10000, 0x20, 0x002000, 0, 0, 0)]
    w = [[0x207FC, 4, HOST], [COUNT, 4, len(objs)], [0x219B8, 4, 1], [0x219BC, 4, 0], [0x219C4, 4, 0], [0x219AC, 4, 0],
         [0x21020, 4, 0x1000], [0x23D68, 4, 0x5000], [0x23D64, 4, 0x100], [0x23D60, 4, (-0x3000) & 0xFFFFFFFF]]
    for k, (metric, rng, radius, flags, seen, scale, run) in enumerate(objs):
        o = 0x30100 + 0x100 * k
        w += [[LIST + 4 * k, 4, o], [o + 0x1C, 4, metric], [o + 0x18, 4, rng], [o + 0x20, 4, radius], [o + 0x14, 4, flags],
              [o + 0x3E, 4, seen], [o + 0x36, 2, scale], [o + 0x2A, 4, run], [o + 4, 4, 0x5100 + 0x100 * k],
              [o + 8, 4, 0x180], [o + 0xC, 4, (-0x2F00) & 0xFFFFFFFF], [o, 4, 0], [o + 0x28, 1, 1]]
    out = [{"name": "far list: scans, collisions, kept, deactivated, retired", "method": "collect_far_objects", "args": [LIST, COUNT],
            "writes": w, "stack_guard": [0x3D000, 0x3F040]},
           {"name": "far list: empty", "method": "collect_far_objects", "args": [LIST, COUNT],
            "writes": [[COUNT, 4, 0x10000], [0x219B8, 4, 2]]}]
    return out


def altitude_cases() -> list:
    """update_altitude_detail_bands: the camera height inside its band (horizon only), across the 0xff band boundary,
    at zero; the near reference moved (the far list re-sorted: hidden referenced kept, hidden unreferenced freed with
    and without a controller and destroy hook, very far moved out, far kept, near activated; freed controllers
    compacted; the controllers reordered and the type-0xf shapes moved last), and the far reference moved too (the very
    far list as well). Plus the two Leyte-only routines on their own: controllers with a target before / after / absent,
    and entity_array_1 partitions."""
    HOR1, HOR2, VB1, VB2 = 0x3C000, 0x3C080, 0x3C100, 0x3C200
    CAM = (0x100000, 0x400, 0x100000)
    DEF8, DEF1, DEFF = 0x3C300, 0x3C310, 0x3C320
    HOOK = STAND_INS["destroy hook"][0]
    w = [[0x207FC, 4, HOST], [0x21594, 4, HOR1], [0x21598, 4, HOR2], [HOR1 + 0x46, 4, VB1], [HOR2 + 0x46, 4, VB2],
         [0x23D68, 4, CAM[0]], [0x23D64, 4, CAM[1]], [0x23D60, 4, CAM[2]], [0x23C3C, 4, 0x400], [0x23C34, 2, 10],
         [DEF8, 2, 8], [DEF1, 2, 1], [DEFF, 2, 0xF], [DEF1 + 0xA, 4, HOOK]]
    # references at the camera: no move
    refs = [[0x23C54, 4, CAM[0]], [0x23C50, 4, CAM[1]], [0x23C4C, 4, CAM[2]], [0x23C48, 4, CAM[0]], [0x23C44, 4, CAM[1]], [0x23C40, 4, CAM[2]]]
    # controllers (small heap blocks, class 4): 0 type 8 targeting 2's object, 1 plain (freed), 2 plain, 3 type 8 without target
    ctls = [0x3C400 + 0x40 * k for k in range(4)]
    objs = {}
    def obj(name, at, kind=1, flags=0, ref=0, ctl=0, dx=0, rng=0):
        objs[name] = at
        return [[at - 4, 1, 4], [at, 4, ctl], [at + 0x28, 1, kind], [at + 0x14, 4, flags], [at + 0x29, 1, ref], [at + 0x18, 4, rng],
                [at + 4, 4, CAM[0] + dx], [at + 8, 4, CAM[1]], [at + 0xC, 4, CAM[2]]]
    lists = []
    lists += obj("kept", 0x3C800, flags=2, ref=1)
    lists += obj("freed", 0x3C900, flags=2, ctl=ctls[1])
    lists += obj("freed bare", 0x3CA00, flags=2)
    lists += obj("very far", 0x3CB00, dx=0x200000)
    lists += obj("far", 0x3CC00, dx=0x10000)
    lists += obj("near", 0x3CD00, dx=0x100)
    lists += obj("vf kept", 0x3CE00, dx=0x300000)
    lists += obj("vf back", 0x3CF00, dx=0x20000)
    lists += obj("vf hidden", 0x3D100, flags=2, ref=2)
    lists += obj("shape f", 0x3D200, kind=2, ctl=ctls[2])
    lists += obj("shape", 0x3D300, kind=2, ctl=ctls[0])
    lists += [[ctls[0] - 4, 1, 4], [ctls[0], 4, objs["shape"]], [ctls[0] + 4, 4, DEF8], [ctls[0] + 0x32, 4, objs["shape f"]],
              [ctls[1] - 4, 1, 4], [ctls[1], 4, objs["freed"]], [ctls[1] + 4, 4, DEF1],
              [ctls[2] - 4, 1, 4], [ctls[2], 4, ctls[0]], [ctls[2] + 4, 4, DEFF],
              [ctls[3] - 4, 1, 4], [ctls[3], 4, 0], [ctls[3] + 4, 4, DEF8], [ctls[3] + 0x32, 4, 0]]
    # shape f's first long (its controller) is what controller 0 targets: entry 2 of the list is that controller
    lists += [[objs["shape f"], 4, ctls[2]]]
    lists += [[0x21BD4 + 4 * k, 4, c] for k, c in enumerate((ctls[0], ctls[1], ctls[2], ctls[3]))] + [[0x219B4, 4, 4]]
    far = ["kept", "freed", "freed bare", "very far", "far", "near"]
    lists += obj("vf freed", 0x3D400, flags=2, ctl=0x3C580)
    lists += [[0x3C580 - 4, 1, 4], [0x3C580, 4, 0x3D400], [0x3C584, 4, DEF1]]   # a controller off the list, with a destroy hook
    lists += obj("vf bare", 0x3D500, flags=2)
    vfar = ["vf kept", "vf back", "vf hidden", "vf freed", "vf bare"]
    lists += [[0x233BC + 4 * k, 4, objs[n]] for k, n in enumerate(far)] + [[0x219C4, 4, len(far)]]
    lists += [[0x22D2C + 4 * k, 4, objs[n]] for k, n in enumerate(vfar)] + [[0x219C0, 4, len(vfar)]]
    lists += [[0x20B44, 4, objs["shape f"]], [0x20B44 + 4, 4, objs["shape"]], [0x20F2C, 4, 2]]
    # a land outline object (byte +0x16 bit 4) in entity_array_1: build_land_outline_at_detail's own near case
    # (land_outline_cases), with the references at its camera so only the band crossing rebuilds it
    lw = next(c for c in land_outline_cases() if c["name"] == "land outline: near, wide limit")["writes"]
    LOBJ = 0x3A000
    land = lw + [[0x23D64, 4, 0x80], [0x23C3C, 4, 0x400], [0x23C50, 4, 0x80], [0x23C48, 4, 0x5000], [0x23C44, 4, 0x80],
                 [0x23C40, 4, 0x5000], [0x20B44, 4, LOBJ], [0x20F2C, 4, 1], [LOBJ + 0x16, 1, 0x10], [LOBJ + 0x28, 1, 2]]
    out = []
    for name, extra in (("height in its band", refs),
                        ("height across 0xff", refs + [[0x23D64, 4, 0x80], [0x23C3C, 4, 0x400]]),
                        ("height zero", refs + [[0x23D64, 4, 0]]),
                        ("near reference moved", refs + [[0x23C54, 4, CAM[0] + 0x10000]] + lists),
                        ("far reference moved", refs + [[0x23C48, 4, CAM[0] + 0x100000]] + lists),
                        ("height across 0xff: a land outline rebuilt", land)):
        out.append({"name": "altitude bands: " + name, "method": "update_altitude_detail_bands", "args": [], "writes": w + extra,
                    "stack_guard": [0x3F000 - 0x2000, 0x3F040] if extra is land else [0x3E000, 0x3F040]})
    out.append({"name": "order controllers", "method": "leyte_order_tracked_controllers", "args": [], "writes": w + lists})
    out.append({"name": "order controllers: none", "method": "leyte_order_tracked_controllers", "args": [], "writes": [[0x219B4, 4, 0]]})
    out.append({"name": "type-0xf shapes last", "method": "leyte_move_type0f_shapes_last", "args": [], "writes": w + lists,
                "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "type-0xf shapes last: empty", "method": "leyte_move_type0f_shapes_last", "args": [], "writes": [[0x20F2C, 4, 0]]})
    return out


def scatter_cases() -> list:
    """update_ground_scatter_objects in spawn_mission_pool_object's world (cloud_cases: its template without vertices, so a
    spawn retires and returns 0; its mission root also gives the re-place path its descriptor and scene record). Points
    in front of the camera (on screen), behind it, and far (re-placed in the square, or on the circle in view 5); cached
    and computed surfaces (the default face's code 0x10 has a template, a cached 0x23 none); slots owned, flagged (+0x14
    bit 1), re-placed in place (same class, vertices; translated), cleaned up and respawned (another class), empty; nothing
    newly visible (hidden); above 0x26ac (slots cleared once); host +0xac off; the reset (view_flag)."""
    spawn_world = next(c for c in cloud_cases() if c["name"] == "spawn pool object: no vertices: retired")["writes"]
    ROOT, LIST, SLOTS, BASE, TMPL = 0x30000, 0x30100, 0x30200, 0x31000, 0x30600
    REC = BASE + 0x40
    CAM = 0x900000
    PLAYER, DEFAULT, VB = 0x3C000, 0x3C100, 0x3C200
    w = spawn_world + [[HOST + 0xAC, 2, 1], [0x23D88, 2, 6], [0x23D86, 2, 0], [0x2B228, 4, 1], [0x20B28, 4, PLAYER],
         [PLAYER + 0x2E, 4, 0], [PLAYER + 0x22, 2, 0x1000], [0x2C5B4, 4, 0x2C5B8], [0x21590, 4, DEFAULT], [DEFAULT + 0x16, 1, 0x10],
         [0x27050, 2, 0x140], [0x2704E, 2, 0x120], [0x2701A, 2, 0xFF60], [0x27018, 2, 0xA0], [0x27016, 2, 0xFF88],
         [0x20B2E, 2, 0x78], [0x2140C + 4, 4, TMPL], [0x2140C + 8, 4, 0]]
    w += [[0x2C5B8 + 4 * k, 4, (0x9E3779B9 * (k + 1)) & 0xFFFFFFFF] for k in range(0x37)]
    w += [[0x23C74 + 2 * k, 2, 0x1000 if k in (0, 4, 8) else 0] for k in range(9)]
    # the 64 points: by default near and behind the camera (off screen, kept)
    pts = {i: (CAM + 0x100, -0x100) for i in range(0x40)}
    pts.update({1: (CAM + 0x10, 0x800), 2: (CAM - 0x20, 0x600), 3: (CAM + 0x30, 0x900), 4: (CAM, 0x400),
                5: (CAM + 0x6000, 0x100), 6: (CAM + 0x100, 0x7000)})
    for i, (x, z) in pts.items():
        w += [[0x2110C + 12 * i, 4, x & 0xFFFFFFFF], [0x2110C + 12 * i + 4, 4, 0], [0x2110C + 12 * i + 8, 4, z & 0xFFFFFFFF],
              [0x210CC + i, 1, 0x3F]]
    w += [[0x210CC + 2, 1, 0x7F], [0x2108C + 2, 1, 0x23],            # point 2: surface cached, no template
          [0x210CC + 0, 1, 0xC0], [0x2107C, 2, 0],                   # slot 0 owns point 0
          [0x210CC + 5, 1, 0xC3]]                                     # point 5 (far) named slot 3
    #        slot object: class +0x10, flags +0x14, vertices
    objs = {0: (1, 0, VB), 1: (1, 2, VB), 2: (1, 0, VB), 3: (2, 0, VB)}
    for k, (cls, flags, vb) in objs.items():
        o = 0x3C400 + 0x100 * k
        w += [[0x2144C + 4 * k, 4, o], [o + 0x10, 2, cls], [o + 0x12, 2, 0], [o + 0x14, 4, flags], [o + 0x46, 4, vb],
              [o + 0x38, 2, 1], [o + 0x3C, 2, 0], [o + 0x15, 1, 0x10 if k == 2 else 0], [o + 0x28, 1, 1],
              [o + 0x32, 2, 3], [o + 0x2E, 4, 0], [o, 4, 0], [0x2107C + 2 * k, 2, 7 + k]]
    w += [[0x210CC + 9, 1, 0x82],                                   # slot 2's point still names it (no 0xc0): detached
          [0x3C600 + 0x2E, 4, 0x3C380], [0x3C380, 2, 2],              # slot 2's fractal reference released
          [0x210CC + 3, 1, 0x7F], [0x2108C + 3, 1, 0x35], [0x2140C + 12, 4, TMPL]]   # point 3: a cached surface with a template
    w += [[REC + 2, 4, 0x10], [REC + 6, 4, 0x20], [REC + 0xA, 4, 0x30], [REC + 0x22, 4, 0x100], [REC + 0x26, 4, 0x200], [REC + 0x2A, 4, 0x300]]
    out = []
    for name, extra in (("square placement", []),
                        ("circle placement (view 5)", [[0x23D88, 2, 5]]),
                        ("reset (view flag)", [[0x23D86, 2, 1]]),
                        ("first use", [[0x2B228, 4, 0]]),
                        ("nothing newly visible", [[0x210CC + i, 1, 0xBF] for i in range(1, 7)]),
                        ("no surface with a template", [x for i in (1, 3, 4) for x in ([0x210CC + i, 1, 0x7F], [0x2108C + i, 1, 0x23])])):
        out.append({"name": "ground scatter: " + name, "method": "update_ground_scatter_objects", "args": [], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "ground scatter: above 0x26ac, slots cleared", "method": "update_ground_scatter_objects", "args": [],
                "writes": w + [[0x23D64, 4, 0x3000]], "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "ground scatter: above 0x26ac, not started", "method": "update_ground_scatter_objects", "args": [],
                "writes": w + [[0x23D64, 4, 0x3000], [0x2B228, 4, 0]]})
    out.append({"name": "ground scatter: off", "method": "update_ground_scatter_objects", "args": [], "writes": w + [[HOST + 0xAC, 2, 0]]})
    return out


def dot_cases() -> list:
    """draw_distant_object_dots: the erase and plot routines stood in; queued objects that are trails (two vertices,
    shifted left or right) or points, in front and on screen at every size, behind, off screen on each side; faces with
    no palette slot, with one and the tally running, with one and the tally starting (assign_face_colour's own case world
    is not needed: a tally already running covers the slot path, and a failing assignment is the second case); the host
    line flag emptying the queue."""
    ERASE, PLOT = STAND_INS["dot erase"][0], STAND_INS["dot plot"][0]
    CTL_TRAIL, CTL_POINT, VB = 0x3C000, 0x3C040, 0x3C080
    w = [[0x207FC, 4, HOST], [0x23FB4, 4, ERASE], [0x23FB0, 4, PLOT], [0x23D68, 4, 0x10000], [0x23D64, 4, 0x2000],
         [0x23D60, 4, 0x30000], [0x27050, 2, 0x140], [0x2704E, 2, 0x120], [0x2701A, 2, 0xFF60], [0x27018, 2, 0xA0],
         [0x27016, 2, 0xFF88], [0x27014, 2, 0x78], [CTL_TRAIL + 0x3B, 1, 2], [CTL_POINT + 0x3B, 1, 0], [0x23FBA, 2, 0x0105]]
    w += [[0x23C74 + 2 * k, 2, 0x1000 if k in (0, 4, 8) else 0] for k in range(9)]
    #         ctl, dz (ahead), dx, shift +0x34, scale +0x36, face slot
    objs = [(CTL_POINT, 0x400, 0, 0, 0, 0xFF), (CTL_POINT, 0x1388, 0x100, 0, 0, 0x03), (CTL_POINT, 0x1D00, 0, 0, 0, 0xFF),
            (CTL_POINT, 0x2700, 0, 0, 0, 0xFF), (CTL_POINT, 0x3000, 0, 0, 0, 0xFF), (CTL_POINT, -0x400, 0, 0, 0, 0xFF),
            (CTL_POINT, 0x400, 0x2000, 0, 0, 0xFF), (CTL_POINT, 0x400, -0x2000, 0, 0, 0xFF), (CTL_POINT, 0x800, 0, 1, 0, 0xFF),
            (CTL_TRAIL, 0x800, 0x10, 1, 3, 0xFF), (CTL_TRAIL, 0x800, -0x10, 2, 0, 0xFF),
            (CTL_TRAIL, 0, 0, 0, 23, 0xFF)]                           # 5000 << 23 overflows: "near" negative
    for k, (ctl, dz, dx, shift, scale, slot) in enumerate(objs):
        o, f = 0x3C200 + 0x80 * k, 0x3D000 + 0x20 * k
        w += [[0x2CB86 + 4 * k, 4, o], [o, 4, ctl], [o + 0x4A, 4, f], [o + 0x34, 2, shift], [o + 0x36, 2, scale],
              [o + 4, 4, (0x10000 + dx) & 0xFFFFFFFF], [o + 8, 4, 0x2000], [o + 0xC, 4, (0x30000 + dz) & 0xFFFFFFFF], [o + 0x46, 4, VB],
              [f + 0xF, 1, slot], [f + 6, 2, 0x4400 + k]]
    w += [[VB + 4 * k, 4, v & 0xFFFFFFFF] for k, v in enumerate((0x20000, 0x4000, 0x60800, 0x20100, 0x4000, 0x60C00))]
    # the overflowing trail sits at the camera (its offset 0 whatever the shift), its vertices just ahead
    w += [[0x3C200 + 0x80 * 11 + 0x46, 4, 0x3C140]] + [[0x3C140 + 4 * k, 4, v] for k, v in enumerate((0, 0, 0x800, 0x10, 0, 0x900))]
    out = [{"name": "distant dots: points and trails", "method": "draw_distant_object_dots", "args": [], "writes": w + [[0x2CB84, 2, len(objs)]],
            "stack_guard": [0x3E000, 0x3F040]},
           *[{"name": "distant dots: the tally starts (assign_face_colour: %s)" % name, "method": "draw_distant_object_dots",
              "args": [], "stack_guard": [0x3E000, 0x3F040],
              "writes": w + [[0x2CB84, 2, 2], [0x23FBA, 2, 0], [0x3D020 + 0xF, 1, 7], [HOST + 0xB4, 2, 0], [0x2100E, 2, 8],
                             [0x23FAC, 4, 0x3D600], [0x23D90, 4, 0x3D400], [0x23D94, 4, 0x3D500], [0x21568, 4, 3], [0x23FD2, 2, 0]]
                       + [[0x3D400 + k, 1, 1 if k in used else 0] for k in range(200)] + [[0x3D500 + k, 1, 0] for k in range(200)]}
             for name, used in (("a free slot", (3, 4)), ("every slot used: the routine ends", tuple(range(200))))],
           {"name": "distant dots: host line flag empties the queue", "method": "draw_distant_object_dots", "args": [],
            "writes": w + [[0x2CB84, 2, len(objs)], [HOST + 0x9E, 2, 1], [0x23FBC, 2, 7]]}]
    return out


def frame_step_cases() -> list:
    """overlay_frame_step (hook 10) over an empty scene: the two horizon objects light (no faces, a few vertices), no
    entities, no far objects, no dots; the span filler (0x26ff0) and the dot erase routine stood in; host hooks off.
    Window 1 in view 6 (the folded-angle camera), window 1 at the 16th frame (overlap collisions run), and window 2 (the
    side view: bounds saved, view mode 2, heading turned and restored)."""
    HOR1, HOR2, HOR3, VB, VIEW, MAP0, MAP1, PAL, REC = 0x3C000, 0x3C080, 0x3C100, 0x3C200, 0x3C300, 0x3C400, 0x3C500, 0x3C600, 0x3C800
    w = [[0x207FC, 4, HOST], [HOST + 0x72, 4, REC], [REC + 6, 2, 0], [HOST + 0x10, 2, 0x40], [HOST + 6, 4, 0x1234],
         [HOST + 0x1A + 0x16 + 4, 4, PAL], [HOST + 0x1A + 0x16 + 8, 2, 0x55], [HOST + 0x1A + 0x2C + 4, 4, PAL],
         [HOST + 0x1A + 0x2C + 8, 2, 0x66], [PAL + 0x1A0, 2, 0x77], [0x23D94, 4, MAP0], [0x23D90, 4, MAP1], [0x23FBA, 2, 5],
         [0x2100E, 2, 8], [0x23D88, 2, 6], [0x23FB4, 4, STAND_INS["dot erase"][0]], [0x26FF0, 4, CODE_POINTERS[0x26FF0][0]],
         [0x23F9C + 4, 4, 0x3C900], [0x20F6C + 4, 4, 0x40800], [0x20F5C + 4, 4, 0x41000],
         [0x23F9C + 8, 4, 0x3CA00], [0x20F6C + 8, 4, 0x41800], [0x20F5C + 8, 4, 0x42000],
         [0x23D68, 4, 0x1000], [0x23D64, 4, 0x500], [0x23D60, 4, 0x2000], [0x23C3C, 4, 0x400], [0x23C34, 2, 10],
         [0x23C54, 4, 0x1000], [0x23C50, 4, 0x500], [0x23C4C, 4, 0x2000], [0x23C48, 4, 0x1000], [0x23C44, 4, 0x500], [0x23C40, 4, 0x2000],
         [0x23D40, 2, 0x100], [0x23D3E, 2, 0x200], [0x23D3C, 2, 0x300], [0x23D38, 4, 0x1400], [0x23D34, 4, 0x500], [0x23D30, 4, 0x600],
         [0x21594, 4, HOR1], [0x21598, 4, HOR2], [0x2159C, 4, HOR3], [0x21021, 1, 0], [0x21023, 1, 3],
         [0x27050, 2, 0x140], [0x2704E, 2, 0x120], [0x2701A, 2, 0xFFE0], [0x27018, 2, 0x20], [0x27016, 2, 0xFFF0],
         [0x27014, 2, 0x10], [0x20B2E, 2, 0x10], [0x20B30, 2, 0x8], [0x27012, 2, 0x20], [0x21010, 2, 0x20], [0x21012, 2, 0x10]]
    for o in (HOR1, HOR2, HOR3):
        w += [[o + 0x46, 4, VB], [o + 0x4E, 4, VIEW], [o + 0x38, 2, 4], [o + 0x3C, 2, 0], [o + 0x3A, 2, 0], [o + 0x4A, 4, VIEW + 0x80],
              [o + 0x32, 2, 3], [o + 0x1C, 4, 0x100], [o + 0x20, 4, 0x200], [o + 0x17, 1, 0x08]]
    w += [[VB + 4 * k, 4, 0x100 * (k + 1)] for k in range(0x20)]
    out = []
    for name, args, extra in (("window 1, view 6", [0, 1], []), ("window 1, the 16th frame", [0, 1], [[0x21023, 1, 0x10]]),
                              ("window 2, the side view", [0, 2], []),
                              ("window 1, haze (the horizon re-banded and shaded, retired)",
                               [0, 1], [[HOST + 0xAA, 2, 1], [HOR1 + 0x17, 1, 0x0A], [HOR1 + 0x32, 2, 0]])):
        out.append({"name": "frame step: " + name, "method": "overlay_frame_step", "args": args, "writes": w + extra,
                    "stack_guard": [0x3D000, 0x3F040]})
    return out


def collision_test_cases() -> list:
    """The broad-phase proximity tests on dispatch_collision's no-handler world (frame_batch3_cases: both controllers'
    definitions without a collision handler, so a dispatch only reads): unflagged pairs, near and far pairs either radius
    deciding; the player test against a type-0x14 object, another type, and a non-player; turret_pre_render aimed and
    idle, firing, unaimed."""
    A, B, CA, CB, DA, DB, PLAYER = 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C400, 0x3C500, 0x3C200
    UNSTUB = (0x16784, 0x167E4)
    base = [[A, 4, CA], [B, 4, CB], [CA + 4, 4, DA], [CB + 4, 4, DB], [DA, 2, 0x11], [DB, 2, 0x14], [0x23D88, 2, 1],
            [A + 4, 4, 0x1000], [A + 8, 4, 0x200], [A + 0xC, 4, 0x3000], [A + 0x20, 4, 0x80], [B + 0x20, 4, 0x80],
            [B + 8, 4, 0x200], [B + 0xC, 4, 0x3000], [0x20B28, 4, PLAYER]]
    out = []
    for name, bx, fa, fb, ra in (("both flagged, near", 0x1040, 0x100000, 0x100000, 0x80), ("both flagged, far", 0x1400, 0x100000, 0x100000, 0x80),
                                 ("both flagged, a's radius decides", 0x1400, 0x100000, 0x100000, 0x400),
                                 ("one flagged", 0x1040, 0x100000, 0, 0x80)):
        w = base + [[B + 4, 4, bx], [A + 0x14, 4, fa], [B + 0x14, 4, fb], [A + 0x20, 4, ra]]
        out.append({"name": "proximity: " + name, "method": "test_proximity_collision", "args": [A, B], "writes": w, "unstub": UNSTUB})
        if name == "both flagged, near":
            out.append({"name": "player vs type 0x14: near", "method": "test_player_vs_type0e_proximity", "args": [A, B], "writes": w, "unstub": UNSTUB})
            out.append({"name": "player vs type 0x14: another type", "method": "test_player_vs_type0e_proximity", "args": [A, B],
                        "writes": w + [[DB, 2, 0xE]], "unstub": UNSTUB})
            out.append({"name": "player vs type 0x14: not the player", "method": "test_player_vs_type0e_proximity", "args": [A, B],
                        "writes": w + [[0x20B28, 4, 0x3EE00]], "unstub": UNSTUB})
    T = 0x3C600
    for name, aim, fire in (("aimed, idle", 0x3C700, 0), ("aimed, firing", 0x3C700, 1), ("unaimed", 0, 0)):
        out.append({"name": "turret pre-render: " + name, "method": "turret_pre_render", "args": [T, 1], "d0": True,
                    "writes": [[T + 0x36, 4, aim], [T + 0x46, 2, fire], [T + 0x4E, 2, 0xC123], [T + 0x22, 2, 0x1111]]})
    return out


def contact_cases() -> list:
    """Contact records: find (present after probing past other pairs, absent with the table full of others), create on a
    fresh heap (faces selected by extent, axes from the normals: each axis largest, ties, a large extent shifted), and
    handle_player_contact: not the player, too far, a shape touched (record created, then found again), with b's
    collision hook (stood in)."""
    ARENA, A, B, CA, DB, VB, FACES = 0x32000, 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C400, 0x3C600
    HOOK = STAND_INS["shape collision hook"][0]
    out = []
    hashv = lambda a, b: ((a & 0xFF) ^ (((b & 0xFF) if (b & 0xFF) < 0x80 else (b & 0xFF) - 0x100) << 2)) & 0xFF
    h = hashv(A, B)
    REC = 0x3C800
    w = [[0x215A4 + 4 * ((h + k) & 0xFF), 4, 0x3CA00 + 0x10 * k] for k in range(3)] + [[0x215A4 + 4 * ((h + 3) & 0xFF), 4, REC],
         [REC, 4, A], [REC + 4, 4, B]] + [[0x3CA00 + 0x10 * k, 4, 0x1111] for k in range(3)]
    out.append({"name": "find contact: after three others", "method": "find_contact_record", "args": [A, B], "writes": w, "d0": True})
    full = [[0x215A4 + 4 * k, 4, 0x3CA00] for k in range(0x100)] + [[0x3CA00, 4, 0x2222]]
    out.append({"name": "find contact: absent, table full", "method": "find_contact_record", "args": [A, B], "writes": full, "d0": True})
    # b: 5 vertices then 4 normals; 4 faces, extents 0x50 / 0x200 / 0x80 / 0x100 against half of metric 0x100
    verts = [(0x10, 0x20, 0x30), (0, 0, 0), (0, 0, 0), (0, 0, 0), (0, 0, 0)]
    normals = [(0x500, 0x40, 0x30), (0x10, -0x600, 0x20), (0x10, 0x20, 0x700), (0x210, 0x220, 0x30), (0, 0, 0)]
    world = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0],
             [B + 0x1C, 4, 0x100], [B + 0x4A, 4, FACES], [B + 0x3C, 2, 5], [B + 0x20, 4, 0x3000], [A + 0x20, 4, 0x2000],
             [B + 0x36, 2, 2], [B + 0x46, 4, VB], [B + 0x38, 2, len(verts)]]
    for k, v in enumerate(verts + normals):
        world += [[VB + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for m, c in enumerate(v)]
    for k, ext in enumerate((0x80, 0x200, 0x90, 0x100, 0x10)):
        world += [[FACES + 0x20 * k + 0x1C, 4, ext]]
    out.append({"name": "create contact: faces selected, axes", "method": "create_shape_contact_record", "args": [A, B], "writes": world,
                "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "create contact: slots taken, a negative metric", "method": "create_shape_contact_record", "args": [A, B],
                "writes": world + [[B + 0x1C, 4, (-0x101) & 0xFFFFFFFF]] + [[0x215A4 + 4 * ((h + k) & 0xFF), 4, 0x3CA00] for k in range(4)],
                "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    pw = world + [[0x20B28, 4, CA], [A, 4, CA], [B, 4, 0x3C380], [0x3C380 + 4, 4, DB], [DB + 0x12, 4, HOOK],
                  [A + 4, 4, 0x1000], [A + 8, 4, 0x200], [A + 0xC, 4, 0x3000], [B + 4, 4, 0x1100], [B + 8, 4, 0x200], [B + 0xC, 4, 0x3000]]
    for name, extra in (("not the player", [[0x20B28, 4, 0x3EE00]]), ("too far", [[B + 4, 4, 0x40000]]),
                        ("a shape touched: record created", []), ("a shape touched again: record found", w[3:6]),
                        ("with b's collision hook", [[B + 0x16, 1, 0x40]])):
        out.append({"name": "player contact: " + name, "method": "handle_player_contact", "args": [A, B], "writes": pw + extra,
                    "stack_guard": [0x3E000, 0x3F040], "unstub": (0x16BE2,)})
    return out


def terrain_cases() -> list:
    """The fractal terrain (1.0.3's tools/original/gen_terrain_cases.py world on Leyte's layout): two face centres around
    one cell, two sloped faces, an optional part angle; a fresh heap for the records. create_fractal_contact_record
    unrotated and rotated; handle_terrain_contact with a new record (unrotated, rotated), the point in the other
    triangle, a negative cell, during a replay, terrain while holding a reference, no controller; fractal_height_at on a
    prepared record (empty cell bounds) at several points."""
    ARENA, A, B, CTL, PARENT, SCENE, PARTS, TEMPLATES, VERTS, FACES, IDX = (0x32000, 0x3C000, 0x3C080, 0x3C100, 0x3C200,
                                                                            0x3C280, 0x3C380, 0x3C400, 0x3C600, 0x3C700, 0x3C780)
    CHANGED = 0x3C7F0

    def terrain(angle=0, a_pos=(1000, 500, 2000)):
        w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [HOST + 0x8C, 4, TEMPLATES],
             [0x21058, 2, 0], [A, 4, CTL], [CTL, 4, A], [A + 0x20, 4, 0x8000], [B + 0x20, 4, 0x10000], [B + 0x24, 4, PARENT],
             [B + 0x10, 2, 2], [B + 0x12, 2, 1], [PARENT + 0x2A, 4, SCENE], [PARENT + 0x32, 4, PARTS], [PARENT + 0x24, 4, 0],
             [PARTS + 0x18 + 4, 2, angle], [SCENE + 0x2E + 2 * 0x18, 4, 0x10], [B + 0x38, 2, 6], [B + 0x3A, 2, 2], [B + 0x36, 2, 0],
             [B + 0x46, 4, VERTS], [B + 0x4A, 4, FACES], [CHANGED, 2, 0]]
        w += [[0x215A4 + 4 * k, 4, 0] for k in range(0x100)]
        w += [[A + 4 + 4 * j, 4, v & 0xFFFFFFFF] for j, v in enumerate(a_pos)]
        base = TEMPLATES + 0x10 + 0x22
        for i, p in enumerate([(0, 0, 0)] * 4 + [(26400, 0, 40000), (26400, 0, 60000)]):
            w += [[base + 12 * i + 4 * j, 4, v & 0xFFFFFFFF] for j, v in enumerate(p)]
        for i, p in enumerate([(-400, 100, -800), (800, 300, 0), (0, 200, 900), (300, 0, 300), (0, 0, 0), (0, 0, 0),
                               (-400 + 30, 100 + 200, -800 - 40), (-400 - 20, 100 + 150, -800 + 10)]):
            w += [[VERTS + 12 * i + 4 * j, 4, v & 0xFFFFFFFF] for j, v in enumerate(p)]
        for f, first in enumerate((1, 2)):
            w += [[IDX + 8 * f, 2, first], [FACES + 0x20 * f + 0x18, 4, IDX + 8 * f]]
        return w

    out = []
    hashv = ((A & 0xFF) ^ ((B & 0xFF) - (0x100 if (B & 0xFF) >= 0x80 else 0)) * 4) & 0xFF
    for name, angle, extra in (("unrotated", 0, []), ("rotated 0x0800", 0x0800, []), ("rotated 0x2800 (cos negative)", 0x2800, []),
                               ("slots taken", 0, [[0x215A4 + 4 * ((hashv + k) & 0xFF), 4, 0x3CFF0] for k in range(3)])):
        out.append({"name": "create fractal record: " + name, "method": "create_fractal_contact_record", "args": [A, B],
                    "writes": terrain(angle) + extra, "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    for name, angle, pos, extra in (("new record, unrotated", 0, (1000, 500, 2000), []), ("new record, rotated", 0x0800, (1000, 500, 2000), []),
                                    ("point in the other triangle", 0, (9000, 500, 1000), []), ("negative cell", 0, (-50000, 500, -30000), []),
                                    ("during a replay (surface byte kept)", 0, (1000, 500, 2000), [[0x21058, 2, 1]]),
                                    ("terrain while holding a reference, no record", 0, (1000, 500, 2000), [[B + 0x16, 1, 2], [A + 0x2E, 4, 0x12345]]),
                                    ("no controller", 0, (1000, 500, 2000), [[A, 4, 0]]),
                                    ("small radii (a non-negative shift)", 0, (1000, 500, 2000), [[A + 0x20, 4, 0x100], [B + 0x20, 4, 0x100]]),
                                    ("small radii, the other triangle", 0, (9000, 500, 1000), [[A + 0x20, 4, 0x100], [B + 0x20, 4, 0x100]]),
                                    ("small radii, near the faces", 0, (-300, 500, -600), [[A + 0x20, 4, 0x100], [B + 0x20, 4, 0x100]]),
                                    ("a holding a fractal reference (released)", 0, (1000, 500, 2000), [[A + 0x2E, 4, 0x3C7E0], [0x3C7E0, 2, 2]])):
        out.append({"name": "terrain contact: " + name, "method": "handle_terrain_contact", "args": [A, B],
                    "writes": terrain(angle, pos) + extra, "stack_guard": [0x3E000, 0x3F040], "unstub": (0x16BE2,)})
    # test_terrain_collision (1.0.3's lifted object: a mesh of 3 vertices and two faces to raise; dispatch_collision's
    # no-handler world for a below the ground: both controllers' definitions without a handler)
    LV, LF, CB, DA, DB = 0x3CC00, 0x3CC40, 0x3CD00, 0x3CD40, 0x3CD80

    def lifted(b5=0):
        w = [[CTL + 4, 4, DA], [DA, 2, 1], [B, 4, CB], [CB + 4, 4, DB], [DB, 2, 2], [0x23D88, 2, 1], [A + 0x15, 1, b5],
             [A + 0x46, 4, LV], [A + 0x4A, 4, LF], [A + 0x38, 2, 3], [A + 0x3C, 2, 2], [LF + 0x1C, 4, 500], [LF + 0x3C, 4, 700],
             [0x219A6, 2, 0], [B + 0x2E, 4, 0], [A + 0x2E, 4, 0], [0x23D68, 4, 0], [0x23D64, 4, 0], [0x23D60, 4, 0]]
        return w + [[LV + 4 * k, 4, (100 * k - 300) & 0xFFFFFFFF] for k in range(9)]

    for name, pos, b5, angle in (("below the ground: lifted, support point, collision", (1000, -400, 2000), 0, 0),
                                 ("above the ground: support point only", (1000, 9000, 2000), 0, 0),
                                 ("frozen vertices and settled (+0x15 bits 4, 3)", (1000, -400, 2000), 0x18, 0),
                                 ("out of range", (0x40000, 0, 0), 0, 0), ("rotated terrain", (1000, -400, 2000), 0, 0x0800)):
        out.append({"name": "terrain collision: " + name, "method": "test_terrain_collision", "args": [A, B],
                    "writes": terrain(angle, pos) + lifted(b5), "stack_guard": [0x3E000, 0x3F040], "unstub": (0x16812,)})
    SUP, LIST, P1 = 0x3CE00, 0x3CE40, 0x3CE80
    held = [[SUP - 4, 1, 4], [SUP, 2, 1], [A + 0x2E, 4, SUP]]
    full = [[LIST - 4, 1, 4], [LIST, 2, 1], [LIST + 2, 2, 1], [LIST + 4, 4, P1], [P1, 2, 1], [P1 + 4, 4, 0x777], [P1 + 8, 4, 0x888], [B + 0x2E, 4, LIST]]
    flat = [[VERTS + 12 * 6 + 4, 4, 100], [VERTS + 12 * 7 + 4, 4, 100]]
    for name, pos, extra in (("holding a support point, below: collides", (1000, -400, 2000), held),
                             ("a full support list: grown", (1000, -400, 2000), full),
                             ("a flat plane: the cell corners", (1000, -400, 2000), flat),
                             ("a flat plane, the other triangle", (9000, -400, 1000), flat),
                             ("small radii (a non-negative shift)", (1000, -400, 2000), [[A + 0x20, 4, 0x100], [B + 0x20, 4, 0x100]]),
                             ("a negative z offset", (1000, -400, -2000), []),
                             ("terrain, holding a support point, no record", (1000, -400, 2000), held + [[B + 0x16, 1, 2]])):
        out.append({"name": "terrain collision: " + name, "method": "test_terrain_collision", "args": [A, B],
                    "writes": terrain(0, pos) + lifted(0) + extra, "stack_guard": [0x3E000, 0x3F040], "unstub": (0x16812,)})
    # a record as create_fractal_contact_record leaves it (unrotated, shifts 0, cell size 0x19c80 >> 2... kept small)
    REC = 0x3C800
    rec = [[REC, 4, A], [REC + 4, 4, B], [REC + 0xA, 2, 0], [REC + 0xC, 2, 2], [REC + 0xE, 2, 0], [REC + 0x14, 2, 0],
           [REC + 0x16, 2, 0x6720], [REC + 0x18, 2, 1], [REC + 0x1A, 2, 0xFFFF], [REC + 0x1C, 2, 0xFFFF], [REC + 0x1E, 2, 1], [REC + 0x20, 2, 0]]
    for name, x, z in (("first cell", 250, 250), ("other triangle", 1500, 100), ("far cell", -9000, 12000)):
        out.append({"name": "height at " + name, "method": "fractal_height_at", "args": [REC, x & 0xFFFFFFFF, z & 0xFFFFFFFF, CHANGED],
                    "writes": terrain() + rec, "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    cached = [[REC + 0x18, 2, 0], [REC + 0x1A, 2, 0x6720], [REC + 0x1C, 2, 0], [REC + 0x1E, 2, 0x6720], [REC + 0x20, 2, 1],
              [REC + 0x22 + 4, 2, 0x40], [REC + 0x30 + 4, 2, 0x40], [REC + 0x30 + 2, 2, 0x10], [REC + 0x30 + 6, 2, 0xFFF0]]
    for name, x, z in (("the cached cell, triangle b", 100, 900), ("the cached cell, outside its z", 100, 0x7000)):
        out.append({"name": "height at " + name, "method": "fractal_height_at", "args": [REC, x & 0xFFFFFFFF, z & 0xFFFFFFFF, CHANGED],
                    "writes": terrain() + rec + cached, "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    return out


def object_collision_cases() -> list:
    """test_object_collision on contact_cases' shape world (b: five faces, normals per axis): too far; b terrain and b a
    bullet target (test_terrain_collision and bullet_hits_shape have their own cases: here a is not of type 0x14 and
    b's terrain flag stays clear);
    a new record (sides stored); an old record whose sides flip for each face (point_in_face_by_axis on every axis, the
    point inside one: dispatched - the no-handler world - and outside); a skipped face (+0x13 bit 1); +0x15 bit 0 on
    either object; b's collision hook (stood in)."""
    ARENA, A, B, CA, CB, DA, DB, VB, FACES, IDX = 0x32000, 0x3C000, 0x3C100, 0x3C200, 0x3C280, 0x3C300, 0x3C340, 0x3C400, 0x3C600, 0x3C780
    HOOK = STAND_INS["shape collision hook"][0]
    # b: a cube's 8 corners, faces x+ / y- / z+ (square), a diagonal one and a degenerate one
    verts = [(0, 0, 0), (0x100, 0, 0), (0x100, 0x100, 0), (0, 0x100, 0), (0, 0, 0x100), (0x100, 0, 0x100), (0x100, 0x100, 0x100), (0, 0x100, 0x100)]
    normals = [(0x100, 0, 0), (0, -0x100, 0), (0, 0, 0x100), (0x80, 0x80, 0x10), (0, 0, 0)]
    w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [0x23D88, 2, 1],
         [A, 4, CA], [B, 4, CB], [CA + 4, 4, DA], [CB + 4, 4, DB], [DA, 2, 0x11], [DB, 2, 0x22], [DB + 0x12, 4, HOOK],
         [A + 4, 4, 0x80], [A + 8, 4, 0x80], [A + 0xC, 4, 0x80], [A + 0x20, 4, 0x2000],
         [B + 4, 4, 0], [B + 8, 4, 0], [B + 0xC, 4, 0], [B + 0x20, 4, 0x3000], [B + 0x1C, 4, 0x100], [B + 0x36, 2, 0],
         [B + 0x46, 4, VB], [B + 0x4A, 4, FACES], [B + 0x38, 2, len(verts)], [B + 0x3C, 2, len(normals)]]
    w += [[0x215A4 + 4 * k, 4, 0] for k in range(0x100)]
    for k, v in enumerate(verts + normals):
        w += [[VB + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for m, c in enumerate(v)]
    quads = [(1, 2, 6, 5), (0, 1, 5, 4), (4, 5, 6, 7), (0, 1, 2, 3), (0, 0, 0, 0)]
    for k, q in enumerate(quads):
        f = FACES + 0x20 * k
        w += [[f + 0x1C, 4, 0x400], [f + 0x14, 2, 4], [f + 0x18, 4, IDX + 8 * k]] + [[IDX + 8 * k + 2 * j, 2, v] for j, v in enumerate(q)]
    # an old record for these five faces (axes y/z, x/z, x/y, then 3 - none - and x/z); the stored sides match what the
    # point at the cube's centre gives (1, 1, 1, 0, 1) except where a case flips one
    REC = 0x3C800
    hashv = ((A & 0xFF) ^ ((B & 0xFF) - (0x100 if (B & 0xFF) >= 0x80 else 0)) * 4) & 0xFF

    def old(sides, shift=0):
        r = [[0x215A4 + 4 * hashv, 4, REC], [REC, 4, A], [REC + 4, 4, B], [REC + 0xA, 2, 5], [REC + 0xC, 2, shift & 0xFFFF], [REC + 0xE, 2, 0]]
        r += [[REC + 0x10 + 2 * k, 2, k] for k in range(5)] + [[REC + 0x1A + 2 * k, 2, s] for k, s in enumerate(sides)]
        return r + [[REC + 0x24 + 2 * k, 2, a] for k, a in enumerate((0, 1, 2, 3, 1))]
    out = []
    for name, extra in (("too far", [[A + 4, 4, 0x40000]]),
                        ("a new record", []),
                        ("an old record, no side flips", old((1, 1, 1, 0, 1))),
                        ("the x+ face flips: inside on y/z, dispatched", old((0, 1, 1, 0, 1))),
                        ("the y- face flips: inside on x/z, dispatched", old((1, 0, 1, 0, 1))),
                        ("the z+ face flips: inside on x/y, dispatched", old((1, 1, 0, 0, 1))),
                        ("the diagonal face flips: no axis", old((1, 1, 1, 1, 1))),
                        ("the diagonal face flips: a negative axis word", old((1, 1, 1, 1, 1)) + [[REC + 0x24 + 6, 2, 0xFFFF]]),
                        ("the x+ face flips, a shifted right", old((0, 1, 1, 0, 1), -1)),
                        ("the x+ face flips, a outside its square", old((0, 1, 1, 0, 1)) + [[A + 8, 4, 0x900]]),
                        ("a skipped face", old((0, 1, 1, 0, 1)) + [[FACES + 0x13, 1, 2]]),
                        ("a without the faces test", old((0, 1, 1, 0, 1)) + [[A + 0x15, 1, 1]]),
                        ("b without the faces test", old((0, 1, 1, 0, 1)) + [[B + 0x15, 1, 1]]),
                        ("b's collision hook", [[B + 0x16, 1, 0x40]]),
                        ("a bullet target, a not a bullet", [[B + 0x16, 1, 4]])):
        out.append({"name": "object collision: " + name, "method": "test_object_collision", "args": [A, B], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040], "unstub": (0x16812,)})
    return out


def bullet_cases() -> list:
    """segment_point_min_distance (D0) for points beside, beyond either end and on a long segment; bullet_hits_shape: out
    of range; a flagged shape (latched or not, by the ring's draw); targets of type 0x14 (ignored), 0x24 (its controller
    marked or not) and another type, and no controller (the bullet retired: entity_retire_cases' plain world)."""
    out = []
    for name, seg, p in (("beside the middle", (0, 0, 0, 0x1000, 0, 0), (0x800, 0x100, 0)), ("beyond the end", (0, 0, 0, 0x1000, 0, 0), (0x3000, 0, 0)),
                         ("before the start", (0x1000, 0x200, -0x300, 0x800, 0x100, 0), (-0x2000, 0, 0)),
                         ("a long segment, words wrapping", (0x10000, 0, 0, 0x9000, 0x7000, -0x5000), (0x14000, 0x3000, -0x2000))):
        out.append({"name": "segment distance: " + name, "method": "segment_point_min_distance", "args": [v & 0xFFFFFFFF for v in seg + p],
                    "writes": [], "d0": True})
    BUL, BCTL, SHAPE, SCTL, SDEF = 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C380
    w = [[BUL, 4, BCTL], [BUL + 4, 4, 0x1000], [BUL + 8, 4, 0x200], [BUL + 0xC, 4, 0x3000], [BCTL + 8, 4, 0x100],
         [BCTL + 0xC, 4, 0xFFFFFFC0], [BCTL + 0x10, 4, 0x81], [SHAPE + 4, 4, 0x1080], [SHAPE + 8, 4, 0x200], [SHAPE + 0xC, 4, 0x3000],
         [SHAPE + 0x20, 4, 0x80], [SHAPE, 4, SCTL], [SCTL + 4, 4, SDEF], [SDEF, 2, 0x11], [0x2C5B4, 4, 0x2C5B8],
         [BUL + 0x28, 1, 1], [BUL + 0x3E, 4, 0]]
    w += [[0x2C5B8 + 4 * k, 4, (0x9E3779B9 * (k + 1)) & 0xFFFFFFFF] for k in range(0x37)]
    for name, extra in (("out of range", [[SHAPE + 4, 4, 0x9000]]), ("a flagged shape", [[SHAPE + 0x16, 1, 4]]),
                        ("a flagged shape, another draw", [[SHAPE + 0x16, 1, 4], [0x2C5B4, 4, 0x2C5C0]]),
                        ("a type-0x14 target", [[SDEF, 2, 0x14]]), ("a type-0x24 target", [[SDEF, 2, 0x24]]),
                        ("a type-0x24 target, another draw", [[SDEF, 2, 0x24], [0x2C5B4, 4, 0x2C5C4]]),
                        ("another type: retired", []), ("no controller: retired", [[SHAPE, 4, 0]]),
                        ("a type-0x24 target, a zero draw (marked)", [[SDEF, 2, 0x24]] + [[0x2C5B8 + 4 * k, 4, 0] for k in range(0x37)]),
                        ("odd negative velocities", [[BCTL + 8, 4, 0xFFFFFF01], [BCTL + 0x10, 4, 0xFFFFFF7F]])):
        out.append({"name": "bullet hits shape: " + name, "method": "bullet_hits_shape", "args": [BUL, SHAPE], "writes": w + extra})
    return out


def pending_pose_cases() -> list:
    """The sinking ship's pose (Leyte only) on sink_cases' world (a square face crossing the waterline, a triangle below
    it): the pre-render hook with nothing pending, a pending shift (vertices moved), a pending re-sink with the chain to
    compose and with it cached (+0x61b), the heading word +0x654 turning the second angle."""
    base = next(c for c in sink_cases() if c["name"] == "sink at waterline: tilted, no re-shade")["writes"]
    OBJ, CTL = 0x30000, 0x3C000
    w = base + [[OBJ, 4, CTL], [CTL, 4, OBJ], [CTL + 0x654, 2, 0x300], [OBJ + 0x38, 2, 6], [OBJ + 0x3C, 2, 2]]
    out = []
    for name, extra in (("nothing pending", []), ("a pending shift", [[CTL + 0x619, 1, 1], [CTL + 0x624, 4, 0x10], [CTL + 0x62C, 4, 0xFFFFFFF8]]),
                        ("a pending re-sink, the chain composed", [[CTL + 0x619, 1, 1], [CTL + 0x628, 4, 1]])):
        out.append({"name": "pending pose: " + name, "method": "leyte_pending_pose_pre_render", "args": [CTL, 1], "writes": w + extra,
                    "d0": True, "stack_guard": [0x3B800, 0x3F040]})
    # a cached chain: the identity matrix with a translation, as compose_template_chain leaves it
    chain = [[CTL + 0x630 + 2 * k, 2, 0x1000 if k in (0, 4, 8) else 0] for k in range(9)] + [[CTL + 0x642, 4, 0x100], [CTL + 0x646, 4, 0], [CTL + 0x64A, 4, 0x80]]
    out.append({"name": "sink with a cached chain", "method": "leyte_sink_with_cached_chain", "args": [OBJ, 0x100, 0x200, 0],
                "writes": w + chain + [[CTL + 0x61B, 1, 1]], "stack_guard": [0x3B800, 0x3F040]})
    return out


def carrier_cases() -> list:
    """The carrier's collision hook (Leyte only) on a fresh heap: a carrier with a deck face (kind 1), a kind-3 face and a
    plain one (square in x / z), an aircraft whose controller (kind 1) heads along the deck: over the deck with the carrier
    moving (motion recorded, ground and surface byte set, the pair added: the table created), not moving, during a
    replay, the carrier tilted (its +0x20 beyond 0x4ff), off the deck, over the kind-3 face; the deck already found; a full pair table
    (grown); no aircraft controller. The ship's hook: a pending shift applied, nothing pending."""
    ARENA, C, CTL, A, OCTL, DEF, VB, FACES, IDX, R = 0x32000, 0x3C000, 0x3C100, 0x3C800, 0x3C900, 0x3CA00, 0x3CB00, 0x3CC00, 0x3CD00, 0x3CE00
    verts = [(0, 0x40, 0), (0x400, 0x40, 0), (0x400, 0x40, 0x400), (0, 0x40, 0x400), (0, 0x40, 0x800), (0x400, 0x40, 0x800),
             (0x800, 0, 0x800), (0x900, 0, 0x800)]
    w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [0x21058, 2, 0],
         [0x21044, 2, 0], [0x21046, 2, 0], [0x21048, 4, 0],
         [C, 4, CTL], [CTL, 4, C], [C + 0x4A, 4, FACES], [C + 0x3C, 2, 3], [C + 0x46, 4, VB], [C + 0x36, 2, 0], [C + 0x38, 2, len(verts)],
         [CTL + 0x698, 2, 0xFFFF], [CTL + 0x69A, 2, 0xFFFF], [CTL + 0x32, 2, 0x40], [CTL + 0x22, 2, 0x1000], [CTL + 0x28, 2, 0x0F00],
         [CTL + 0x676, 2, 0x300], [CTL + 0xA, 2, 0x1111], [CTL + 0x12, 2, 0x2222],
         [A, 4, OCTL], [A + 4, 4, 0x200], [A + 8, 4, 0x60], [A + 0xC, 4, 0x200], [OCTL + 4, 4, DEF], [DEF, 2, 1],
         [OCTL + 0x20, 2, 0x100], [OCTL + 0x22, 2, 0x1100],
         [R, 4, A], [R + 4, 4, C], [R + 0xC, 2, 0], [R + 0xE, 2, 0]]
    w += [[VB + 12 * k + 4 * m, 4, c & 0xFFFFFFFF] for k, v in enumerate(verts) for m, c in enumerate(v)]
    for k, (kind, q) in enumerate(((1, (0, 1, 2, 3)), (3, (3, 2, 5, 4)), (0, (6, 7, 6, 7)))):
        f = FACES + 0x20 * k
        w += [[f + 0x17, 1, kind], [f + 0x16, 1, 0x30 + k], [f + 0x14, 2, 4], [f + 0x18, 4, IDX + 8 * k]]
        w += [[IDX + 8 * k + 2 * j, 2, v] for j, v in enumerate(q)]
    out = []
    for name, extra in (("over the deck, the carrier moving", []), ("over the deck, the carrier still", [[CTL + 0x32, 2, 0]]),
                        ("over the deck, during a replay", [[0x21058, 2, 1]]), ("the carrier tilted (beyond 0x4ff)", [[CTL + 0x20, 2, 0x900]]),
                        ("off the deck", [[A + 4, 4, 0x2000]]), ("over the kind-3 face", [[A + 0xC, 4, 0x600]]),
                        ("the deck already found", [[CTL + 0x698, 2, 0], [CTL + 0x69A, 2, 1], [CTL + 0x696, 2, 0x40]]),
                        ("a full pair table: grown", [[0x21044, 2, 1], [0x21046, 2, 1], [0x21048, 4, 0x3CF00], [0x3CF00 - 8, 4, 0x2],
                                                      [0x3CF00 - 4, 1, 0xFF], [0x3CF00, 4, 0x5555], [0x3CF04, 4, 0x6666]]),
                        ("a deck at or below the waterline (no pair)", [[VB + 4, 4, 0]]),
                        ("no aircraft controller", [[A, 4, 0]]),
                        ("a record shifted right", [[R + 0xC, 2, 0xFFFF]]),
                        ("a pending pose applied first", [[CTL + 0x619, 1, 1], [CTL + 0x62C, 4, 0x8]])):
        out.append({"name": "carrier deck: " + name, "method": "leyte_carrier_deck_hook", "args": [C, A, R], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    for name, extra in (("a pending shift", [[CTL + 0x619, 1, 1], [CTL + 0x624, 4, 0x10]]), ("nothing pending", []),
                        ("no record", [[CTL + 0x619, 1, 1]])):
        args = [C, A, R if name != "no record" else 0]
        out.append({"name": "ship hook: " + name, "method": "leyte_ship_collision_hook", "args": args, "writes": w + extra})
    return out


def effect_cases() -> list:
    """spawn_effect and spawn_debris_fragment in spawn_mission_pool_object's worlds (cloud_cases): a template that gets a
    slot (an object, its controller filled, the initialiser - stood in - called, three debris fragments) and one that
    retires (0). Live play (logged in the ring), a replay (state -1 continues, another state stops unless kind 1), the
    explosion sound timers near and far in view mode 1, none in view 3."""
    free = next(c for c in cloud_cases() if c["name"] == "spawn pool object: a free slot")["writes"]
    dead = next(c for c in cloud_cases() if c["name"] == "spawn pool object: no vertices: retired")["writes"]
    TMPL = 0x30600
    INIT = STAND_INS["effect initialiser"][0]
    common = [[0x2C81A + 4 * k, 4, TMPL] for k in range(4)] + [[0x205BC + 4 * k, 4, INIT] for k in range(4)] + \
             [[0x2CC10, 4, TMPL], [0x2C698, 2, 0xF], [0x21058, 2, 0], [0x23D88, 2, 1], [0x21020, 4, 0x77], [0x2C5B4, 4, 0x2C5B8],
              [0x23D68, 4, 0], [0x23D64, 4, 0], [0x23D60, 4, 0]]
    out = []
    for name, world, args, extra in (
            ("an object, debris, near", free, [2, 0x1234, 0x5678, 0x9A, 1, 0x100, 0x200, 0x300], []),
            ("retired, far", dead, [2, 1, 2, 3, 0, 0x8000, 0, 0], []),
            ("retired, between", dead, [3, 1, 2, 3, 0, 0x20000, 0, 0], []),
            ("kind 1, no sound", dead, [1, 1, 2, 3, 0, 0x100, 0, 0], []),
            ("view 3, no sound", dead, [2, 1, 2, 3, 0, 0x100, 0, 0], [[0x23D88, 2, 3]]),
            ("replaying, state -1", dead, [2, 1, 2, 3, 0, 0x100, 0, 0], [[0x21058, 2, 0xFFFF]]),
            ("replaying, stopped", dead, [2, 1, 2, 3, 0, 0x100, 0, 0], [[0x21058, 2, 1]]),
            ("replaying, kind 1 continues", dead, [1, 1, 2, 3, 0, 0x100, 0, 0], [[0x21058, 2, 1]])):
        out.append({"name": "spawn effect: " + name, "method": "spawn_effect", "args": [a & 0xFFFFFFFF for a in args],
                    "writes": world + common + extra, "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    for name, world in (("an object", free), ("retired", dead)):
        out.append({"name": "debris fragment: " + name, "method": "spawn_debris_fragment", "args": [0x100, 0x200, 0x300, 0],
                    "writes": world + common, "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    return out


def small_flight_cases() -> list:
    """any_mission_kills_recorded (D0) with no kills, the first and the last of the twelve words set, the word past them;
    build_attitude_matrix_3ang_alt over several angle sets (signs, quadrants, the sine products' word truncation) and
    finalize_attitude_matrix_3ang_alt on sines / cosines written directly."""
    out = []
    for name, extra in (("none", []), ("the first", [[HOST + 0xBE, 2, 1]]), ("the last", [[HOST + 0xD4, 2, 3]]),
                        ("only the word past them", [[HOST + 0xD6, 2, 5]])):
        out.append({"name": "kills recorded: " + name, "method": "any_mission_kills_recorded", "args": [],
                    "writes": [[0x207FC, 4, HOST]] + [[HOST + 0xBE + 2 * k, 2, 0] for k in range(12)] + extra, "d0": True})
    M = 0x3C000
    for a in ((0, 0, 0), (0x400, 0x800, 0xC00), (0x3F00, 0x1234, 0x2F00), (0x2000, 0x1000, 0x3000), (0x1555, 0x2AAA, 0x0123)):
        out.append({"name": "attitude 3ang alt: (0x%x, 0x%x, 0x%x)" % a, "method": "build_attitude_matrix_3ang_alt",
                    "args": list(a) + [M], "writes": []})
    for sc in ((0x1000, 0, 0x1000, 0, 0x1000, 0), (0xB50, 0xB50, 0xF000 & 0xFFFF, 0x800, 0x7FF, 0x8000 & 0xFFFF),
               (0x7FFF, 0x8001 & 0xFFFF, 0x7FFF, 0x7FFF, 0x8000, 0x8000)):
        w = [[0x23D2C - 2 * k, 2, v] for k, v in enumerate(sc)]
        out.append({"name": "finalize 3ang alt: %s" % ",".join("%x" % v for v in sc), "method": "finalize_attitude_matrix_3ang_alt",
                    "args": [M], "writes": w})
    return out


def tracking_camera_cases() -> list:
    """update_tracking_camera (D0, the word result): a live player shot past its start frame; a designated aircraft near
    (a new 20-frame hold; behind the player, or behind the aircraft when it heads with the player and is ahead) and far;
    a hold running on a followed aircraft that is no longer designated; the glide with a follow just ended (re-aimed) and
    plain; replay state 3; the countdown reaching 0x1e (state 3) and 0 (not crashed -1, crashed without a parachute 1,
    host +0x98 == 2, bailed out: resolve_bailout_outcome over an empty near list)."""
    PLAYER, POBJ, TARGET, TOBJ, SHOT, SOBJ = 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C400, 0x3C500
    w = [[0x207FC, 4, HOST], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [TARGET, 4, TOBJ], [SHOT, 4, SOBJ],
         [POBJ + 4, 4, 0x10000], [POBJ + 8, 4, 0x2000], [POBJ + 0xC, 4, 0x30000], [PLAYER + 0x20, 2, 0x100], [PLAYER + 0x22, 2, 0x1000],
         [PLAYER + 0x24, 2, 0], [TOBJ + 4, 4, 0x10800], [TOBJ + 8, 4, 0x2100], [TOBJ + 0xC, 4, 0x30400], [TARGET + 0x22, 2, 0x1100],
         [TARGET + 0x20, 2, 0], [TARGET + 0x24, 2, 0x80], [SOBJ + 4, 4, 0x11000], [SOBJ + 8, 4, 0x2200], [SOBJ + 0xC, 4, 0x31000],
         [SOBJ + 0x42, 4, 5], [0x21020, 4, 10], [0x21068, 4, 0], [0x21070, 4, 0], [0x2B258, 4, 0], [0x2106C, 4, 0],
         [0x21056, 2, 0x50], [0x21058, 2, 1], [0x23D68, 4, 0xF000], [0x23D64, 4, 0x2400], [0x23D60, 4, 0x2F000],
         [0x23D44, 4, 0x10], [0x23D48, 4, 0xFFFFFFF0], [0x23D4C, 4, 0x20], [0x2B25C, 4, 0x2F800], [0x2B260, 4, 0x2500],
         [0x2B264, 4, 0xF400], [HOST + 0x98, 2, 0], [0x21064, 4, 0]]
    out = []
    for name, extra in [("a live player shot", [[0x21068, 4, SHOT]]),
                        ("a shot before its start frame", [[0x21068, 4, SHOT], [SOBJ + 0x42, 4, 0x20]]),
                        ("a designated aircraft near: a new hold", [[0x21070, 4, TARGET]]),
                        ("a designated aircraft turned away", [[0x21070, 4, TARGET], [TARGET + 0x22, 2, 0x3000]]),
                        ("a designated aircraft far", [[0x21070, 4, TARGET], [TOBJ + 4, 4, 0x30000]]),
                        ("a hold on an aircraft no longer designated", [[0x2B258, 4, 5], [0x2106C, 4, TARGET]]),
                        ("the glide, a follow just ended", [[0x2106C, 4, TARGET], [0x2B258, 4, 0], [0x21070, 4, 0]]),
                        ("the glide", []), ("replay state 3", [[0x21058, 2, 3]]),
                        ("the countdown at 0x1f", [[0x21056, 2, 0x1F]]),
                        ("the countdown ends: not crashed", [[0x21056, 2, 1]]),
                        ("the countdown ends: crashed, no parachute", [[0x21056, 2, 1], [PLAYER + 0x698, 1, 4]]),
                        ("the countdown ends: host +0x98 2", [[0x21056, 2, 1], [HOST + 0x98, 2, 2]]),
                        ("the countdown ends: bailed out", [[0x21056, 2, 1], [PLAYER + 0x698, 1, 4], [0x21064, 4, 0x3C600], [0x20B26, 2, 0]]),
                        ("a designated aircraft at negative coordinates, 0x8800 away",
                         [[0x21070, 4, TARGET], [POBJ + 4, 4, 0xFFFF0001], [POBJ + 0xC, 4, 0xFFFE0003], [TOBJ + 4, 4, 0xFFFF8801],
                          [TOBJ + 8, 4, 0x2001], [TOBJ + 0xC, 4, 0xFFFE0001]])] + [
                        ("a designated aircraft ahead (%s)" % label, [[0x21070, 4, TARGET], [TOBJ + 4, 4, (0x10000 + dx) & 0xFFFFFFFF],
                                                                     [TOBJ + 0xC, 4, (0x30000 + dz) & 0xFFFFFFFF], [TARGET + 0x22, 2, 0x1000]])
                        for label, dx, dz in (("+x", 0x6000, 0), ("-x", -0x6000, 0), ("+z", 0, 0x6000), ("-z", 0, -0x6000))]:
        out.append({"name": "tracking camera: " + name, "method": "update_tracking_camera", "args": [], "writes": w + extra,
                    "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    return out


def replay_view_cases() -> list:
    """begin_replay_view on a player controller with a pose ring (75 entries of 0x14 bytes): entry 5 and entry 0 (the
    previous wrapping to 0x4a), the side by replay_request, the goal raised to 0x280 above the player's ground, a
    contact-tracked player object (its records dropped), a live follow target (designated) and a dead one. The clip
    bounds and the sound stop run as ported."""
    PLAYER, POBJ, FOLLOW, FOBJ = 0x3C000, 0x3CA00, 0x3CB00, 0x3CC00
    w = [[0x207FC, 4, HOST], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [PLAYER + 0x2E, 4, 0x100], [0x21054, 2, 0],
         [0x2106C, 4, 0], [0x21070, 4, 0], [0x2B258, 4, 7], [0x23D68, 4, 0], [0x23D64, 4, 0], [0x23D60, 4, 0]]
    w += [[0x215A4 + 4 * k, 4, 0] for k in range(256)]
    for k in range(0x4b):
        e = PLAYER + 0x36 + 0x14 * k
        w += [[e, 4, 0x10000 + 0x100 * k], [e + 4, 4, 0x800 + 0x10 * k], [e + 8, 4, 0x30000 - 0x80 * k], [e + 0xE, 2, (0x200 * k) & 0x3FFF]]
    out = []
    for name, extra in (("entry 5", [[PLAYER + 0x612, 2, 5]]), ("entry 0, the previous wrapping", [[PLAYER + 0x612, 2, 0]]),
                        ("the other side", [[PLAYER + 0x612, 2, 5], [0x21054, 2, 1]]),
                        ("the goal raised above the ground", [[PLAYER + 0x612, 2, 5], [PLAYER + 0x2E, 4, 0x5000]]),
                        ("a contact-tracked player", [[PLAYER + 0x612, 2, 5], [POBJ + 0x14, 4, 0x40]]),
                        ("a live follow target", [[PLAYER + 0x612, 2, 5], [0x2106C, 4, FOLLOW], [FOLLOW, 4, FOBJ]]),
                        ("a dead follow target", [[PLAYER + 0x612, 2, 5], [0x2106C, 4, FOLLOW], [FOLLOW, 4, 0]])):
        out.append({"name": "replay view: " + name, "method": "begin_replay_view", "args": [], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    return out


def facility_cases() -> list:
    """probe_nearest_facility_kind (D0): a near list with a carrier (deck tags unknown, both known, only the deck known:
    skipped), types 0x1c / 0x20 within and beyond 0x671f, types 0x18 / 0x19 within and beyond their range, an object
    without a controller and one of another type; the nearest wins."""
    OBJ = 0x3C000
    base = [[OBJ + 4, 4, 0x10000], [OBJ + 8, 4, 0x1000], [OBJ + 0xC, 4, 0x20000]]

    def facility(k, t, dx, rng=0x4000, deck=(0xFFFF, 0xFFFF), ctl=True):
        o, c, d = 0x3C100 + 0x40 * k, 0x3C800 + 0x40 * k, 0x3CF00 + 4 * k
        w = [[0x20804 + 4 * k, 4, o], [o + 4, 4, (0x10000 + dx) & 0xFFFFFFFF], [o + 8, 4, 0x1000], [o + 0xC, 4, 0x20000], [o + 0x18, 4, rng]]
        if ctl:
            w += [[o, 4, c], [c + 4, 4, d], [d, 2, t], [c + 0x698, 2, deck[0]], [c + 0x69A, 2, deck[1]]]
        else:
            w += [[o, 4, 0]]
        return w

    out = []
    for name, objs in (("nothing", []),
                       ("a carrier near", [(0x1B, 0x3000)]), ("a carrier with both deck tags", [(0x1B, 0x3000, 0x4000, (0, 1))]),
                       ("a carrier with only its deck tag: skipped", [(0x1B, 0x3000, 0x4000, (0, 0xFFFF))]),
                       ("a carrier beyond 0x671f", [(0x1B, 0x7000)]),
                       ("type 0x1c near, type 0x20 nearer", [(0x1C, 0x5000), (0x20, 0x1000)]),
                       ("type 0x18 within its range", [(0x18, 0x1000, 0x4000)]), ("type 0x19 within its range", [(0x19, 0x1000, 0x4000)]),
                       ("type 0x19 beyond its range", [(0x19, 0x5000, 0x4000)]),
                       ("no controller, another type, then a carrier", [(0x1C, 0x100, 0x4000, (0xFFFF, 0xFFFF), False), (0x11, 0x100), (0x1B, 0x2000)]),
                       ("the nearer first", [(0x19, 0x500, 0x4000), (0x1C, 0x2000)])):
        w = base + [[0x20B26, 2, len(objs)]]
        for k, spec in enumerate(objs):
            w += facility(k, *spec)
        out.append({"name": "facility kind: " + name, "method": "probe_nearest_facility_kind", "args": [OBJ], "writes": w, "d0": True})
    return out


def type80_point_cases() -> list:
    """point_near_camera_or_type80_object: the cached table built through the scene (for_each_mission_scene_object with
    Leyte's visitor: a kind-0 leaf's type-0x80 face collected, a kind-1 leaf's skipped, a 0x20 face ignored) on a fresh heap,
    a prebuilt table (no scene walk), near objects with a type-0x80 face (nearer: taken, remembered in 0x20b3c) or hidden,
    a best point beyond 0x4073f (0). leyte_object_type80_centroid on its own: a centroid, a hidden object, the face flagged
    (+0x13 bit 1), a centroid at or below 0 (y), no type-0x80 face."""
    BASE, GROUP, L1, L2, ARENA, POINT, T = 0x30000, 0x30100, 0x30300, 0x30500, 0x32000, 0x3C000, 0x3C100
    OBJ, VB, FACES, IDX = 0x3C400, 0x3C500, 0x3C600, 0x3C700

    def leaf(w, addr, kind, verts, faces):
        w += [[addr, 1, 2], [addr + 1, 1, kind], [addr + 0x18, 2, 0], [addr + 0x1A, 2, len(verts)], [addr + 0x1E, 2, len(faces)]]
        for k, (x, y, z) in enumerate(verts):
            w += [[addr + 0x22 + 12 * k + 4 * m, 4, v & 0xFFFFFFFF] for m, v in enumerate((x, y, z))]
        face = addr + 0x22 + 12 * len(verts) + 12 * len(faces)
        for ftype, idx in faces:
            w += [[face + 8, 1, len(idx)], [face + 9, 1, ftype]] + [[face + 0xE + 2 * i, 2, v] for i, v in enumerate(idx)]
            face += 0xE + 2 * len(idx)

    w = [[a, 4, 0] for a in range(BASE, 0x31000, 4)]
    w += [[0x207FC, 4, HOST], [HOST + 0x8C, 4, BASE], [HOST + 0x90, 2, 1], [BASE + 0x18 + 0x8E, 4, GROUP - BASE],
          [GROUP, 1, 3], [GROUP + 0x18, 2, 2], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0],
          [0x23D68, 4, 0], [0x23D64, 4, 0], [0x23D60, 4, 0], [0x20B38, 4, 0], [0x20B34, 2, 0], [0x20B32, 2, 0], [0x20B24, 4, 0]]
    w += [[GROUP + 0x1A + 0x14, 4, L1 - BASE], [GROUP + 0x1A + 8, 4, 0x1000], [GROUP + 0x1A + 0x10, 4, 0x2000]]
    w += [[GROUP + 0x1A + 0x18 + 0x14, 4, L2 - BASE], [GROUP + 0x1A + 0x18 + 8, 4, 0x300]]
    leaf(w, L1, 0, [(0, 0, 0), (0x40, 0, 0), (0, 0, 0x40)], [(0x80, [0, 1, 2]), (0x20, [0, 1, 2])])
    leaf(w, L2, 1, [(0, 0, 0), (0x40, 0, 0), (0, 0, 0x40)], [(0x80, [0, 1, 2])])
    many = list(w)
    leaf(many, L1, 0, [(0, 0, 0), (0x40, 0, 0), (0, 0, 0x40)], [(0x80, [0, 1, 2])] * 11)
    w += [[POINT + 4 * k, 4, 0x7777] for k in range(3)]
    obj = [[OBJ + 0x46, 4, VB], [OBJ + 0x4A, 4, FACES], [OBJ + 0x3C, 2, 2], [OBJ + 0x36, 2, 0], [OBJ, 4, 0],
           [FACES + 0x16, 1, 0x20], [FACES + 0x20 + 0x16, 1, 0x80], [FACES + 0x20 + 0x14, 2, 3], [FACES + 0x20 + 0x18, 4, IDX]]
    obj += [[IDX + 2 * k, 2, k] for k in range(3)]
    obj += [[VB + 12 * k + 4 * m, 4, c] for k, p in enumerate(((0x100, 0x10, 0x100), (0x140, 0x20, 0x100), (0x100, 0x30, 0x140))) for m, c in enumerate(p)]
    near = [[0x20804, 4, OBJ], [0x20B24, 4, 1]]
    prebuilt = [[0x20B38, 4, T], [0x20B34, 2, 2], [0x20B32, 2, 10], [T, 4, 0x5000], [T + 4, 4, 0x100], [T + 8, 4, 0x5000],
                [T + 12, 4, 0x2000], [T + 16, 4, 0x80], [T + 20, 4, 0x1000]]
    out = []
    for name, extra in (("the table built from the scene", []), ("the table built, a nearer object", obj + near),
                        ("a prebuilt table", prebuilt), ("a prebuilt table, a hidden object", prebuilt + obj + near + [[OBJ + 0x17, 1, 2]]),
                        ("everything beyond 0x4073f", prebuilt + [[0x23D68, 4, 0x200000]])):
        out.append({"name": "type-0x80 point: " + name, "method": "point_near_camera_or_type80_object", "args": [POINT],
                    "writes": w + extra, "d0": True, "stack_guard": [0x3D000, 0x3F040]})
    out.append({"name": "type-0x80 point: eleven faces (the table grown)", "method": "point_near_camera_or_type80_object",
                "args": [POINT], "writes": many + [[POINT + 4 * k, 4, 0x7777] for k in range(3)], "d0": True, "stack_guard": [0x3D000, 0x3F040]})
    for name, extra in (("a centroid", []), ("hidden", [[OBJ + 0x17, 1, 2]]), ("the face flagged", [[FACES + 0x20 + 0x13, 1, 2]]),
                        ("a centroid at or below 0", [[VB + 4 + 12 * k, 4, (-0x40) & 0xFFFFFFFF] for k in range(3)]),
                        ("no type-0x80 face", [[FACES + 0x20 + 0x16, 1, 0x20]]),
                        ("a ship with a pending shift", [[OBJ, 4, 0x3C800], [0x3C800, 4, OBJ], [0x3C804, 4, 0x3C900], [0x3C900, 2, 0x1B],
                                                         [0x3C800 + 0x619, 1, 1], [0x3C800 + 0x624, 4, 0x10], [OBJ + 0x38, 2, 3]]),
                        ("a ship with nothing pending", [[OBJ, 4, 0x3C800], [0x3C804, 4, 0x3C900], [0x3C900, 2, 0x1B]]),
                        ("another controller type", [[OBJ, 4, 0x3C800], [0x3C804, 4, 0x3C900], [0x3C900, 2, 0x23]])):
        out.append({"name": "object type-0x80 centroid: " + name, "method": "leyte_object_type80_centroid", "args": [OBJ, POINT],
                    "writes": w + obj + extra, "d0": True})
    return out


def control_input_cases() -> list:
    """enqueue_control_inputs_avg4: inputs within and beyond each limit (both signs; the aileron's from the definition word
    +0x4c), the ring wrapping from slot 3, negative sums rounded toward zero, the damage bit (nothing)."""
    CTL, DEF = 0x3C000, 0x3C800
    w = [[CTL + 4, 4, DEF], [DEF + 0x4C, 2, 0x180], [CTL + 0x699, 1, 0]]
    w += [[CTL + 0x652 + 2 * k, 2, (0x10 * (k + 1)) & 0xFFFF] for k in range(4)] + [[CTL + 0x65A + 2 * k, 2, (-0x20 * (k + 1)) & 0xFFFF] for k in range(4)]
    w += [[CTL + 0x662 + 2 * k, 2, (0x5 * (k + 1)) & 0xFFFF] for k in range(4)] + [[CTL + 0x66A, 2, 0x100], [CTL + 0x66C, 2, (-0x140) & 0xFFFF],
                                                                                 [CTL + 0x66E, 2, 0x32]]
    out = []
    for name, ring, args in (("within the limits", 0, (0x10, 0x100, 0x100)), ("beyond, positive", 1, (0x80, 0x300, 0x200)),
                             ("beyond, negative", 2, (-0x80, -0x300, -0x200)), ("the ring wrapping", 3, (-0x3, -0x7, -0x101)),
                             ("the limits exactly", 0, (0x40, 0x200, 0x180))):
        out.append({"name": "control inputs: " + name, "method": "enqueue_control_inputs_avg4", "args": [CTL] + [a & 0xFFFFFFFF for a in args],
                    "writes": w + [[CTL + 0x650, 2, ring]]})
    out.append({"name": "control inputs: damaged", "method": "enqueue_control_inputs_avg4", "args": [CTL, 1, 2, 3], "writes": w + [[CTL + 0x699, 1, 1]]})
    return out


def frame_advance_cases() -> list:
    """advance_frame_and_tick_controllers: live play (controllers ticked - stood in - or dropped, the list compacted, ticks
    appending controllers moved down), a replay frame (visibility of pool objects and shapes: born later, dead, alive with
    the hidden flag, born this frame; the logged effects of an empty log), a replay start (state 1)."""
    TICK = STAND_INS["controller tick"][0]
    DEF, NODEF = 0x3C000, 0x3C010
    w = [[DEF + 2, 4, TICK], [NODEF + 2, 4, 0], [0x21020, 4, 0x100], [0x21058, 2, 0], [0x21070, 4, 0x5555],
         [0x2C698, 2, 0], [0x20B26, 2, 0], [0x20F2E, 2, 0]]
    w += [[0x2C69A + 0x18 * k + 0x14, 4, 0x7FFFFFFF] for k in range(16)]      # the effect log: every entry an end mark
    ctls = [(True, DEF), (False, DEF), (True, NODEF), (True, DEF)]
    for k, (live, d) in enumerate(ctls):
        c = 0x3C100 + 0x40 * k
        w += [[0x21BD4 + 4 * k, 4, c], [c, 4, 0x3CF00 if live else 0], [c + 4, 4, d]]
    w += [[0x219B4, 4, 5], [0x219B6, 2, 4], [0x21BD4 + 16, 4, 0x3C300], [0x3C300, 4, 0x3CF00], [0x3C304, 4, DEF]]
    objs = []
    for k, (born, dead, flags, typ) in enumerate(((0x200, 0, 0, 2), (0x50, 0x80, 0, 2), (0x50, 0, 2, 2), (0x101, 0, 0x40, 2),
                                                  (0x50, 0, 0, 1))):
        for base, table in ((0x3C400, 0x20804), (0x3CA00, 0x20B44)):      # the pool's objects, then entity_array_1's own
            o = base + 0x80 * k
            objs += [[o + 0x42, 4, born], [o + 0x3E, 4, dead], [o + 0x14, 4, flags | 0x40], [o + 0x17, 1, flags], [o + 0x28, 1, typ]]
            objs += [[table + 4 * k, 4, o]]
    out = []
    for name, extra in (("live play", []), ("a replay frame", objs + [[0x21058, 2, 2], [0x20B26, 2, 5], [0x20F2E, 2, 5]]),
                        ("a replay start", [[0x21058, 2, 1]])):
        out.append({"name": "frame advance: " + name, "method": "advance_frame_and_tick_controllers", "args": [], "writes": w + extra})
    return out


def sound_cases() -> list:
    """play_game_sound over every kind (once, looped, 100 with and without its record, > 100 with the caller's A1, a lower
    priority refused, a missing record) and update_game_sounds across views (no sound, a stopped view, 8 with explosions and
    sound 14, the follow views with each flag, the engine stalled / fast / idle, engine sound off, a voice on channel 1);
    the host's play / rate / status / stop routines stood in through SERVICES-like stand-ins."""
    PLAY, RATE, STATUS, STOP = SOUND_HOST
    TABLE, RECS, PLAYER = 0x3C000, 0x3C200, 0x3C800
    w = [[0x207FC, 4, HOST], [HOST + 0x296, 4, TABLE], [HOST + 0x2E6, 4, PLAY], [HOST + 0x2EE, 4, RATE], [HOST + 0x2F2, 4, STATUS],
         [HOST + 0x2FA, 4, STOP], [HOST + 0x9A, 2, 1], [HOST + 0x9C, 2, 1], [0x2B24C, 4, 0], [0x2B250, 4, 0xFFFFFFFF],
         [0x20B28, 4, PLAYER], [0x21070, 4, PLAYER]]
    for k in range(16):
        r = RECS + 0x20 * k
        w += [[TABLE + 4 * k, 4, r], [r + 4, 4, 0x200 + k], [r + 0xC, 4, 0x10 * k], [r + 0x10, 4, 0x20 * k]]
    w += [[TABLE + 4 * 13, 4, 0]]
    out = []
    for name, sid, extra in (("once (1)", 1, []), ("looped (5)", 5, []), ("once (14, Leyte)", 14, []), ("looped (15)", 15, []),
                             ("100", 100, []), ("100 without its record", 100, [[TABLE + 0x1C, 4, 0]]),
                             ("a missing record (13)", 13, []), ("a lower priority refused", 6, [[0x2B24C, 4, 3]]),
                             ("above 100", 0x70, []), ("zero", 0, [])):
        out.append({"name": "play sound: " + name, "method": "play_game_sound", "args": [sid], "writes": w + extra})
    flags = [[f, 2, 1] for f in (0x2103C, 0x21040, 0x2103A, 0x2103E, 0x21030, 0x2102E, 0x2102C, 0x2102A, 0x21024, 0x21026)]
    busy = [[next(e[1] for e in CALL_LOGS if e[0] == STATUS) - 4, 4, 1]]      # the status routine says busy
    for name, extra in [("no sound", [[HOST + 0x9A, 2, 0], [0x21032, 2, 4], [0x21034, 2, 2]] + flags), ("view 3: everything stops", [[0x23D88, 2, 3]]),
                        ("view 8: explosions and 14", [[0x23D88, 2, 8], [0x21034, 2, 5], [0x21032, 2, 3], [0x2102E, 2, 1]]),
                        ("view 1: every flag", [[0x23D88, 2, 1], [PLAYER + 0x68A, 1, 0x10], [PLAYER + 0x68C, 1, 0x5], [0x21034, 2, 2]] + flags),
                        ("view 1: 14 already playing", [[0x23D88, 2, 1], [0x2102E, 2, 1], [0x2B24C, 4, 14]]),
                        ("view 1: engine stalled", [[0x23D88, 2, 1], [PLAYER + 0x677, 1, 1], [PLAYER + 0x678, 4, 0x3000]]),
                        ("view 1: engine fast", [[0x23D88, 2, 1], [PLAYER + 0x678, 4, 0x5000], [0x2B250, 4, 10]]),
                        ("view 1: engine idle", [[0x23D88, 2, 1], [PLAYER + 0x678, 4, 0x100]]),
                        ("view 7: engine sound off", [[0x23D88, 2, 7], [HOST + 0x9C, 2, 0]]),
                        ("view 2: a voice on channel 1", [[0x23D88, 2, 2], [0x2B24C, 4, 3]])] + [
                        (name, extra + busy) for name, extra in (
                            ("busy: view 1, 14 already playing", [[0x23D88, 2, 1], [0x2102E, 2, 1], [0x2B24C, 4, 14]]),
                            ("busy: view 1, engine fast, the same choice", [[0x23D88, 2, 1], [PLAYER + 0x678, 4, 0x5000], [0x2B250, 4, 10]]),
                            ("busy: view 1, engine idle", [[0x23D88, 2, 1], [PLAYER + 0x678, 4, 0x100]]),
                            ("busy: view 2, a voice on channel 1", [[0x23D88, 2, 2], [0x2B24C, 4, 3]]),
                            ("busy: view 7, the designated aircraft stalled", [[0x23D88, 2, 7], [PLAYER + 0x677, 1, 1]]),
                            ("busy: view 8, 14 playing", [[0x23D88, 2, 8], [0x2102E, 2, 1], [0x2B24C, 4, 14]]),
                            ("busy: view 1, explosion timers running", [[0x23D88, 2, 1], [0x21032, 2, 7], [0x21034, 2, 0]]),
                            ("busy: view 7, a near explosion", [[0x23D88, 2, 7], [0x21034, 2, 3]]))]:
        out.append({"name": "game sounds: " + name, "method": "update_game_sounds", "args": [], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    return out


def runway_cases() -> list:
    """find_nearest_runway_approach_ends and try_begin_landing_approach (kept by the generated port: its cases check it
    end to end): runway shapes next to a type-0x19 facility (so the facility probe says 0x200) in the pool and in
    entity_array_1, a runway face with its long side first and one turned (corners rotated), a nearer second runway, a
    non-runway face; the aircraft near (an approach starts), too near / far (refused) and with no runway (1)."""
    OWN, NEAR, FAR, PLAYER = 0x3C000, 0x3C010, 0x3C020, 0x3C040
    out = []

    def runway(k, at_x, at_z, turned, typ=2):
        o, faces, verts, idx = 0x3C200 + 0x100 * k, 0x3C800 + 0x80 * k, 0x3CC00 + 0x80 * k, 0x3D000 + 0x10 * k
        quad = [(0, 0, 0), (0x200, 0, 0), (0x200, 0, 0x2000), (0, 0, 0x2000)]
        if turned:
            quad = quad[1:] + quad[:1]
        w = [[o + 0x28, 1, typ], [o + 4, 4, at_x], [o + 8, 4, 0], [o + 0xC, 4, at_z], [o + 0x4A, 4, faces], [o + 0x3C, 2, 2],
             [o + 0x46, 4, verts], [o + 0x36, 2, 0], [o, 4, 0], [o + 0x18, 4, 0x100],
             [faces + 0x16, 1, 0x20], [faces + 0x20 + 0x16, 1, 0x70], [faces + 0x20 + 0x14, 2, 4], [faces + 0x20 + 0x18, 4, idx]]
        w += [[idx + 2 * j, 2, j] for j in range(4)]
        w += [[verts + 12 * j + 4 * m, 4, (c + (at_x if m == 0 else at_z if m == 2 else 0)) & 0xFFFFFFFF] for j, p in enumerate(quad) for m, c in enumerate(p)]
        return o, w

    def facility(k, at_x, at_z):
        o, c, d = 0x3D400 + 0x40 * k, 0x3D600 + 0x40 * k, 0x3D800 + 4 * k
        return o, [[o, 4, c], [c + 4, 4, d], [d, 2, 0x19], [o + 4, 4, at_x], [o + 8, 4, 0], [o + 0xC, 4, at_z], [o + 0x18, 4, 0x8000]]

    def world(runways, own_xz):
        w = [[OWN + 4, 4, own_xz[0] & 0xFFFFFFFF], [OWN + 8, 4, 0x400], [OWN + 0xC, 4, own_xz[1] & 0xFFFFFFFF]]
        pool, ents = [], []
        for k, (at_x, at_z, turned, in_pool) in enumerate(runways):
            o, rw = runway(k, at_x, at_z, turned)
            f, fw = facility(k, at_x + 0x100, at_z)
            w += rw + fw
            (pool if in_pool else ents).append(o)
            pool.append(f)
        w += [[0x20804 + 4 * k, 4, o] for k, o in enumerate(pool)] + [[0x20B24, 4, len(pool)]]
        w += [[0x20B44 + 4 * k, 4, o] for k, o in enumerate(ents)] + [[0x20F2C, 4, len(ents)]]
        return w

    for name, runways, own in (("none", [], (0, 0)), ("one runway in the pool", [(0x10000, 0x10000, False, True)], (0x10100, 0x20000)),
                               ("a turned runway in entity_array_1", [(0x10000, 0x10000, True, False)], (0x10100, 0x5000)),
                               ("two runways, the second nearer", [(0x10000, 0x10000, False, True), (0x30000, 0x10000, False, True)], (0x30100, 0x18000)),
                               ("too far", [(0x10000, 0x10000, False, True)], (0x80000, 0x80000)),
                               ("a runway at negative coordinates", [(-0x10001, -0x10001, False, True)], (-0xFF01, -0x20001))):
        w = world(runways, own)
        out.append({"name": "runway ends: " + name, "method": "find_nearest_runway_approach_ends", "args": [OWN, NEAR, FAR], "writes": w,
                    "d0": True, "stack_guard": [0x3E000, 0x3F040]})
        if runways:
            lw = w + [[PLAYER, 4, OWN], [PLAYER + 0x68C, 1, 5], [PLAYER + 0x698, 2, 0x4000], [PLAYER + 0x32, 4, 0x12345678]]
            out.append({"name": "landing approach: " + name, "method": "try_begin_landing_approach", "args": [PLAYER], "writes": lw,
                        "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    return out


def steer_sample_cases() -> list:
    """build_relative_steer_sample (D0): targets near and far (beyond 0x8000 and 0xffff horizontally, steep and shallow,
    above and below), the aircraft low (raised; at or below 1000 the throttle maximised) and high, attitudes turned."""
    CTL, OBJ, H, VEC, PE, HE = 0x3C000, 0x3C800, 0x3C900, 0x3C910, 0x3C920, 0x3C924
    w = [[CTL, 4, OBJ], [OBJ + 4, 4, 0x10000], [OBJ + 8, 4, 0x3000], [OBJ + 0xC, 4, 0x20000], [CTL + 0xC, 4, 0x10],
         [CTL + 0x2E, 4, 0x100], [CTL + 0x20, 2, 0x100], [CTL + 0x22, 2, 0x1200], [CTL + 0x24, 2, 0x3F00]]
    out = []
    for name, target, extra in (("near, level", (0x10400, 0x3000, 0x20300), []),
                                ("far, steep", (0x1A000, 0x5000, 0x20000), []),
                                ("far, shallow", (0x19000, 0x3400, 0x21000), []),
                                ("very far, below", (0x30000, 0x1000, 0x30000), []),
                                ("far, below, steep", (0x18800, 0x1000, 0x20000), []),
                                ("far, below, shallow", (0x18800, 0x2E00, 0x20000), []),
                                ("far behind, below, shallow", (0x7800, 0x2E00, 0x20000), []),
                                ("low (raised)", (0x10400, 0x1000, 0x20300), [[OBJ + 8, 4, 0x900], [CTL + 0xC, 4, 0]]),
                                ("very low (throttle)", (0x10400, 0x100, 0x20300), [[OBJ + 8, 4, 0x200], [CTL + 0xC, 4, 0]]),
                                ("behind and below, turned", (0xF000, 0x2000, 0x1E000), [[CTL + 0x22, 2, 0x2A00], [CTL + 0x20, 2, 0x3E00]])):
        out.append({"name": "steer sample: " + name, "method": "build_relative_steer_sample",
                    "args": [CTL] + [v & 0xFFFFFFFF for v in target] + [H, VEC, PE, HE], "writes": w + extra, "d0": True,
                    "stack_guard": [0x3E000, 0x3F040]})
    return out


def flight_input_cases() -> list:
    """process_flight_input_and_outcome (hook 14, D0 the result) on a quiet frame (sound off, no controllers, the mouse
    controls off by +0x685 bit 0): the key cases of the table at 0x86cc (views, look offsets, keyboard steering, throttle
    keys and slew, gear / flaps / brakes / 'N' / 'J' / 'M', the target keys), the results 3 / 4 taken before the controller
    tick (the caller's A5 stored into 0x21070), the throttle ramp (clamps, engine off, decay), the gun trigger, the pitch
    mirror, the stopped-aircraft outcomes (status, ditching, touchdown on and off a runway, the deck-distance odds near a
    friendly carrier, the refuel record) and the view cameras 1 / 2 / 5 / 7."""
    PLAYER, POBJ, DEFN, TARGET, TOBJ = 0x3C000, 0x3C700, 0x3C780, 0x3C800, 0x3CF00
    CARRIER, CCTL, CDEF, FACE, LIST, VERTS = 0x3D000, 0x3D100, 0x3D7A0, 0x3D7C0, 0x3D7E0, 0x3D800
    w = [[0x207FC, 4, HOST], [HOST + 0x9A, 2, 0], [HOST + 0x98, 2, 0], [HOST + 0xA0, 2, 0], [HOST + 0xA4, 2, 0],
         [HOST + 0xD8, 2, 0], [HOST + 0xD2, 2, 0], [HOST + 0x37A, 2, 0x100], [HOST + 0x37C, 2, 0xA0], [HOST + 0x37E, 2, 0],
         [0x27052, 2, 0x100], [0x27054, 2, 0xA0], [0x2B26E, 2, 0x100], [0x2B26C, 2, 0xA0],
         [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [PLAYER + 4, 4, DEFN], [DEFN + 0x1C, 2, 100], [DEFN + 0x46, 2, 0x123],
         [PLAYER + 0x20, 2, 0x100], [PLAYER + 0x22, 2, 0x1200], [PLAYER + 0x24, 2, 0x3F00], [PLAYER + 0x2E, 4, 0x100],
         [PLAYER + 0x32, 4, 0xFCF6], [PLAYER + 0x612, 2, 5], [PLAYER + 0x626, 2, 0x20], [PLAYER + 0x685, 1, 1],
         [PLAYER + 0x696, 2, 3], [PLAYER + 0x676, 1, 4], [PLAYER + 0x680, 2, 0x200],
         [POBJ + 4, 4, 0x10000], [POBJ + 8, 4, 0x2000], [POBJ + 0xC, 4, 0x30000],
         [TARGET, 4, TOBJ], [TARGET + 0x612, 2, 2], [TOBJ + 4, 4, 0x11000], [TOBJ + 8, 4, 0x2100], [TOBJ + 0xC, 4, 0x31000],
         [0x23D88, 2, 3], [0x21020, 4, 0x100], [0x219B6, 2, 0], [0x21070, 4, 0], [0x2C5B4, 4, 0x2C5B8]]
    w += [[0x2C5B8 + 4 * k, 4, (0x9E3779B1 * (k + 3)) & 0xFFFFFFFF] for k in range(55)]
    w += [[0x2C698, 2, 0]] + [[0x2C69A + 0x18 * k + 0x14, 4, 0x7FFFFFFF] for k in range(16)]      # the effect log: end marks
    for ctl in (PLAYER, TARGET):
        for k in range(0x4B):
            e = ctl + 0x36 + 0x14 * k
            w += [[e, 4, 0x10000 + 0x100 * k + ctl], [e + 4, 4, 0x800 + 0x10 * k], [e + 8, 4, 0x30000 - 0x80 * k],
                  [e + 0xC, 2, (0x100 * k) & 0x3FFF], [e + 0xE, 2, (0x200 * k) & 0x3FFF], [e + 0x10, 2, (0x300 * k + 1) & 0x3FFF]]
    target = [[0x21070, 4, TARGET]]
    ground = [[PLAYER + 0x64C, 1, 1], [PLAYER + 0x680, 2, 0]]
    runway = [[PLAYER + 0x2C, 1, 0x30]]
    touchdown = [[0x21050, 2, 2]]
    fuel = [[PLAYER + 0x691, 1, 20], [PLAYER + 0x692, 1, 30], [PLAYER + 0x68D, 1, 0xFE]]
    # a friendly carrier (type 0x1b, both deck tags unknown) at the player, its deck face 0x70 over two view vertices
    carrier = [[0x20B26, 2, 1], [0x20804, 4, CARRIER], [CARRIER, 4, CCTL], [CCTL + 4, 4, CDEF], [CDEF, 2, 0x1B],
               [CCTL + 0x698, 2, 0xFFFF], [CCTL + 0x69A, 2, 0xFFFF], [CARRIER + 4, 4, 0x10100], [CARRIER + 8, 4, 0x2000],
               [CARRIER + 0xC, 4, 0x30000], [CARRIER + 0x28, 1, 2], [CARRIER + 0x3C, 2, 2], [CARRIER + 0x4A, 4, FACE],
               [CARRIER + 0x4E, 4, VERTS], [FACE + 0x16, 1, 0x60], [FACE + 0x20 + 0x16, 1, 0x70], [FACE + 0x20 + 0x14, 2, 2],
               [FACE + 0x20 + 0x18, 4, LIST], [LIST, 2, 1], [LIST + 2, 2, 0]]

    # a friendly field (type 0x1c) near the player, after the carrier in the pool when both are there
    fac100 = [[0x20B26, 2, 1], [0x20804, 4, 0x3DA00], [0x3DA00, 4, 0x3DA80], [0x3DA80 + 4, 4, 0x3DB80], [0x3DB80, 2, 0x1C],
              [0x3DA00 + 4, 4, 0x10800], [0x3DA00 + 8, 4, 0x2000], [0x3DA00 + 0xC, 4, 0x30000]]

    def verts(near):
        return [[VERTS, 4, 0x10100 + near], [VERTS + 4, 4, 0x2000], [VERTS + 8, 4, 0x30000],
                [VERTS + 12, 4, 0x10100 + 2 * near], [VERTS + 16, 4, 0x2000], [VERTS + 20, 4, 0x30000 + near]]
    damaged = [[PLAYER + 0x698, 2, 0x2]]
    down, up, none = 3, 4, 0
    out = []
    for name, key, ev, extra, a5 in [
            ("'P' pauses (4), A5 stored", 0x50, down, target, 0xC0000), ("'`' quits (3)", 0x60, down, [], 0x0C1234),
            ("ESC in a replay's view 8 (3)", 0x1B, down, [[0x23D88, 2, 8], [0x21058, 2, 2], [0x21056, 2, 5]], 0),
            ("'V': view 1, the object hidden", 0x56, down, [], 0), ("'C': view 5, the object shown", 0x43, down, [[POBJ + 0x14, 4, 0x41]], 0),
            ("'C' in view 5: back to view 1", 0x43, down, [[0x23D88, 2, 5]], 0),
            ("'E' with a target: view 7 on its ring", 0x45, down, target, 0), ("'E' without a target", 0x45, down, [], 0),
            ("view 7 on the ground: reset", 0, none, target + [[0x23D88, 2, 7], [PLAYER + 0x64C, 1, 1]], 0),
            ("left arrow in view 1, eased", 0x1C, down, [[0x23D88, 2, 1], [HOST + 0xA0, 2, 1], [0x23D30, 4, 0x3FF0]], 0),
            ("right arrow in view 2, snapped back", 0x1D, down, [[0x23D88, 2, 2], [0x2B238, 4, 0x3800], [0x23D30, 4, 0x3FF0]], 0),
            ("up arrow at the 0x3580 limit", 0x1E, down, [[0x23D88, 2, 1], [0x2B234, 4, 0x3580]], 0),
            ("up arrow", 0x1E, down, [[0x23D88, 2, 1], [0x2B234, 4, 0x200]], 0),
            ("down arrow", 0x1F, down, [[0x23D88, 2, 2], [0x2B234, 4, 0x200], [HOST + 0xA0, 2, 1]], 0),
            ("'R' in view 1", 0x52, down, [[0x23D88, 2, 1]], 0), ("'R' in view 4", 0x52, down, [[0x23D88, 2, 4]], 0),
            ("'T' in view 6: reset", 0x54, down, [[0x23D88, 2, 6]], 0),
            ("space on the ground (brakes)", 0x20, down, ground + [[PLAYER + 0x680, 2, 1]], 0),
            ("space released while steering", 0x20, up, [[0x2B270, 2, 1], [0x2B230, 2, 1]], 0),
            ("'G' gear down, no approach host", 0x47, down, [], 0), ("'G' gear up", 0x47, down, [[PLAYER + 0x68A, 1, 1]], 0),
            ("'F' flaps", 0x46, down, [[PLAYER + 0x68C, 1, 2]], 0), ("'F' flaps from 0", 0x46, down, [], 0),
            ("'N' (Leyte)", 0x4E, down, [[PLAYER + 0x682, 2, 0x11]], 0), ("'n' at 3: no change", 0x6E, down, [[PLAYER + 0x682, 2, 0x13]], 0),
            ("'N' bailed out: ignored", 0x4E, down, [[PLAYER + 0x682, 2, 1], [0x2105C, 2, 1]], 0),
            ("'J'", 0x4A, down, [], 0), ("'M' engine off", 0x4D, down, [], 0), ("'B' high enough", 0x42, down, [], 0),
            ("'B' too low", 0x42, down, [[PLAYER + 0x2E, 4, 0x1FD0]], 0),
            ("ctrl-S slew on", 0x13, down, [], 0), ("ctrl-S slew off", 0x13, down, [[PLAYER + 0x32, 4, 0x112A4]], 0),
            ("ctrl-X marks the target", 0x18, down, target, 0), ("tab cycles the view distance", 9, down, [[0x2CC1E, 4, 0xCE40]], 0),
            ("'7' in slew", 0x37, down, [[PLAYER + 0x32, 4, 0x112A4], [PLAYER + 0x698, 2, 0x4002]], 0),
            ("'7' not in slew", 0x37, down, [], 0),
            ("'5' centres the stick", 0x35, down, [[HOST + 0x37A, 2, 0x150], [0x2B26E, 2, 0x150]], 0),
            ("'4' steers left", 0x34, down, [], 0), ("'6' released", 0x36, up, [[0x2B270, 2, 1], [0x2B26A, 2, 1]], 0),
            ("'8' steers up, far off centre", 0x38, down, [[HOST + 0x37C, 2, 0x10], [0x2B26C, 2, 0x10]], 0),
            ("'2' steers down", 0x32, down, [[HOST + 0x37A, 2, 0x1F0], [0x2B26E, 2, 0x1F0]], 0),
            ("steering back to centre", 0, none, [[0x2B270, 2, 1], [HOST + 0x37A, 2, 0x110], [0x2B26E, 2, 0x110],
                                                  [HOST + 0x37C, 2, 0x70], [0x2B26C, 2, 0x70]], 0),
            ("steering ended by the mouse", 0, none, [[0x2B270, 2, 1], [HOST + 0x37A, 2, 0x110], [HOST + 0x37E, 2, 1]], 0),
            ("'A' with a target", 0x61, down, target, 0), ("'z' rudder released", 0x7A, up, [[0x21042, 2, 0x40]], 0),
            ("'Z' rudder", 0x5A, down, [], 0), ("'?' rudder", 0x3F, down, [], 0),
            ("'+' starts the throttle ramp", 0x2B, down, [], 0), ("'-' released", 0x2D, up, [[0x2B232, 2, 0xFFFF]], 0),
            ("'+' at 9: clamped, the ramp ends", 0x3D, down, [[PLAYER + 0x676, 1, 9]], 0),
            ("'-' at 0: clamped, the ramp ends", 0x5F, down, [[PLAYER + 0x676, 1, 0]], 0),
            ("'+' with the engine off", 0x2B, down, [[PLAYER + 0x698, 2, 0x4000]], 0),
            ("the ramp decays", 0, none, [[0x2B232, 2, 4]], 0), ("the ramp decays (negative)", 0, none, [[0x2B232, 2, 0xFFFC]], 0),
            ("'+' in view 6: a zoom step", 0x2B, down, [[0x23D88, 2, 6], [0x27020, 2, 2], [0x27050, 2, 0x40], [0x2704E, 2, 0x30]], 0),
            ("'-' in view 6: a zoom step back", 0x2D, down, [[0x23D88, 2, 6], [0x27020, 2, 2], [0x2703E, 2, 0x20], [0x27026, 2, 0x18]], 0),
            ("the gun trigger", 0, none, [[HOST + 0x37E, 2, 1]], 0),
            ("the gun trigger, no ammo", 0, none, [[HOST + 0x37E, 2, 1], [PLAYER + 0x696, 2, 1]], 0),
            ("the pitch mirrored", 0, none, [[0x23D40, 2, 0x1800], [0x23D3E, 2, 0x3000]], 0),
            ("the pitch mirrored past 0x2000", 0, none, [[0x23D40, 2, 0x2800]], 0),
            ("a key-up filtered out", 0x51, up, [], 0), ("a key beyond the table", 0x80, down, [], 0),
            ("stopped: host status 1", 0, none, ground + [[HOST + 0xD8, 2, 1]], 0),
            ("stopped: rolling (RPM)", 0, none, ground + [[PLAYER + 0x680, 2, 1]], 0),
            ("stopped: +0x62a set, the word +0x30 positive", 0, none, ground + [[PLAYER + 0x62A, 4, 1], [PLAYER + 0x30, 2, 1]], 0),
            ("stopped: +0x62a and +0x62e set", 0, none, ground + [[PLAYER + 0x62A, 4, 1], [PLAYER + 0x62E, 4, 1], [PLAYER + 0x30, 2, 1]], 0),
            ("stopped: +0x62a set, the word +0x30 0", 0, none, ground + [[PLAYER + 0x62A, 4, 1]], 0),
            ("stopped: ditched intact", 0, none, ground + fuel + [[PLAYER + 0x698, 2, 0x1000]], 0),
            ("stopped: ditched damaged", 0, none, ground + [[PLAYER + 0x698, 2, 0x1002]], 0),
            ("stopped: ditched near a carrier", 0, none, ground + carrier + verts(0x40) + [[PLAYER + 0x698, 2, 0x1002]], 0),
            ("touchdown on a runway", 0, none, ground + runway + touchdown, 0),
            ("touchdown on a runway by a friendly carrier, scored", 0, none, ground + runway + touchdown + carrier + verts(0x40) + [[HOST + 0xD2, 2, 1]], 0),
            ("touchdown off the runway, damaged", 0, none, ground + touchdown + damaged + fuel, 0),
            ("touchdown off the runway, empty", 0, none, ground + touchdown, 0),
            ("touchdown off the runway intact, no kills", 0, none, ground + touchdown + fuel, 0),
            ("touchdown by a carrier, damaged, the deck near", 0, none, ground + touchdown + damaged + fuel + carrier + verts(0x10), 0),
            ("touchdown by a carrier, damaged, the deck far", 0, none, ground + touchdown + damaged + fuel + carrier + verts(0x6000), 0),
            ("touchdown by a carrier, damaged, the deck far, other odds", 0, none, ground + touchdown + damaged + fuel + carrier
             + verts(0x6000) + [[0x2C5B4, 4, 0x2C5D8]], 0),
            ("touchdown by a carrier, damaged, the deck far, more odds", 0, none, ground + touchdown + damaged + fuel + carrier
             + verts(0x6000) + [[0x2C5B4, 4, 0x2C620]], 0),
            ("stopped on a runway, engine off: refuel record", 0, none, ground + runway + fuel + [[PLAYER + 0x698, 2, 0x4000]], 0),
            ("stopped on a runway, recorded already", 0, none, ground + runway + fuel + [[PLAYER + 0x698, 2, 0x4000], [0x20B2C, 2, 1]], 0),
            ("stopped on a runway, engine running", 0, none, ground + runway + fuel, 0),
            ("view 5 camera", 0, none, [[0x23D88, 2, 5], [PLAYER + 0x612, 2, 3]], 0),
            ("a replay running: state 1 -> 2", 0, none, [[0x21058, 2, 1], [0x21056, 2, 5]], 0),
            ("a replay ended in view 8: view 1, hidden", 0, none, [[0x21058, 2, 1], [0x23D88, 2, 8]], 0),
            ("a replay ended in view 8, bailed out: view 9", 0, none, [[0x21058, 2, 0], [0x23D88, 2, 8], [0x21064, 4, 0x3D900]], 0),
            ("replay state 3: flagged controllers only", 0, none, [[0x21058, 2, 3], [0x21056, 2, 5]], 0),
            ("view 9 without a parachute: the outcome", 0, none, [[0x21058, 2, 2], [0x21056, 2, 5], [0x23D88, 2, 9]], 0),
            ("instant replay requested in the air", 0, none, [[HOST + 0x98, 2, 1], [0x21054, 2, 0], [0x21056, 2, 0]], 0),
            ("instant replay requested on the ground", 0, none, ground + [[PLAYER + 0x680, 2, 1], [HOST + 0x98, 2, 1]], 0),
            ("'A' released", 0x41, up, target, 0), ("'.' rudder off", 0x2E, down, [[0x21042, 2, 0x40]], 0),
            ("'G' gear down, an approach host", 0x47, down, [[HOST + 0xA4, 2, 1]], 0),
            ("space released on the ground", 0x20, up, ground + [[PLAYER + 0x680, 2, 1], [PLAYER + 0x64F, 1, 1]], 0),
            ("space pressed while steering", 0x20, down, [[0x2B270, 2, 1]], 0),
            ("'6' held: steering right", 0, none, [[0x2B270, 2, 1], [0x2B26A, 2, 1]], 0),
            ("steering far right of centre", 0, none, [[0x2B270, 2, 1], [HOST + 0x37A, 2, 0x10], [0x2B26E, 2, 0x10]], 0),
            ("steering far below centre", 0, none, [[0x2B270, 2, 1], [HOST + 0x37C, 2, 0x1A0], [0x2B26C, 2, 0x1A0]], 0),
            ("'8' released", 0x38, up, [[0x2B268, 2, 0xFFFF]], 0), ("'2' released", 0x32, up, [[0x2B268, 2, 1]], 0),
            ("'4' released", 0x34, up, [[0x2B26A, 2, 0xFFFF]], 0),
            ("view 2 eased back near 0", 0, none, [[0x23D88, 2, 2], [0x23D34, 4, 0x3FF0], [0x23D30, 4, 0x10]], 0),
            ("view 2 eased back, not yet", 0, none, [[0x23D88, 2, 2], [0x23D34, 4, 0x10], [0x23D30, 4, 0x3000]], 0),
            ("view 2 easing, negative steps", 0, none, [[0x23D88, 2, 2], [HOST + 0xA0, 2, 1], [0x2B234, 4, 0x3F00], [0x2B238, 4, 0x3E00]], 0),
            ("view 6 camera, eased", 0, none, [[0x23D88, 2, 6], [POBJ + 0x20, 4, 0x50], [0x2B23C, 4, 0x20], [0x2B240, 4, 0x3F80]], 0),
            ("view 6 camera, snapped", 0, none, [[0x23D88, 2, 6], [POBJ + 0x20, 4, 0x50], [0x2B23C, 4, 0x2000], [0x2B240, 4, 0x1000]], 0),
            ("view 6 camera, a remembered object without its face", 0, none, [[0x23D88, 2, 6], [0x20B3C, 4, TOBJ], [0x2B23C, 4, 0x3000]], 0),
            ("view 6 camera, far (shifted)", 0, none, [[0x23D88, 2, 6], [0x23D68, 4, 0xFFF00000], [0x23D60, 4, 0x200000]], 0),
            ("stopped: ditched by a friendly field", 0, none, ground + fac100 + [[PLAYER + 0x698, 2, 0x1002]], 0),
            ("stopped: ditched, only the second tank", 0, none, ground + [[PLAYER + 0x698, 2, 0x1000], [PLAYER + 0x692, 1, 5]], 0),
            ("touchdown on a field's runway", 0, none, ground + runway + touchdown + fac100, 0),
            ("touchdown off a field's runway, damaged", 0, none, ground + touchdown + damaged + fac100, 0),
            ("touchdown off a field's runway, intact", 0, none, ground + touchdown + fuel + fac100, 0),
            ("touchdown on a deck (0x70) by a carrier, scored", 0, none, ground + touchdown + carrier + verts(0x40) + [[HOST + 0xD2, 2, 1], [PLAYER + 0x2C, 1, 0x70]], 0),
            ("touchdown on a deck (0x60), scored once already", 0, none, ground + touchdown + carrier + verts(0x40) + [[HOST + 0xD2, 2, 1], [PLAYER + 0x2C, 1, 0x60], [0x21052, 2, 1]], 0),
            ("touchdown by a carrier, damaged, on its deck point", 0, none, ground + touchdown + damaged + carrier + verts(0), 0),
            ("touchdown by a carrier, damaged, the deck very far", 0, none, ground + touchdown + damaged + carrier + verts(0x40000), 0),
            ("touchdown by a carrier, damaged, the deck very far, other odds", 0, none, ground + touchdown + damaged + carrier + verts(0x40000) + [[0x2C5B4, 4, 0x2C640]], 0),
            ("view 7 camera, in the air", 0, none, target + [[0x23D88, 2, 7]], 0)]:
        c = {"name": "flight input: " + name, "method": "process_flight_input_and_outcome", "args": [key, ev], "writes": w + extra,
             "d0": True, "stack_guard": [0x3E000, 0x3F040]}
        if a5:
            c["a5_in"] = a5
        out.append(c)
    return out


def bailout_view_cases() -> list:
    """update_bailout_view (D0): no parachute, its object gone (+0x17 bit 1), its landing timer past 0x5a0 (each through
    resolve_bailout_outcome over an empty near list, landed low: status 1), a chute born this frame near the player
    (behind it), one high above its ground but far (its pose ring entry after +0x60e, wrapping), one low and old (the
    ring), no player controller."""
    PLAYER, POBJ, CH, CO = 0x3C000, 0x3C700, 0x3C800, 0x3CF00
    w = [[0x207FC, 4, HOST], [HOST + 0xD8, 2, 0], [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [POBJ + 4, 4, 0x10000],
         [POBJ + 8, 4, 0x2000], [POBJ + 0xC, 4, 0x30000], [0x21064, 4, CH], [CH, 4, CO], [CH + 0x2E, 4, 0x100],
         [CH + 0x60E, 2, 0x10], [CO + 4, 4, 0x10400], [CO + 8, 4, 0x1800], [CO + 0xC, 4, 0x30200], [CO + 0x42, 4, 0x100],
         [0x21020, 4, 0x100], [0x20B26, 2, 0], [0x23D68, 4, 0xF000], [0x23D64, 4, 0x2400], [0x23D60, 4, 0x2F000],
         [0x2C5B4, 4, 0x2C5B8]]
    w += [[0x2C5B8 + 4 * k, 4, (0x9E3779B1 * (k + 5)) & 0xFFFFFFFF] for k in range(55)]
    for k in range(0x4B):
        e = CH + 0x32 + 0x14 * k
        w += [[e, 4, 0x10000 + 0x100 * k], [e + 4, 4, 0x800 + 0x10 * k], [e + 8, 4, 0x30000 - 0x80 * k]]
    out = []
    for name, extra in (("no parachute", [[0x21064, 4, 0]]), ("the chute object gone", [[CO + 0x17, 1, 2]]),
                        ("landed: the timer past 0x5a0", [[CH + 0x61E, 2, 1], [CH + 0x614, 2, 0x5A1], [CO + 8, 4, 0x300]]),
                        ("the timer running, not past", [[CH + 0x61E, 2, 1], [CH + 0x614, 2, 0x5A0]]),
                        ("born this frame, near: behind the chute", []),
                        ("high above its ground, far: the ring", [[CO + 0x42, 4, 0], [CO + 4, 4, 0x30000]]),
                        ("low and old: the ring, wrapping", [[CO + 0x42, 4, 0], [CO + 8, 4, 0x400], [CH + 0x60E, 2, 0x4A]]),
                        ("no player controller", [[0x20B28, 4, 0]])):
        out.append({"name": "bailout view: " + name, "method": "update_bailout_view", "args": [], "writes": w + extra,
                    "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    return out


def small_tick_cases() -> list:
    """tick_shadow following a live owner and retiring with a retired owner whose controller's +0 is clear (an owner
    retired but still controlled follows on); tick_view_attached_object at two camera poses."""
    SH, SOBJ, OWNER, OCTL = 0x3C000, 0x3C100, 0x3C200, 0x3C300
    w = [[SH, 4, SOBJ], [SH + 0x32, 4, OWNER], [OWNER, 4, OCTL], [OWNER + 4, 4, 0x12345], [OWNER + 0xC, 4, 0xFFFE0000],
         [OWNER + 0x29, 1, 3], [OCTL, 4, OWNER], [OCTL + 8, 4, 0x40], [OCTL + 0x10, 4, 0xFFFFFF00], [OCTL + 0x2E, 4, 0x777],
         [OCTL + 0x22, 2, 0x1234], [SOBJ + 0x10, 2, 5], [SOBJ + 0x12, 2, 5]]
    out = []
    for name, extra in (("following", []), ("owner retired, still controlled", [[OWNER + 0x17, 1, 2]]),
                        ("owner retired: the shadow retires", [[OWNER + 0x17, 1, 2], [OCTL, 4, 0]])):
        out.append({"name": "tick shadow: " + name, "method": "tick_shadow", "args": [SH], "writes": w + extra})
    VA, VOBJ = 0x3C400, 0x3C500
    for name, cam in (("camera ahead", (0x20000, 0x3000, 0x10000, 0x1234, 0x0567)), ("camera behind, angles 0", (0, 0, 0, 0, 0))):
        out.append({"name": "tick view attached: " + name, "method": "tick_view_attached_object", "args": [VA],
                    "writes": [[VA, 4, VOBJ], [VOBJ + 4, 4, 0x11000], [VOBJ + 8, 4, 0x2000], [VOBJ + 0xC, 4, 0xFFF00000],
                               [0x23D68, 4, cam[0]], [0x23D64, 4, cam[1]], [0x23D60, 4, cam[2]], [0x23D3E, 2, cam[3]],
                               [0x23D40, 2, cam[4]]]})
    return out


def _ordnance_pool_world(free: bool) -> list:
    """The mission pool cloud_cases builds, with 0x2cbfe's bullet template: a free slot whose object gets vertices
    (free) or one whose object gets none and retires (spawn_mission_pool_object returns 0)."""
    ROOT, LIST, SLOTS, BASE, ARENA, TMPL = 0x30000, 0x30100, 0x30200, 0x31000, 0x32000, 0x30600
    REC_OFF, TYPE = 0x40, 1
    REC = BASE + REC_OFF
    count, used, nverts = (3, (0, 1), 3) if free else (3, (), 0)
    w = [[0x207FC, 4, HOST], [HOST + 0x384, 4, ARENA], [0x27188, 4, 0], [0x27198, 4, 0], [HOST + 0x8C, 4, BASE],
         [HOST + 0xA8, 2, 0], [HOST + 0xE, 2, 8], [HOST + 0xAA, 2, 0], [0x2100E, 2, 8], [0x23FAC, 4, 0x30800],
         [0x20F2C, 4, 0], [0x20B24, 4, 0], [0x219C4, 4, 0], [0x219B4, 4, 0], [0x2B22C, 4, 0], [0x21068, 4, 0],
         [0x2107A, 2, 0], [0x21078, 2, 9], [0x23D68, 4, 0x900000], [0x23D64, 4, 0], [0x23D60, 4, 0], [0x23C2C, 4, ROOT],
         [ROOT + 0x24, 4, 0], [ROOT + 0x2A, 4, LIST], [ROOT + 0x32, 4, SLOTS + 8], [ROOT + 0x30, 2, count], [ROOT + 0x2E, 2, 3],
         [SLOTS, 4, 0x10], [SLOTS + 4, 1, 0xFF], [LIST + 0x1A + 0x14 + 0x18 * TYPE, 4, REC_OFF], [TMPL + 0x10, 2, TYPE],
         [0x2CBFE, 4, TMPL]]
    for k in range(count):
        w += [[SLOTS + 8 + 0x18 * k + m, 1, 0] for m in range(0x18)]
        if k in used:
            w.append([SLOTS + 8 + 0x18 * k + 0x14, 4, 0x3FF00])
    w += [[REC, 1, 2], [REC + 1, 1, 0], [REC + 0x16, 2, 0], [REC + 0x18, 2, 0], [REC + 0x1A, 2, nverts], [REC + 0x1C, 2, 0],
          [REC + 0x1E, 2, 0], [REC + 0x20, 2, 0]]
    return w


def turret_cases() -> list:
    """spawn_ordnance_scattered (D0) into an empty and a free pool; tick_turret: the facility probe once, a carrier
    followed / released (retired, without vertices, burning), a target released (retired, without vertices),
    word +0x46 holding fire, out of range, aiming in a replay, bursts (none started, one started, counting down,
    firing into the pool with scatter of either sign), a distance needing several shift steps."""
    MATRIX = 0x3C800
    rng = [[0x2C5B4, 4, 0x2C5B8]] + [[0x2C5B8 + 4 * k, 4, (0x9E3779B1 * (k + 7)) & 0xFFFFFFFF] for k in range(55)]
    mat = [[MATRIX + 2 * k, 2, v & 0xFFFF] for k, v in enumerate((0x4000, 0, 0, 0, 0x3000, -0x2000, 0, 0x2000, 0x3000))]
    out = []
    for name, free in (("into a pool without room", False), ("into a free slot", True)):
        out.append({"name": "scattered ordnance: " + name, "method": "spawn_ordnance_scattered",
                    "args": [0x1234, 1 if free else 0, 0x10000, 0x2000, 0x30000, MATRIX], "writes": _ordnance_pool_world(free) + rng + mat,
                    "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    T, TOBJ, CAR, CCTL, TGT = 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3CA00
    w = [[T, 4, TOBJ], [T + 0x40, 2, 1], [T + 0x32, 4, CAR], [T + 0x36, 4, TGT], [T + 0x42, 4, 0x8000], [CAR, 4, CCTL],
         [CAR + 4, 4, 0x10000], [CAR + 8, 4, 0x2000], [CAR + 0xC, 4, 0x30000], [CAR + 0x46, 4, 0x3D000], [CAR + 0x29, 1, 2],
         [TGT + 4, 4, 0x13000], [TGT + 8, 4, 0x2800], [TGT + 0xC, 4, 0x31000], [TGT + 0x46, 4, 0x3D000], [TGT + 0x29, 1, 2],
         [0x21058, 2, 0], [0x20B26, 2, 0], [TOBJ + 0x10, 2, 5], [TOBJ + 0x12, 2, 5]]
    w += rng
    for name, extra in (("the facility probe, once", [[T + 0x40, 2, 0], [0x21058, 2, 1]]),
                        ("following its carrier, aiming in a replay", [[0x21058, 2, 1]]),
                        ("the carrier retired", [[CAR + 0x17, 1, 2]]), ("the carrier without vertices", [[CAR + 0x46, 4, 0]]),
                        ("the carrier burning", [[CCTL + 0x699, 1, 0x10]]),
                        ("the carrier without a controller", [[CAR, 4, 0], [0x21058, 2, 1]]),
                        ("no carrier, the turret retired", [[T + 0x32, 4, 0], [TOBJ + 0x17, 1, 2]]),
                        ("no target", [[T + 0x36, 4, 0]]), ("the target retired", [[TGT + 0x17, 1, 2]]),
                        ("the target without vertices", [[TGT + 0x46, 4, 0]]), ("holding fire", [[T + 0x46, 2, 1]]),
                        ("out of range", [[T + 0x42, 4, 0x100]]), ("far, several shifts", [[T + 0x42, 4, 0x7FFFFFFF], [TGT + 4, 4, 0x200000]]),
                        ("no burst: the draw", []), ("no burst: other draws", [[0x2C5B4, 4, 0x2C5D0]]),
                        ("no burst: more draws", [[0x2C5B4, 4, 0x2C600]]), ("no burst: a start", [[0x2C5B4, 4, 0x2C5E4]]), ("no burst: another start", [[0x2C5B4, 4, 0x2C620]]),
                        ("a burst counting down", [[T + 0x4A, 2, 5], [T + 0x48, 2, 3]]),
                        ("a burst firing, positive scatter", _ordnance_pool_world(False) + [[T + 0x4A, 2, 5], [T + 0x48, 2, 1],
                                                                                           [T + 0x50, 2, 0x45], [T + 0x52, 2, 0x13]]),
                        ("a burst firing, negative scatter", _ordnance_pool_world(False) + [[T + 0x4A, 2, 1], [T + 0x48, 2, 0],
                                                                                           [T + 0x50, 2, 0xFFBB], [T + 0x52, 2, 0xFFF1]])):
        out.append({"name": "tick turret: " + name, "method": "tick_turret", "args": [T], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    return out


def spawner_cases() -> list:
    """spawn_enemy_fighter (D0) into the pool without room; tick_aircraft_spawner: none left, a replay, slots released
    (retired, controller cleared), an undamaged aircraft holding launches, the 1-in-512 draw failing, and launches
    of one, two and three (the bearing from either side, the pool taking none) with a damaged aircraft in a slot."""
    rng = [[0x2C5B4, 4, 0x2C5B4]] + [[0x2C5B8 + 4 * k, 4, (0x9E3779B1 * (k + 7)) & 0xFFFFFFFF] for k in range(55)]
    launch = [[0x2C614, 4, 0x12000000], [0x2C690, 4, 0]]          # the first draw: & 0x1ff == 0
    pool = _ordnance_pool_world(False) + [[0x2C5AE, 4, 0x30600]]
    out = [{"name": "enemy fighter: into a pool without room", "method": "spawn_enemy_fighter",
            "args": [0x10000, 0x8000, 0x30000, 0x10, 0x2000, 0x30, 1], "writes": pool, "d0": True, "stack_guard": [0x3E000, 0x3F040]}]
    SP, SPOBJ, PLAYER, POBJ = 0x3C000, 0x3C100, 0x3C200, 0x3C300
    A, AC, B, BC = 0x3C400, 0x3C500, 0x3C600, 0x3C700
    w = pool + rng + [[SP, 4, SPOBJ], [SP + 0x32, 2, 5], [SPOBJ + 4, 4, 0x50000], [SPOBJ + 0xC, 4, 0x60000], [SPOBJ + 0x18, 4, 0x30000],
                      [0x20B28, 4, PLAYER], [PLAYER, 4, POBJ], [POBJ + 4, 4, 0x10000], [POBJ + 0xC, 4, 0x20000], [0x21058, 2, 0],
                      [A, 4, AC], [AC, 4, A], [A + 0x29, 1, 2], [B, 4, BC], [BC, 4, B], [B + 0x29, 1, 2]]
    for name, extra in (("none left", [[SP + 0x32, 2, 0]]), ("a replay", [[0x21058, 2, 2]]),
                        ("an undamaged aircraft holds", [[SP + 0x34, 4, A]]),
                        ("slots released: retired, controller cleared", [[SP + 0x34, 4, A], [A + 0x17, 1, 2], [SP + 0x40, 4, B], [BC, 4, 0]]),
                        ("the draw fails", [[SP + 0x34, 4, A], [AC + 0x698, 2, 4]]),
                        ("a launch, a damaged aircraft in slot 0", launch + [[SP + 0x34, 4, A], [AC + 0x698, 2, 4]]),
                        ("a launch with two left", launch + [[SP + 0x32, 2, 2]]),
                        ("a launch with one left, the spawner west", launch + [[SP + 0x32, 2, 1], [SPOBJ + 4, 4, 0xFFFF0000]]),
                        ("a launch, the spawner south, far", launch + [[SPOBJ + 0xC, 4, 0xFF000000], [SPOBJ + 0x18, 4, 0x7FFFF]]),
                        ("a launch, other draws", launch + [[0x2C5C0, 4, 0x55555555], [0x2C61C, 4, 0x12345678]])):
        out.append({"name": "aircraft spawner: " + name, "method": "tick_aircraft_spawner", "args": [SP], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    return out


def shadow_render_cases() -> list:
    """shadow_pre_render (D0): a retired owner (4), a ship owner (type 8: untouched), an aircraft owner banked by
    0, 0x800 and 0xc00 (template x beyond and within the half size, either sign, a stretched x clamped to it), a
    larger scale word +0x36, an empty face."""
    SH, SOBJ, OWNER, OCTL, ODEF = 0x3C000, 0x3C100, 0x3C200, 0x3C300, 0x3C400
    ROOT, LIST, BASE, FACE, IDX, VERTS = 0x3C500, 0x3C600, 0x3C800, 0x3CC00, 0x3CC40, 0x3CD00
    TYPE, OFF = 2, 0x100
    xs = (0x400, -0x500, 0x20, -0x10, 0x3C, -0x33, 0x7FFF, 0x40)
    w = [[SH, 4, SOBJ], [SH + 0x32, 4, OWNER], [OWNER, 4, OCTL], [OCTL + 4, 4, ODEF], [ODEF, 2, 3], [OCTL + 0x24, 2, 0],
         [SOBJ + 0x4A, 4, FACE], [SOBJ + 0x24, 4, ROOT], [ROOT + 0x2A, 4, LIST], [LIST + 0x2E + 0x18 * TYPE, 4, OFF],
         [SOBJ + 0x10, 2, TYPE], [0x207FC, 4, HOST], [HOST + 0x8C, 4, BASE], [SOBJ + 0x46, 4, VERTS], [SOBJ + 0x36, 2, 0],
         [FACE + 0x18, 4, IDX], [FACE + 0x14, 2, len(xs)]]
    for k, x in enumerate(xs):
        w += [[BASE + OFF + 0x22 + 12 * k, 4, x & 0xFFFFFFFF], [IDX + 2 * k, 2, k], [VERTS + 12 * k, 4, 0x11111111]]
    out = []
    for name, extra in (("a retired owner", [[OWNER + 0x17, 1, 2]]), ("a ship owner", [[ODEF, 2, 8]]),
                        ("an aircraft level", []), ("an aircraft banked 0x800", [[OCTL + 0x24, 2, 0x800]]),
                        ("an aircraft banked 0xc00", [[OCTL + 0x24, 2, 0xC00]]), ("an aircraft banked 0xfc0", [[OCTL + 0x24, 2, 0xFC0]]),
                        ("type 7, scale 2", [[ODEF, 2, 7], [SOBJ + 0x36, 2, 2], [OCTL + 0x24, 2, 0x900]]),
                        ("an empty face", [[FACE + 0x14, 2, 0]])):
        out.append({"name": "shadow render: " + name, "method": "shadow_pre_render", "args": [SH], "writes": w + extra, "d0": True})
    return out


def damage_small_cases() -> list:
    """tally_destroyed_entity (D0): word +0x52 negative, type 0 and 3 away from bases, near a friendly base (a carrier
    facility) with no controller, with a counted type and with each spared type (0x10 / 0x11 / 0x12 / 0x15), near an
    enemy field (0x100); apply_random_aircraft_damage (D0) over several draws: spared by the definition's +0x50, each
    roll band, the high band with bits clear / set / bit 1, by the player."""
    E, ECTL, EDEF = 0x3C000, 0x3C100, 0x3C200
    FO, FCTL, FDEF = 0x3C300, 0x3C400, 0x3C500
    base = [[0x207FC, 4, HOST], [HOST + 0xD6, 2, 7], [HOST + 0xBC + 6, 2, 2], [E + 4, 4, 0x10000], [E + 8, 4, 0x1000],
            [E + 0xC, 4, 0x20000], [E, 4, 0], [0x20B26, 2, 0]]

    def near(t):
        return [[0x20B26, 2, 1], [0x20804, 4, FO], [FO, 4, FCTL], [FCTL + 4, 4, FDEF], [FDEF, 2, t], [FCTL + 0x698, 2, 0xFFFF],
                [FCTL + 0x69A, 2, 0xFFFF], [FO + 4, 4, 0x10100], [FO + 8, 4, 0x1000], [FO + 0xC, 4, 0x20000]]
    out = []
    for name, extra in (("type negative", [[E + 0x52, 2, 0xFFFF]]), ("type 0", [[E + 0x52, 2, 0]]), ("type 3", [[E + 0x52, 2, 3]]),
                        ("near a friendly base, no controller", near(0x1B) + [[E + 0x52, 2, 3]]),
                        ("near a friendly base, type 4", near(0x1B) + [[E + 0x52, 2, 3], [E, 4, ECTL], [ECTL + 4, 4, EDEF], [EDEF, 2, 4]]),
                        ("near an enemy field, type 3", near(0x1C) + [[E + 0x52, 2, 3]])) + tuple(
                        ("near a friendly base, spared type 0x%x" % t, near(0x1B) + [[E + 0x52, 2, 1], [E, 4, ECTL], [ECTL + 4, 4, EDEF], [EDEF, 2, t]])
                        for t in (0x10, 0x11, 0x12, 0x15)):
        out.append({"name": "tally: " + name, "method": "tally_destroyed_entity", "args": [E], "writes": base + extra, "d0": True})
    C, CDEF = 0x3C600, 0x3C700
    w = [[C + 4, 4, CDEF], [CDEF + 0x50, 2, 0xFFFF], [C + 0x698, 2, 0]]
    for k in range(12):
        cur = 0x2C5B4 + 8 * k
        for name, extra, by in (("draw %d" % k, [], 0), ("draw %d, bits 6.. set, bit 1, by the player" % k, [[C + 0x698, 2, 0x202]], 1)):
            out.append({"name": "random damage: " + name, "method": "apply_random_aircraft_damage", "args": [C, by],
                        "writes": w + extra + [[0x2C5B4, 4, cur]] + [[0x2C5B8 + 4 * j, 4, (0x9E3779B1 * (j + 3)) & 0xFFFFFFFF] for j in range(55)],
                        "d0": True})
    out.append({"name": "random damage: spared", "method": "apply_random_aircraft_damage", "args": [C, 1],
                "writes": w + [[CDEF + 0x50, 2, 0], [0x2C5B4, 4, 0x2C5B4]] + [[0x2C5B8 + 4 * j, 4, (0x9E3779B1 * (j + 3)) & 0xFFFFFFFF] for j in range(55)],
                "d0": True})
    return out


def destruction_effect_cases() -> list:
    """spawn_destruction_effect (D0) through spawn_effect's logged ring (the template retires): airborne, near on a
    runway / deck surface (0x30 / 0x60 / 0x70: the ground height), near on water 0x10, near elsewhere, the flagged
    type 0x23 / 0x24 face, size rounding of a negative size."""
    dead = next(c for c in cloud_cases() if c["name"] == "spawn pool object: no vertices: retired")["writes"]
    TMPL = 0x30600
    common = [[0x2C81A + 4 * k, 4, TMPL] for k in range(4)] + [[0x2CC10, 4, TMPL], [0x2C698, 2, 3], [0x21058, 2, 0],
              [0x23D88, 2, 3], [0x21020, 4, 0x77], [0x2C5B4, 4, 0x2C5B8]]
    O, C, D, F = 0x3C000, 0x3C100, 0x3C200, 0x3C300
    w = dead + common + [[O, 4, C], [O + 4, 4, 0x10000], [O + 8, 4, 0x500], [O + 0xC, 4, 0x20000], [C + 8, 4, 0x10],
                         [C + 0xC, 4, 0x20], [C + 0x10, 4, 0xFFFFFFF0], [C + 0x2E, 4, 0x4C0], [C + 4, 4, D], [D, 2, 3],
                         [O + 0x4A, 4, F], [F + 0x13, 1, 2]]
    out = []
    for name, size, extra in (("airborne", 0x40, [[C + 0x2E, 4, 0x100]]), ("near, on a runway (0x30)", 0x40, [[C + 0x2C, 1, 0x30]]),
                              ("near, on a deck (0x60)", 0x41, [[C + 0x2C, 1, 0x60]]), ("near, on a deck (0x70)", 0x42, [[C + 0x2C, 1, 0x70]]),
                              ("near, on water (0x10)", 0x40, [[C + 0x2C, 1, 0x10]]), ("near, elsewhere (0x20)", 0x40, [[C + 0x2C, 1, 0x20]]),
                              ("near, type 0x24 flagged", 0x40, [[C + 0x2C, 1, 0x40], [D, 2, 0x24]]),
                              ("near, type 0x23 unflagged", 0x40, [[C + 0x2C, 1, 0x40], [D, 2, 0x23], [F + 0x13, 1, 0]]),
                              ("near, a negative size", -5, [[C + 0x2C, 1, 0x50]])):
        out.append({"name": "destruction effect: " + name, "method": "spawn_destruction_effect", "args": [O, size & 0xFFFFFFFF],
                    "writes": w + extra, "d0": True, "stack_guard": [0x3E000, 0x3F040]})
    return out


def sinkable_hit_cases() -> list:
    """apply_hit_to_sinkable_object: the anchor cleared (this controller, word 2) or kept, credit, no hits left, the last
    hit (folded angle either sign, sinking started or not), one and two left, many left (the drop register 0), a
    carrier with an unknown deck tag (a quarter of a, either sign), over several draws."""
    C, D = 0x3C000, 0x3C100
    rng = [[0x2C5B8 + 4 * j, 4, (0x9E3779B1 * (j + 11)) & 0xFFFFFFFF] for j in range(55)]
    w = rng + [[C + 4, 4, D], [D, 2, 0x20], [C + 0x656, 2, 1], [C + 0x658, 2, 0x100], [0x21020, 4, 0x500], [0x2104C, 4, 0],
               [0x23D84, 2, 2], [C + 0x69A, 2, 0xFFFF]]
    out = []
    for name, credit, extra in (("the anchor cleared, credit, the last hit", 1, [[0x2104C, 4, C]]),
                                ("the anchor kept (word 1)", 0, [[0x2104C, 4, C], [0x23D84, 2, 1]]),
                                ("no hits left", 1, [[C + 0x656, 2, 0]]),
                                ("the last hit, angle negative", 0, [[C + 0x658, 2, 0x3000]]),
                                ("the last hit, sinking started", 0, [[C + 0x670, 2, 1]]),
                                ("one left", 0, [[C + 0x656, 2, 2]]), ("one left, sinking started", 0, [[C + 0x656, 2, 2], [C + 0x670, 2, 1]]),
                                ("two left", 0, [[C + 0x656, 2, 3]]), ("many left", 0, [[C + 0x656, 2, 9]]),
                                ("a carrier, deck tag unknown, the last hit", 0, [[D, 2, 0x1B]]),
                                ("a carrier, deck tag unknown, angle negative", 0, [[D, 2, 0x1B], [C + 0x658, 2, 0x3000]]),
                                ("a carrier, deck tag known", 0, [[D, 2, 0x1B], [C + 0x69A, 2, 0]])):
        for k, cur in enumerate((0x2C5B4, 0x2C5D4, 0x2C618)):
            out.append({"name": "sinkable hit: %s (draws %d)" % (name, k), "method": "apply_hit_to_sinkable_object",
                        "args": [C, credit], "writes": w + extra + [[0x2C5B4, 4, cur]]})
    return out


def blast_cases() -> list:
    """apply_blast_damage over object_pool objects (one to four per case, controllers past the high call logs) around a
    blast at (0x10000, 0x1000, 0x20000): aircraft inside / just outside (the draw, sure_hit, credit), a ship inside (its
    hit with the approx_dist3_xyz D1), a large-range ship with hit_ships (modes 1 / 2, the carrier tag, outside 1.5
    ranges), a controller-less wreck after a ship (the stale type), type 0x12, types 0x27 / 0x28, a flagged object that
    is the player's / a flagged 0x24 wreck / another flagged one, an object beyond range + 0x7cf; the destruction path
    through spawn_destruction_effect (pool retiring) and leyte_destroy_entity's tally and missions 2 / 3 / 6."""
    dead = next(c for c in cloud_cases() if c["name"] == "spawn pool object: no vertices: retired")["writes"]
    TMPL = 0x30600
    common = dead + [[0x2C81A + 4 * k, 4, TMPL] for k in range(4)] + [[0x2CC10, 4, TMPL], [0x2C698, 2, 3], [0x21058, 2, 0],
                     [0x23D88, 2, 3], [0x21020, 4, 0x77], [0x2C5B4, 4, 0x2C5B8], [0x20B28, 4, 0], [HOST + 0x90, 2, 0],
                     [HOST + 0xD2, 2, 0], [0x27062, 2, 0], [0x27058, 2, 0], [0x2705A, 2, 0]]
    common += [[0x2C5B8 + 4 * j, 4, (0x9E3779B1 * (j + 5)) & 0xFFFFFFFF] for j in range(55)]
    BX, BY, BZ = 0x10000, 0x1000, 0x20000

    def objs(specs):
        w = [[0x20B26, 2, len(specs)]]
        for k, sp in enumerate(specs):
            o, c, d = 0x3C000 + 0x100 * k, 0x41000 + 0x700 * k, 0x3C900 + 0x10 * k
            dx, rng, typ = sp["dx"], sp.get("range", 0x400), sp.get("type")
            w += [[0x20804 + 4 * k, 4, o], [o + 4, 4, (BX + dx) & 0xFFFFFFFF], [o + 8, 4, sp.get("y", BY)], [o + 0xC, 4, BZ],
                  [o + 0x20, 4, rng & 0xFFFFFFFF], [o + 0x14, 4, sp.get("flags", 0)], [o + 0x10, 2, 1], [o + 0x12, 2, 1],
                  [o + 0x52, 2, sp.get("tally", 0xFFFF)]]
            if typ is None:
                w += [[o, 4, 0]]
            else:
                w += [[o, 4, c], [c + 4, 4, d], [d, 2, typ], [c + 0x656, 2, sp.get("hits", 3)], [c + 0x69A, 2, sp.get("deck", 0)],
                      [c + 0x658, 2, 0x100]]
            if sp.get("player"):
                w += [[0x20B28, 4, c]]
        return w
    out = []
    for name, specs, args, extra in (
            ("an aircraft inside, credited", [{"dx": 0x100, "type": 3}], (0x800, 1, 0, 0), []),
            ("an aircraft just outside, the draw", [{"dx": 0x500, "type": 3}], (0x800, 0, 0, 0), []),
            ("an aircraft outside, other draws", [{"dx": 0x600, "type": 7}], (0x800, 0, 0, 0), [[0x2C5B4, 4, 0x2C600]]),
            ("an aircraft outside, sure_hit: random damage", [{"dx": 0x500, "type": 3}], (0x800, 1, 1, 0), []),
            ("a ship inside: its hit (many hits left)", [{"dx": 0x100, "type": 0x1C, "hits": 9}], (0x800, 1, 0, 0), []),
            ("a ship inside, then a wreck without a controller", [{"dx": 0x100, "type": 0x1C}, {"dx": 0x80}], (0x800, 0, 0, 0), []),
            ("an aircraft, then a wreck: the stale type", [{"dx": 0x100, "type": 3}, {"dx": 0x80}], (0x800, 0, 0, 0), []),
            ("type 0x12", [{"dx": 0x500, "type": 0x12}], (0x800, 0, 0, 0), []),
            ("type 0x27 within 0x5db", [{"dx": 0x800, "type": 0x27}], (0x800, 0, 0, 0), []),
            ("type 0x28, sure_hit", [{"dx": 0x800, "type": 0x28}], (0x800, 0, 1, 0), []),
            ("an object within 0x257 past its range, another past it", [{"dx": 0x600, "type": 0x30}, {"dx": 0x700, "type": 0x30}], (0x800, 0, 0, 0), []),
            ("beyond range + 0x7cf", [{"dx": 0x2000, "type": 3}], (0x800, 0, 0, 0), []),
            ("flagged: the player's, a 0x24 wreck, another", [{"dx": 0x100, "type": 3, "flags": 2, "player": True},
                                                             {"dx": 0x100, "type": 0x24, "flags": 0x10002},
                                                             {"dx": 0x100, "type": 0x30, "flags": 0x200}], (0x800, 0, 0, 0), []),
            ("a large ship, hit_ships 1, within 1.5 ranges", [{"dx": 0x1000, "type": 0x1E, "range": 0xC00}], (0x200, 0, 0, 1), []),
            ("a large ship, hit_ships 1, beyond", [{"dx": 0x1300, "type": 0x1E, "range": 0xC00}], (0x200, 0, 0, 1), []),
            ("a large carrier, hit_ships 2, its deck unknown", [{"dx": 0x400, "type": 0x1B, "range": 0xC00, "deck": 0xFFFF}], (0x200, 0, 0, 2), []),
            ("a large carrier, hit_ships 2, its deck known", [{"dx": 0x400, "type": 0x1B, "range": 0xC00}], (0x200, 0, 0, 2), []),
            ("a large 0x20, hit_ships 2", [{"dx": 0x400, "type": 0x20, "range": 0xC00}], (0x200, 0, 0, 2), []),
            ("a large ship, hit_ships 0x10000", [{"dx": 0x400, "type": 0x1E, "range": 0xC00}], (0x200, 0, 0, 0x10000), []),
            ("a large aircraft, hit_ships 1", [{"dx": 0x400, "type": 3, "range": 0xC00}], (0x200, 0, 0, 1), []),
            ("a wreck on the ground, credited (tally)", [{"dx": 0x100, "type": 0x30, "y": 0, "tally": 2, "range": 0x1800}], (0x2000, 1, 0, 0), []),
            ("a flagged 0x23 wreck", [{"dx": 0x100, "type": 0x23, "flags": 0x10002, "range": -0x40}], (0x800, 0, 0, 0), []),
            ("mission 2: a type-10 target", [{"dx": 0x100, "type": 0x30, "tally": 10}], (0x800, 1, 0, 0),
             [[HOST + 0x90, 2, 2], [0x27062, 2, 2]]),
            ("mission 3: a type-10 target", [{"dx": 0x100, "type": 0x30, "tally": 10}], (0x800, 1, 0, 0), [[HOST + 0x90, 2, 3]]),
            ("mission 6: type 0x28, the fifth", [{"dx": 0x100, "type": 0x28}], (0x800, 1, 0, 0), [[HOST + 0x90, 2, 6], [0x27058, 2, 4]]),
            ("mission 6: type 0x27, the fourth", [{"dx": 0x100, "type": 0x27}], (0x800, 1, 0, 0), [[HOST + 0x90, 2, 6], [0x2705A, 2, 3]])):
        radius, credit, sure, ships = args
        out.append({"name": "blast: " + name, "method": "apply_blast_damage", "args": [BX, BY, BZ, radius, credit, sure, ships],
                    "writes": common + objs(specs) + extra, "stack_guard": [0x3E000, 0x3F040]})
    # leyte_destroy_entity's mission points, reached directly: an entity on each point of mission 2 and on mission 3's
    for name, x, z, mission in (("mission 2, the first point", 0x498EB4, -0x1D34A4, 2), ("mission 2, the third point", 0x4995EE, -0x1D33DC, 2),
                                ("mission 3, its point", 0x5CF903, -0x3E0E, 3), ("mission 3, away", 0x5CF903, 0, 3)):
        E = 0x3C000
        w = common + [[E, 4, 0], [E + 4, 4, x & 0xFFFFFFFF], [E + 0xC, 4, z & 0xFFFFFFFF], [E + 0x52, 2, 10], [E + 0x10, 2, 1], [E + 0x12, 2, 1],
                      [HOST + 0x90, 2, mission], [0x27062, 2, 2], [0x20B26, 2, 0]]
        out.append({"name": "destroy entity: " + name, "method": "leyte_destroy_entity", "args": [0, E], "writes": w,
                    "stack_guard": [0x3E000, 0x3F040]})
    return out


def flak_cases() -> list:
    """tick_flak_gun: a retired gun (target released / none), the one-time aim (a gun object with bit 20: its heading; a
    barrel face found after another, no barrel face), a replay, due bursts (spawn_effect into a retiring pool, a blast
    over an empty pool) and one not yet due, a target released (no shells, retired, vertex-less), the 1-in-64 shot
    draw failing and passing (out of range, pitch too low, heading off the barrel, all slots busy, fired: the burst
    point with its ground floor)."""
    dead = next(c for c in cloud_cases() if c["name"] == "spawn pool object: no vertices: retired")["writes"]
    TMPL = 0x30600
    common = dead + [[0x2C81A + 4 * k, 4, TMPL] for k in range(4)] + [[0x2CC10, 4, TMPL], [0x2C698, 2, 3], [0x21058, 2, 0],
                     [0x23D88, 2, 3], [0x21020, 4, 0x200], [0x2C5B4, 4, 0x2C5B4], [0x20B28, 4, 0]]
    common += [[0x2C5B8 + 4 * j, 4, (0x9E3779B1 * (j + 5)) & 0xFFFFFFFF] for j in range(55)]
    shot = [[0x2C614, 4, 0x00150000], [0x2C690, 4, 0]]                  # the first draw: & 0x3f == 0x15
    G, GO, T, TC, FACES, IDX, VERTS = 0x3C000, 0x3C700, 0x3C800, 0x41000, 0x3C900, 0x3CA00, 0x3CB00
    w = common + [[G, 4, GO], [G + 0x40, 2, 1], [G + 0x36, 4, T], [G + 0x46, 2, 5], [G + 0x42, 4, 0x8000], [G + 0x56, 2, 0],
                  [G + 0x22, 2, 0x1234], [GO + 4, 4, 0x10000], [GO + 8, 4, 0x100], [GO + 0xC, 4, 0x20000], [GO + 0x46, 4, VERTS],
                  [GO + 0x4A, 4, FACES], [GO + 0x3C, 2, 2], [GO + 0x38, 2, 3], [GO + 0x36, 2, 1],
                  [T, 4, TC], [T + 4, 4, 0x11000], [T + 8, 4, 0x3000], [T + 0xC, 4, 0x21000], [T + 0x46, 4, 0x3D000], [T + 0x29, 1, 2],
                  [TC + 8, 4, 0x40], [TC + 0xC, 4, 0xFFFFFFF0], [TC + 0x10, 4, 0x20], [TC + 0x2E, 4, 0x2000],
                  [FACES + 0x17, 1, 0], [FACES + 0x20 + 0x17, 1, 1], [FACES + 0x20 + 0x14, 2, 3], [FACES + 0x20 + 0x18, 4, IDX]]
    for k, v in enumerate(((0x100, 0x40, 0x200), (0x180, 0x40, 0x220), (0x140, 0x60, 0x1E0), (0, 0, 0), (0x10, 0x300, 0x20), (0x20, 0x320, 0))):
        w += [[VERTS + 12 * k + 4 * m, 4, v[m] & 0xFFFFFFFF] for m in range(3)]
    w += [[IDX + 2 * k, 2, k] for k in range(3)]
    out = []
    for name, extra in (("retired, its target released", [[GO + 0x17, 1, 2]]), ("retired, no target", [[GO + 0x17, 1, 2], [G + 0x36, 4, 0]]),
                        ("the aim from its heading (bit 20), a replay", [[G + 0x40, 2, 0], [GO + 0x15, 1, 0x10], [0x21058, 2, 1]]),
                        ("the aim from its barrel face, a replay", [[G + 0x40, 2, 0], [0x21058, 2, 1]]),
                        ("no barrel face, no target", [[G + 0x40, 2, 0], [FACES + 0x20 + 0x17, 1, 0], [G + 0x36, 4, 0]]),
                        ("bursts due and not yet", [[G + 0xD2, 4, 0x1F0], [G + 0x5A, 4, 0x11000], [G + 0x5E, 4, 0x3000], [G + 0x62, 4, 0x21000],
                                                    [G + 0xD2 + 12, 4, 0x300], [G + 0xD2 + 36, 4, 0x200], [G + 0x5A + 108, 4, 0x12000]]),
                        ("out of shells", [[G + 0x46, 2, 0]]), ("the target retired", [[T + 0x17, 1, 2]]),
                        ("the target without vertices", [[T + 0x46, 4, 0]]), ("the draw fails", []),
                        ("the shot: out of range", shot + [[G + 0x42, 4, 0x100]]),
                        ("the shot: pitch too low", shot + [[T + 8, 4, 0x100]]),
                        ("the shot: heading 0", shot), ("the shot: heading 0x1800", shot + [[G + 0x56, 2, 0x1800]]),
                        ("the shot: heading 0x3000", shot + [[G + 0x56, 2, 0x3000]]),
                        ("the shot: all slots busy", shot + [[G + 0x56, 2, 0x3000]] + [[G + 0xD2 + 4 * k, 4, 0x900] for k in range(10)]),
                        ("the shot: the burst floored", shot + [[G + 0x56, 2, 0x3000], [TC + 0x2E, 4, 0x8000]]),
                        ("the shot: far, below", shot + [[G + 0x56, 2, 0x3000], [G + 0x42, 4, 0x7FFFFFFF], [T + 4, 4, 0xFFF00000], [T + 8, 4, 0x80000]])):
        out.append({"name": "flak gun: " + name, "method": "tick_flak_gun", "args": [G], "writes": w + extra, "stack_guard": [0x3E000, 0x3F040]})
    return out


def ship_tick_cases() -> list:
    """leyte_ship_tick: live play (the mover stood in, the wake kept beside the ship or spawned into a pool without
    room, no wake for a ship at rest, the ring cursor wrapping) and replay frames (sinking: nothing; the drift with the
    heading unchanged; a heading change reshaping the ship, also with its buffers regrown on a fresh heap and its face
    lists refilled; the ring's first record against the last)."""
    dead = next(c for c in cloud_cases() if c["name"] == "spawn pool object: no vertices: retired")["writes"]
    MOVER = STAND_INS["controller tick"][0]
    S, O, D, W, WC = 0x41000, 0x3C000, 0x3C100, 0x3C200, 0x3C300
    TM, VB, FACES = 0x3D000, 0x3D400, 0x3D600
    w = dead + [[0x2CDD4 + 4, 4, 0x30600], [0x21058, 2, 0], [HOST + 0xB2, 2, 0], [S, 4, O], [S + 4, 4, D], [D, 2, 0x1C],
                [S + 0x38, 4, MOVER], [S + 0x32, 2, 0x63], [S + 0x20, 2, 0x10], [S + 0x22, 2, 0x1234], [S + 0x24, 2, 0x20],
                [S + 0x654, 2, 0x0200], [S + 0x692, 2, 7], [S + 0x618, 1, 5], [S + 0x68E, 4, W], [W, 4, WC],
                [O + 4, 4, 0x10000], [O + 8, 4, 0x100], [O + 0xC, 4, 0x20000], [W + 4, 4, 0x10100], [W + 0xC, 4, 0x1FF00],
                [S + 0x620, 4, TM], [TM + 0x1A, 2, 3], [TM + 0x1E, 2, 1], [O + 0x46, 4, VB], [O + 0x36, 2, 1], [O + 0x4A, 4, FACES],
                [S + 0x14, 4, 0x100], [S + 0x18, 4, 0x80], [S + 0x1C, 4, 0x40], [S + 0x642, 4, 8], [S + 0x646, 4, 4], [S + 0x64A, 4, 2]]
    w += [[S + 0x630 + 2 * k, 2, v] for k, v in enumerate((0x4000, 0, 0, 0, 0x4000, 0, 0, 0, 0x4000))]
    for k, v in enumerate(((0x100, 0x20, 0x300), (-0x200, 0x40, 0x100), (0x50, -0x60, 0x70), (0x10, 0x20, 0x30))):
        w += [[TM + 0x22 + 12 * k + 4 * m, 4, v[m] & 0xFFFFFFFF] for m in range(3)]
    rec = TM + 0x22 + 0x30
    w += [[rec + 8, 1, 2], [rec + 0xE, 2, 0], [rec + 0x10, 2, 1], [rec + 0x12, 2, 2], [rec + 0x14, 2, 3]]
    for k in range(0x4B):
        e = S + 0x3C + 0x14 * k
        w += [[e, 4, 0x10000 + 0x40 * k], [e + 4, 4, 0x100], [e + 8, 4, 0x20000 - 0x20 * k], [e + 0xE, 2, 0x0200], [e + 0x12, 2, k]]
    out = []
    for name, extra in (("live: mover, wake kept", []), ("live: the cursor wraps", [[S + 0x618, 1, 0x4A]]),
                        ("live: a wake without a controller", [[W, 4, 0]]),
                        ("live: at rest, no wake", [[S + 0x32, 2, 0]]),
                        ("replay: sinking", [[0x21058, 2, 2], [S + 0x670, 2, 1]]),
                        ("replay: the drift, heading kept", [[0x21058, 2, 2]]),
                        ("replay: the first record against the last", [[0x21058, 2, 2], [S + 0x618, 1, 0]]),
                        ("replay: a heading change reshapes", [[0x21058, 2, 2], [S + 0x3C + 0x14 * 5 + 0xE, 2, 0x0A00]]),
                        ("replay: reshaped, buffers kept (64-byte blocks), lists refilled", [[0x21058, 2, 2], [S + 0x3C + 0x14 * 5 + 0xE, 2, 0x3000],
                                                                     [S + 0x61A, 1, 1], [VB - 4, 1, 6], [O + 0x4E, 4, 0x3D500], [0x3D500 - 4, 1, 6],
                                                                     [FACES + 0x18, 4, 0x3D700], [0x3D700 - 4, 1, 6]]),
                        ("replay: reshaped and shaded", [[0x21058, 2, 2], [S + 0x3C + 0x14 * 5 + 0xE, 2, 0x1000], [HOST + 0xB2, 2, 1]])):
        out.append({"name": "ship tick: " + name, "method": "leyte_ship_tick", "args": [S], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    return out


def sinking_step_cases() -> list:
    """build_attitude_matrix (Leyte's closed-form finalize) over angle sets; step_sinking_object on pending_pose_cases'
    cached-chain world: not due, a retired object, a step afloat (the carry turning a pool object of type 0xa and a
    controller-list object), the last step, a deck ship (0x1b) lowering its deck, sinking far enough to go under (the
    carry removing, the credited tally, the mission outcomes 2 / 4 / 9 and type 0x20's count), no hits left."""
    out = []
    M = 0x3C000
    for name, angles in (("zero", (0, 0, 0, 0)), ("each", (0x400, 0x800, 0xC00, 0x1000)), ("wide", (0x3F00, 0x2100, 0x1800, 0x3000)),
                         ("halves", (0x2000, 0x2000, 0x2000, 0x2000)), ("mixed", (0x123, 0x3456, 0x2A00, 0x0FF0))):
        out.append({"name": "attitude matrix 4: " + name, "method": "build_attitude_matrix", "args": list(angles) + [M],
                    "writes": [[M + 2 * k, 2, 0x5555] for k in range(9)]})
    base = next(c for c in pending_pose_cases() if c["name"] == "sink with a cached chain")["writes"]
    OBJ, CTL, DEF = 0x30000, 0x3C000, 0x3C800
    P, PC, PD, L = 0x41000, 0x41100, 0x41200, 0x41300
    w = base + [[0x207FC, 4, HOST], [HOST + 0x90, 2, 0], [HOST + 0xD2, 2, 0], [0x21020, 4, 0x500], [CTL + 4, 4, DEF], [DEF, 2, 0x1D],
                [CTL + 0x66C, 4, 0x4F0], [CTL + 0x658, 2, 0x10], [CTL + 0x65A, 2, 0x3FF0], [CTL + 0x65C, 2, 0x20], [CTL + 0x65E, 2, 0x8],
                [CTL + 0x660, 2, 0xFFF8], [CTL + 0x662, 2, 0x4], [CTL + 0x666, 2, 0x10], [CTL + 0x66A, 2, 3], [CTL + 0x668, 2, 0x1E],
                [CTL + 0x656, 2, 1], [CTL + 0x22, 2, 0x0800], [CTL + 0x18, 4, 0x40], [CTL + 0x14, 4, 0x20], [CTL + 0x1C, 4, 0x30],
                [OBJ + 0x20, 4, 0x4000], [0x20B26, 2, 1], [0x20804, 4, P], [P, 4, PC], [PC + 4, 4, PD], [PD, 2, 0xA],
                [P + 4, 4, 0x80], [P + 8, 4, 0x40], [P + 0xC, 4, 0x40], [P + 0x10, 2, 1], [P + 0x12, 2, 1],
                [CTL + 0x678, 2, 1], [CTL + 0x67A, 4, L], [L + 4, 4, 0x60], [L + 8, 4, 0x50], [L + 0xC, 4, 0x20], [L + 0x10, 2, 1], [L + 0x12, 2, 1],
                [0x27060, 2, 2], [0x27056, 2, 8],
                [0x23C2C, 4, 0x30100], [0x2107A, 2, 3], [0x21078, 2, 9], [L + 0x24, 4, 0x30100]]   # retire_entity on the sink world's root
    for name, extra in (("not due", [[CTL + 0x66C, 4, 0x600]]), ("a retired object", [[OBJ + 0x17, 1, 2]]),
                        ("a step afloat", []), ("the last step", [[CTL + 0x66A, 2, 1]]),
                        ("a carrier lowering its deck", [[DEF, 2, 0x1B], [CTL + 0x696, 2, 0x40]]),
                        ("no hits left", [[CTL + 0x656, 2, 0]]),
                        ("gone under, credited, the pool empty", [[CTL + 0x666, 2, 0x4000], [CTL + 0x672, 2, 1], [0x20B26, 2, 0]])) + tuple(
                        ("gone under (drop 0x%x), %s" % (drop, label), extra2 + [[CTL + 0x666, 2, drop], [0x20B26, 2, 0]])
                        for drop in (0x400, 0x4000)
                        for label, extra2 in (("mission 2 carrier", [[DEF, 2, 0x1B], [HOST + 0x90, 2, 2]]),
                                              ("mission 4", [[HOST + 0x90, 2, 4]]),
                                              ("type 0x20", [[DEF, 2, 0x20], [CTL + 0x696, 2, 4]]),
                                              ("mission 9 carrier", [[DEF, 2, 0x1B], [HOST + 0x90, 2, 9], [CTL + 0x69A, 2, 0xFFFF]]))):
        out.append({"name": "sinking step: " + name, "method": "step_sinking_object", "args": [CTL], "writes": w + extra,
                    "stack_guard": [0x3B800, 0x3F040]})
    for name, args, extra in (("turned", [CTL, 0x10, 0x3FF0, 0x20, 0], []), ("level", [CTL, 0, 0, 0, 0], []),
                              ("a type 0xb, a list object below the sea", [CTL, 0x100, 0x200, 0x300, 0], [[PD, 2, 0xB], [L + 8, 4, 0xFFFFF000]]),
                              ("another type: skipped, the list object far", [CTL, 0, 0, 0, 0], [[PD, 2, 0xC], [L + 4, 4, 0x900000]]),
                              ("removing", [CTL, 0, 0, 0, 1], [[0x20B26, 2, 0]]),
                              ("flagged objects skipped", [CTL, 0, 0, 0, 0], [[P + 0x14, 4, 2], [L + 0x14, 4, 0x200]])):
        out.append({"name": "carry attached: " + name, "method": "leyte_carry_attached_objects", "args": args, "writes": w + extra,
                    "stack_guard": [0x3B800, 0x3F040]})
    return out


def ship_move_cases() -> list:
    """The ship movers on ship_tick_cases' world (controller 0x41000, a template and cached chain; the ship at y 0 so a
    turn reshapes it; a route block at 0x41800 already made): overlay_printf through the host printf stand-in (a plain
    and a widened format); attaching to a ship's list (room, full: the message); the first move's links (a type-3
    group with an AA gun of type 0x24 and another part); leyte_ship_mover (sinking not due, kind 0, kind 0x63 with a
    route, kind 0x63 without one in a scene that has none, kind 1 through 0x206ac, a kind without a handler); the steering
    states 0 (type 0x21 and other), 2 (point far / near, the index wrapping), 3 (a small and a clamped turn, an aircraft
    on deck turned with the ship), a countdown state; the next point at both reach radii; the carried objects moving
    (one retired and released); the landing ship (afloat: counting down, probing water / land ahead and at the bow,
    beaching; beached: troops not due, landed on dry ground, none found on water, sinking)."""
    base = next(c for c in ship_tick_cases() if c["name"] == "ship tick: live: mover, wake kept")["writes"]
    S, O, D = 0x41000, 0x3C000, 0x3C100
    R = 0x41800
    G, GT, P3, P3T, GUN, GUNC, GUND, OTHER = 0x41900, 0x41940, 0x41A00, 0x41A40, 0x41B00, 0x41B80, 0x41BC0, 0x41C00
    DFACE = 0x3E800
    w = base + [[S + 0x61B, 1, 1], [S + 0x32, 2, 0x63], [S + 0x34, 4, R], [S + 0x674, 4, 0x980], [S + 0x68E, 4, 0], [S + 0x670, 2, 0],
                [S + 0x66C, 4, 0x7FFFFFFF], [S + 0x678, 2, 0], [R, 2, 2], [R + 4, 2, 0], [R + 6, 2, 3], [0x21590, 4, DFACE],
                [DFACE + 0x16, 1, 0x10], [0x20F2E, 2, 0], [0x20B26, 2, 0], [0x20674, 4, 0x41E00], [0x20678, 4, 0x41E04],
                [0x41E00, 4, 0], [0x41E04, 4, 0], [0x21020, 4, 0x300], [S + 0x8, 4, 0x40], [S + 0x10, 4, 0xFFFFFF80], [O + 8, 4, 0]]
    for k, (x, z) in enumerate(((0x12000, 0x20000), (0x10000, 0x24000), (0x10100, 0x20100))):
        w += [[R + 8 + 12 * k, 4, x], [R + 0xC + 12 * k, 4, 0], [R + 0x10 + 12 * k, 4, z]]
    w += [[0x207FC, 4, HOST], [HOST + 0x29A, 4, SERVICES[0x29A][0]]]
    out = []
    FMT = 0x41D00
    fmt = b"a%db%xc%sd\0"
    pw = [[FMT + k, 1, b] for k, b in enumerate(fmt)]
    out.append({"name": "printf: widened", "method": "overlay_printf", "args": [FMT, 1, 2, 3, 4, 5], "writes": w + pw, "d0": True})
    out.append({"name": "printf: the AA message", "method": "overlay_printf", "args": [0x17CD2, 0, 0, 0, 0, 0], "writes": w, "d0": True})
    for name, n in (("room", 2), ("full", 4)):
        out.append({"name": "attach to ship: " + name, "method": "leyte_attach_to_ship", "args": [S, GUN],
                    "writes": w + [[S + 0x678, 2, n], [GUN + 0x29, 1, 1]]})
    # the first move: the ship's group G holds a type-3 part P3 whose table lists the gun (type 0x24) and another part
    fw = w + [[O + 0x24, 4, G], [G + 0x30, 2, 2], [G + 0x32, 4, GT], [GT + 0x14, 4, OTHER], [GT + 0x18 + 0x14, 4, P3],
              [OTHER + 0x28, 1, 2], [P3 + 0x28, 1, 3], [P3 + 0x30, 2, 2], [P3 + 0x32, 4, P3T], [P3T + 0x14, 4, GUN],
              [P3T + 0x18 + 0x14, 4, OTHER], [GUN, 4, GUNC], [GUNC + 4, 4, GUND], [GUND, 2, 0x24], [OTHER, 4, 0],
              [G + 0x2A, 4, 0x41C80], [0x41C80 + 0x1A + 0x14 + 0x18, 4, 0x200], [O + 0x10, 2, 1], [S + 0x61B, 1, 0]]
    out.append({"name": "ship first move", "method": "leyte_ship_first_move", "args": [S], "writes": fw, "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "ship first move: a group without a type-3 part", "method": "leyte_ship_first_move", "args": [S],
                "writes": fw + [[P3 + 0x28, 1, 1]], "stack_guard": [0x3E000, 0x3F040]})
    for name, extra in (("sinking, not due", [[S + 0x670, 2, 1], [S + 0x68E, 4, 0]]), ("kind 0", [[S + 0x32, 2, 0]]),
                        ("kind 0x63 with its route", []),
                        ("kind 0x63, no route in the scene", [[S + 0x34, 4, 0], [O + 0x24, 4, G], [G + 0x30, 2, 0], [G + 0x24, 4, 0]]),
                        ("kind 1 (0x206ac)", [[S + 0x32, 2, 1]]), ("kind 2 (no handler)", [[S + 0x32, 2, 2]]),
                        ("the first call links the ship", fw[len(w):] + [[S + 0x32, 2, 0]]),
                        ("kind 1 without a route: one made from 0x2067c", [[S + 0x32, 2, 1], [S + 0x34, 4, 0], [O + 0x24, 4, G],
                                                                           [O + 0x12, 2, 1], [G + 0x32, 4, GT], [G + 0x24, 4, 0],
                                                                           [GT + 0x18 + 2, 2, 0x400], [GT + 0x18 + 4, 2, 0x100],
                                                                           [GT + 0x18 + 8, 4, 0x100], [GT + 0x18 + 0x10, 4, 0x200]]),
                        ("kind 0x63, a route in the scene", [[S + 0x34, 4, 0], [O + 0x24, 4, G], [G + 0x30, 2, 2], [G + 0x32, 4, GT],
                                                             [G + 0x24, 4, 0], [GT + 0x14, 4, OTHER], [GT + 0x18 + 0x14, 4, P3],
                                                             [OTHER + 0x28, 1, 2], [OTHER + 0x3C, 2, 2], [OTHER, 4, 0],
                                                             [P3 + 0x28, 1, 2], [P3 + 0x3C, 2, 1], [P3, 4, 0], [P3 + 0x4A, 4, 0x41D40],
                                                             [0x41D40 + 0x13, 1, 2], [0x41D40 + 0x14, 2, 3], [0x41D40 + 0x18, 4, 0x41D80],
                                                             [0x41D80, 2, 2], [0x41D82, 2, 0], [0x41D84, 2, 1], [P3 + 0x46, 4, 0x41DA0],
                                                             [P3 + 0x36, 2, 1]] + [[0x41DA0 + 12 * k + 4 * m, 4, v & 0xFFFFFFFF]
                                                                                    for k, pt in enumerate(((0x20000, 0, 0x40000), (0x24000, 0, 0x40100), (0x30000, 0, 0x30000)))
                                                                                    for m, v in enumerate(pt)])):
        out.append({"name": "ship mover: " + name, "method": "leyte_ship_mover", "args": [S], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    for name, extra in (("state 0", [[R, 2, 0]]), ("state 0, type 0x21", [[R, 2, 0], [D, 2, 0x21]]),
                        ("state 2, far", []), ("state 2, at the point", [[O + 4, 4, 0x11F00], [O + 0xC, 4, 0x20000]]),
                        ("state 2, at the last point: the index wraps", [[R + 4, 2, 2], [O + 4, 4, 0x10100], [O + 0xC, 4, 0x20100]]),
                        ("state 3, a small turn", [[R, 2, 3], [S + 0x22, 2, 0x3C00]]),
                        ("state 3, a clamped turn", [[R, 2, 3], [S + 0x22, 2, 0x2000]]),
                        ("state 3, the other way", [[R, 2, 3], [S + 0x22, 2, 0x0800]]),
                        ("state 3, turning its carried guns", [[R, 2, 3], [S + 0x22, 2, 0x2000], [S + 0x678, 2, 2], [S + 0x67A, 4, GUN],
                                                              [S + 0x67E, 4, 0], [GUN + 4, 4, 0x10400], [GUN + 8, 4, 0x80], [GUN + 0xC, 4, 0x1FE00]]),
                        ("state 3, an aircraft on deck turns too", [[R, 2, 3], [S + 0x22, 2, 0x2000], [0x41E00, 4, 0x41F00],
                                                                      [0x41F00, 4, 0x3C800], [0x41F00 + 0x64C, 1, 1], [0x3C800 + 4, 4, 0x10200],
                                                                      [0x3C800 + 0xC, 4, 0x20100], [0x41F00 + 0x22, 2, 0x100], [O + 0x20, 4, 0x4000]]),
                        ("state 8 counting down", [[R, 2, 8], [S + 0x678, 2, 2], [S + 0x67A, 4, GUN], [S + 0x67E, 4, OTHER],
                                                   [GUN, 4, GUNC], [GUNC, 4, GUN], [OTHER + 0x17, 1, 2], [OTHER, 4, 0x41C40], [0x41C40, 4, 0],
                                                   [OTHER + 0x29, 1, 3]])):
        out.append({"name": "steer ship: " + name, "method": "leyte_steer_ship", "args": [S], "writes": w + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
    out.append({"name": "chain xform always: the position", "method": "apply_template_chain_xform_always", "args": [O, 0, 0, 0],
                "writes": w + [[O + 0x24, 4, G], [O + 0x12, 2, 1], [G + 0x32, 4, GT], [G + 0x24, 4, 0], [GT + 0x18 + 2, 2, 0x400],
                               [GT + 0x18 + 8, 4, 0x100], [GT + 0x18 + 0x10, 4, 0x200]], "stack_guard": [0x3E000, 0x3F040]})
    for name, speed in (("slow", 0x800), ("fast", 0x980)):
        out.append({"name": "next route point: " + name, "method": "leyte_next_route_point", "args": [O, R + 4, 0x41D80, speed],
                    "writes": w + [[O + 4, 4, 0x12000 + 0x1500], [O + 0xC, 4, 0x20000]], "d0": True})
    L = [[S + 0x698, 2, 0], [S + 0x696, 2, 2], [S + 0x69C, 4, 0x200], [S + 0x64E, 2, 0x20], [S + 0x652, 2, 0xFFE0], [S + 0x32, 2, 0]]
    land = [[DFACE + 0x16, 1, 0x20]]
    dead = next(c for c in cloud_cases() if c["name"] == "spawn pool object: no vertices: retired")["writes"]
    pool = dead + [[0x2CD68 + 4 * 0x27, 4, 0x30600]]
    for name, extra in (("afloat, counting down", [[S + 0x69A, 2, 5]]), ("afloat, water ahead", [[S + 0x69A, 2, 0]]),
                        ("afloat, land ahead", [[S + 0x69A, 2, 0]] + land), ("afloat, water at the bow", [[S + 0x69A, 2, 0xFFFF]]),
                        ("afloat, beaching", [[S + 0x69A, 2, 0xFFFF], [O + 0x3C, 2, 2], [O + 0x4A, 4, 0x41C00 + 0x100]] + land +
                         [[0x41D00 + 0x17, 1, 1], [0x41D00 + 0x13, 1, 2], [0x41D20 + 0x17, 1, 0]]),
                        ("beached, sinking", [[S + 0x698, 2, 1], [S + 0x670, 2, 1]]),
                        ("beached, no troops", [[S + 0x698, 2, 1], [S + 0x696, 2, 0]]),
                        ("beached, not due", [[S + 0x698, 2, 1], [S + 0x69C, 4, 0x400]]),
                        ("beached, troops land", pool + [[S + 0x698, 2, 1]] + land),
                        ("beached, water all round", pool + [[S + 0x698, 2, 1]])):
        out.append({"name": "landing ship: " + name, "method": "leyte_landing_ship_mover", "args": [S], "writes": w + L + extra,
                    "stack_guard": [0x3E000, 0x3F040]})
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
        # a case running one of the stood-in routines itself drops that stand-in ("unstub")
        logs = [e for e in CALL_LOGS if e[0] not in c.get("unstub", ())]
        for address, base, nargs, *deref in logs:
            emu.stubs[address] = lambda e, b=base, n=nargs, d=tuple(deref): log_call(e, b, n, *d)
        # a routine that reads a register its caller left (find_anchor_point's D5) gets it here, and the port the
        # same value as an extra argument (pushed but unread by the original)
        emu.d[5] = c.get("d5_in", 0) & 0xFFFFFFFF
        # a hook that stores its caller's A5 (process_flight_input_and_outcome) gets it here, and the port as caller_a5
        emu.a[5] = c.get("a5_in", 0) & 0xFFFFFFFF
        before = bytes(emu.mem)
        try:
            d0 = emu.call(ROUTINES[c["method"]], c["args"], STACK_TOP)
        except (RuntimeError, IndexError) as e:
            raise RuntimeError("%s: %s" % (c["name"], e)) from None
        guard = c.get("stack_guard", STACK_GUARD)
        changes = [[a, emu.mem[a]] for a in range(MEMORY_SIZE) if not (guard[0] <= a < guard[1]) and emu.mem[a] != before[a]]
        cases.append({"name": c["name"], "method": c["method"], "args": c["args"], "zero": [], "writes": c["writes"],
                      "call_logs": logs, "changes": changes})
        if "stack_guard" in c:
            cases[-1]["stack_guard"] = c["stack_guard"]
        if c.get("d0"):
            cases[-1]["d0"] = d0 & 0xFFFFFFFF
        if "a5_in" in c:
            cases[-1]["a5_in"] = c["a5_in"]
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
