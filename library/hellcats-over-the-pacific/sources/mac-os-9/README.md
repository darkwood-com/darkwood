# Original Macintosh media

The files here are the project's original evidence and are never modified.
Read the floppies with `tools/mac_resources/hfs.py` (read-only, no
dependencies). `checksum.md5` is the archive's own MD5 list for the three disks.

| File | SHA-256 | Contents |
|---|---|---|
| `Hellcats_1_0_3.dsk` | `59791cca…5cf5d4` | 800K HFS volume "Hellcats": the **1.0.3** application `Hellcats:Hellcats:Hellcats` (APPL/HELL, resource fork 406,544 bytes, no data fork), `Pacific Conflict` (HELL/HELL, 255,360 bytes), README, Gravis MouseStick files. The authoritative source of `sources/resedit-1-0-3/` (`tools/resedit.py`). |
| `Version_1_0_4.dsk` | `7e888dfb…c659` | Volume "Update to v1.0.4": the updater `Hellcats 1.0.3 --> 1.0.4` (APPL/PATa, data 286, resource fork 142,434). It carries whole replacement resources under type names XORed with 0x01 (`BNED` CODE, `roe!` snd, `RH[D` SIZE, `wdsr` vers, `IDM0` HEL1) and a `CHK~` table. |
| `LeyteGulf.dsk` | `3b0a2f54…97ab` | Volume "Leyte Gulf": the separate application `Leyte Gulf` (APPL/HEL1, "Hellcats: Leyte, version 1.0"; data 315,530, resource fork 460,385). Its data fork holds its own overlay. |
| `Hellcats_1_0_4.appledouble` | `a36f245a…f956` | AppleDouble of a played **1.0.4** application (APPL/HELL, resource fork 718,900 bytes, with written HEL1 settings and HEL2 caches). Recorded as `version_1_0_4` in the resource metadata. Provenance unknown. |
| `Hellcats_Over_the_Pacific.SIT` | `e8eac100…7c38` | StuffIt 5 archive (2014 download): `Hellcats` (APPL/HELL, resource fork 1,183,400 bytes), `Pacific Conflict` (255,360) and `Commands and Help`. **Not yet decompressed**: its application is a third, larger copy whose version is unknown. |
| `Pacific Conflict` | `ee6040c7…5030` | The scenery overlay, identical to the file on `Hellcats_1_0_3.dsk`. |
| `Commands and Help` | `fc75ab13…2c2a` | The key list (the same length as the SIT member's data fork). |
| `docs/` | | Manuals, maps and packages for Hellcats and Leyte Gulf, the FAQ and `hacks.txt`. |

## Removed copies

These were removed after verification (they remain in Git history):

- `../hellcats-over-the-pacific.zip`: a 2014 wrapper holding
  `Hellcats_Over_the_Pacific.SIT` (kept above, byte-identical, CRC `f56f0133`)
  and `hellcatsoverthepacific.zip`, whose 14 members are byte-identical to
  the three disks, `checksum.md5` and `docs/` here.
- `Hellcats_1_0_3_to_1_0_4_updated.zip`: the updater as AppleDouble, with the
  same data fork, resource fork and type/creator as on `Version_1_0_4.dsk`.
- `Hellcats`, `Hellcats.bin`: empty files.
- `Hellcats.rsrc`: 121,559 bytes that are not a resource fork (its header
  words are `0xc8c8c8c8`), most likely a failed extraction of the SIT's
  application.
