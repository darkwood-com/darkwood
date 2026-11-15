#!/usr/bin/env python3
"""Semantic names for Leyte Gulf overlay functions where the binary evidence allows it. Writes
CODE/Leyte Gulf overlay.names.json; --check compares. Needs capstone (shell.nix).

1. Code identity (correspondence.json): a Leyte function (Ghidra export, CODE/Leyte Gulf
   overlay.functions.json) gets the name of the Hellcats 1.0.3 function found there byte for byte
   ("identical") or with only relocated operands moved ("moved"): the same code. Instruction-shape
   matches ("same_instructions") are candidates, unnamed, until their operand changes are explained.
   An offset several 1.0.3 functions were found at (functions equal in 1.0.3, or equal but for
   relocated operands: the matcher reports the first Leyte copy for each) is "ambiguous".
2. Call sites: in a named pair the code is the same, so a PC-relative call (bsr / jsr / pea / lea
   d16(pc)) at the same offset reaches the same function on both sides: the Hellcats target names
   the Leyte one, when every such call agrees on one Leyte function. A target whose code also
   matches the Hellcats function there (masked as the matcher masks) is named ("identical" /
   "moved" plus "call site"): this resolves ambiguous offsets (which equal copy is which). One whose
   code differs is "called": the role is proven by the call, the code is not the same. Repeated
   until no name is added (a newly named function's calls count too).

3. Structure layout: a candidate whose instructions all match the Hellcats function's once
   addresses, PC-relative displacements and branch targets are ignored and register-relative
   displacements (structure field offsets, d16(An) / (An)) may differ is named with evidence
   "layout": same instructions, registers, addressing and immediates, only fields and globals moved
   (Leyte's structures are re-laid out: the host world, the objects); an immediate counts as layout
   when a relocated long lies in the instruction in both binaries (an address the loader relocates:
   a code or data pointer, not a constant). Any other difference (an
   immediate, a frame or allocation size, a register) keeps it a candidate, the first differences
   listed. Layout-named functions take no part in steps 2's call pairing nor in the relocation
   pairing of semantic_overlay.py (their byte offsets can shift).

Every other function is Leyte's own (new or rewritten).
"""
import re
import json
import struct
import sys
from pathlib import Path

import capstone

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
sys.path.insert(0, str(ROOT / "tools" / "leyte"))
import leyte_correspondence as matcher  # noqa: E402
from semantic_overlay import HEADER, HELLCATS, overlay  # noqa: E402

BASE = ROOT / "sources" / "leyte-gulf-1-0"
OUT = BASE / "CODE" / "Leyte Gulf overlay.names.json"
PC_RELATIVE = {0x6100, 0x4EBA, 0x487A, 0x41FA, 0x43FA, 0x45FA, 0x47FA, 0x49FA, 0x4BFA}   # bsr.w, jsr / pea / lea d16(pc)
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True        # go on past inline data (switch tables): else the comparison stops there unseen


_BRANCH = re.compile(r"^(b|db|j)")
_BIT_OPS = {"bset", "bclr", "btst", "bchg"}


def _operands(mnemonic: str, ops: str) -> str:
    """An instruction's operands with what layout moves masked: branch / call targets, PC-relative
    displacements, absolute long addresses, register-relative displacements ((An) as $0(An))."""
    if _BRANCH.match(mnemonic) and mnemonic not in _BIT_OPS and not mnemonic.startswith("bf"):
        ops = re.sub(r"\$[0-9a-f]+", "T", ops)
    ops = re.sub(r"-?\$[0-9a-f]+\(pc", "(pc", ops)
    ops = re.sub(r"\$[0-9a-f]+\.l", "ABS", ops)
    ops = re.sub(r"(?<![\w)$-])\((a[0-7]|sp)\)(?!\+)", r"$0(\1)", ops)
    return re.sub(r"-?\$[0-9a-f]+(?=\((?:a[0-7]|sp)[,)])", "D", ops)


def _relocated(ins, relocs: set) -> bool:
    """Whether a relocated long (its file offset in `relocs`) starts inside the instruction: its
    immediate is then an address the loader relocates, not a constant."""
    return any(at in relocs for at in range(ins.address, ins.address + ins.size))


def layout_differences(md, hcode: bytes, h0: int, lcode: bytes, l0: int, hrel: set = frozenset(), lrel: set = frozenset()):
    """None when the instruction counts differ; else the instructions that differ beyond layout. An
    immediate is layout too when a relocated long lies in the instruction on both sides (hrel / lrel:
    the relocations' file offsets)."""
    hi, li = list(md.disasm(hcode, h0)), list(md.disasm(lcode, l0))
    if len(hi) != len(li):
        return None
    out = []
    for a, b in zip(hi, li):
        ha, lb = _operands(a.mnemonic, a.op_str), _operands(b.mnemonic, b.op_str)
        if ha != lb and _relocated(a, hrel) and _relocated(b, lrel):
            ha, lb = re.sub(r"#\$[0-9a-f]+", "#R", ha), re.sub(r"#\$[0-9a-f]+", "#R", lb)
        if a.mnemonic != b.mnemonic or ha != lb:
            out.append("%s %s | %s" % (a.mnemonic, a.op_str, b.op_str))
    return out


def call_pairs(named: list, hfile: bytes, lfile: bytes, hnames: dict, sizes: dict) -> dict:
    """Hellcats function address -> {Leyte address: [caller names]} over the named pairs' calls."""
    out = {}
    for f in named:
        h0, l0 = int(f["hellcats_1_0_3"], 16), int(f["leyte"], 16)
        for ins in MD.disasm(hfile[h0:h0 + sizes[f["ghidra"]]], h0):
            w = int.from_bytes(hfile[ins.address:ins.address + 2], "big")
            if w not in PC_RELATIVE or ins.size != 4:
                continue
            off = ins.address - h0
            assert int.from_bytes(lfile[l0 + off:l0 + off + 2], "big") == w, "%s +0x%x" % (f["name"], off)
            ht = ins.address + 2 + struct.unpack(">h", hfile[ins.address + 2:ins.address + 4])[0]
            lt = l0 + off + 2 + struct.unpack(">h", lfile[l0 + off + 2:l0 + off + 4])[0]
            if ht in hnames:
                out.setdefault(ht, {}).setdefault(lt, set()).add(f["name"])
    return out


def build() -> dict:
    index = json.loads((BASE / "CODE" / "Leyte Gulf overlay.functions.json").read_text())
    corr = [f for f in json.loads((BASE / "correspondence.json").read_text())["functions"]
            if f["segment"].startswith("Pacific Conflict") and f["leyte"]]
    inv = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text())["functions"]
    hnames = {int(f["original_address"], 16): f["semantic_name"] or f["original_function"]
              for f in inv if f["segment"] == "Pacific Conflict"}
    hstarts = sorted(hnames)
    hfile, lfile = HELLCATS.read_bytes(), (BASE / "Leyte Gulf overlay").read_bytes()
    hcode, hrel = overlay(hfile)
    lcode, lrel = overlay(lfile)
    hrel4 = {o + i for o in hrel for i in range(4)}
    lmasked = bytes(0 if any(o - i in lrel for i in range(4)) else b for o, b in enumerate(lcode))
    sizes = {f["name"]: f["size"] for f in index}

    def hsize(h: int) -> int:
        k = hstarts.index(h)
        return (hstarts[k + 1] if k + 1 < len(hstarts) else len(hcode) + HEADER) - h

    def same_code(h: int, l: int):
        """"identical" / "moved" when the Hellcats function's code is at Leyte offset l, else None."""
        code = hcode[h - HEADER:h - HEADER + hsize(h)]
        if lcode[l - HEADER:l - HEADER + len(code)] == code:
            return "identical"
        return "moved" if matcher.masked_pattern(code, h - HEADER, hrel4).match(lmasked, l - HEADER) else None

    by_offset = {}
    for f in corr:
        by_offset.setdefault(int(f["leyte"], 16), []).append(f)
    state = {}       # Leyte entry -> row
    for f in index:
        e = int(f["entry"], 16)
        claims = by_offset.get(e, [])
        row = {"leyte": f["entry"], "ghidra": f["name"]}
        if len(claims) > 1:
            row.update(kind="ambiguous", claims=[{"name": x["name"], "hellcats_1_0_3": x["address"], "evidence": x["match"]}
                                                 for x in claims])
        elif claims and claims[0]["match"] in ("identical", "moved"):
            row.update(kind="named", name=claims[0]["name"], hellcats_1_0_3=claims[0]["address"], evidence=claims[0]["match"])
        elif claims:
            row.update(kind="candidate", candidate=claims[0]["name"], hellcats_1_0_3=claims[0]["address"], evidence=claims[0]["match"])
        else:
            row.update(kind="own")
        state[e] = row

    def drop_claim(name: str) -> None:
        """A Hellcats name placed elsewhere leaves the ambiguous offsets that also claimed it."""
        for e, row in state.items():
            if row["kind"] == "ambiguous":
                row["claims"] = [c for c in row["claims"] if c["name"] != name]
                if len(row["claims"]) == 1:
                    c = row.pop("claims")[0]
                    if c["evidence"] in ("identical", "moved"):
                        row.update(kind="named", name=c["name"], hellcats_1_0_3=c["hellcats_1_0_3"], evidence=c["evidence"])
                    else:
                        row.update(kind="candidate", candidate=c["name"], hellcats_1_0_3=c["hellcats_1_0_3"], evidence=c["evidence"])

    while True:
        named = [r for r in state.values() if r["kind"] == "named" and not r["evidence"].startswith("layout")]
        placed = {r["name"] for r in state.values() if r["kind"] in ("named", "called")}
        added = False
        for h, targets in sorted(call_pairs(named, hfile, lfile, hnames, sizes).items()):
            name = hnames[h]
            if name in placed or len(targets) != 1:
                continue
            (l, callers), = targets.items()
            row = state.get(l)
            if row is None or row["kind"] in ("named", "called"):
                continue
            via = "call site in %s" % ", ".join(sorted(callers))
            code = same_code(h, l)
            for key in ("claims", "candidate", "evidence", "hellcats_1_0_3"):
                row.pop(key, None)
            if code:
                row.update(kind="named", name=name, hellcats_1_0_3="0x%x" % h, evidence="%s; %s" % (code, via))
            else:
                row.update(kind="called", name=name, hellcats_1_0_3="0x%x" % h, evidence=via)
            drop_claim(name)
            added = True
            break      # recompute from the new name
        if not added:
            break

    for e in sorted(state):
        row = state[e]
        if row["kind"] != "candidate":
            continue
        h = int(row["hellcats_1_0_3"], 16)
        diffs = layout_differences(MD, hfile[h:h + hsize(h)], h, lfile[e:e + sizes[row["ghidra"]]], e,
                                   {o + HEADER for o in hrel}, {o + HEADER for o in lrel})
        if diffs is None:
            row["differences"] = ["instruction count differs"]
        elif diffs:
            row["differences"] = diffs[:3]
        else:
            name = row.pop("candidate")
            row.update(kind="named", name=name, evidence="layout: same instructions, structure offsets / addresses moved")

    groups = {k: [] for k in ("named", "called", "candidate", "ambiguous", "own")}
    for e in sorted(state):
        row = dict(state[e])
        groups[row.pop("kind")].append(row)
    return {"schema": 2, "generated_by": "tools/leyte/names_from_correspondence.py",
            "counts": {"functions": len(index), "named": len(groups["named"]), "called": len(groups["called"]),
                       "candidates": len(groups["candidate"]), "ambiguous": len(groups["ambiguous"]),
                       "leyte_own": len(groups["own"])},
            "named": groups["named"], "called": groups["called"], "candidates": groups["candidate"],
            "ambiguous": groups["ambiguous"], "leyte_own": groups["own"]}


def main() -> int:
    text = json.dumps(build(), indent=1) + "\n"
    if "--check" in sys.argv:
        if not OUT.exists() or OUT.read_text() != text:
            print("FAIL Leyte names: %s is not what the export and correspondence give" % OUT.relative_to(ROOT))
            return 1
        print("PASS Leyte names")
        return 0
    OUT.write_text(text)
    print("wrote %s %s" % (OUT.relative_to(ROOT), json.loads(text)["counts"]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
