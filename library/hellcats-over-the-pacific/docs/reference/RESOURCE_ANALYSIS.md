# ResEdit resource analysis

The curated resource tree is reproducible and locally inspectable. Each resource
item under `sources/resedit-1-0-3/` has a sibling binary payload and JSON metadata
record. `sources/resedit-1-0-3/inventory.json` is only an index; the resource-local
JSON files are the primary inspection records.

## Coverage

The requested type list contains **30** directory names, despite being described
as 29. All 30 are retained exactly:

`ALRT`, `BNDL`, `CNTL`, `CODE`, `DATA`, `DITL`, `DLOG`, `DREL`, `FREF`, `HEL1`,
`HELL`, `ICN#`, `ICON`, `MDPL`, `MENU`, `PICT`, `SIZE`, `STR#`, `TEXT`, `ZERO`,
`cicn`, `clut`, `icI4`, `icI8`, `ics#`, `ics4`, `ics8`, `snds`, `styl`, `vers`.

Run `python3 tools/resedit.py inventory` for authoritative detailed counts or
`python3 tools/progress_stats.py` for the compact cross-domain summary.
Executable references are direct type/ID references supported by decompilation
annotations; type-level named-resource call sites remain separate. Neither is a
claim of runtime observation or proof that every named resource executes.

## Evidence and edition provenance

Every payload is the Hellcats **1.0.3** application's, read from its resource
fork on the original floppy `sources/mac-os-9/Hellcats_1_0_3.dsk`
(`tools/mac_resources/hfs.py`, file `Hellcats:Hellcats:Hellcats`). Each record
states it (`evidence.source_version`, `source_container`).

1. A ResEdit hexadecimal export must equal the disk payload, or be a recorded
   prefix of it (`evidence.resedit_export_status`). Exports are never rewritten.
2. The protected `CODE/ID*.bin` files must match both their hex exports and the
   disk; they are never rewritten.
3. The 1.0.4 fork embedded in `sources/mac-os-9/Hellcats_1_0_4.appledouble` is
   recorded per resource under `version_1_0_4` (identical, differs with its
   size, hash and decoded properties, or absent). It never supplies a 1.0.3
   payload.

Twelve resources differ in 1.0.4:

- `CODE:2` (19,994 → 24,832 bytes), `vers:1` (1.0.3 → 1.0.4) and eight
  sounds (`snds` 2186, 13493, 15490, 16059, 16815, 21266, 24100, 25233): the
  updater on `Version_1_0_4.dsk` carries these as whole replacements.
- `HEL1:127` and `HEL1:128`: the saved settings and scores, equal in size,
  which the 1.0.4 copy had rewritten by being played.

The 1.0.4 sounds were the mirror's payloads until the 1.0.3 disk was read; they
remain in the AppleDouble fork and the updater for the 1.0.4 version profile.
`DITL:135`'s export stops eight bytes into item 9; the disk holds the complete
544 bytes, whose last item is the empty editable unlock-code field
(`0000 0000 00e6 001f 00f7 00dd 10 00`).

The directory labels `snds`, `icI4`, and `icI8` are preserved. The parsed
resource map independently establishes their original four-character types as
`snd `, `icl4`, and `icl8`; metadata records both identities rather than silently
renaming directories.

## Hellcats 1.0.4

`tools/mac_resources/update_1_0_4.py --check` (run by QA) rebuilds the 1.0.4 fork from the 1.0.3
disk and the updater on `Version_1_0_4.dsk`, as the updater describes it, and checks
`sources/hellcats-1-0-4/` (written by `--write`): the 13 resources 1.0.4 changes or adds, as
`<TYPE>/<id>.bin`, and `manifest.json` with their hashes, the CODE 2 patch and the table below.
`tools/original/import_resedit.py` mirrors the non-CODE ones into `godot/assets/hellcats-1.0.4/`,
and `resource_files.gd` reads them over the 1.0.3 mirror when its `version` is "1.0.4"
(`tests/original/mac/test_resource_versions.gd`). With that version the runtime also runs CODE 2
as `port/ID02_v1_0_4.gd` (1.0.3's with the eight sound entries on the 1.0.4 driver, `port/ID02_104.gd`)
and its Sound Manager plays SndPlayDoubleBuffer; the smoke session's `--version 1.0.4` selects it.

- Its data fork (286 bytes) names the file it patches (Str31 `Hellcats`, `APPL`/`HELL`, at 0x00
  and again at 0x48), holds a Macintosh date at 0x74 (`0xa5d7d54a`, March 1992) and, from 0xb0,
  one 10-byte record per installed resource: `01 03 80 00`, type, id. It lists 11: `vers` 1, the
  eight sounds, `HEL1` 127 and `CODE` 2. The record at 0x80 and the word 3 at 0x7c are not decoded.
- Its resource fork carries each payload under its type XORed with 0x01 (`BNED` CODE, `roe!`
  snd, `wdsr` vers, `IDM0` HEL1, `RH[D` SIZE), named `1-` + the installed name; attributes and
  names match the 1.0.4 copy. It also carries `SIZE` 0 and 1, which the list omits and 1.0.3
  lacks (it has `SIZE` -1 only); the 1.0.4 copy holds both byte for byte, so they are added.
- The result equals `Hellcats_1_0_4.appledouble` on 119 resources. The rest are the game's own
  writes: `HEL1` 127 (the updater installs fresh saved settings; the copy's were played with) and
  128, and the `HEL2` caches 127, 200, 300, 400, 500.

CODE 2 is a binary patch of 1.0.3's (the tool's `code2` report, checked by `--check`): the bytes
before the first function and all 119 other ID02 functions are unchanged and at their offsets, so
CODE 0's jump table still fits. The eight sound-driver entries (`init_sound_system` 0x3dd0 ..
`shutdown_sound` 0x4318, [SOUND_SYSTEM.md](../topics/SOUND_SYSTEM.md)) each had their first
instruction, `LINK A6`, overwritten by `JMP d16(PC)` into 4,838 bytes appended at 0x4e1a..0x6100
(24 bytes changed in all); the 1.0.3 bodies remain below them. The appended code, the only new
code in 1.0.4, is a second sound driver on the Sound Manager instead of the VBL hook and
`SoundBase` [disassembly; names STRONG INFERENCE]:

| 1.0.4 offset | Role |
|---|---|
| 0x4e1a | double-back procedure (Pascal, `RTD #8`: channel, buffer): mixes the four channels into the next 1,850-sample buffer; also the base of the driver's data (`LEA 0x4e1a(PC),A4`) |
| 0x4fa6 | `alloc_sound_buffer_node`'s replacement: pops a node from a channel's free list (four channels of 24 nodes in a ring) |
| 0x5006 | `init_sound_system`'s replacement: `SndNewChannel` (sampled synth 5, init 0x8c), primes both buffers through 0x4e1a, then `SndPlayDoubleBuffer` (`_SoundDispatch` 0x00200008) |
| 0x51fe, 0x52f4, 0x541e, 0x547c, 0x5506, 0x5568 | the replacements of `play_sample_on_channel`, `enqueue_sample_on_channel`, `set_sound_playback_rate`, `release_channel_loop`, `silence_sound_channel`, `is_channel_playing` |
| 0x55ba | `shutdown_sound`'s replacement: silences the three channels, `SndDisposeChannel` (quietNow), `DisposePtr` of its buffers |
| 0x5634 | an empty routine (`RTS`) |
| 0x5636..0x6100 | 2,762 zero bytes: the driver's variables, inside the code segment |

It uses 68020 instructions (`MULU.L`, and `RTD` from the 68010), so 1.0.4 needs a 68020 Mac; the
traps are `SndNewChannel`, `SndDisposeChannel`, `_SoundDispatch`, `NewPtrClear` and
`DisposePtr`. Running it needs the runtime's Sound Manager to provide `SndPlayDoubleBuffer`. Every routine is
reconstructed in `sources/hellcats-1-0-4/CODE/ID02.delta.decomp.c` and checked on the interpreter
against the bytes by `tools/original/check_1_0_4_sound.py` (in QA). Channels 1..3 are copied 1,850
bytes a buffer with no end check inside it, so a sound ending mid-buffer is read up to 1,850 bytes on:
the padding each of the eight replaced sounds gained, which `play_sample_on_channel` takes back off
their length. Channel 0 (the engine) alone is resampled at the playback rate.

The `CHK~` table (121 records of 12 bytes) and the date are not decoded; they decide whether the
updater accepts a file, not what it writes.

## Metadata model

Each JSON record separates:

- `resource`: original name, exact type/ID, descriptive identity and purpose;
- `evidence`: byte path, size, SHA-256, origin, fork comparison and all exports;
- `decoded`: deterministic fields, offsets, elements and unconsumed regions;
- `relationships`: proven outgoing and automatically generated reverse links;
- `code_references`: source path, line and supporting decompilation annotation;
- `claims` and `claim_evidence`: claim-level `PROVEN`, `VERIFIED`, `INFERRED`, or
  `UNKNOWN` status;
- `review`: human notes and claims preserved verbatim across regeneration;
- `unresolved`: explicit remaining questions and provenance mismatches.

Generated fields are reproducible. Human review belongs in `review`, which the
generator carries forward. Do not put reviewed conclusions inside `decoded`.

## Supported deterministic decoders

Complete field decoders cover `ALRT`, `BNDL`, `CNTL`, `CODE` segment facts,
`DITL`, `DLOG`, `DREL`, `FREF`, `HEL1` record structure, `MENU`, `SIZE`, `STR#`,
`TEXT`, `ZERO` word structure, `cicn`, `clut`, the monochrome/indexed icon
families, `snds`, `styl`, and `vers`.

Important details:

- Every DITL item includes bounds, type/flags, exact content, padding,
  placeholders and a structural or reviewed semantic identity.
- Every menu item, string, style run, bundle mapping, relocation entry, palette
  entry and sampled-sound command is an individual element. Menu entries include
  the decoded `do_menu_command` action and claim status; the Apple About target
  remains inferred while the other fixed resource items are byte-verified.
- `snds` payloads are verified classic format-1 `snd ` resources with a
  sampled-sound header, unsigned 8-bit mono data, 16.16 sample rate, loop points
  and no compression. No arbitrary PCM carving or assumed 22,050 Hz rate is used.
- All 14 `cicn` resources have parsed PixMaps, masks, monochrome bitmaps, embedded
  color tables and indexed pixels. Decoded RGB pixels match the existing PNGs
  exactly (zero differing pixels).
- `PICT` opcode streams are walked to `OpEndPic` (Inside Macintosh: Imaging With
  QuickDraw, appendix A) and consumed exactly: PICT 128 is version 2 (one
  `PackBitsRect`), PICTs 129 / 130 are version 1 `PackBitsRect` bands between
  picture comments, and PICTs 131 / 1000 are version 1 text pictures whose
  strings `inspect` lists. Rendered exports are indexed beside them, and the
  three bitmap pictures, rendered independently from their opcodes (PackBits rows,
  PICT 128's colour table), match their PNGs pixel for pixel. The two text
  pictures are not rendered, since they need the System's fonts.
- CODE records link the protected binary, Ghidra export, semantic decompilation,
  and function annotations found in that decompilation. CODE 0 has no function
  bodies: its `ID00.c` is an explicitly reconstructed structural export, and
  `ID00.decomp.c` exposes all 175 validated LoadSeg jump-table entries.

## Relationships

The generator resolves `ALRT -> DITL`, `DLOG -> DITL`, DITL picture/icon items,
and `BNDL -> FREF/ICN#`, then writes reverse references. No dangling relationship
currently exists in the curated tree. A same-ID `TEXT`/`styl` pair is not asserted
as a link without executable or format evidence.

## Commands

From the repository root:

```sh
python3 tools/resedit.py generate
python3 tools/resedit.py inventory
python3 tools/resedit.py inspect ALRT 259
python3 tools/resedit.py inspect DITL 137 --detail
python3 tools/resedit.py inspect clut 999
python3 tools/resedit.py inspect HEL1 128
python3 tools/resedit.py inspect CODE 1
python3 tools/resedit.py search mission
python3 tools/resedit.py references DITL 259
python3 tools/resedit.py unknown
python3 tools/resedit.py verify
python3 tools/qa/test_resedit.py
```

`inspect` is concise by default; `--detail` exposes offsets, bytes and evidence.
`verify` re-parses the inputs, checks metadata drift, validates every hash and
ensures every discovered item has both binary and JSON companions.

## Remaining uncertainty

(`DATA:0` is resolved: CODE 1 0x34, the C runtime's start-up, copies its words up
from CurStackBase and, after each zero word, clears as many bytes as the next word
of `ZERO:0`. Its 1459 words hold 90 zero words, one per `ZERO:0` entry, and the
expansion is exactly CODE 0's 0x225e bytes below A5. `inspect DATA 0` lists each
run with its A5 offset; `DREL:0` then relocates its pointers.)

- `MDPL:128` is mostly resolved, and no game code reads it: it is the layout
  record of PICT 131's first text object, kept by the drawing program that
  signed the picture's application comments 'MDPL'. Its directory blocks tile
  the resource. Its three Geneva styles and their runs (bold 10, plain 10,
  plain 9 from characters 0 / 26 / 38) match the picture's TxFace / TxSize
  changes, and its 31 line starts cover the 311-character text. The first 17
  match the picture's line starts and the later ones sit one character on. The
  header before 0x5c and the block at 0x136..0x182 stay uninterpreted.
- `DITL:135` is complete from the 1.0.3 disk (its ResEdit export is eight bytes
  short). No code opens it: ID05's only GetNewDialog (0x5f4) opens DLOG 1000.
- The text PICTs (131, 1000) are not rendered independently: their exports'
  glyphs depend on the System fonts.
- The indexed icons are checked through the System's default colour tables
  (clut 4 / clut 8, as the Finder draws them), not through any palette matched
  by ID. Both icl4, both icl8 and both ics4 match their PNG exports pixel for
  pixel, transparent export pixels aside. Both ics8 exports differ on the ramp
  indices (215 and up), the same indices the icl8 exports render exactly, so
  those two exports are the suspects. The Godot mirror decodes icons from
  their bytes through the same tables and does not read them.
