extends "res://original/mac/toolbox/menu_manager.gd"

## The Native edition's Menu Manager: the Original one (the menus in their MENU handles, MenuSelect's
## tracking and choice, MenuKey) unchanged, plus a snapshot of the bar and of the menu pulled down,
## taken on the program's thread each time the Original draws or closes them, for the Native overlay
## (menu_overlay.gd). It reads the Mac memory only.

const APPLE := 0x14                ## the system font's Apple logo character, the Apple menu's title

var _lock := Mutex.new()
var _snapshot: Dictionary = {"version": 0, "bar": [], "open": {}}
var _open: Dictionary = {}

## {version, bar: [{title, rect, hilited, dimmed, apple}], open: {} or {rect, rows: [{text, enabled,
## mark, key, separator, chosen}]}} in Mac screen coordinates.
func snapshot() -> Dictionary:
	_lock.lock()
	var s: Dictionary = _snapshot.duplicate(true)
	_lock.unlock()
	return s

func _publish() -> void:
	var bar: Array = []
	for t in _layout():
		var h: int = t[0]
		var title: PackedByteArray = _title(h)
		bar.append({"title": _string(title), "apple": title == PackedByteArray([APPLE]),
			"rect": Rect2i(t[1], 0, t[2] - t[1], machine.MENU_BAR_HEIGHT - 1),
			"hilited": memory.read_s16(memory.read_u32(h)) == hilited and hilited != 0,
			"dimmed": (memory.read_u32(memory.read_u32(h) + 0xa) & 1) == 0})
	_lock.lock()
	_snapshot = {"version": int(_snapshot["version"]) + 1, "bar": bar, "open": _open.duplicate(true)}
	_lock.unlock()

static func _string(bytes: PackedByteArray) -> String:
	var out := ""
	for b in bytes:
		out += String.chr(b if b < 0x80 else Files.MAC_ROMAN_HIGH[b - 0x80])
	return out

const Files := preload("res://assets/ui/resource_files.gd")

func draw_menu_bar() -> void:
	super()
	_publish()

func _draw_menu(h: int, box: Rect2i, choice: int) -> void:
	super(h, box, choice)
	var rows: Array = []
	var items: Array = _items(h)
	for i in items.size():
		var at: int = items[i]
		var text: PackedByteArray = _text(at)
		var attrs: int = at + memory.read_u8(at) + 1
		var mark: int = memory.read_u8(attrs + 2)
		var key: int = memory.read_u8(attrs + 1)
		var separator: bool = text == PackedByteArray([0x2d])
		rows.append({"text": _string(text), "enabled": _enabled(h, i + 1), "separator": separator,
			"mark": "" if mark == 0 else ("✓" if mark == 0x12 else _string(PackedByteArray([mark]))),
			"key": "⌘" + String.chr(key) if key > 0x20 else "",
			"chosen": choice == i + 1 and _enabled(h, i + 1) and not separator})
	_open = {"rect": box, "rows": rows}
	_publish()

func _restore(saved: PackedByteArray, box: Rect2i) -> void:
	super(saved, box)
	_open = {}
	_publish()
