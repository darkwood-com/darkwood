# Native edition (in progress)

The same game rules as the Original edition, on Godot's own facilities instead of the emulated
Macintosh. It starts as adapters around `../original/` (roadmap phase 6) and replaces the
emulated state subsystem by subsystem (phase 7), each checked against Original by deterministic
traces (phase 4). Native never changes Original.

What `--edition=native` runs today (`Game.tscn`, inheriting `original/Game.tscn`):

| Slice | Native | Still Original |
|---|---|---|
| Keyboard | `input/controls.gd`: every game command is a Godot InputMap action (remappable), posting the Mac key the program reads | the program, the emulated Macintosh, screen, mouse |
| Dialogs | `dialogs/dialog_manager.gd` (the Original Dialog Manager plus a snapshot of the front dialog's buttons, check boxes, radio buttons, static and edit text) and `dialogs/dialog_overlay.gd` (those items as Godot controls over the screen, at its scale) | the dialog records, ModalDialog, hit testing and editing, which the game reads and decides by; clicks reach it as Original's mouse events; user items (lists, maps) stay drawn by the game; the edit field's caret is not shown yet |
| Sound driver (1.0.3) | `sound/sound_driver.gd`: the game's two-channel mixer on typed state (node rings, rate, handlers) instead of A5 globals and records in emulated memory; `sound/native_id02.gd` routes CODE 2's eight sound entries to it and runs its tick as the sound hook | the sample bytes (the game's 'snd ' handles) and SoundBase, read and written in the Mac memory; 1.0.4 keeps its own driver |

Checks: `tests/native/test_native_dialogs.gd` (every DLOG opened, drawn, set and clicked on an
Original and a Native machine leaves the same Mac memory byte for byte; the snapshot's items are the
DITL's, the overlay builds the matching controls), `tests/native/test_native_sound.gd` (the same calls on an Original and a Native machine: every
tick's samples and every is-playing answer equal) and the smoke session's `--edition native`, whose
stepped mission 2 matches Original's recorded trace on the game state (`state_trace.gd`
NATIVE_FIELDS: the A5 world without the sound block, the world above low memory, the RNG).
Everything not listed is the Original edition's, so Native plays exactly as Original does.
