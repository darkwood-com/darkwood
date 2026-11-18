extends RefCounted

## The Native edition's font for the text it draws itself over the Mac screen (menus: menu_overlay.gd,
## dialogs: dialog_overlay.gd). The overlays scale their text with the window, so it is rendered at the
## output resolution rather than as scaled Mac pixels; this keeps it sharp there:
## - glyph positions on whole pixels (no subpixel positioning, which renders stems across two pixels);
## - full hinting, so stems and baselines snap to the pixel grid;
## - greyscale antialiasing (LCD subpixel antialiasing would colour-fringe on the white menus).
## A system UI face close to the Mac's (Geneva / Lucida Grande on a Mac, Segoe UI on Windows, Roboto on
## Android) is used when present; otherwise Godot's own default font, with the same settings.
## Presentation only: the game's logic never reads it (Original's menus and dialogs are untouched).

const FACES := ["Geneva", "Lucida Grande", "Helvetica Neue", "Segoe UI", "Roboto", "Helvetica", "Arial", "DejaVu Sans"]

static var _font: Font = null
static var _theme: Theme = null

## The shared font (built once).
static func font() -> Font:
	if _font == null:
		var f := SystemFont.new()
		f.font_names = PackedStringArray(FACES)
		f.antialiasing = TextServer.FONT_ANTIALIASING_GRAY
		f.hinting = TextServer.HINTING_NORMAL
		f.subpixel_positioning = TextServer.SUBPIXEL_POSITIONING_DISABLED
		f.multichannel_signed_distance_field = false
		f.generate_mipmaps = false
		f.allow_system_fallback = true
		_font = f
	return _font

## A theme whose default font is font(): set on an overlay, every control it builds inherits it.
static func theme() -> Theme:
	if _theme == null:
		_theme = Theme.new()
		_theme.default_font = font()
	return _theme

## A point on whole pixels (the overlays place their controls there, so edges and text are not
## drawn across two pixels at a fractional screen scale).
static func snap(p: Vector2) -> Vector2:
	return p.round()
