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
| overlay (443 functions; 108,380 -> 128,856 bytes of code) | 36 | 63 | 82 | 262 |

ID01, ID05, ID06 and ID07 keep their sizes; ID02, ID03 and ID04 grow (ID02 19,994 -> 22,122),
ID08 shrinks (1,096 -> 932: it loads the overlay from the application's own data fork instead of
the scenery file). The host is nearly all 1.0.3's (89 %); about 41 % of the overlay is recognisably 1.0.3's code (a fifth unchanged but
for relocations); the rest is new or rewritten, so Leyte Gulf is a title of its own, not a
recompiled 1.0.3: a port reuses verified functions only where the first two columns say so, and
"same instructions" functions only after their operand changes are explained.

`tools/mac_resources/leyte_correspondence.py` writes the same matching per function, with the
Leyte offset found, as `sources/leyte-gulf-1-0/correspondence.json` (generated; `--check` compares).
Functions under six bytes count as not found.

## Open before a port

- The host's service table to the overlay and ID08's loader for an embedded overlay.
- Mission and world data in the larger overlay.
