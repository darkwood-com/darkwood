#!/usr/bin/env python3
"""Where each routine of Hellcats 1.0.3's overlay is in Leyte Gulf's: sources/leyte-gulf-1-0/
overlay_placement.json. correspondence.json places a routine only when its whole body matches; this
follows the code instead. From routine pairs already known, the two instruction streams are aligned
(difflib over the instructions with their numbers masked, branches by mnemonic) and, in the runs that
match, the operands at the same place vote for address pairs: direct call targets (bsr / jsr), relocated
longs (the a.out relocation records of each overlay) and PC-relative operands of non-branches. A pair is
accepted when it wins its votes both ways (twice the runner-up, from 1.0.3's side and from Leyte's) and
contradicts nothing accepted before; a pair of routine entries is aligned in turn, until nothing new
comes. What is left is tried by similarity (the same alignment's ratio against Leyte's unpaired
routines: 0.6 or more and 0.1 ahead of the next, for both sides), and the votes run again.

Seeds, none of them resting on the generated port (gen_overlay_port.py reads this file):
- overlay_map.json's text pairs (the hooks the overlay stores in the host world, calls of functions the
  correspondence aligns byte for byte);
- correspondence.json rows whose Leyte routine no other row claims and whose alignment is whole (every
  instruction in one matching run);
- the routines whose hand-written emulator cases run them under their 1.0.3 names, at the Leyte addresses
  those cases use (gen_leyte_overlay_reconstructed_cases.ROUTINES): placed when they were written from
  Leyte's bytes, and checked against Leyte's own code ever since, whether the port's function is still
  the hand-written one or the generated one that superseded it.

A placement says where to look, not that the code is the same: gen_overlay_port.py still proves a
function instruction by instruction at the place given, and a routine that differs is ported by hand
from the alignment (instructions, matching count). Routine starts are the inventory's (1.0.3) and
Ghidra's (Leyte) without the false entries inside a routine (an entry before the previous routine's
last return), 1.0.3's kept where the port has a function. Addresses are image addresses (file - 0x20),
as in overlay_map.json. Needs capstone (shell.nix) and the disk images (sources/mac-os-9).

    python3 tools/leyte/overlay_placement.py [--check]
"""
from __future__ import annotations

import collections
import difflib
import json
import re
import struct
import sys
from pathlib import Path

import capstone

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
sys.path.insert(0, str(ROOT / "tools" / "leyte"))
import hfs  # noqa: E402

OUT = ROOT / "sources" / "leyte-gulf-1-0" / "overlay_placement.json"
HEADER = 0x20
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True
PC_REL = re.compile(r"\$([0-9a-f]+)\(pc")
NOT_BRANCH = ("bset", "bclr", "btst", "bchg", "bf", "bkpt")
FUNCTION = re.compile(r"^(?:static )?func (\w+)\(", re.M)


def _image(img: bytes):
    """Text and data, the offsets of the relocated longs, and the text size."""
    code_size, data_size, _bss = struct.unpack(">III", img[4:16])
    first = struct.unpack(">I", img[24:28])[0] // 8
    at = HEADER + code_size + data_size
    relocated = set()
    for i in range(first):
        off, flags = struct.unpack(">II", img[at + 8 * i:at + 8 * i + 8])
        if not (flags >> 7) & 1:
            relocated.add(off)
    return img[HEADER:HEADER + code_size + data_size], relocated, code_size


def _is_branch(mnemonic: str) -> bool:
    return mnemonic.startswith(("b", "db")) and not mnemonic.startswith(NOT_BRANCH)


def _natural_end(mem: bytes, text: int, at: int):
    """The address after the first rts / jmp / bra no earlier forward branch passes (None: not found)."""
    far = at
    for a in MD.disasm(mem[at:min(text, at + 0x4000)], at):
        m = a.mnemonic
        if _is_branch(m) and not m.startswith("bsr"):
            t = re.search(r"\$([0-9a-f]+)$", a.op_str)
            if t:
                far = max(far, int(t.group(1), 16))
        if (m in ("rts", "rte", "rtd", "rtr") or m.startswith(("jmp", "bra"))) and a.address + a.size > far:
            return a.address + a.size
    return None


def _routines(mem: bytes, text: int, entries, always=()) -> list:
    out, end = [], 0
    for e in sorted(entries):
        if e < end and e not in always:
            continue
        n = _natural_end(mem, text, e)
        out.append(e)
        end = n if n is not None else e + 2
    return out


def _key(i) -> str:
    if _is_branch(i.mnemonic):
        return i.mnemonic.split(".")[0]
    return i.mnemonic + " " + re.sub(r"-?\$[0-9a-f]+", "#", i.op_str)


def build() -> dict:
    hmem, hrel, htext = _image((ROOT / "sources" / "mac-os-9" / "Pacific Conflict").read_bytes())
    lmem, lrel, ltext = _image(hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data)
    inv = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text())["functions"]
    hnames = {int(f["original_address"], 16) - HEADER: f["semantic_name"] for f in inv if f["segment"] == "Pacific Conflict"}
    ported = set(FUNCTION.findall((ROOT / "godot" / "original" / "port" / "pacific_conflict.gd").read_text()))
    hstarts = _routines(hmem, htext, hnames, {a for a, n in hnames.items() if n in ported})
    lfun = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "Leyte Gulf overlay.functions.json").read_text())
    lstarts = _routines(lmem, ltext, [int(f["entry"], 16) - HEADER for f in lfun])
    corr = [r for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
            if r["segment"].startswith("Pacific")]
    omap = {int(m["hellcats_1_0_3"], 16): int(m["leyte"], 16)
            for m in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_map.json").read_text())["mapped"]}

    pair, evidence = {}, {}                 # 1.0.3 routine entry -> Leyte's; how it was placed
    address = dict(omap)                    # every address pair accepted (text, data, bss)
    votes_of = {}
    hcache, lcache = {}, {}

    def body(mem, text, starts, at):
        later = [s for s in starts if s > at]
        stop = later[0] if later else text
        end = _natural_end(mem, text, at)
        if end is not None and at < end < stop:
            stop = end
        return list(MD.disasm(mem[at:stop], at))

    def hbody(a):
        if a not in hcache:
            hcache[a] = body(hmem, htext, hstarts, a)
        return hcache[a]

    def lbody(a):
        if a not in lcache:
            lcache[a] = body(lmem, ltext, sorted(set(lstarts) | set(pair.values())), a)
        return lcache[a]

    def align(h, l):
        a, b = hbody(h), lbody(l)
        return a, b, difflib.SequenceMatcher(None, [_key(i) for i in a], [_key(i) for i in b], autojunk=False)

    def targets(i, rel, mem):
        out = []
        if i.mnemonic.startswith(("bsr", "jsr")):
            t = re.match(r"^\$([0-9a-f]+)(\.[wl])?$", i.op_str)
            if t:
                out.append(("call", int(t.group(1), 16)))
        for k in range(i.size):
            if i.address + k in rel:
                out.append(("long", struct.unpack(">I", mem[i.address + k:i.address + k + 4])[0]))
        if not _is_branch(i.mnemonic):
            out += [("pc", int(x, 16)) for x in PC_REL.findall(i.op_str)]
        return out

    def place(h, l, how):
        pair[h] = l
        evidence[h] = how
        address.setdefault(h, l)
        lcache.clear()

    # Seeds.
    for h, l in sorted(omap.items()):
        if h in hnames and h < htext and l < ltext and h in hstarts:
            place(h, l, "map")
    claimed = collections.Counter(r["leyte"] for r in corr if r["leyte"])
    whole = []
    for r in corr:
        h = int(r["address"], 16) - HEADER
        if not r["leyte"] or claimed[r["leyte"]] > 1 or h in pair or h not in hstarts:
            continue
        l = int(r["leyte"], 16) - HEADER
        a, b, sm = align(h, l)
        if a and len(a) == len(b) and sm.ratio() == 1.0:
            whole.append((h, l))
    for h, l in whole:
        if l not in pair.values() and address.get(h, l) == l:
            place(h, l, "correspondence")
    import gen_leyte_overlay_reconstructed_cases as reconstructed_cases
    haddr = {n: a for a, n in hnames.items()}
    for name, l in sorted(reconstructed_cases.ROUTINES.items()):
        h = haddr.get(name)
        if name in ported and h is not None and h in hstarts and h not in pair and l not in pair.values() and address.get(h, l) == l:
            place(h, l, "cases")

    def run_votes() -> int:
        votes = collections.defaultdict(collections.Counter)
        for h, l in list(pair.items()):
            a, b, sm = align(h, l)
            for tag, i1, i2, j1, j2 in sm.get_opcodes():
                if tag != "equal":
                    continue
                for x, y in zip(a[i1:i2], b[j1:j2]):
                    tx, ty = targets(x, hrel, hmem), targets(y, lrel, lmem)
                    if len(tx) == len(ty):
                        for (kx, vx), (ky, vy) in zip(tx, ty):
                            if kx == ky:
                                votes[vx][vy] += 1
        back = collections.defaultdict(collections.Counter)
        for hv, c in votes.items():
            for lv, n in c.items():
                back[lv][hv] += n
        new = 0
        for hv in sorted(votes):
            top = votes[hv].most_common(2)
            lv, n = top[0]
            if len(top) > 1 and top[1][1] * 2 > n:
                continue
            other = back[lv].most_common(2)
            if other[0][0] != hv or (len(other) > 1 and other[1][1] * 2 > other[0][1]):
                continue
            if address.get(hv, lv) != lv:
                continue
            votes_of[hv] = n
            if hv not in address:
                address[hv] = lv
                new += 1
            if hv in hstarts and hv < htext and lv < ltext and hv not in pair and lv not in pair.values():
                place(hv, lv, "calls:%d" % n)
                new += 1
        return new

    while run_votes():
        pass
    # What the votes do not reach, by similarity against Leyte's unpaired routines.
    taken = set(pair.values())
    free = [l for l in lstarts if l not in taken]
    lkeys = {l: [_key(i) for i in lbody(l)] for l in free}
    candidates = {}
    for h in hstarts:
        if h in pair:
            continue
        hk = [_key(i) for i in hbody(h)]
        if len(hk) < 4:
            continue
        hist = collections.Counter(hk)
        rough = []
        for l in free:
            lk = lkeys[l]
            if 0.4 * len(hk) <= len(lk) <= 2.5 * len(hk):
                common = sum((hist & collections.Counter(lk)).values())
                if common * 2 >= min(len(hk), len(lk)):
                    rough.append((common / max(len(hk), len(lk)), l))
        best = sorted(((difflib.SequenceMatcher(None, hk, lkeys[l], autojunk=False).ratio(), l)
                       for _, l in sorted(rough, reverse=True)[:6]), reverse=True)
        if best:
            candidates[h] = best[:3]
    claims = collections.defaultdict(list)
    for h, best in candidates.items():
        if best[0][0] >= 0.6 and (len(best) == 1 or best[0][0] - best[1][0] >= 0.1):
            claims[best[0][1]].append((best[0][0], h))
    for l, cs in sorted(claims.items()):
        cs.sort(reverse=True)
        if (len(cs) == 1 or cs[0][0] - cs[1][0] >= 0.1) and address.get(cs[0][1], l) == l:
            place(cs[0][1], l, "similarity:%.2f" % cs[0][0])
    while run_votes():
        pass

    routines, unplaced = [], []
    for h in hstarts:
        name = hnames.get(h, "?")
        if h in pair:
            a, b, sm = align(h, pair[h])
            equal = sum(i2 - i1 for tag, i1, i2, _j1, _j2 in sm.get_opcodes() if tag == "equal")
            routines.append({"name": name, "hellcats_1_0_3": "0x%x" % h, "leyte": "0x%x" % pair[h], "evidence": evidence[h],
                             "instructions": [len(a), len(b)], "matching": equal})
        else:
            unplaced.append({"name": name, "hellcats_1_0_3": "0x%x" % h,
                             "candidates": [["0x%x" % l, round(r, 2)] for r, l in candidates.get(h, [])]})
    taken = set(pair.values())
    doc = {"schema": 1, "generated_by": "tools/leyte/overlay_placement.py",
           "counts": {"routines_1_0_3": len(hstarts), "routines_leyte": len(lstarts), "placed": len(routines),
                      "unplaced": len(unplaced), "by_evidence": dict(sorted(collections.Counter(
                          r["evidence"].split(":")[0] for r in routines).items())),
                      "addresses_beyond_the_map": len([h for h in address if h not in omap])},
           "routines": routines, "unplaced": unplaced,
           "addresses": [{"hellcats_1_0_3": "0x%x" % h, "leyte": "0x%x" % l, "votes": votes_of.get(h, 0)}
                         for h, l in sorted(address.items()) if h not in omap],
           "leyte_only": ["0x%x" % l for l in lstarts if l not in taken]}
    return doc


def main() -> int:
    text = json.dumps(build(), indent=1) + "\n"
    if "--check" in sys.argv:
        ok = OUT.is_file() and OUT.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte overlay placement: %s" % OUT.relative_to(ROOT))
        return 0 if ok else 1
    OUT.write_text(text)
    d = json.loads(text)["counts"]
    print("wrote %s: %d of %d routines placed %s, %d addresses beyond the map, %d Leyte routines unpaired" % (
        OUT.relative_to(ROOT), d["placed"], d["routines_1_0_3"], d["by_evidence"], d["addresses_beyond_the_map"],
        d["routines_leyte"] - d["placed"]))
    return 0


if __name__ == "__main__":
    sys.exit(main())
