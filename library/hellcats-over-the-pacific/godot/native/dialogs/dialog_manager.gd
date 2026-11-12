extends "res://original/mac/toolbox/dialog_manager.gd"

## The Native edition's Dialog Manager: the Original one (records, ModalDialog, hit testing, editing:
## everything the game reads and decides by) unchanged, plus a snapshot of the front dialog's standard
## items that the Native overlay (dialog_overlay.gd) draws with Godot controls. The snapshot is taken
## on the program's thread whenever a dialog is drawn, an item changes or a dialog goes, and read by
## Godot's main thread under a lock: it only reads the Mac memory and changes nothing.

const SHOWN := [BUTTON, CHECKBOX, RADIO, STATIC_TEXT, EDIT_TEXT]

var _lock := Mutex.new()
var _snapshot: Dictionary = {"version": 0, "items": []}

## The front dialog's standard items: [{index, type, enabled, active, rect (global, Mac screen),
## text, value}], with a version that changes with each new snapshot.
func snapshot() -> Dictionary:
	_lock.lock()
	var s: Dictionary = _snapshot.duplicate(true)
	_lock.unlock()
	return s

func publish() -> void:
	var items: Array = []
	var d: int = windows.front_window()
	if dialogs.has(d) and windows.is_visible(d):
		var origin: Vector2i = -qd.port_bits(d)[2].position
		var list: Array = dialogs[d]["items"]
		for i in list.size():
			var e: Dictionary = list[i]
			if not (e["type"] in SHOWN) or e["handle"] == 0:
				continue
			var entry := {"index": i + 1, "type": e["type"], "enabled": e["enabled"], "active": true,
				"rect": Rect2i((e["box"] as Rect2i).position + origin, (e["box"] as Rect2i).size), "text": "", "value": 0}
			if e["type"] in [BUTTON, CHECKBOX, RADIO]:
				var c: int = memory.read_u32(e["handle"])
				var title := PackedByteArray()
				for k in memory.read_u8(c + 0x28):
					title.append(memory.read_u8(c + 0x29 + k))
				entry["text"] = _mac_string(title)
				entry["value"] = memory.read_s16(c + 0x12)
				entry["active"] = memory.read_u8(c + 0x11) != 0xff and memory.read_u8(c + 0x10) != 0
			elif e["type"] == STATIC_TEXT:
				entry["text"] = _mac_string(_substitute(_text(e["handle"])))
			else:
				entry["text"] = _mac_string(_text(e["handle"]))
			items.append(entry)
	_lock.lock()
	_snapshot = {"version": int(_snapshot["version"]) + 1, "items": items}
	_lock.unlock()

## Mac OS Roman text as a Godot string, Return as a line break.
static func _mac_string(bytes: PackedByteArray) -> String:
	var out := ""
	for b in bytes:
		out += "\n" if b == 0x0d else String.chr(b if b < 0x80 else Files.MAC_ROMAN_HIGH[b - 0x80])
	return out

func draw_dialog(d: int) -> int:
	var r: int = super(d)
	publish()
	return r

func _draw_item(d: int, e: Dictionary) -> void:
	super(d, e)
	publish()

func dispose(d: int) -> int:
	var r: int = super(d)
	publish()
	return r

func _set_hilite(h: int, state: int) -> void:
	super(h, state)
	publish()

func _set_ctl(h: int, field: int, v: int) -> int:
	var r: int = super(h, field, v)
	publish()
	return r
