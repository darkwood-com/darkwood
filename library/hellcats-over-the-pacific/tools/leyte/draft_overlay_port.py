#!/usr/bin/env python3
"""A Leyte draft of a 1.0.3 overlay port function, for porting it by hand: the routine pair
sources/leyte-gulf-1-0/overlay_placement.json places (tools/leyte/overlay_placement.py) is aligned as
that tool aligns it, and from the instructions that match it takes every relocated long and PC-relative
operand pair (image addresses), every displacement pair d16(An) (record fields; the frame's apart:
a6 in a routine that links it, a7) and every immediate that differs. The draft is the function's body
in godot/original/port/pacific_conflict.gd with
- DAT_ constants and image-address literals replaced by Leyte's address (the overlay map, the
  placement's address pairs, this pair's own); an unmapped one is kept and flagged;
- each "+ 0x.." / "- 0x.." that a displacement of the routine pairs ONE way rewritten; one paired several
  ways left as it is and flagged with its candidates; one next to a moved field (within 3) moved with it
  and flagged as derived;
- decimal offsets left as they are (the port writes strides and counts that way), flagged when one is,
  or sits next to, a moved field.
Under the draft: the instructions that do not match (both sides), the differing immediates, the
prologues when they differ (stack depths then move), the fields moved and the addresses paired.

A draft is never the port. What it flags is settled from the listing (--listing prints Leyte's
instructions), and the result is written into pacific_conflict_reconstructed.gd with emulator cases
(gen_leyte_overlay_reconstructed_cases.py) that run Leyte's own code; tools/leyte/overlay_case_coverage.py
then shows which of its instructions no case executes. Read-only: prints, writes nothing.

    python3 tools/leyte/draft_overlay_port.py NAME [NAME ...] [--listing]
    python3 tools/leyte/draft_overlay_port.py --piece _helper --of caller [--listing]
        a private helper of the port (no routine of its own) drafted with its caller's routine pair
Needs capstone (shell.nix) and the disk images (sources/mac-os-9).
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
import hfs  # noqa: E402

HEADER = 0x20
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True
PC_REL = re.compile(r"\$([0-9a-f]+)\(pc")
DISP = re.compile(r"(-?)\$([0-9a-f]+)\((a[0-7])")
IMM = re.compile(r"#(-?)\$([0-9a-f]+)")
NOT_BRANCH = ("bset", "bclr", "btst", "bchg", "bf", "bkpt")


def image(img):
    code_size, data_size, _ = struct.unpack(">III", img[4:16])
    first = struct.unpack(">I", img[24:28])[0] // 8
    at = HEADER + code_size + data_size
    rel = set()
    for i in range(first):
        off, flags = struct.unpack(">II", img[at + 8 * i:at + 8 * i + 8])
        if not (flags >> 7) & 1:
            rel.add(off)
    return img[HEADER:HEADER + code_size + data_size], rel, code_size


def is_branch(m):
    return m.startswith(("b", "db")) and not m.startswith(NOT_BRANCH)


def natural_end(mem, text, at):
    far = at
    for a in MD.disasm(mem[at:min(text, at + 0x4000)], at):
        m = a.mnemonic
        if is_branch(m) and not m.startswith("bsr"):
            t = re.search(r"\$([0-9a-f]+)$", a.op_str)
            if t:
                far = max(far, int(t.group(1), 16))
        if (m in ("rts", "rte", "rtd", "rtr") or m.startswith(("jmp", "bra"))) and a.address + a.size > far:
            return a.address + a.size
    return None


def key(i):
    if is_branch(i.mnemonic):
        return i.mnemonic.split(".")[0]
    return i.mnemonic + " " + re.sub(r"-?\$[0-9a-f]+", "#", i.op_str)


hmem, hrel, htext = image((ROOT / "sources" / "mac-os-9" / "Pacific Conflict").read_bytes())
lmem, lrel, ltext = image(hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data)
PLACEMENT = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_placement.json").read_text())
PLACED = {r["name"]: r for r in PLACEMENT["routines"]}
ADDRESS = {int(m["hellcats_1_0_3"], 16): int(m["leyte"], 16)
           for m in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_map.json").read_text())["mapped"]}
ADDRESS.update({int(a["hellcats_1_0_3"], 16): int(a["leyte"], 16) for a in PLACEMENT["addresses"]})
ADDRESS.update({int(r["hellcats_1_0_3"], 16): int(r["leyte"], 16) for r in PLACEMENT["routines"]})
PORT = (ROOT / "godot" / "original" / "port" / "pacific_conflict.gd").read_text()
CONSTS = {m.group(1): int(m.group(2), 16) for m in re.finditer(r"^const (DAT_[0-9a-f]+)\s*:\s*int\s*=\s*0x([0-9a-fA-F]+)", PORT, re.M)}
IMAGE_END = 0x267C8          # 1.0.3's image: text + data + bss (overlay_map.json "images")


def routine(mem, text, at):
    end = natural_end(mem, text, at)
    return list(MD.disasm(mem[at:end if end else at + 0x400], at))


def function_text(name):
    lines = PORT.split("\n")
    i = next(k for k, l in enumerate(lines) if re.match(r"^(?:static )?func " + re.escape(name) + r"\(", l))
    j = i + 1
    while j < len(lines) and (lines[j].startswith(("\t", " ")) or lines[j] == ""):
        j += 1
    while lines[j - 1] == "":
        j -= 1
    k = i
    while k > 0 and lines[k - 1].startswith("##"):
        k -= 1
    return lines[k:i], lines[i:j]


def draft(name, listing=False, piece=None):
    """piece: a private helper of the port (no routine of its own) drafted with name's pairs."""
    p = PLACED[name]
    h, l = int(p["hellcats_1_0_3"], 16), int(p["leyte"], 16)
    A, B = routine(hmem, htext, h), routine(lmem, ltext, l)
    sm = difflib.SequenceMatcher(None, [key(i) for i in A], [key(i) for i in B], autojunk=False)
    amap, dmap, fmap = dict(), collections.defaultdict(collections.Counter), collections.defaultdict(collections.Counter)
    imm, blocks = [], []
    # a6 is the frame pointer only in a routine that links it; otherwise an address register like the others
    frame_regs = ("a6", "a7") if any(i.mnemonic.startswith("link") for i in A[:3]) else ("a7",)
    for tag, i1, i2, j1, j2 in sm.get_opcodes():
        if tag != "equal":
            blocks.append((tag, A[i1:i2], B[j1:j2]))
            continue
        for x, y in zip(A[i1:i2], B[j1:j2]):
            hx = [struct.unpack(">I", hmem[x.address + k:x.address + k + 4])[0] for k in range(x.size) if x.address + k in hrel]
            ly = [struct.unpack(">I", lmem[y.address + k:y.address + k + 4])[0] for k in range(y.size) if y.address + k in lrel]
            if len(hx) == len(ly):
                amap.update(zip(hx, ly))
            if not is_branch(x.mnemonic):
                px, py = PC_REL.findall(x.op_str), PC_REL.findall(y.op_str)
                if len(px) == len(py):
                    amap.update((int(u, 16), int(v, 16)) for u, v in zip(px, py))
            dx, dy = DISP.findall(x.op_str), DISP.findall(y.op_str)
            if len(dx) == len(dy):
                for (s1, v1, r1), (s2, v2, r2) in zip(dx, dy):
                    a, b = (-1 if s1 else 1) * int(v1, 16), (-1 if s2 else 1) * int(v2, 16)
                    (fmap if r1 in frame_regs else dmap)[a][b] += 1
            ix, iy = IMM.findall(x.op_str), IMM.findall(y.op_str)
            if len(ix) == len(iy) and not hx:
                for (s1, v1), (s2, v2) in zip(ix, iy):
                    if (s1, v1) != (s2, v2):
                        imm.append((x.address, "%s %s" % (x.mnemonic, x.op_str), y.address, "%s %s" % (y.mnemonic, y.op_str)))
    head, body = function_text(piece or name)
    flags = []
    address = dict(ADDRESS)
    address.update(amap)

    def addr_of(v):
        return address.get(v)
    out = []
    for line in body:
        code, sep, comment = line.partition("#")
        def dat(m):
            v = CONSTS.get(m.group(0))
            if v is None:
                return m.group(0)
            t = addr_of(v)
            if t is None:
                flags.append("unmapped %s (0x%x)" % (m.group(0), v))
                return m.group(0)
            return "@0x%x@" % t   # already Leyte's: shielded from the literal pass below
        code = re.sub(r"\bDAT_[0-9a-f]+\b", dat, code)

        def field(m):
            sign, v = m.group(1), int(m.group(2), 16)
            left = re.split(r"[=(,\[]", code[:m.start()])[-1]
            framed = bool(re.search(r"\b\w*a[67]\b", left))
            k = -v if sign == "-" else v
            table = fmap if framed else dmap
            if k not in table:
                # not a displacement of the routine: a byte / word inside a moved field, or the long holding
                # a moved byte (within 3 of it), moves with it - shown as derived, to be checked
                near = {next(iter(table[d])) + (k - d) for d in table if len(table[d]) == 1 and next(iter(table[d])) != d and 0 < abs(k - d) <= 3}
                if len(near) == 1 and not framed:
                    t = near.pop()
                    flags.append("derived %s 0x%x -> %s0x%x (next to a moved field)" % (sign, v, "-" if t < 0 else "", abs(t)))
                    return "%s 0x%x" % ("-" if t < 0 else "+", abs(t))
                return m.group(0)
            if len(table[k]) > 1:
                flags.append("%s%s 0x%x pairs %s" % ("frame " if framed else "", sign, v, ", ".join("%s0x%x x%d" % ("-" if t < 0 else "", abs(t), n) for t, n in sorted(table[k].items()))))
                return m.group(0)
            t = next(iter(table[k]))
            return "%s 0x%x" % ("-" if t < 0 else "+", abs(t))
        code = re.sub(r"([+-]) 0x([0-9a-fA-F]+)\b", field, code)
        # decimal offsets are not rewritten (the port also writes strides and counts that way): one that is,
        # or sits next to, a moved displacement is flagged with where that field went
        for m in re.finditer(r"([\w)\]]+) ([+-]) (\d+)\b", code):
            k = (-1 if m.group(2) == "-" else 1) * int(m.group(3))
            hits = {d: next(iter(dmap[d])) for d in dmap if len(dmap[d]) == 1 and next(iter(dmap[d])) != d and abs(k - d) <= 3}
            amb = [d for d in dmap if len(dmap[d]) > 1 and d == k]
            if hits or amb:
                flags.append("decimal \"%s %s %s\": %s" % (m.group(1), m.group(2), m.group(3), ", ".join(
                    ["0x%x->0x%x gives 0x%x" % (d, t, t + (k - d)) for d, t in sorted(hits.items())] + ["0x%x pairs several ways" % d for d in amb])))

        def literal(m):
            v = int(m.group(1), 16)
            if 0x1000 <= v < IMAGE_END and v in amap:
                return "0x%x" % amap[v]
            return m.group(0)
        code = re.sub(r"(?<![\w$@])0x([0-9a-fA-F]+)\b", literal, code)
        code = re.sub(r"@(0x[0-9a-f]+)@", r"\1", code)
        out.append(code + sep + comment)
    print("## ---- draft: %s, 1.0.3 0x%x -> Leyte 0x%x; %d / %d instructions, %d matching (%s)" % (
        name, h, l, len(A), len(B), p["matching"], p["evidence"]))
    print("\n".join(head))
    print("\n".join(out))
    print("## ---- to settle:")
    for f in sorted(set(flags)):
        print("##   " + f)
    pa = [i for i in A[:4] if i.mnemonic.startswith(("link", "movem")) or "-(a7)" in i.op_str]
    pb = [i for i in B[:4] if i.mnemonic.startswith(("link", "movem")) or "-(a7)" in i.op_str]
    if [(i.mnemonic, i.op_str) for i in pa] != [(i.mnemonic, i.op_str) for i in pb]:
        print("##   prologue 1.0.3: %s | Leyte: %s" % ("; ".join("%s %s" % (i.mnemonic, i.op_str) for i in pa), "; ".join("%s %s" % (i.mnemonic, i.op_str) for i in pb)))
    for ha, ht, la, lt in imm:
        print("##   immediate 0x%x %s | 0x%x %s" % (ha, ht, la, lt))
    for tag, xs, ys in blocks:
        print("##   %s:" % tag)
        for i in xs:
            print("##       1.0.3 0x%x %s %s" % (i.address, i.mnemonic, i.op_str))
        for i in ys:
            print("##       Leyte 0x%x %s %s" % (i.address, i.mnemonic, i.op_str))
    moved = {k: next(iter(v)) for k, v in dmap.items() if len(v) == 1 and next(iter(v)) != k}
    print("##   fields moved: %s" % ", ".join("0x%x->0x%x" % (k, v) for k, v in sorted(moved.items())))
    print("##   addresses: %s" % ", ".join("0x%x->0x%x" % (k, v) for k, v in sorted(amap.items())))
    if listing:
        for i in B:
            print("##   L 0x%x %s %s" % (i.address, i.mnemonic, i.op_str))


if __name__ == "__main__":
    args = sys.argv[1:]
    if "--piece" in args:              # draft_port.py --piece _helper --of caller
        draft(args[args.index("--of") + 1], "--listing" in args, args[args.index("--piece") + 1])
    else:
        for n in [a for a in args if not a.startswith("--")]:
            draft(n, "--listing" in args)
            print()
