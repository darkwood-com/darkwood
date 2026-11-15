#!/usr/bin/env python3
"""Leyte Gulf's overlay port from Hellcats 1.0.3's (godot/original/port/pacific_conflict.gd), where the
two overlays prove it, function by function, as gen_host_ports.py's "partial" mode does for the host.

A ported function is verified for Leyte when, against the Leyte routine the correspondence places it at
(correspondence.json; a row whose Leyte offset another row claims is left out), both disassemble
(capstone 68020, past inline data) to the same instructions:
- the same count and mnemonics; branches' targets move with the code;
- every relocated long (an image address: the a.out relocation records of each overlay) at the same
  place in both, paired through sources/leyte-gulf-1-0/overlay_map.json (tools/leyte/overlay_map.py);
- every A5-relative operand (the host's globals and jump-table slots) paired through a5_map.json;
- every other operand equal (the relocated longs and A5 displacements masked).
- a record-field displacement d16(An) (An not A5 / A7) that differs is accepted only through the
  record-layout pairs: a 1.0.3 displacement every aligned instruction pair of the two overlays (the
  same count and mnemonics, the same address register) pairs with one and the same Leyte displacement.
  A displacement seen paired two ways (0x4 -> 0x4 and -> 0x14: different records) is ambiguous: a
  function needing it is accepted only when every aligned instruction of that function pairs it with
  one and the same Leyte displacement (the function settles it for itself, and its port's offsets are
  rewritten with that pair); otherwise the instruction refuses its function. Each verified function
  lists the pairs it rests on.
Anything else refuses the function, with the instruction that did.

--report prints the proof's counts; --write writes the port (godot/original/leyte/port/
pacific_conflict.gd) and its manifest (sources/leyte-gulf-1-0/overlay_port.json); --check compares.

    python3 tools/leyte/gen_overlay_port.py [--report | --write | --check]
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
sys.path.insert(0, str(ROOT / "tools" / "leyte"))
import hfs  # noqa: E402
from a5_map import a5_operands  # noqa: E402
from overlay_map import _image, HEADER  # noqa: E402

PORT = ROOT / "godot" / "original" / "port" / "pacific_conflict.gd"
DEBUG_RELOCATE: dict = {}
LEYTE_FUNCTIONS = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "CODE" / "Leyte Gulf overlay.functions.json").read_text())
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True
A5_DISP = re.compile(r"-?\$[0-9a-f]+\(a5")
ABS = re.compile(r"\$[0-9a-f]+(\.[wl])?")


DISP = re.compile(r"(-?)\$([0-9a-f]+)\((a[0-46])")
PC_REL = re.compile(r"\$([0-9a-f]+)\(pc")


def _mask(ins, relocated: bool, layout: dict | None = None, used: dict | None = None) -> str:
    ops = PC_REL.sub("PC(pc", A5_DISP.sub("A5", ins.op_str))
    if layout is not None:
        def sub(m):
            v = (-1 if m.group(1) else 1) * int(m.group(2), 16)
            if v in layout and layout[v] != v:
                used[v] = layout[v]
                w = layout[v]
                return "%s$%x(%s" % ("-" if w < 0 else "", abs(w), m.group(3))
            return m.group(0)
        ops = DISP.sub(sub, ops)
    if relocated:
        ops = ABS.sub("ADDR", ops)
    return ops


def _local_layout(his, lis, ambiguous: dict) -> dict:
    """The ambiguous displacements this one function settles: each 1.0.3 displacement that every
    aligned instruction pair of the function (the same mnemonic, the same address register) pairs
    with one and the same Leyte displacement here. One seen two ways in the function stays ambiguous."""
    seen = collections.defaultdict(set)
    for a, b in zip(his, lis):
        if a.mnemonic != b.mnemonic:
            continue
        da, db = DISP.findall(a.op_str), DISP.findall(b.op_str)
        if len(da) != len(db):
            continue
        for x, y in zip(da, db):
            h = (-1 if x[0] else 1) * int(x[1], 16)
            if h in ambiguous and x[2] == y[2]:
                seen[h].add((-1 if y[0] else 1) * int(y[1], 16))
    return {h: next(iter(ls)) for h, ls in seen.items() if len(ls) == 1}


def record_layout(hcode, lcode, starts, hsize, corr, claimed) -> tuple[dict, dict]:
    """Record-field displacement pairs over every aligned function pair: consistent ones (one Leyte
    displacement each) and the ambiguous ones (several)."""
    pairs = collections.defaultdict(set)
    for r in corr:
        if not r["leyte"] or claimed[r["leyte"]] > 1 or r["match"] == "not_found":
            continue
        h0, l0 = int(r["address"], 16) - HEADER, int(r["leyte"], 16) - HEADER
        later = [s for s in starts if s > h0]
        h1 = later[0] if later else hsize
        his = list(MD.disasm(hcode[h0:h1], h0))
        lis = list(MD.disasm(lcode[l0:l0 + (h1 - h0) + 0x400], l0))[:len(his)]
        if len(his) != len(lis) or any(a.mnemonic != b.mnemonic for a, b in zip(his, lis)):
            continue
        for a, b in zip(his, lis):
            da, db = DISP.findall(a.op_str), DISP.findall(b.op_str)
            if len(da) != len(db):
                continue
            for x, y in zip(da, db):
                if x[2] == y[2]:
                    pairs[(-1 if x[0] else 1) * int(x[1], 16)].add((-1 if y[0] else 1) * int(y[1], 16))
    consistent = {h: next(iter(ls)) for h, ls in pairs.items() if len(ls) == 1}
    ambiguous = {h: sorted(ls) for h, ls in pairs.items() if len(ls) > 1}
    return consistent, ambiguous


def verify() -> dict:
    hcode, hrel, hsize, _, _ = _image((ROOT / "sources" / "mac-os-9" / "Pacific Conflict").read_bytes())
    lcode, lrel, lsize, _, _ = _image(hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf").data)
    omap = {int(m["hellcats_1_0_3"], 16): int(m["leyte"], 16) for m in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_map.json").read_text())["mapped"]}
    a5map = {m["hellcats_1_0_3"]: m["leyte"] for m in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "a5_map.json").read_text())["mapped"]}
    inv = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text())["functions"]
    starts = sorted(int(f["original_address"], 16) - HEADER for f in inv if f["segment"] == "Pacific Conflict")
    corr = [r for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
            if r["segment"].startswith("Pacific")]
    claimed = collections.Counter(r["leyte"] for r in corr if r["leyte"])
    ported = set(re.findall(r"^func (\w+)\(", PORT.read_text(), re.M))
    layout, ambiguous = record_layout(hcode, lcode, starts, hsize, corr, claimed)
    verified, refused, uses, relocated = {}, {}, {}, {}

    images = set(omap.values())
    learned: dict = {}

    def prove(h0: int, h1: int, l0: int, learn: dict | None = None):
        his = list(MD.disasm(hcode[h0:h1], h0))
        lis = list(MD.disasm(lcode[l0:l0 + (h1 - h0) + 0x400], l0))[:len(his)]
        used: dict = {}
        if len(lis) != len(his) or not his:
            return "instruction counts differ", used
        local = dict(layout)
        local.update(_local_layout(his, lis, ambiguous))
        for a, b in zip(his, lis):
            if a.mnemonic != b.mnemonic:
                return "+%#x: %s | %s" % (a.address, a.mnemonic, b.mnemonic), used
            hs = [k for k in range(a.size) if a.address + k in hrel]
            ls = [k for k in range(b.size) if b.address + k in lrel]
            if hs != ls:
                return "+%#x: relocated longs at different places" % a.address, used
            for k in hs:
                hv = struct.unpack(">I", hcode[a.address + k:a.address + k + 4])[0]
                lv = struct.unpack(">I", lcode[b.address + k:b.address + k + 4])[0]
                if omap.get(hv) != lv and not _learn(learn, hv, lv):
                    return "+%#x: image address %#x -> %#x not in the map (%s)" % (
                        a.address, hv, lv, "unpaired" if hv not in omap else "maps to %#x" % omap[hv]), used
            for hv, lv in zip(a5_operands(a), a5_operands(b)):
                if a5map.get(hv) != lv and not (hv == lv and hv not in a5map):
                    return "+%#x: A5 %s -> %s not in the A5 map" % (a.address, hex(hv), hex(lv)), used
            branch = a.mnemonic.startswith(("b", "j", "db")) and a.mnemonic not in ("bset", "bclr", "btst", "bchg")
            # A PC-relative operand reaches an image address: through the map, then masked.
            pa, pb = PC_REL.findall(a.op_str), PC_REL.findall(b.op_str)
            if not branch and pa and (len(pa) != len(pb) or any(omap.get(int(x, 16)) != int(y, 16)
                                                                 and not _learn(learn, int(x, 16), int(y, 16)) for x, y in zip(pa, pb))):
                return "+%#x: PC-relative %s -> %s not in the map" % (a.address, pa, pb), used
            if not branch and _mask(a, bool(hs), local, used) != _mask(b, bool(ls)):
                amb = [d for d in ((-1 if x[0] else 1) * int(x[1], 16) for x in DISP.findall(a.op_str)) if d in ambiguous]
                return "+%#x: %s %s | %s%s" % (a.address, a.mnemonic, a.op_str, b.op_str,
                                               " (ambiguous displacement %s)" % ", ".join(hex(d) for d in amb) if amb else ""), used
        return None, used

    def _learn(learn, hv: int, lv: int) -> bool:
        """A relocation proof may pair an address the map does not reach, injectively: hv unpaired, lv
        no other address's image (in the map or learned)."""
        if learn is None or hv in omap or lv in images:
            return False
        if learn.get(hv, lv) != lv or any(v == lv and k != hv for k, v in list(learn.items()) + list(learned.items())):
            return False
        if learned.get(hv, lv) != lv:
            return False
        learn[hv] = lv
        return True

    extent = {}
    for r in corr:
        name = r["name"]
        if name not in ported:
            continue
        h0 = int(r["address"], 16) - HEADER
        later = [s for s in starts if s > h0]
        extent[name] = (h0, later[0] if later else hsize)
        if not r["leyte"]:
            refused[name] = "not found in Leyte"
            continue
        if claimed[r["leyte"]] > 1:
            refused[name] = "its Leyte routine is claimed by another function too"
            continue
        why, used = prove(*extent[name], int(r["leyte"], 16) - HEADER)
        if why:
            refused[name] = why
        else:
            verified[name] = r["leyte"]
            uses[name] = used
    # A refused function looked for among Leyte's overlay functions (Ghidra's, of nearly its size: within
    # 4 bytes) not taken by a verified one: exactly one passing the proof relocates it (the correspondence's
    # placement collided or missed it); several leave it refused, listed.
    taken = {int(a, 16) for a in verified.values()}
    candidates = [(int(f["entry"], 16), int(f["size"])) for f in LEYTE_FUNCTIONS]
    for name in sorted(refused):
        if name not in extent or "its port reaches" in refused[name]:
            continue
        h0, h1 = extent[name]
        found = []
        for entry, size in candidates:
            if entry in taken or abs(size - (h1 - h0)) > 4:
                continue
            learn: dict = {}
            why, used = prove(h0, h1, entry - HEADER, learn)
            if why is None:
                found.append((entry, used, learn))
        if len(found) == 1:
            entry, used, learn = found[0]
            learned.update(learn)
            verified[name] = "0x%x" % entry
            uses[name] = used
            relocated[name] = "0x%x" % entry
            taken.add(entry)
            del refused[name]
        elif found:
            refused[name] += " (and %d Leyte functions pass: %s)" % (len(found), ", ".join("0x%x" % e for e, _, _ in found))
        elif DEBUG_RELOCATE.get(name):
            for entry, size in candidates:
                if abs(size - (h1 - h0)) <= 4:
                    print("  %s at 0x%x: %s" % (name, entry, prove(h0, h1, entry - HEADER)[0]))
    return {"verified": verified, "refused": refused, "layout_used": uses, "relocated": relocated,
            "layout_pairs": layout, "ambiguous": ambiguous,
            "learned": {"0x%x" % k: "0x%x" % v for k, v in sorted(learned.items())},
            "layout": {"consistent": len(layout), "ambiguous": len(ambiguous)}}


OUT = ROOT / "godot" / "original" / "leyte" / "port" / "pacific_conflict.gd"


def _sx(v: int) -> str:
    return "-0x%x" % -v if v < 0 else "0x%x" % v
MANIFEST = ROOT / "sources" / "leyte-gulf-1-0" / "overlay_port.json"
## The port's own dispatch, not game logic: kept for every port.
INFRASTRUCTURE = {"_init", "_jsr"}
LITERAL = re.compile(r"(?<![\w$])0x([0-9a-fA-F]+)\b")
TABLE = re.compile(r"^\tcode_table\[0x([0-9a-f]+)\] = (.*)$")
TABLE_TARGET = re.compile(r"Callable\(self, \"(\w+)\"\)|return (\w+)\(")


def build() -> dict:
    """The Leyte overlay port: pacific_conflict.gd with only the functions verified by instruction and
    clean through their GDScript call closure kept (the rest refuse, as gen_host_ports.py's partial
    ports do), their image-address literals (0x1000 and up, inside the image) rewritten through
    overlay_map.json; a function holding an unmapped one is refused. The constructor's code_table keeps
    the verified functions only, keyed by their Leyte addresses."""
    import gen_host_ports as hp
    v = verify()
    src = PORT.read_text()
    bodies, consts = hp._bodies(src)
    omap = {int(m["hellcats_1_0_3"], 16): int(m["leyte"], 16) for m in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_map.json").read_text())["mapped"]}
    image_end = json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "overlay_map.json").read_text())["images"]["hellcats_1_0_3"]
    image_end = image_end["text"] + image_end["data"] + image_end["bss"]
    omap.update({int(k, 16): int(x, 16) for k, x in v["learned"].items()})   # pairs relocation proofs learned
    # Every verified function; one resting on record-layout pairs gets exactly those pairs rewritten in
    # its own body, in offset form only (below).
    clean = set(v["verified"])
    refused = dict(v["refused"])
    # The rewrite reaches hex offset literals only. A function resting on a moved field is refused when
    # its body also writes that offset another way the rewrite would miss or misread: in decimal
    # ("obj + 4") or as a stack-frame offset ("entry_a7 - 0x14", which is not a field).
    for n in sorted(clean):
        moved = {k for k, x in v["layout_used"].get(n, {}).items() if k != x}
        if not moved or n not in bodies:
            continue
        code = "\n".join(line.partition("#")[0] for line in bodies[n].split("\n"))
        decimal = {(-1 if sg == "-" else 1) * int(d) for sg, d in re.findall(r"([+-]) (\d+)\b", code)} & moved
        frame = {(-1 if sg == "-" else 1) * int(d, 16) for sg, d in re.findall(r"\ba[67]\w* ([+-]) 0x([0-9a-fA-F]+)\b", code)} & moved
        if decimal or frame:
            clean.discard(n)
            refused[n] = "a moved record field it rests on is also written %s: %s" % (
                "in decimal" if decimal else "as a frame offset", ", ".join(hex(x) for x in sorted(decimal | frame)))
    def values(body: str) -> set:
        out = {int(m.group(1), 16) for m in LITERAL.finditer(body)}
        return out | {consts[w] for w in re.findall(r"\b(DAT_[0-9a-f]+)\b", body) if w in consts}
    # A call through _jsr(<code address>) is a call too: the function at that 1.0.3 runtime address.
    by_address = {int(r["address"], 16) - HEADER: r["name"] for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
                  if r["segment"].startswith("Pacific")}
    def jsr_targets(body: str) -> set:
        return {by_address.get(int(x, 16), "code address 0x%s" % x) for x in re.findall(r"_jsr\(0x([0-9a-f]+)", body)}
    # Private helpers (pieces of original functions, "_name", no correspondence of their own) taken as
    # they are when nothing in their body could be a record field Leyte moved: no offset-form literal
    # ("+ 0x.." / "- 0x..") whose 1.0.3 value is a changed or ambiguous displacement. Their image
    # addresses go through the map (an unmapped one refuses, as for any function); their own calls are
    # checked through the closure below.
    corr_names = set(by_address.values())
    changed_disp = {k for k, x in v["layout_pairs"].items() if k != x} | set(v["ambiguous"])
    helpers = set()
    for n, body in bodies.items():
        if not n.startswith("_") or n in INFRASTRUCTURE or n in corr_names:
            continue
        offsets = {(-1 if m.group(1) == "-" else 1) * int(m.group(2), 16) for m in re.finditer(r"([+-]) 0x([0-9a-fA-F]+)\b", body)}
        if offsets & changed_disp:
            refused[n] = "private helper holding a record offset Leyte moves: %s" % ", ".join(hex(x) for x in sorted(offsets & changed_disp)[:4])
        else:
            helpers.add(n)
    clean |= helpers
    changed = True
    while changed:
        changed = False
        for n in sorted(clean):
            reach = hp._reach(n, bodies)
            reach_calls = set(reach) | {t for b in reach for t in jsr_targets(bodies[b])}
            bad = sorted(b for b in reach_calls if b != n and b not in clean and b not in INFRASTRUCTURE)
            unmapped = sorted(x for b in reach if b not in INFRASTRUCTURE for x in values(bodies[b])
                              if 0x1000 <= x < image_end and x not in omap)
            if bad or unmapped:
                clean.discard(n)
                refused[n] = "its port reaches %s" % "; ".join(
                    (["unverified " + ", ".join(bad[:6])] if bad else []) +
                    (["unmapped image addresses " + ", ".join(hex(x) for x in unmapped[:6])] if unmapped else []))
                changed = True
    keep = clean | INFRASTRUCTURE
    refuse_all = {n: "not verified for Leyte" for n in bodies if n not in keep}
    out = hp._refuse(src, refuse_all)
    out = re.sub(r"^class_name (\w+)", r"class_name Leyte\1", out, count=1, flags=re.M)
    leyte_at = {n: int(a, 16) - HEADER for n, a in v["verified"].items()}
    hcode_at = {r["name"]: int(r["address"], 16) - HEADER for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
                if r["segment"].startswith("Pacific")}
    lines = []
    current = None
    for line in out.split("\n"):
        f = re.match(r"^(?:static )?func (\w+)\(", line)
        if f:
            current = f.group(1)
        elif line and not line.startswith(("\t", " ")):
            current = None
        t = TABLE.match(line)
        if t:
            # Kept only for a kept function whose own 1.0.3 address is the key, re-keyed to its Leyte one.
            m = TABLE_TARGET.search(t.group(2))
            name = (m.group(1) or m.group(2)) if m else None
            if name in clean and int(t.group(1), 16) == hcode_at.get(name):
                lines.append("\tcode_table[0x%x] = %s" % (leyte_at[name], t.group(2)))
            continue
        code, sep, comment = line.partition("#")
        pairs = v["layout_used"].get(current) or {}
        if pairs:
            # The function's own record-field pairs, where the code adds or takes the offset: "+ 0xce",
            # "- 0x6bc" (a negative displacement written as a subtraction).
            def field(m, _p=pairs):
                sign, x = m.group(1), int(m.group(2), 16)
                key = -x if sign == "-" else x
                if key in _p:
                    y = _p[key]
                    return "%s 0x%x" % ("-" if y < 0 else "+", abs(y))
                return m.group(0)
            code = re.sub(r"([+-]) 0x([0-9a-fA-F]+)\b", field, code)
        def sub(m):
            x = int(m.group(1), 16)
            return "0x%x" % omap[x] if 0x1000 <= x < image_end and x in omap else m.group(0)
        lines.append(LITERAL.sub(sub, code) + sep + comment)
    header = ("## GENERATED by tools/leyte/gen_overlay_port.py from original/port/pacific_conflict.gd for Leyte Gulf's\n"
              "## overlay: the functions verified by instruction against Leyte's overlay and clean through their call\n"
              "## closure, their image addresses through sources/leyte-gulf-1-0/overlay_map.json; the others\n"
              "## refuse. Do not edit. Comments keep 1.0.3's addresses.\n")
    manifest = {"schema": 1, "generated_by": "tools/leyte/gen_overlay_port.py", "port": "original/leyte/port/pacific_conflict.gd",
                "functions": {n: v["verified"][n] for n in sorted(clean) if n in v["verified"]},
                "helpers": sorted(n for n in clean if n not in v["verified"]),
                "relocated": {n: a for n, a in sorted(v["relocated"].items()) if n in clean},
                "record_fields": {n: {_sx(k): _sx(x) for k, x in sorted(v["layout_used"][n].items())}
                                  for n in sorted(clean) if v["layout_used"].get(n)},
                "learned_addresses": v["learned"],
                "record_layout": {_sx(k): _sx(x) for k, x in sorted(v["layout_pairs"].items())},
                "refused": dict(sorted((n, w) for n, w in refused.items() if n in bodies))}
    return {OUT: header + "\n".join(lines), MANIFEST: json.dumps(manifest, indent=1) + "\n"}


def main() -> int:
    if "--write" in sys.argv or "--check" in sys.argv:
        files = build()
        if "--check" in sys.argv:
            bad = [p for p, t in files.items() if not p.is_file() or p.read_text() != t]
            for p in bad:
                print("FAIL Leyte overlay port: %s is not what the generator gives" % p.relative_to(ROOT))
            if not bad:
                print("PASS Leyte overlay port")
            return 1 if bad else 0
        for p, t in files.items():
            p.write_text(t)
        m = json.loads(files[MANIFEST])
        print("wrote %s: %d functions kept, %d refused" % (OUT.relative_to(ROOT), len(m["functions"]), len(m["refused"])))
        return 0
    v = verify()
    kinds = collections.Counter(re.sub(r"\+0x[0-9a-f]+: ", "", w).split(" ")[0] if w.startswith("+") else w for w in v["refused"].values())
    print("verified %d (%d through record-layout pairs, %d relocated by proof), refused %d; layout pairs %s" % (
        len(v["verified"]), sum(1 for u in v["layout_used"].values() if u), len(v["relocated"]), len(v["refused"]), v["layout"]))
    for k, n in kinds.most_common(12):
        print("  %3d %s" % (n, k))
    if "--report" in sys.argv:
        for name, why in sorted(v["refused"].items())[:25]:
            print("  refused %s: %s" % (name, why))
    return 0


if __name__ == "__main__":
    sys.exit(main())
