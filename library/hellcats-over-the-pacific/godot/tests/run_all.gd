extends SceneTree
## Project-local headless test runner.
## Usage:
##   godot --headless --path . --script res://tests/run_all.gd

const TEST_PATHS := [
	"res://tests/launcher/test_launcher.gd",
	"res://tests/native/test_native_controls.gd",
	"res://tests/native/test_native_sound.gd",
	"res://tests/native/test_native_dialogs.gd",
	"res://tests/native/test_native_saves.gd",
	"res://tests/native/test_native_menus.gd",
	"res://tests/native/test_native_rng.gd",
	"res://tests/native/test_native_trig.gd",
	"res://tests/native/test_native_views.gd",
	"res://tests/native/test_native_object_pass.gd",
	"res://tests/native/test_native_aircraft.gd",
	"res://tests/native/test_native_ai_pursuit.gd",
	"res://tests/native/test_native_flight_model.gd",
	"res://tests/native/test_native_object_release.gd",
	"res://tests/native/test_native_ground_scatter.gd",
	"res://tests/native/test_native_replay_camera.gd",
	"res://tests/native/test_native_camera_angles.gd",
	"res://tests/original/mac/test_stepped_run.gd",
	"res://tests/original/mac/test_resource_versions.gd",
	"res://tests/original/port/test_id02_104_sound.gd",
	"res://tests/original/port/test_c68k.gd",
	"res://tests/original/port/test_memory_68k.gd",
	"res://tests/original/port/test_contact_pair_hash.gd",
	"res://tests/original/port/test_find_contact_record.gd",
	"res://tests/original/port/test_reset_radar_tracks.gd",
	"res://tests/original/port/test_abs_int.gd",
	"res://tests/original/port/test_approx_dist3_xyz.gd",
	"res://tests/original/port/test_camera_metric.gd",
	"res://tests/original/port/test_sin_cos_fixed14.gd",
	"res://tests/original/port/test_attitude_bases.gd",
	"res://tests/original/port/test_attitude_matrix.gd",
	"res://tests/original/port/test_attitude_variants.gd",
	"res://tests/original/port/test_transform_vecs12.gd",
	"res://tests/original/port/test_transform_int3.gd",
	"res://tests/original/port/test_template_chain_xform.gd",
	"res://tests/original/port/test_node_chain.gd",
	"res://tests/original/port/test_scene_traversal.gd",
	"res://tests/original/port/test_type80_visitor.gd",
	"res://tests/original/port/test_clip_polygon.gd",
	"res://tests/original/port/test_collect_map_features.gd",
	"res://tests/original/port/test_plot_map_symbols.gd",
	"res://tests/original/port/test_segment_point_min_distance.gd",
	"res://tests/original/port/test_overlay_rng_retire.gd",
	"res://tests/original/port/test_bullet_hits.gd",
	"res://tests/original/port/test_dispatch_collision.gd",
	"res://tests/original/port/test_facility_tally_cleanup.gd",
	"res://tests/original/port/test_spawn_destruction_effect.gd",
	"res://tests/original/port/test_tick_bullet.gd",
	"res://tests/original/port/test_surface_type.gd",
	"res://tests/original/port/test_spawn_effect.gd",
	"res://tests/original/port/test_pool_spawn.gd",
	"res://tests/original/port/test_heap.gd",
	"res://tests/original/port/test_object_creation.gd",
	"res://tests/original/port/test_object_buffers.gd",
	"res://tests/original/port/test_activate.gd",
	"res://tests/original/port/test_shading.gd",
	"res://tests/original/port/test_sink.gd",
	"res://tests/original/port/test_land_outline.gd",
	"res://tests/original/port/test_factories.gd",
	"res://tests/original/port/test_effect_init.gd",
	"res://tests/original/port/test_aircraft_create.gd",
	"res://tests/original/port/test_effect_animate.gd",
	"res://tests/original/port/test_effect_debris.gd",
	"res://tests/original/port/test_controller_ticks.gd",
	"res://tests/original/port/test_ground_fire.gd",
	"res://tests/original/port/test_trails.gd",
	"res://tests/original/port/test_sinking_step.gd",
	"res://tests/original/port/test_inverse_trig.gd",
	"res://tests/original/port/test_turret.gd",
	"res://tests/original/port/test_blast_damage.gd",
	"res://tests/original/port/test_flak_gun.gd",
	"res://tests/original/port/test_wave_spawner.gd",
	"res://tests/original/port/test_aircraft_spawner.gd",
	"res://tests/original/port/test_firing_ship.gd",
	"res://tests/original/port/test_small_handlers.gd",
	"res://tests/original/port/test_aircraft_handlers.gd",
	"res://tests/original/port/test_aircraft_notice.gd",
	"res://tests/original/port/test_fighter_notice.gd",
	"res://tests/original/port/test_overlap_dispatch.gd",
	"res://tests/original/port/test_landing_gear.gd",
	"res://tests/original/port/test_damage_helpers.gd",
	"res://tests/original/port/test_damage_state.gd",
	"res://tests/original/port/test_aircraft_tick.gd",
	"res://tests/original/port/test_flight_helpers.gd",
	"res://tests/original/port/test_steering.gd",
	"res://tests/original/port/test_flight_misc.gd",
	"res://tests/original/port/test_flight_model.gd",
	"res://tests/original/port/test_replays.gd",
	"res://tests/original/port/test_id01_divide.gd",
	"res://tests/original/port/test_id07_09.gd",
	"res://tests/original/port/test_id08_09_glue.gd",
	"res://tests/original/port/test_resedit_dialogs.gd",
	"res://tests/original/port/test_resedit_interface.gd",
	"res://tests/original/port/test_resedit_text.gd",
	"res://tests/original/port/test_resedit_media.gd",
	"res://tests/original/mac/test_unexported_routines.gd",
	"res://tests/original/mac/test_sound_channels.gd",
	"res://tests/original/mac/test_event_mask.gd",
	"res://tests/original/mac/test_throttle_keys.gd",
	"res://tests/original/mac/test_menu_bar.gd",
	"res://tests/original/mac/test_alert.gd",
	"res://tests/original/mac/test_dialog_edit.gd",
]

func _init() -> void:
	var failed: Array[String] = []
	print("Running %d Godot project-local tests..." % TEST_PATHS.size())

	for test_path in TEST_PATHS:
		var script := load(test_path)
		if script == null:
			print("FAIL load %s" % test_path)
			failed.append(test_path)
			continue

		var test_object = script.new()
		if not test_object.has_method("run"):
			print("FAIL run() missing %s" % test_path)
			failed.append(test_path)
			continue

		print("-- %s" % test_path)
		var ok := bool(test_object.run())
		if ok:
			print("OK %s" % test_path)
		else:
			print("FAILED %s" % test_path)
			failed.append(test_path)

	var pass_count := TEST_PATHS.size() - failed.size()
	print("Test result: %d passed, %d failed" % [pass_count, failed.size()])
	if not failed.is_empty():
		print("Failures:")
		for test_path in failed:
			print(" - %s" % test_path)

	quit(0 if failed.is_empty() else 1)
