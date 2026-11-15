#!/usr/bin/env python3
"""Leyte Gulf's A5 world against Hellcats 1.0.3's, from binary evidence: sources/leyte-gulf-1-0/a5_map.json.

The host segments' correspondence (correspondence.json: CODE 1..9 functions found in Leyte's CODE
"identical" or "moved", the A5- / PC-relative displacements masked) pairs each such function with
its Leyte copy; the instructions are the same, so an A5-relative operand at the same position names
the same global (negative offsets, the A5 world) or jump-table slot (positive, above A5) in both.
Functions whose Leyte offset several 1.0.3 functions were matched to are left out (the matcher reports
the first copy of equal code: which is which is not proven). An offset paired with one Leyte offset
is mapped ("same" when equal, "relocated" otherwise) with the functions it rests on; one paired with
several is a conflict, mapped nowhere. Offsets the host code uses that no pair reaches are counted
as unresolved. Needs capstone (shell.nix).

    python3 tools/leyte/a5_map.py [--check]
"""
from __future__ import annotations

import collections
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

OUT = ROOT / "sources" / "leyte-gulf-1-0" / "a5_map.json"
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True        # go on past inline data (switch tables): else the comparison stops there unseen
A5_OPERAND = re.compile(r"(-?)\$([0-9a-f]+)\(a5\)")


def a5_operands(ins) -> list:
    return [(-1 if sign else 1) * int(v, 16) for sign, v in A5_OPERAND.findall(ins.op_str)]


def build() -> dict:
    ley = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf")
    lcode = {e["id"]: e["payload"] for e in resedit.parse_fork(ley.rsrc) if e["type"] == "CODE"}
    corr = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
    inv = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text())["functions"]

    pairs = collections.defaultdict(lambda: collections.defaultdict(set))
    used_offsets = set()
    counts = collections.Counter()
    for n in range(1, 10):
        seg = "ID%02d" % n
        hcode = (ROOT / "sources" / "resedit-1-0-3" / "CODE" / ("%s.bin" % seg)).read_bytes()
        starts = sorted(int(f["original_address"], 16) for f in inv if f["segment"] == seg)
        for ins in MD.disasm(hcode, 0):
            used_offsets.update(a5_operands(ins))
        rows = [r for r in corr if r["segment"] == seg and r["match"] in ("identical", "moved")]
        claims = collections.Counter(r["leyte"] for r in rows)
        for r in rows:
            if claims[r["leyte"]] > 1:
                counts["left out (ambiguous Leyte copy)"] += 1
                continue
            h, l = int(r["address"], 16), int(r["leyte"], 16)
            k = starts.index(h)
            end = starts[k + 1] if k + 1 < len(starts) else len(hcode)
            his = list(MD.disasm(hcode[h:end], h))
            lis = list(MD.disasm(lcode[n][l:l + (end - h)], l))
            counts[r["match"]] += 1
            for a, b in zip(his, lis):
                if a.mnemonic != b.mnemonic:
                    break
                for hv, lv in zip(a5_operands(a), a5_operands(b)):
                    pairs[hv][lv].add("%s:%s" % (seg, r["name"]))

    mapped, conflicts = [], []
    for hv in sorted(pairs):
        found = pairs[hv]
        row = {"hellcats_1_0_3": hv, "kind": "global" if hv < 0 else "jump_table"}
        if len(found) == 1:
            (lv, fns), = found.items()
            mapped.append(dict(row, leyte=lv, status="same" if lv == hv else "relocated", evidence=sorted(fns)))
        else:
            conflicts.append(dict(row, leyte=sorted(found), evidence={str(k): sorted(v) for k, v in sorted(found.items())}))
    unresolved = sorted(o for o in used_offsets if o not in pairs)
    leyte_size = struct.unpack(">I", lcode[0][4:8])[0]
    hellcats_size = struct.unpack(">I", (ROOT / "sources" / "resedit-1-0-3" / "CODE" / "ID00.bin").read_bytes()[4:8])[0]
    return {"schema": 1, "generated_by": "tools/leyte/a5_map.py",
            "a5_world": {"hellcats_1_0_3": hellcats_size, "leyte": leyte_size},
            "counts": {"functions": dict(sorted(counts.items())), "mapped": len(mapped),
                       "mapped_globals": sum(1 for m in mapped if m["kind"] == "global"),
                       "mapped_jump_table": sum(1 for m in mapped if m["kind"] == "jump_table"),
                       "conflicts": len(conflicts), "unresolved_used_by_1_0_3_host": len(unresolved)},
            "mapped": mapped, "conflicts": conflicts, "unresolved": unresolved}


def main() -> int:
    text = json.dumps(build(), indent=1) + "\n"
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte A5 map: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(text)
    print("wrote %s %s" % (OUT.relative_to(ROOT), json.loads(text)["counts"]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
