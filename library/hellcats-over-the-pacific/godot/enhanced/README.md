# Enhanced edition

The Native edition with optional improvements, each behind its own switch; Enhanced never changes
Original or Native. `godot --path godot -- --edition=enhanced` runs it (`--version=1.0.4` too).

## Mobile landscape controller (first improvement)

`touch_controller.gd`: an on-screen controller for touch screens in landscape, shown on touch
screens (or on a desktop with `--touch`, a preview), hidden otherwise. It adds no game behaviour:
each button sends the key its Native action is bound to (`native/input/controls.gd`), as a key event
through Godot's input, so the Native keyboard path takes it like a key (same Mac key and character,
down and up). Remapping an action remaps its button.

| Group | Buttons (the game's own keys) |
|---|---|
| Stick pad, bottom left | `8` / `2` keyboard elevator, `4` / `6` aileron, `5` centre (the original's keyboard flying) |
| Actions, bottom right | FIRE (space), BOMB `b`, FLAPS `f`, GEAR `g`, throttle `+` / `-` (`=` / `-`) |
| Top right | views `v` / `c` / `t`, target lock `a`, pause `p`, OK (Return) |

Touch semantics: a button is down while a touch that began on it stays on it; lifting the touch,
dragging it off, focus loss or hiding the controller releases it; each touch is tracked by index, so
buttons combine (multi-touch). A held button is a held key without auto-repeat, as on a Mac with key
repeat off: the game steers, or ramps the throttle (a notch every second frame), for the whole hold;
a tap is one press and one release. Layout: inside the display's safe area, sized from the
viewport's height (`ui_scale`), the centre of the picture left clear; the game picture keeps its
aspect ratio. The project's handheld orientation is sensor landscape.

Menus and dialogs: the Native edition's menus and dialogs are Godot controls, and the game's own
pointer (the Mac mouse) reads Godot's mouse events; touches reach both through Godot's
touch-to-mouse emulation (a default project setting). Not yet tested by touch on a device.

## Application background (mobile)

`app_clock.gd`: while the application is paused (a phone's app switch, a call), the Mac sleeps, as
a Macintosh put to sleep does: TickCount (the program's clock; ID02 paces its flight frames on it)
stops, and the program's thread waits at its next TickCount until the application resumes, so a
flight neither goes on unseen nor jumps by the time away. The touch controller lets go of its keys
on the pause; Godot stops feeding the speaker, so the sound stops with the picture. The clock is
installed by the Enhanced Menu Manager (`menu_manager.gd`, the Native one plus the clock): its install
runs after the Event Manager's and before the program's thread starts, so the trap is replaced before
the program can call it. Only TickCount is shifted; event timestamps and the Sound Manager's
durations keep the host clock. No gameplay change.

Tests: `tests/enhanced/test_enhanced_clock.gd` (TickCount frozen across a pause and not counting
the time away, a program thread waiting while paused and going on at the resume, a halt releasing
the wait, the controller releasing its keys on the pause), and `tests/enhanced/test_enhanced_touch.gd` (layouts at five phone / tablet sizes, tap / hold /
multi-touch / drag-off / focus loss, each button's Mac key and character through the Event Manager,
desktop default off).

## Android (debug)

- `godot/export_presets.cfg`, preset "Android": debug, arm64-v8a, the game's raw data included
  (`*.bin`, `*.json`, the scenery overlay), tests excluded. Export:
  `Godot --headless --path godot --export-debug "Android" <apk>`; the editor settings need the
  Android SDK, a JDK 17 (`export/android/java_sdk_path`) and the debug keystore
  (`~/.android/debug.keystore`, Android's standard debug key). The project imports ETC2 / ASTC
  textures (required for Android). On a mobile device the launcher starts this edition.
- Emulator (Android 37, arm64 Play Store image, `Medium_Phone_API_37.0`, landscape 2400 x 1080),
  verified by screenshots: the game starts in landscape with the controller; the pilot roster and
  the mission dialog answer touches (OK, a mission's radio button); Flight School loads and flies;
  the `+` button raises the throttle (RPM and manifold pressure up). No script error in the log.
  Software rendering makes the emulator slow (the instrument panel takes over a minute to build).
- Background and return on the emulator (2026-11-14, loop 13): Home, a minute away, relaunch, twice:
  the log shows "application paused: the Mac sleeps" and "application resumed: 4301 ticks slept"
  (then 6635 in all); the same process resumes (no restart); the game uses 1.2 % CPU in the
  background against 18 % in the foreground, and its thread runs again after the resume. That
  emulator run was memory-starved (8 GB host, two cores, 1.5 GB guest): the program did not reach
  the pilot roster in nine minutes, so the resume was not seen mid-flight.
- Mid-flight background on the emulator (2026-11-14, loop 22; host GPU: `-gpu host`, Apple M1, the
  game in flight within two minutes): three OK taps through the roster and the mission dialog, the
  + button for the throttle, then Home for a minute and relaunch. The log shows "application paused"
  and "resumed: 3704 ticks slept" (62 s); the same process resumes; the screenshot just after the
  resume shows the scene as it was before Home (no time passed in the game), and 15 s later the game
  has moved on. An earlier attempt hit an ANR ("Input dispatching timed out") during a 10 s long
  press: the system's own gesture monitor timed out at the same moment and the host was swapping
  (3.9 GB of 5 GB, the emulator holding 569 MB of a 4 GB guest), the app's UI thread idle and its
  engine and game threads running: the emulated device stalled, not shown to be the game's. A
  delayed tap then hit the ANR dialog's Close app. With Google's background apps force-stopped and
  short taps only, no ANR.
- Not verified: a physical device; the menus by touch; a whole mission by touch.

## iOS

Checked 2026-11-14 (loop 23): Xcode 27.2 is the active developer directory, the iOS 27.2 Simulator
runtime is installed and an iPhone 18 Pro is booted. `export_presets.cfg` has an "iOS" preset
(Xcode project only); the project icon is the game's own Finder icon (ICN# / icl8 128, the APPL's
icon through BNDL 132 / FREF 130), which the iOS export requires. The export succeeds:
`Godot --headless --path godot --export-debug "iOS" <dir>/Hellcats.xcodeproj`. Godot requires an App
Store Team ID even for that; the preset's `SIMULATOR0` is a placeholder for Simulator builds, which
are built unsigned (`xcodebuild ... -sdk iphonesimulator CODE_SIGNING_ALLOWED=NO`). No paid
enrollment, no signing identity.

The official Godot 4.7.2 template's Simulator `libgodot.a` holds x86_64 code only (`lipo -archs`),
which this Apple Silicon Mac's Simulator cannot link (no arm64 `_main`); MoltenVK's Simulator slice
has arm64 and the camera plugin is unused. Fix (no Rosetta, no paid team): an arm64 Simulator
`libgodot.a` built from Godot's source at the installed engine's commit (4.7.2-stable, ed1daf0bf;
`scons platform=ios target=template_debug simulator=yes arch=arm64 generate_bundle=no`, 8 min, ~1 GB
of build tree outside the project) and made fat with the official x86_64 slice into a copy of the
official template, kept at `~/Library/Caches/hellcats-ios-template/4.7.2/ios.zip` (253 MB).
`tools/mobile/ios_simulator.sh <work dir>` points the iOS preset at it for the export only (restored
after), exports, builds unsigned (`CODE_SIGNING_ALLOWED=NO`), installs and launches on the booted
Simulator. The Simulator has neither Metal nor Vulkan: the project's Compatibility renderer is the
one it needs.

Run on the iPhone 18 Pro Simulator (iOS 27.2, 2026-11-15), by touch only (Simulator taps on the
game's own dialogs and the controller): the game starts in landscape; the pilot roster's OK closes
it; Select a Mission takes Flight School and OK; the flight starts with the cockpit and instruments;
a 3 s press on + raises the RPM and manifold pressure and the aircraft rolls, the button released
after; Home for 30 s then reopening: "application paused: the Mac sleeps" / "resumed: 1622 ticks
slept", the same process. The controller first took its overlay layout on this phone: its safe area
(186 px insets both sides for the Dynamic Island, 60 px below) left side bars of 321 px, under the
side-bar minimum; the bars now run to the screen's edges when the safe ones are too narrow, the safe
area kept vertically (tests/enhanced/test_enhanced_touch.gd has this phone's measured geometry). The
mission dialog's doubled radio labels (Android and iOS) were the Native overlay's transparent
check boxes over the Mac's drawing: fixed (PROJECT_STATUS, job d44decba). Not yet
tried: audio, a whole mission. Not a device result.
