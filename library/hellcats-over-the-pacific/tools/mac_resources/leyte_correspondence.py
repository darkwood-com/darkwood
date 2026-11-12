#!/usr/bin/env python3
"""Where each of Hellcats 1.0.3's decompiled functions is in Leyte Gulf, as a generated file:
sources/leyte-gulf-1-0/correspondence.json (one record per function: segment, 1.0.3 address and
name, how it matched and the Leyte offset). The matching is leyte_match.py's: "identical" (the same
bytes), "moved" (the same once relocated / PC- or A5-relative operands are masked), "same_instructions"
(the same instruction sequence, other operands) or "not_found". Needs capstone (shell.nix).

    python3 tools/mac_resources/leyte_correspondence.py [--check]
"""
from __future__ import annotations

import json
import re
import struct
import sys
from pathlib import Path

import capstone

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
import hfs  # noqa: E402
from tools import resedit  # noqa: E402

OUT = ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json"
A5_PC = {0x4EAD, 0x4EBA, 0x6100, 0x41FA, 0x43FA, 0x45FA, 0x47FA, 0x49FA, 0x4BFA, 0x487A, 0x4EFA, 0x2F2D, 0x486D,
         0x41ED, 0x43ED, 0x45ED, 0x47ED, 0x49ED, 0x4BED, 0x3B6D, 0x2B6D, 0x202D, 0x222D, 0x206D, 0x226D, 0x302D,
         0x322D, 0x4A6D, 0x426D, 0x42AD, 0x3F2D, 0x1B6D, 0x4AAD, 0x2B40, 0x3B40, 0x2B41, 0x3B41, 0x2B47, 0x3B47}
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True


def masked_pattern(code: bytes, start: int, relocated: set) -> re.Pattern:
    out, i = b"", 0
    while i < len(code):
        if start + i in relocated:
            out += b"\x00\x00"
            i += 2
            continue
        w = int.from_bytes(code[i:i + 2], "big")
        out += re.escape(code[i:i + 2])
        i += 2
        if w in A5_PC and i + 2 <= len(code) and start + i not in relocated:
            out += b".."
            i += 2
    return re.compile(out, re.S)


def stream(code: bytes) -> tuple[str, list]:
    """The mnemonic sequence as one string, and for each instruction its string index and address."""
    text, marks = "", []
    for ins in MD.disasm(code, 0):
        marks.append((len(text), ins.address))
        text += ins.mnemonic + " "
    return text, marks


def match(functions: list, old: bytes, new: bytes, relocated_old: set, relocated_new: set, segment: str) -> list:
    new_masked = bytes(0 if i in relocated_new else b for i, b in enumerate(new))
    text, marks = stream(new)
    index = {k: a for k, a in marks}
    rows = []
    for k, (addr, name) in enumerate(functions):
        end = functions[k + 1][0] if k + 1 < len(functions) else len(old)
        code = old[addr:end]
        row = {"segment": segment, "address": f"0x{addr:x}", "name": name, "match": "not_found", "leyte": None}
        if len(code) >= 6:
            at = new.find(code)
            if at >= 0:
                row.update(match="identical", leyte=f"0x{at:x}")
            else:
                m = masked_pattern(code, addr, relocated_old).search(new_masked)
                if m:
                    row.update(match="moved", leyte=f"0x{m.start():x}")
                else:
                    seq = stream(code)[0]
                    at = text.find(seq) if seq else -1
                    if at >= 0 and at in index:
                        row.update(match="same_instructions", leyte=f"0x{index[at]:x}")
        rows.append(row)
    return rows


def overlay(img: bytes) -> tuple[bytes, set]:
    code_size, data_size = struct.unpack(">II", img[4:12])
    first = struct.unpack(">I", img[24:28])[0] // 8
    at = 0x20 + code_size + data_size
    relocated = set()
    for i in range(first):
        off, flags = struct.unpack(">II", img[at + 8 * i:at + 8 * i + 8])
        if not (flags >> 7) & 1:
            relocated.update(range(off, off + 4))
    return img[0x20:0x20 + code_size], relocated


def build() -> dict:
    ley = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf")
    code = {(e["type"], e["id"]): e["payload"] for e in resedit.parse_fork(ley.rsrc)}
    inv = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text())["functions"]
    rows = []
    for n in range(1, 10):
        seg = f"ID0{n}"
        fs = sorted((int(f["original_address"], 16), f["semantic_name"] or f["original_function"]) for f in inv if f["segment"] == seg)
        rows += match(fs, (ROOT / "sources" / "resedit-1-0-3" / "CODE" / f"{seg}.bin").read_bytes(), code[("CODE", n)], set(), set(), seg)
    pcode, prel = overlay((ROOT / "sources" / "mac-os-9" / "Pacific Conflict").read_bytes())
    lcode, lrel = overlay(ley.data)
    fs = sorted((int(f["original_address"], 16), f["semantic_name"] or f["original_function"]) for f in inv if f["segment"] == "Pacific Conflict")
    rows += match(fs, pcode, lcode, prel, lrel, "Pacific Conflict -> Leyte overlay")
    counts = {}
    for r in rows:
        key = "host" if r["segment"].startswith("ID") else "overlay"
        counts.setdefault(key, {}).setdefault(r["match"], 0)
        counts[key][r["match"]] += 1
    return {"schema": "hellcats-leyte-correspondence-v1", "generated_by": "tools/mac_resources/leyte_correspondence.py",
            "leyte": "sources/mac-os-9/LeyteGulf.dsk Leyte Gulf:Leyte Gulf", "counts": counts, "functions": rows}


def main() -> int:
    text = json.dumps(build(), indent=1) + "\n"
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + f" {OUT.relative_to(ROOT)}")
        return 0 if ok else 1
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(text)
    print(json.dumps(json.loads(text)["counts"]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
