#!/usr/bin/env python3
"""Leyte Gulf's overlay addresses against Hellcats 1.0.3's: sources/leyte-gulf-1-0/overlay_map.json,
the overlay's counterpart of a5_map.json. Both overlays are a.out images whose code carries relocation
records naming every long that holds an image address. In a function pair the correspondence calls
"identical" or "moved" (correspondence.json: the same bytes once relocated longs and PC- / A5-relative
operands are masked), each relocated long of 1.0.3's function sits at the same place in Leyte's, which
must be relocated too: the two values are the same address in each image (image offset: file offset
- 0x20, the 1.0.3 port's runtime addresses). Every pair is recorded with the functions it rests on;
an address paired two ways is a conflict, left out of the map. Needs the disk images (sources/mac-os-9).

A "same_instructions" pair (the same instruction sequence, other operands) adds pairs aligned by
instruction rather than by byte: both functions disassembled (capstone, 68020, past inline data), the
same count and mnemonics required, and in each instruction pair the relocated longs at the same offset
inside the instruction paired. A pair that fails the alignment adds nothing. In every aligned pair (identical and moved ones too), a
PC-relative operand's target (capstone gives it as the image address) pairs the same way: tables and
routines the code reaches without a relocation. Needs capstone (shell.nix).

    python3 tools/leyte/overlay_map.py [--check]
"""
from __future__ import annotations

import json
import struct

import capstone
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
import hfs  # noqa: E402

OUT = ROOT / "sources" / "leyte-gulf-1-0" / "overlay_map.json"
PC_REL = __import__("re").compile(r"\$([0-9a-f]+)\(pc")
HEADER = 0x20


def _image(img: bytes):
    code_size, data_size, bss_size = struct.unpack(">III", img[4:16])
    first = struct.unpack(">I", img[24:28])[0] // 8
    at = HEADER + code_size + data_size
    relocated = set()
    for i in range(first):
        off, flags = struct.unpack(">II", img[at + 8 * i:at + 8 * i + 8])
        if not (flags >> 7) & 1:
            relocated.add(off)
    return img[HEADER:HEADER + code_size], relocated, code_size, data_size, bss_size


def build() -> dict:
    hcode, hrel, hsize, hdata, hbss = _image((ROOT / "sources" / "mac-os-9" / "Pacific Conflict").read_bytes())
    lcode, lrel, lsize, ldata, lbss = _image(hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data)
    inv = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text())["functions"]
    starts = sorted(int(f["original_address"], 16) - HEADER for f in inv if f["segment"] == "Pacific Conflict")
    rows = [r for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
            if r["segment"].startswith("Pacific") and r["match"] in ("identical", "moved")]
    # A Leyte offset two correspondence rows claim (similar small functions placed on one routine: eight
    # register_*_template wrappers on the overlay's 0x1370c, sin / cos_fixed14 on 0x23c4) is no
    # evidence for any of them.
    claimed: dict = {}
    for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]:
        if r["segment"].startswith("Pacific") and r["leyte"]:
            claimed.setdefault(r["leyte"], []).append(r["name"])
    collided = {nm for names in claimed.values() if len(names) > 1 for nm in names}
    rows = [r for r in rows if r["name"] not in collided]
    pairs: dict = {}
    for r in rows:
        h0 = int(r["address"], 16) - HEADER
        l0 = int(r["leyte"], 16) - HEADER
        later = [s for s in starts if s > h0]
        h1 = later[0] if later else hsize
        for off in range(h0, h1):
            if off not in hrel:
                continue
            lo = l0 + (off - h0)
            if lo not in lrel:
                continue
            hv = struct.unpack(">I", hcode[off:off + 4])[0]
            lv = struct.unpack(">I", lcode[lo:lo + 4])[0]
            pairs.setdefault(hv, {}).setdefault(lv, set()).add(r["name"])
    md = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
    md.skipdata = True
    same = [r for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
            if r["segment"].startswith("Pacific") and r["match"] in ("same_instructions", "moved", "identical") and r["name"] not in collided]
    aligned = 0
    for r in same:
        h0 = int(r["address"], 16) - HEADER
        l0 = int(r["leyte"], 16) - HEADER
        later = [s for s in starts if s > h0]
        h1 = later[0] if later else hsize
        his = list(md.disasm(hcode[h0:h1], h0))
        lis = list(md.disasm(lcode[l0:l0 + (h1 - h0) + 0x400], l0))[:len(his)]
        if len(lis) != len(his) or any(a.mnemonic != b.mnemonic for a, b in zip(his, lis)):
            continue
        aligned += 1
        for a, b in zip(his, lis):
            pa, pb = PC_REL.findall(a.op_str), PC_REL.findall(b.op_str)
            if len(pa) == len(pb) and not (a.mnemonic.startswith(("b", "db")) and a.mnemonic not in ("bset", "bclr", "btst", "bchg")):
                for x, y in zip(pa, pb):
                    pairs.setdefault(int(x, 16), {}).setdefault(int(y, 16), set()).add(r["name"])
            hs = [k for k in range(a.size) if a.address + k in hrel]
            ls = [k for k in range(b.size) if b.address + k in lrel]
            if hs != ls:
                continue
            for k in hs:
                hv = struct.unpack(">I", hcode[a.address + k:a.address + k + 4])[0]
                lv = struct.unpack(">I", lcode[b.address + k:b.address + k + 4])[0]
                pairs.setdefault(hv, {}).setdefault(lv, set()).add(r["name"])
    mapped, conflicts = [], []
    for hv in sorted(pairs):
        by = pairs[hv]
        kind = "text" if hv < hsize else ("data" if hv < hsize + hdata else "bss")
        if len(by) == 1:
            lv = next(iter(by))
            mapped.append({"hellcats_1_0_3": "0x%x" % hv, "leyte": "0x%x" % lv, "kind": kind, "evidence": sorted(by[lv])})
        else:
            conflicts.append({"hellcats_1_0_3": "0x%x" % hv, "leyte": {"0x%x" % lv: sorted(f) for lv, f in sorted(by.items())}})
    counts = {"pairs_from_functions": len(rows), "functions_aligned_by_instruction": aligned,
              "collided_rows_left_out": len(collided),
              "mapped": len(mapped), "conflicts": len(conflicts),
              "by_kind": {k: sum(1 for m in mapped if m["kind"] == k) for k in ("text", "data", "bss")}}
    return {"schema": 1, "generated_by": "tools/leyte/overlay_map.py",
            "images": {"hellcats_1_0_3": {"text": hsize, "data": hdata, "bss": hbss}, "leyte": {"text": lsize, "data": ldata, "bss": lbss}},
            "counts": counts, "mapped": mapped, "conflicts": conflicts}


def main() -> int:
    text = json.dumps(build(), indent=1) + "\n"
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte overlay map: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(text)
    print(json.loads(text)["counts"])
    return 0


if __name__ == "__main__":
    sys.exit(main())
