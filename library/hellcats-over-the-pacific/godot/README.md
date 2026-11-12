# Godot Project

This directory is the complete Godot project root. Open this directory in the editor; `project.godot`, scenes, data, scripts, and tests all live below it.

`launcher/Main.tscn` is the main scene; it runs the Original edition (`original/`: the port, its compatibility helpers and mappings, and the emulated Macintosh in `mac/`). `assets/` holds the original resources and their decoders. `native/` holds the Native edition, so far Original with InputMap controls (its README); `enhanced/` is not implemented yet. The launcher picks the edition from the command line, Original by default: `godot --path godot -- --edition=original|native|enhanced`.

Tests live under `tests/original/`: `port/` replays the deterministic cases recorded from the original code, `mac/` holds the emulated Macintosh's tests and the end-to-end sessions. The commands are in the repository README's [Validation](../README.md#validation) section; what each check proves is in [the Original port contract](../docs/port/STRICT_PORT_FOUNDATION.md#verification).
