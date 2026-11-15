# Leyte Gulf (investigation)

*Hellcats: Leyte, version 1.0* is a separate application on `sources/mac-os-9/LeyteGulf.dsk`
(`Leyte Gulf:Leyte Gulf`, APPL/HEL1), not a scenery file for 1.0.3: it has its own CODE 0..9 and
its overlay inside its own data fork (same header as *Pacific Conflict*: word 0x0107, code size
0x1f758 against 0x1a75c). Its media and checksums are in `sources/mac-os-9/README.md`. Nothing of it
is ported; this page records what is known before any port.

## How much is Hellcats 1.0.3's code

`tools/mac_resources/leyte_match.py` looks for each of 1.0.3's decompiled functions in Leyte's
code: byte for byte; then with the operands that move with the image masked (in the host segments
A5- and PC-relative displacements, by opcode list; in the overlay PC-relative displacements and every
long its relocation table names, 2,964 in 1.0.3's and 3,248 in Leyte's); then by instruction
sequence with every operand ignored (capstone, in `shell.nix`):

| Code | identical | moved only | same instructions, other operands | not found |
|---|---|---|---|---|
| host segments ID01..ID09 (297 functions) | 77 | 98 | 89 | 33 |
| overlay (443 functions; 108,380 -> 128,856 bytes of code) | 36 | 73 | 145 | 189 |

ID01, ID05, ID06 and ID07 keep their sizes; ID02, ID03 and ID04 grow (ID02 19,994 -> 22,122),
ID08 shrinks (1,096 -> 932: it loads the overlay from the application's own data fork instead of
the scenery file). The host is nearly all 1.0.3's (89 %); about 57 % of the overlay is recognisably 1.0.3's code (a quarter unchanged but
for relocations); the rest is new or rewritten, so Leyte Gulf is a title of its own, not a
recompiled 1.0.3: a port reuses verified functions only where the first two columns say so, and
"same instructions" functions only after their operand changes are explained.

`tools/mac_resources/leyte_correspondence.py` writes the same matching per function, with the
Leyte offset found, as `sources/leyte-gulf-1-0/correspondence.json` (generated; `--check` compares).
Functions under six bytes count as not found. The overlay's 1.0.3 addresses are file offsets (the
code starts after the 0x20-byte header) and so are the Leyte offsets written; until loop 12 both
tools read the 1.0.3 code 0x20 bytes late, so a match covered a function's bytes from +0x20 into
the next one's, and a function whose first 0x20 bytes changed could be placed inside an instruction
(erase_dots_depth4 at Leyte 0x19fb4, inside the instruction at 0x19fb0: the Ghidra function that
failed to decompile). Every overlay match now starts on the 1.0.3 function's first word (254 of
254).

## Runtime architecture (measured, loop 29)

| | Hellcats 1.0.3 | Leyte Gulf 1.0 |
|---|---|---|
| A5 world (below A5) / above A5 | 0x225e / 0x598 | 0x2322 / 0x5e0 |
| Jump table | 175 entries | 184 entries |
| CODE 1 / 5 / 6 / 7 | 558 / 3 064 / 3 942 / 618 bytes | same sizes, same code: CODE 1 differs in 2 bytes, an A5 offset (0x4b2 -> 0x50a, +0x58); 5 / 6 / 7 in their headers (jump-table position) and A5 offsets |
| CODE 2 / 3 / 4 / 9 | 19 994 / 29 142 / 15 738 / 1 100 | 22 122 / 29 778 / 17 900 / 1 216 |
| CODE 8 (overlay loader) | 1 096 | 932: loads the overlay from the application's own data fork |
| Overlay | file "Pacific Conflict": format 0x107, code 0x1a75c, data 0xdc8, bss 0xb2a4, entry 0x10f2, relocations 0x7078 bytes | the data fork: format 0x107, code 0x1f758, data 0xff4, bss 0xc6c0, entry 0x175a, relocations 0x7f50 bytes |
| Resources | (1.0.3's) | its own: 12 DLOG, 21 DITL, 7 ALRT, 5 MENU, 5 PICT, 25 cicn, 14 snd, HEL1, TEXT, clut... |

What loading Leyte in the Original runtime needs, from that:

- A title profile: the disk image and the application (`LeyteGulf.dsk`, `Leyte Gulf:Leyte Gulf`), its
  jump table and segment data generated from its CODE 0 (as `original/mac/data/jump_table.json` is
  from 1.0.3's), its resources.
- Host ports for Leyte's CODE 1..9. The 1.0.3 ports cannot run as they are even where the code is the
  same: they hold the A5 offsets as constants, and Leyte's A5 world is laid out differently (+0x58 in
  places, 0xc4 larger). The evidence for a Leyte variant is the host correspondence: 77 identical and
  98 moved functions, whose masked A5 displacements pair 1.0.3's A5 offsets with Leyte's (as the
  overlay's relocated longs pair its globals): an A5 offset map, then each port's constants remapped
  and checked against Leyte's bytes; the 89 same-instruction and 33 new functions need their own
  reconstruction.
- The overlay loader reading the overlay from the application's data fork (CODE 8's way) instead of
  a "Pacific Conflict" file, with its header (same format 0x107) and relocations.
- The overlay port: 184 functions named so far (Leyte Gulf overlay.names.json); 274 Leyte's own.

A5 offset map (`sources/leyte-gulf-1-0/a5_map.json`, tools/leyte/a5_map.py, `--check` in the QA): in the
host's identical / moved pairs (171 used; 4 whose Leyte copy several 1.0.3 functions claim left out),
an A5-relative operand at the same position names the same global or jump-table slot in both: 162
offsets mapped (115 globals, 47 jump-table slots; 38 globals at the same offset, the others moved in
blocks: -126, -134, -62, -122... so a per-offset map, not a shift), 1 conflict (slot 0x28a, two Leyte
offsets: mapped nowhere), 98 offsets the 1.0.3 host uses not reached yet (in its same-instruction and
new functions).

Host ports (sources/leyte-gulf-1-0/host_ports.json, tools/leyte/gen_host_ports.py, checked in QA): ID01
reused unchanged (its six ported routines byte-identical); ID07 generated from 1.0.3's port, A5
constants remapped through the map, jump-table slots verified to reach the same routines (CODE 0's
entries and the correspondence), and one reviewed immediate (STR# 129: 123 strings in Leyte, 120 in
1.0.3); its cases are Leyte's own CODE 7 run on the 68000 emulator (tools/leyte/gen_leyte_id07_cases.py),
and the port matches them (tests/leyte/port/test_leyte_id07.gd). That immediate was found by the
emulator, not by the instruction comparison: capstone stopped at ID07's inline switch table. All
Leyte tools now disassemble past inline data (skipdata), which also withdrew one layout name
(animate_smoke_trail: the counts differ past its data) and found one more A5 pair.

Title profile, first stage: Leyte's resource fork (113 resources) and CODE 0..9 extracted verbatim
(tools/leyte/extract_resources.py) and mirrored in godot/assets/leyte-gulf-1.0/; the runtime's
resource layer (assets/ui/resource_files.gd) reads them with `title = "leyte-gulf"`, never falling
back to Hellcats' (tests/leyte/test_leyte_resources.gd).

Jump table (godot/original/leyte/data/jump_table.json, tools/leyte/build_jump_table.py): Leyte's 184
slots from its CODE 0, named where a Leyte port proves the routine (5 so far).

## Open before a port

- The A5 map's 98 unreached offsets and its conflict; the Leyte CODE 0 jump table data.
- The host's service table to the overlay (ID02's install_overlay_service_table counterpart) and
  CODE 8's loader for the embedded overlay.
- Mission and world data in the larger overlay.
