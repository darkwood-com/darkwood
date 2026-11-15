# Leyte Gulf 1.0 — reconstruction sources

- `correspondence.json`: each of Hellcats 1.0.3's 740 functions located in Leyte Gulf's code
  (tools/mac_resources/leyte_correspondence.py; docs/topics/LEYTE_GULF.md).
- `Leyte Gulf overlay`: the application's data fork (`Leyte Gulf:Leyte Gulf` on
  sources/mac-os-9/LeyteGulf.dsk) extracted verbatim, SHA-256
  `110906ae003abe1679713a60e48deb8391d1b2663c1dcfa0f6415cf54c844944`
  (tools/leyte/extract_overlay.py; `--check` in the Python QA). The disk image is never written.
- `CODE/Leyte Gulf overlay.c` and `.functions.json`: the Ghidra export, the reconstruction's first
  stage (as Pacific Conflict.c for 1.0.3), made by tools/leyte/ghidra_export.py with Ghidra 12.2
  headless (Motorola 68000 BE, raw binary at base 0: FUN_ names are file offsets), seeded with the
  overlay entry and the exact (identical / moved) correspondences: 511 functions, all decompiled,
  no bad instruction data. SHA-256 of the C as exported:
  `02170b6342f1bf4186192e2be8916bfa615b40c2bbe079c087bc290d4425ea17`. (The first export, 523
  functions, was seeded from offsets 0x20 off: twelve seeds fell inside instructions, one of them
  the function that failed, FUN_00019fb4, inside FUN_00019fac; docs/topics/LEYTE_GULF.md.)
  Not yet reviewed or protected. The overlay uses
  68020 instructions (`muls.l` in FUN_00019fac), which Ghidra's default 68000 language decodes.
- `CODE/Leyte Gulf overlay.names.json`: 1.0.3 names where the evidence allows
  (tools/leyte/names_from_correspondence.py): 183 named: 89 by code (identical / moved; sin_fixed14 /
  cos_fixed14 placed by a call site) and 94 by layout (same instructions, registers, addressing and
  immediates, an immediate the relocation table marks as an address in both counting as layout;
  only structure field offsets and addresses moved: Leyte re-lays out the host world, by
  +0x3c in places, and the objects, their position from +0x18 to +4); 10 "called" (role proven by a
  call site, code different); 32 candidates (an immediate, a size, a register or the instruction
  count differs: listed); 12 ambiguous; 274 Leyte's own.
- `CODE/Leyte Gulf overlay.decomp.c`: the semantic C's first stage, generated
  (tools/leyte/semantic_overlay.py, `--check` in the Python QA): the 183 named functions' Leyte Ghidra
  C under their Hellcats names (Ghidra names kept; calls to the "called" ones named too), and 99
  globals named where a relocated long of a named pair ties the Leyte address to one named Hellcats
  global from the 89 same-code pairs (0 conflicts; 9 paired addresses have no Hellcats name). Not
  reviewed. Next: the 12 ambiguous offsets, the 32 candidates' differences, then Leyte's own 274.
- `a5_map.json`: Leyte's A5 world against 1.0.3's (tools/leyte/a5_map.py), 162 offsets mapped from the
  host correspondence; `host_ports.json`: what each Leyte host port is (tools/leyte/gen_host_ports.py):
  ID01 reused (byte-identical), ID07 generated with its A5 constants remapped and one reviewed
  immediate (123 strings, not 120), replayed against Leyte's own CODE 7 on the 68000 emulator
  (tests/leyte/port/test_leyte_id07.gd).
- `resources/<TYPE>/<id>.bin`, `CODE/ID00..09.bin`, `resources.json`: Leyte's resource fork extracted
  verbatim from the disk image (tools/leyte/extract_resources.py, `--check` in the QA), as
  resedit-1-0-3 lays out 1.0.3's; mirrored for the runtime in godot/assets/leyte-gulf-1.0/.
