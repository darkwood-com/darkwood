extends RefCounted

## A typed view over the overlay's camera and per-frame object-pass globals in the shared Mac memory
## (no copy; names, addresses and widths as in pacific_conflict.gd's constants, signedness from the
## port's reads). Readers / writers are the Original port's functions at those addresses; the
## camera is written by the camera set-up (0x10355.., 0x14015.., 0x16565.., 0x17050..), the lists
## by the object pass (collect_near_objects / collect_far_objects / build_visible_object_list,
## activate / sort helpers).
##
## Aliasing: visible_count (long 0x1c1d4) has its low word at 0x1c1d6, which the visible culling
## reads as a word; *_count longs are read by the original's dbra as their low word.

const CAMERA_Z := 0x1d830           ## long, signed
const CAMERA_Y := 0x1d834           ## long, signed
const CAMERA_X := 0x1d838           ## long, signed
const NEG_CAMERA_BANK := 0x1d80c    ## word (camera_angle3)
const NEG_CAMERA_HEADING := 0x1d80e ## word
const NEG_CAMERA_PITCH := 0x1d810   ## word
const FRAME_COUNTER := 0x1b838      ## long
const BAND_LIMITS := 0x1b190        ## longs, ascending distance thresholds (index -1 readable)
const VIEW_POINT_COUNT := 0x1c1d0   ## long
const VIEW_POINTS := 0x1c43c        ## 3 longs each
const VISIBLE_COUNT := 0x1c1d4      ## long
const VISIBLE_LIST := 0x1c9dc       ## object pointers
const FAR_COUNT := 0x1c1dc          ## entity_array_3_count, long
const FAR_LIST := 0x1d0e4           ## entity_array_3, object pointers
const OBJECT_POOL := 0x1b5a4        ## object pointers, metric-sorted each frame
const OBJECT_COUNT := 0x1b734       ## long (low word 0x1b736 the dbra count elsewhere)
const CARRIED_PAIR_COUNT := 0x1b85c ## word
const CARRIED_PAIRS := 0x1b860      ## pointer to (carrier object, rider object) long pairs
const OVERLAP_COUNT := 0x1c1c4      ## long
const OVERLAP_LIST := 0x1c1e4       ## objects with long +4 bit 0x8000
const VISIBLE_FIRST_PASS := 0x1c1c0 ## word: visible_list entries of the near pass
const CAMERA_ANGLE4 := 0x1d808      ## long: (type record +0x44 - controller +0x626) & 0x3fff
const PLAYER_CONTROLLER := 0x1b738  ## player_object: pointer to the player controller
const PLAYER_SHOT_CTL := 0x1b880    ## long
const CHILD_WINDOW_HI := 0x1b88c    ## word
const CHILD_WINDOW_LO := 0x1b88e    ## word
const SCATTER_SLOTS := 0x1bc64      ## 7 object pointers
const CONTACTS := 0x1bdbc           ## 256 contact record pointers
const SCENE_ROOT := 0x1d6fc         ## scene root object (+0x30 child table)
const RETIRE_COUNT := 0x24cf0       ## long
const REPLAY_STATE := 0x1b870       ## word
const DESIGNATED_ENTITY_2 := 0x1b888 ## controller pointer
const PARACHUTE_OBJECT := 0x1b87c   ## long: the parachute's controller
const REPLAY_REQUEST := 0x1b86c     ## word
const REPLAY_FRAMES_LEFT := 0x1b86e ## word
const VIEW_FOLLOW_TARGET := 0x1b884 ## controller pointer
const VIEW_POINT_STRIDE := 12
# Ground scatter (update_ground_scatter_objects 0x5e28).
const HOST_WORLD := 0x1b5a0         ## host world record (word +0xac scatter on, long +0x8c template offset)
const SCATTER_STARTED := 0x24cec    ## long
const VIEW_RESET := 0x1d856         ## word view_flag_d856: reset the scatter points
const VIEW_MODE := 0x1d858          ## word: views <= 5 are the cockpit / player views
const SCATTER_POINTS := 0x1b924     ## 64 points, 3 longs each
const SCATTER_STATE := 0x1b8e4      ## 64 bytes: bit 7 on screen, bit 6 surface cached, low 6 the slot (0x3f none)
const SCATTER_SURFACE := 0x1b8a4    ## 64 bytes: cached surface type
const SCATTER_SLOT_POINT := 0x1b894 ## 7 words: each slot's point index
const SCATTER_TEMPLATES := 0x1bc24  ## templates by surface >> 4, longs
const CAMERA_MATRIX := 0x1d744      ## 3x3 view matrix (camera block, built by the matrix builders)
const SCREEN_LEFT := 0x20aee        ## words: the projection window
const SCREEN_RIGHT := 0x20aec
const SCREEN_TOP := 0x20aea
const SCREEN_BOTTOM := 0x1b73e
const PROJECTION_X := 0x20b28       ## words: projection scales
const PROJECTION_Y := 0x20b26

const C68kScript := preload("res://original/compat/c68k.gd")

var memory
## The camera blocks' storage (native_block.gd): the Native overlay passes its own (Godot-backed);
## without them the angles and the position are the Mac memory's.
var angles = null
var position = null
var view = null

func _init(memory_, angles_ = null, position_ = null, view_ = null) -> void:
	memory = memory_
	angles = angles_
	position = position_
	view = view_

func _view_word(address: int) -> int:
	return view.read_unsigned(address, 2) if view != null else memory.read_u16(address)

func _set_view_word(address: int, v: int) -> void:
	if view != null:
		view.write(address, 2, v)
	else:
		memory.write_u16(address, v)

func _position(address: int) -> int:
	return C68kScript.s32(position.read_unsigned(address, 4)) if position != null else memory.read_s32(address)

func _set_position(address: int, v: int) -> void:
	if position != null:
		position.write(address, 4, v)
	else:
		memory.write_u32(address, v)

func _angle(address: int, width: int) -> int:
	if angles != null:
		return angles.read_unsigned(address, width)
	return memory.read_u16(address) if width == 2 else memory.read_u32(address)

func _set_angle(address: int, width: int, v: int) -> void:
	if angles != null:
		angles.write(address, width, v)
	elif width == 2:
		memory.write_u16(address, v)
	else:
		memory.write_u32(address, v)

func camera_x() -> int: return _position(CAMERA_X)
func camera_y() -> int: return _position(CAMERA_Y)
func camera_z() -> int: return _position(CAMERA_Z)
func neg_camera_heading() -> int: return C68kScript.s16(_angle(NEG_CAMERA_HEADING, 2))
func neg_camera_pitch() -> int: return C68kScript.s16(_angle(NEG_CAMERA_PITCH, 2))
func neg_camera_bank() -> int: return C68kScript.s16(_angle(NEG_CAMERA_BANK, 2))
func frame_counter() -> int: return memory.read_s32(FRAME_COUNTER)
func band_limit(band: int) -> int: return memory.read_s32(BAND_LIMITS + band * 4)

func view_point_count() -> int: return memory.read_s32(VIEW_POINT_COUNT)
func set_view_point_count(v: int) -> void: memory.write_u32(VIEW_POINT_COUNT, v)
func view_point_address(i: int) -> int: return VIEW_POINTS + i * VIEW_POINT_STRIDE

func visible_count() -> int: return memory.read_s32(VISIBLE_COUNT)
func append_visible(obj: int) -> void:
	memory.write_u32(VISIBLE_LIST + visible_count() * 4, obj)
	memory.write_u32(VISIBLE_COUNT, memory.read_u32(VISIBLE_COUNT) + 1)

func far_count() -> int: return memory.read_s32(FAR_COUNT)
func append_far(obj: int) -> void:
	memory.write_u32(FAR_LIST + far_count() * 4, obj)
	memory.write_u32(FAR_COUNT, memory.read_u32(FAR_COUNT) + 1)

func object_count() -> int: return memory.read_s32(OBJECT_COUNT)
func pool_object(i: int) -> int: return memory.read_u32(OBJECT_POOL + 4 * i)
func set_pool_object(i: int, obj: int) -> void: memory.write_u32(OBJECT_POOL + 4 * i, obj)
func carried_pair_count() -> int: return memory.read_s16(CARRIED_PAIR_COUNT)
func set_carried_pair_count(v: int) -> void: memory.write_u16(CARRIED_PAIR_COUNT, v)
func carrier(k: int) -> int: return memory.read_u32(memory.read_u32(CARRIED_PAIRS) + 8 * k)
func rider(k: int) -> int: return memory.read_u32(memory.read_u32(CARRIED_PAIRS) + 8 * k + 4)
func append_overlap(obj: int) -> void:
	memory.write_u32(OVERLAP_LIST + memory.read_s32(OVERLAP_COUNT) * 4, obj)
	memory.write_u32(OVERLAP_COUNT, memory.read_u32(OVERLAP_COUNT) + 1)
func set_visible_count(v: int) -> void: memory.write_u32(VISIBLE_COUNT, v)
func visible_count_word() -> int: return memory.read_u16(VISIBLE_COUNT + 2)   ## aliased low word
func visible_first_pass() -> int: return memory.read_u16(VISIBLE_FIRST_PASS)
func set_visible_first_pass(v: int) -> void: memory.write_u16(VISIBLE_FIRST_PASS, v)

func set_camera_x(v: int) -> void: _set_position(CAMERA_X, v)
func set_camera_y(v: int) -> void: _set_position(CAMERA_Y, v)
func set_camera_z(v: int) -> void: _set_position(CAMERA_Z, v)
func set_neg_camera_pitch(v: int) -> void: _set_angle(NEG_CAMERA_PITCH, 2, v)
func set_neg_camera_heading(v: int) -> void: _set_angle(NEG_CAMERA_HEADING, 2, v)
func set_neg_camera_bank(v: int) -> void: _set_angle(NEG_CAMERA_BANK, 2, v)
func set_camera_angle4(v: int) -> void: _set_angle(CAMERA_ANGLE4, 4, v)
func player_controller() -> int: return memory.read_u32(PLAYER_CONTROLLER)
func player_shot_ctl() -> int: return memory.read_u32(PLAYER_SHOT_CTL)
func set_player_shot_ctl(v: int) -> void: memory.write_u32(PLAYER_SHOT_CTL, v)
func child_window_lo() -> int: return memory.read_s16(CHILD_WINDOW_LO)
func child_window_hi() -> int: return memory.read_s16(CHILD_WINDOW_HI)
func scatter_slot(k: int) -> int: return memory.read_u32(SCATTER_SLOTS + 4 * k)
func set_scatter_slot(k: int, v: int) -> void: memory.write_u32(SCATTER_SLOTS + 4 * k, v)
func scene_root() -> int: return memory.read_u32(SCENE_ROOT)
func count_retire() -> void: memory.write_u32(RETIRE_COUNT, memory.read_u32(RETIRE_COUNT) + 1)
func contact_slot_address(k: int) -> int: return CONTACTS + 4 * k
func replay_state() -> int: return memory.read_s16(REPLAY_STATE)
func set_designated_entity_2(v: int) -> void: memory.write_u32(DESIGNATED_ENTITY_2, v)

func host_world() -> int: return memory.read_u32(HOST_WORLD)
func scatter_enabled() -> int: return memory.read_u16(host_world() + 0xac)
func scatter_started() -> int: return memory.read_u32(SCATTER_STARTED)
func set_scatter_started(v: int) -> void: memory.write_u32(SCATTER_STARTED, v)
func view_reset() -> int: return _view_word(VIEW_RESET)
func set_view_reset(v: int) -> void: _set_view_word(VIEW_RESET, v)
func view_mode() -> int: return C68kScript.s16(_view_word(VIEW_MODE))
func scatter_point_address(i: int) -> int: return SCATTER_POINTS + 12 * i
func scatter_state(i: int) -> int: return memory.read_u8(C68kScript.u32(SCATTER_STATE + i))   ## index unbounded, as the original
func set_scatter_state(i: int, v: int) -> void: memory.write_u8(C68kScript.u32(SCATTER_STATE + i), v)
func scatter_surface(i: int) -> int: return memory.read_u8(SCATTER_SURFACE + i)
func set_scatter_surface(i: int, v: int) -> void: memory.write_u8(SCATTER_SURFACE + i, v)
func scatter_slot_point(k: int) -> int: return memory.read_s16(SCATTER_SLOT_POINT + 2 * k)
func set_scatter_slot_point(k: int, v: int) -> void: memory.write_u16(SCATTER_SLOT_POINT + 2 * k, v)
func scatter_template(surface: int) -> int: return memory.read_u32(C68kScript.u32(SCATTER_TEMPLATES + C68kScript.asr32(surface, 4) * 4))
func screen_left() -> int: return memory.read_s16(SCREEN_LEFT)
func screen_right() -> int: return memory.read_s16(SCREEN_RIGHT)
func screen_top() -> int: return memory.read_s16(SCREEN_TOP)
func screen_bottom() -> int: return memory.read_s16(SCREEN_BOTTOM)
func projection_x() -> int: return memory.read_s16(PROJECTION_X)
func projection_y() -> int: return memory.read_s16(PROJECTION_Y)
func parachute_object() -> int: return memory.read_u32(PARACHUTE_OBJECT)
func replay_request() -> int: return memory.read_s16(REPLAY_REQUEST)
func set_replay_request(v: int) -> void: memory.write_u16(REPLAY_REQUEST, v)
func replay_frames_left() -> int: return memory.read_s16(REPLAY_FRAMES_LEFT)
func set_replay_frames_left(v: int) -> void: memory.write_u16(REPLAY_FRAMES_LEFT, v)
func set_replay_state(v: int) -> void: memory.write_u16(REPLAY_STATE, v)
func view_follow_target() -> int: return memory.read_u32(VIEW_FOLLOW_TARGET)
func set_view_follow_target(v: int) -> void: memory.write_u32(VIEW_FOLLOW_TARGET, v)
func designated_entity_2() -> int: return memory.read_u32(DESIGNATED_ENTITY_2)
func set_view_mode(v: int) -> void: _set_view_word(VIEW_MODE, v)
