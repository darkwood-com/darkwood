#!/usr/bin/env python3
"""Leyte Gulf's overlay port from Hellcats 1.0.3's (godot/original/port/pacific_conflict.gd), where the
two overlays prove it, function by function, as gen_host_ports.py's "partial" mode does for the host.

A ported function is verified for Leyte when, against the Leyte routine the correspondence places it at
(correspondence.json; a row whose Leyte offset another row claims is left out) or, failing that, the one
overlay_placement.json finds by following the code (tools/leyte/overlay_placement.py), both disassemble
(capstone 68020, past inline data) to the same instructions:
- the same count and mnemonics; branches' targets move with the code;
- every relocated long (an image address: the a.out relocation records of each overlay) at the same
  place in both, paired through sources/leyte-gulf-1-0/overlay_map.json (tools/leyte/overlay_map.py);
- every other operand equal (the relocated longs masked). The overlay reaches the host through its own global
  (host_world), never through A5: a5 is an ordinary address register here (1.0.3's update_tracking_camera keeps an
  object in it), its displacements record fields like any other's.
- a record-field displacement d16(An) (An not A7) that differs is accepted only through the
  record-layout pairs: a 1.0.3 displacement every aligned instruction pair of the two overlays (the
  same count and mnemonics, the same address register) pairs with one and the same Leyte displacement.
  A displacement seen paired two ways (0x4 -> 0x4 and -> 0x14: different records) is ambiguous: a
  function needing it is accepted only when every aligned instruction of that function pairs it with
  one and the same Leyte displacement (the function settles it for itself, and its port's offsets are
  rewritten with that pair); otherwise the instruction refuses its function. Each verified function
  lists the pairs it rests on.
Anything else refuses the function, with the instruction that did.

What the port keeps (build): a verified function whose own GDScript body is sound for Leyte -
- every image address it names goes through the map. A hex literal inside the image that the map does
  not reach is taken as a plain number only on the function's own evidence: the value is an operand of
  one of its 1.0.3 instructions outside any relocated long or PC-relative reference (cmpi.l #$1000,
  andi.w #$3fff), and none of its relocated longs or PC-relative operands has that value; or every
  occurrence in the body is a bit mask ("& 0xffff"); or it is one of the port's word-arithmetic
  constants (WORD_IDIOMS: 0xffff for a dbra counter run out, 0x10000, ...) that no relocated long and
  no PC-relative operand of the whole 1.0.3 overlay has as its value. Anything else is an unmapped image address and
  refuses the function. A code address below 0x1000 that the function's instructions reference and its
  body names refuses it too (the rewrite starts at 0x1000);
- a moved record field it rests on is rewritten in offset form only, and every offset the body adds
  ("+ 0x..") must be one the function's own instructions hold: a displacement (moved through its pairs,
  or the same in Leyte), an immediate number, or a byte / word inside a field they address (within 3
  of its displacement: the low byte of a word field, the long holding a flag byte) or the sum of two operands (adda.w #9 then
  1(a0)), which moves with the field (the manifest's derived_fields). An offset they do not hold, or
  one these readings place two ways, refuses the function, and so does a moved displacement that is
  also an immediate number of its arithmetic (stack adjustments left out);
- the proof covers the whole routine: an inventory entry the code runs past (a forward branch over it,
  or no return before it) is a false entry inside the routine and does not end it;
- a literal inside the function's own 1.0.3 routine is a position in it (the port's state-machine labels):
  when the two routines are laid out alike (every instruction pair the same size) it moves to the same
  offset in Leyte's routine; one that is also an immediate number of the function is refused.
The port's call closure is NOT required: a function it calls that is not kept stays a refusing stub
(assert) in the generated port, so reaching it stops the run with its name - never a silent success -
until that function is verified here or written for Leyte in pacific_conflict_reconstructed.gd, which
extends the generated port and overrides it by name. The manifest lists each kept function's open
calls. Private helpers (pieces of original functions, no address) have no instructions of their own:
one is kept on the evidence of every kept function it is a piece of - each offset it adds must be, in
each, a displacement the proof left where it was or a plain number - and refuses when it holds an
offset Leyte moves anywhere, or one no kept owner shows.

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


DISP = re.compile(r"(-?)\$([0-9a-f]+)\((a[0-6])")
PC_REL = re.compile(r"\$([0-9a-f]+)\(pc")


## (An) is the displacement 0: written out, so a field at a record's start pairs like any other (1.0.3's
## controller pointer 8(obj) is Leyte's (obj); 1.0.3's type byte (obj) is Leyte's 0x28(obj)).
BARE = re.compile(r"(?<![\w$-])\((a[0-46])\)(?!\+)")


def _operands(ins) -> str:
    return BARE.sub(r"$0(\1)", ins.op_str)


def _mask(ins, relocated: bool, layout: dict | None = None, used: dict | None = None) -> str:
    ops = PC_REL.sub("PC(pc", _operands(ins))
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
        da, db = DISP.findall(_operands(a)), DISP.findall(_operands(b))
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
            da, db = DISP.findall(_operands(a)), DISP.findall(_operands(b))
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
    ported = set(re.findall(r"^(?:static )?func (\w+)\(", PORT.read_text(), re.M))
    layout, ambiguous = record_layout(hcode, lcode, starts, hsize, corr, claimed)
    verified, refused, uses, relocated = {}, {}, {}, {}

    images = set(omap.values())
    learned: dict = {}
    # Where overlay_placement.py finds each routine (by following the code: calls and address references
    # through aligned routines), and the address pairs it accepted. A place to try the proof at, never a
    # proof: image addresses, as the map's.
    placement = json.loads(PLACEMENT.read_text()) if PLACEMENT.is_file() else {"routines": [], "addresses": []}
    placed_at = {r["name"]: int(r["leyte"], 16) for r in placement["routines"]}
    placed_address = {int(a["hellcats_1_0_3"], 16): int(a["leyte"], 16) for a in placement["addresses"]}
    placed_address.update({int(r["hellcats_1_0_3"], 16): int(r["leyte"], 16) for r in placement["routines"]})

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
            branch = a.mnemonic.startswith(("b", "j", "db")) and not a.mnemonic.startswith(("bset", "bclr", "btst", "bchg"))
            # A PC-relative operand reaches an image address: through the map, then masked.
            pa, pb = PC_REL.findall(a.op_str), PC_REL.findall(b.op_str)
            if not branch and pa and (len(pa) != len(pb) or any(omap.get(int(x, 16)) != int(y, 16)
                                                                 and not _learn(learn, int(x, 16), int(y, 16)) for x, y in zip(pa, pb))):
                return "+%#x: PC-relative %s -> %s not in the map" % (a.address, pa, pb), used
            if not branch and _mask(a, bool(hs), local, used) != _mask(b, bool(ls)):
                amb = [d for d in ((-1 if x[0] else 1) * int(x[1], 16) for x in DISP.findall(_operands(a))) if d in ambiguous]
                return "+%#x: %s %s | %s%s" % (a.address, a.mnemonic, a.op_str, b.op_str,
                                               " (ambiguous displacement %s)" % ", ".join(hex(d) for d in amb) if amb else ""), used
        return None, used

    def _learn(learn, hv: int, lv: int) -> bool:
        """A relocation proof may pair an address the map does not reach, injectively: hv unpaired, lv
        no other address's image (in the map or learned), and not against what overlay_placement.json
        saw the two overlays' aligned code pair that address with."""
        if learn is None or hv in omap or lv in images:
            return False
        if placed_address.get(hv, lv) != lv:
            return False
        if learn.get(hv, lv) != lv or any(v == lv and k != hv for k, v in list(learn.items()) + list(learned.items())):
            return False
        if learned.get(hv, lv) != lv:
            return False
        learn[hv] = lv
        return True

    computed_end: dict = {}      # natural end -> the routine's last instruction is a computed jmp (a switch)

    def natural_end(code: bytes, size: int, at: int):
        """The address after the first rts / jmp / bra of the routine at `at` that no earlier forward
        branch passes (None when the walk finds none within 0x4000 bytes)."""
        far = at
        for a in MD.disasm(code[at:min(size, at + 0x4000)], at):
            m = a.mnemonic
            if m.startswith(("b", "db")) and not m.startswith(("bset", "bclr", "btst", "bchg", "bsr", "bf", "bkpt")):
                t = re.search(r"\$([0-9a-f]+)$", a.op_str)
                if t:
                    far = max(far, int(t.group(1), 16))
            if (m in ("rts", "rte", "rtd", "rtr") or m.startswith(("jmp", "bra"))) and a.address + a.size > far:
                computed_end[a.address + a.size] = m.startswith("jmp") and not re.match(r"^\$[0-9a-f]+(\.[wl])?$", a.op_str)
                return a.address + a.size
        return None
    ported_at = {int(r["address"], 16) - HEADER for r in corr if r["name"] in ported}

    def routine_end(h0: int) -> int:
        """Where the routine at h0 ends: the next inventory entry, unless the code runs past it - a forward
        branch over it, or no return / jump before it - and the port has no function of its own there:
        a false entry inside the routine (Ghidra lists FUN_00003be0 inside update_object_camera_metric,
        FUN_00001db8 inside sin_fixed14). The routine then runs to the first entry the port has a function
        at (a named piece of it), or to its natural end: past its last return it holds nothing the proof
        needs (data or padding, which differs between the images) - unless it ends on a switch's computed
        jump, whose cases run on to the next entry."""
        later = [x for x in starts if x > h0]
        if not later:
            return hsize
        end = natural_end(hcode, hsize, h0)
        if end is None or end <= later[0]:
            return later[0]
        for x in later:
            if x >= end:
                return x if computed_end.get(end) else end
            if x in ported_at:
                return x
        return end

    extent = {}
    for r in corr:
        name = r["name"]
        if name not in ported:
            continue
        h0 = int(r["address"], 16) - HEADER
        extent[name] = (h0, routine_end(h0))
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
    # A function the correspondence does not place, places on a routine another row claims too, or places
    # where the proof fails, is tried where overlay_placement.json finds its routine: the same proof, at
    # that one address, an address the map does not reach paired as a relocation proof pairs it.
    placed = {}
    for name in sorted(refused):
        at = placed_at.get(name)
        if name not in extent or at is None or at + HEADER in taken:
            continue
        learn: dict = {}
        why, used = prove(*extent[name], at, learn)
        if why is None:
            learned.update(learn)
            verified[name] = "0x%x" % (at + HEADER)
            uses[name] = used
            placed[name] = verified[name]
            taken.add(at + HEADER)
            del refused[name]
        elif refused[name].startswith(("not found in Leyte", "its Leyte routine is claimed")):
            refused[name] = "placed at 0x%x (overlay_placement.json), where %s" % (at + HEADER, why)
    # A Leyte routine's size as its code gives it (Ghidra's ends at a false entry inside the routine, as
    # 1.0.3's inventory does), Ghidra's own when the walk finds no end.
    candidates = []
    for f in LEYTE_FUNCTIONS:
        entry = int(f["entry"], 16)
        end = natural_end(lcode, lsize, entry - HEADER)
        candidates.append((entry, max(int(f["size"]), end - (entry - HEADER)) if end is not None else int(f["size"])))
    for name in sorted(refused):
        if name not in extent or "its port reaches" in refused[name]:
            continue
        h0, h1 = extent[name]
        found = []
        for entry, size in candidates:
            if entry in taken or abs(size - (h1 - h0)) > 4:
                continue
            # The map may already place the routine's entry (a hook the overlay stores in the host world,
            # a call from an aligned function): then only that Leyte routine is a candidate (sin_fixed14
            # and cos_fixed14 pass each other's proof, their tables learned either way).
            if h0 in omap and omap[h0] != entry - HEADER:
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
    # Each verified function's own operand evidence, for the port's literals: the image addresses its
    # 1.0.3 instructions reference (relocated longs, PC-relative operands of non-branches) and the
    # numbers they hold anywhere else.
    # Every image address 1.0.3's overlay refers to at all: the value of each relocated long (text and
    # data) and each PC-relative operand of a non-branch, over the whole image.
    himage = (ROOT / "sources" / "mac-os-9" / "Pacific Conflict").read_bytes()
    referenced = {struct.unpack(">I", himage[HEADER + off:HEADER + off + 4])[0] for off in hrel}
    for k, h0 in enumerate(starts):
        for a in MD.disasm(hcode[h0:starts[k + 1] if k + 1 < len(starts) else hsize], h0):
            if not (a.mnemonic.startswith(("b", "j", "db")) and not a.mnemonic.startswith(("bset", "bclr", "btst", "bchg"))):
                referenced |= {int(x, 16) for x in PC_REL.findall(a.op_str)}
    # The routine pairs laid out alike (the same instruction sizes, pair by pair): a position inside one is the
    # same offset inside the other (the port's own labels: scan_convert_edge_rows' state machine).
    own_routine = {}
    for name, at in verified.items():
        h0, h1 = extent[name]
        l0 = int(at, 16) - HEADER
        his = list(MD.disasm(hcode[h0:h1], h0))
        lis = list(MD.disasm(lcode[l0:l0 + (h1 - h0) + 0x400], l0))[:len(his)]
        if his and len(his) == len(lis) and all(a.size == b.size for a, b in zip(his, lis)):
            own_routine[name] = (h0, his[-1].address + his[-1].size, l0)
    image_refs, plain_numbers, displacements, immediates = {}, {}, {}, {}
    for name in verified:
        refs, nums, disps, imms = set(), set(), set(), set()
        for a in MD.disasm(hcode[extent[name][0]:extent[name][1]], extent[name][0]):
            vals = {struct.unpack(">I", hcode[a.address + k:a.address + k + 4])[0] for k in range(a.size) if a.address + k in hrel}
            refs |= vals
            branch = a.mnemonic.startswith(("b", "j", "db")) and not a.mnemonic.startswith(("bset", "bclr", "btst", "bchg"))
            pcs = {int(x, 16) for x in PC_REL.findall(a.op_str)}
            if not branch:
                refs |= pcs
            nums |= {int(x, 16) for x in re.findall(r"\$([0-9a-f]+)", a.op_str)} - vals - pcs
            disps |= {(-1 if sg else 1) * int(x, 16) for sg, x in re.findall(r"(-?)\$([0-9a-f]+)\(a[0-7]", a.op_str)}
            if not (a.op_str.endswith(", a7") or a.mnemonic.startswith(("link", "unlk"))):      # popping arguments
                imms |= {int(x, 16) for x in re.findall(r"#-?\$([0-9a-f]+)", a.op_str)} - vals
        image_refs[name], plain_numbers[name] = sorted(refs), sorted(nums)
        displacements[name], immediates[name] = sorted(disps), sorted(imms)
    return {"verified": verified, "refused": refused, "layout_used": uses, "relocated": relocated, "placed": placed,
            "image_refs": image_refs, "plain_numbers": plain_numbers, "referenced": sorted(referenced),
            "displacements": displacements, "immediates": immediates, "own_routine": own_routine,
            "layout_pairs": layout, "ambiguous": ambiguous,
            "learned": {"0x%x" % k: "0x%x" % v for k, v in sorted(learned.items())},
            "layout": {"consistent": len(layout), "ambiguous": len(ambiguous)}}


OUT = ROOT / "godot" / "original" / "leyte" / "port" / "pacific_conflict.gd"


def _sx(v: int) -> str:
    return "-0x%x" % -v if v < 0 else "0x%x" % v
MANIFEST = ROOT / "sources" / "leyte-gulf-1-0" / "overlay_port.json"
PLACEMENT = ROOT / "sources" / "leyte-gulf-1-0" / "overlay_placement.json"
## The port's own dispatch, not game logic: kept for every port.
INFRASTRUCTURE = {"_init", "_jsr", "_bsr_file"}
LITERAL = re.compile(r"(?<![\w$])0x([0-9a-fA-F]+)\b")
TABLE = re.compile(r"^\tcode_table\[0x([0-9a-f]+)\] = (.*)$")
TABLE_TARGET = re.compile(r"Callable\(self, \"(\w+)\"\)|return (\w+)\(")


## Constants of the port's word and long arithmetic (a dbra counter run out, sign and carry bits, word
## masks). One is taken as a number only while nothing in 1.0.3's overlay refers to that value as an
## image address (verify()'s "referenced").
WORD_IDIOMS = {0xffff, 0xfffe, 0xfffc, 0xff00, 0x7fff, 0x8000, 0x10000, 0x1fff, 0x3fff}


def build() -> dict:
    """The Leyte overlay port: pacific_conflict.gd with the functions verified by instruction whose own
    body is sound kept (the rest refuse, as gen_host_ports.py's partial ports do), their image-address
    literals (0x1000 and up, inside the image) rewritten through overlay_map.json; a function holding an
    unmapped one is refused (see the module's notes for what counts as a plain number). A kept function
    may call one that refuses: the stub stops the run. The constructor's code_table keeps the verified
    functions only, keyed by their Leyte addresses."""
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
    referenced = set(v["referenced"])
    refs = {n: set(x) for n, x in v["image_refs"].items()}
    nums = {n: set(x) for n, x in v["plain_numbers"].items()}
    disps = {n: set(x) for n, x in v["displacements"].items()}
    imms = {n: set(x) for n, x in v["immediates"].items()}
    moved_of = {n: {k: x for k, x in v["layout_used"].get(n, {}).items() if k != x} for n in v["verified"]}

    def code_of(n: str) -> str:
        return "\n".join(line.partition("#")[0] for line in bodies[n].split("\n"))

    def offset_literals(code: str) -> set:
        """The hex literals the code adds or takes ("+ 0x..", "- 0x.."), signed, stack-frame arithmetic
        left out (the operand before the sign is a6 / a7 or a name holding one: entry_a7 - 0x24)."""
        out = set()
        for m in re.finditer(r"([+-]) 0x([0-9a-fA-F]+)\b", code):
            left = re.split(r"[=(,\n\[]", code[:m.start()])[-1]
            if re.search(r"\b\w*a[67]\b", left) or (re.match(r"\s*a7\s*$", code[:m.start()].rsplit("\n", 1)[-1].split("=")[0]) and "=" in code[:m.start()].rsplit("\n", 1)[-1]):
                continue                 # "entry_a7 - 4 - 0x12", "a7 = body - 0x24": a stack position
            out.add((-1 if m.group(1) == "-" else 1) * int(m.group(2), 16))
        return out

    def values(body: str) -> set:
        out = {int(m.group(1), 16) for m in LITERAL.finditer(body)}
        return out | {consts[w] for w in re.findall(r"\b(DAT_[0-9a-f]+)\b", body) if w in consts}

    def masks_only(body: str, x: int) -> bool:
        hits = [m for m in LITERAL.finditer(body) if int(m.group(1), 16) == x]
        return bool(hits) and all(body[max(0, m.start() - 2):m.start()] == "& " for m in hits)

    own_routine = v["own_routine"]

    def own_position(n: str, x: int):
        """x as a position inside n's own routine, at the same offset in Leyte's (None outside it, or when x is
        also a plain number of n's instructions: then it is refused as two things under one number)."""
        r = own_routine.get(n)
        if not r or not (r[0] <= x < r[1]) or x in nums.get(n, ()):
            return None
        return r[2] + (x - r[0])

    def unmapped(n: str, owners: list) -> list:
        """The literals of n's body inside the image the map does not reach and no owner's instructions
        show as a plain number (owners: the verified functions whose code the body is a piece of)."""
        out = []
        for x in sorted(values(bodies[n])):
            if not (0x1000 <= x < image_end) or x in omap or own_position(n, x) is not None:
                continue
            literal = any(int(m.group(1), 16) == x for m in LITERAL.finditer(bodies[n]))
            number = literal and owners and all(x in nums[f] and x not in refs[f] for f in owners)
            idiom = literal and x in WORD_IDIOMS and x not in referenced
            if not number and not idiom and not (literal and masks_only(bodies[n], x)):
                out.append(x)
        return out
    # A verified function is judged on its own body, against its own instructions' operands.
    derived_fields = {}
    for n in sorted(clean):
        if n not in bodies:
            continue
        code = code_of(n)
        moved = moved_of[n]
        why = None
        if moved:
            # The rewrite reaches hex offset literals only: a moved field also written in decimal
            # ("obj + 4"), as a stack-frame offset or as a bare literal would keep 1.0.3's number.
            decimal = {(-1 if sg == "-" else 1) * int(d) for sg, d in re.findall(r"([+-]) (\d+)\b", code)} & set(moved)
            frame = {(-1 if sg == "-" else 1) * int(d, 16) for sg, d in re.findall(r"\ba[67]\w* ([+-]) 0x([0-9a-fA-F]+)\b", code)} & set(moved)
            reached = re.sub(r"[+-] 0x[0-9a-fA-F]+\b", "", code)
            reached = re.sub(r"(_host_memory_call\([^,()]+,\s*[^,()]+,\s*)0x[0-9a-fA-F]+\b", r"\1", reached)
            bare = {int(x, 16) for x in re.findall(r"(?<![\w.])0x([0-9a-fA-F]+)\b", reached)} & {k for k in moved if k > 0}
            if decimal or frame or bare:
                kind = "in decimal" if decimal else ("as a frame offset" if frame else "as a bare literal")
                why = "a moved record field it rests on is also written %s: %s" % (
                    kind, ", ".join(hex(x) for x in sorted(decimal | frame | bare)))
        # Every offset the body adds must be one its instructions hold: a displacement (moved through
        # the function's pairs, or the same in Leyte), an immediate number, or a byte / word inside a
        # field they address (within 3 above its displacement), which moves with that field. A moved
        # displacement that is also an immediate of the function is two things under one number.
        if not why and any(k == 0 for k in moved):
            why = "a record field it rests on moved from displacement 0 (to %s), which a body writes as the bare record" % _sx(moved[0])
        own = {}
        parts = disps[n] | nums[n]
        for x in sorted(offset_literals(code)):
            if why:
                break
            if x in moved:
                if abs(x) in imms[n]:
                    why = "%s is a moved record field and an immediate number in its instructions" % _sx(x)
                continue
            # An operand of the instructions as it is (a displacement, a number - negative ones as the
            # byte, word or long the code holds) stays. Otherwise where the offset ends up in Leyte, by
            # the readings its instructions allow: a byte or word inside a field, or the sum of two
            # operands (adda.w #9 then 1(a0)); they must agree.
            if x == 0 or x in disps[n] or {abs(x), x & 0xff, x & 0xffff, x & 0xffffffff} & nums[n]:
                continue                 # an operand of its instructions, which the proof left where it was
            targets = {moved.get(d, d) + (x - d) for d in disps[n] if 0 < abs(x - d) <= 3}
            targets |= {moved.get(a, a) + moved.get(x - a, x - a) for a in parts if a > 0 and x - a > 0 and (x - a) in parts}
            if len(targets) != 1:
                why = "its body adds an offset its instructions %s: %s" % ("do not hold" if not targets else "read two ways", _sx(x))
                continue
            target = targets.pop()
            if target != x:
                own[x] = target
        if not why:
            low = sorted(x for x in values(bodies[n]) if 0x20 <= x < 0x1000 and x in refs[n])
            bad = unmapped(n, [n])
            if bad or low:
                why = "its body names %s" % "; ".join(
                    (["unmapped image addresses " + ", ".join(hex(x) for x in bad[:6])] if bad else []) +
                    (["code addresses below 0x1000 the rewrite does not reach: " + ", ".join(hex(x) for x in low[:6])] if low else []))
        if why:
            clean.discard(n)
            refused[n] = why
        elif own:
            derived_fields[n] = own
    by_address = {int(r["address"], 16) - HEADER: r["name"] for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]
                  if r["segment"].startswith("Pacific")}
    corr_names = set(by_address.values())
    private = {n for n in bodies if n.startswith("_") and n not in INFRASTRUCTURE and n not in corr_names}

    def calls(n: str) -> set:
        return {c for c in re.findall(r"\b(\w+)\(", bodies[n]) if c in bodies and c != n}

    def pieces(n: str) -> set:
        """n and the private helpers it reaches through private helpers only."""
        seen, todo = set(), [n]
        while todo:
            b = todo.pop()
            if b in seen:
                continue
            seen.add(b)
            todo += [c for c in calls(b) if c in private]
        return seen
    # Private helpers (pieces of original functions, "_name", no correspondence of their own) have no
    # instructions of their own to be proven on: one is kept on the evidence of every kept function it is
    # a piece of. Each offset it adds must be, in each of them, a displacement the proof left where it
    # was or a plain number; an offset Leyte moves anywhere (a changed or ambiguous displacement of the
    # overlay), or one no owner's instructions show, refuses it. A helper adding no offset needs no owner.
    changed_disp = {k for k, x in v["layout_pairs"].items() if k != x} | set(v["ambiguous"])
    owners_of = collections.defaultdict(list)
    for f in sorted(clean):
        for h in pieces(f) - {f}:
            owners_of[h].append(f)
    helpers = set()
    for h in sorted(private):
        offsets = offset_literals(code_of(h))
        owners = owners_of.get(h, [])
        if offsets & changed_disp:
            refused[h] = "private helper holding a record offset Leyte moves: %s" % ", ".join(_sx(x) for x in sorted(offsets & changed_disp)[:4])
            continue
        unshown = sorted(x for x in offsets
                         if x != 0 and (not owners or any(
                             x in moved_of[f] or not (x in disps[f] or {abs(x), x & 0xff, x & 0xffff, x & 0xffffffff} & nums[f]) for f in owners)))
        if unshown:
            refused[h] = "private helper adding offsets %s: %s" % (
                "no kept function it is a piece of vouches for" if not owners else "its owners' instructions do not all show unmoved",
                ", ".join(_sx(x) for x in unshown[:4]))
            continue
        bad = unmapped(h, owners)
        if bad:
            refused[h] = "private helper naming unmapped image addresses %s" % ", ".join(hex(x) for x in bad[:6])
            continue
        helpers.add(h)
    clean |= helpers
    # What a kept function calls that refuses (itself, or through the kept helpers it is made of): the
    # generated stub stops the run there until the function is verified or written for Leyte.
    open_calls = {}
    for n in sorted(clean):
        if n in private:
            continue
        reach = set()
        for b in pieces(n):
            if b in clean or b == n:
                reach |= calls(b)
        missing = sorted(c for c in reach if c not in clean and c not in INFRASTRUCTURE)
        if missing:
            open_calls[n] = missing
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
        pairs = {**(v["layout_used"].get(current) or {}), **derived_fields.get(current, {})}
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
            # The host world's service slots, reached through _host_memory_call(entry, depth, offset, ...):
            # the offset is a record displacement too (the host world's), rewritten through the same pairs.
            def host(m, _p=pairs):
                x = int(m.group(2), 16)
                return "%s0x%x" % (m.group(1), _p[x]) if x in _p else m.group(0)
            code = re.sub(r"(_host_memory_call\([^,()]+,\s*[^,()]+,\s*)0x([0-9a-fA-F]+)\b", host, code)
        def sub(m, _n=current):
            x = int(m.group(1), 16)
            own = own_position(_n, x) if _n in own_routine else None
            if own is not None:
                return "0x%x" % own
            return "0x%x" % omap[x] if 0x1000 <= x < image_end and x in omap else m.group(0)
        lines.append(LITERAL.sub(sub, code) + sep + comment)
    header = ("## GENERATED by tools/leyte/gen_overlay_port.py from original/port/pacific_conflict.gd for Leyte Gulf's\n"
              "## overlay: the functions verified by instruction against Leyte's overlay, their image addresses through\n"
              "## sources/leyte-gulf-1-0/overlay_map.json; the others refuse (a kept function reaching one stops there,\n"
              "## unless pacific_conflict_reconstructed.gd writes it for Leyte). Do not edit. Comments keep 1.0.3's\n"
              "## addresses.\n")
    manifest = {"schema": 1, "generated_by": "tools/leyte/gen_overlay_port.py", "port": "original/leyte/port/pacific_conflict.gd",
                "functions": {n: v["verified"][n] for n in sorted(clean) if n in v["verified"]},
                "helpers": sorted(n for n in clean if n not in v["verified"]),
                "relocated": {n: a for n, a in sorted(v["relocated"].items()) if n in clean},
                "placed": {n: a for n, a in sorted(v["placed"].items()) if n in clean},
                "record_fields": {n: {_sx(k): _sx(x) for k, x in sorted(v["layout_used"][n].items())}
                                  for n in sorted(clean) if v["layout_used"].get(n)},
                "derived_fields": {n: {_sx(k): _sx(x) for k, x in sorted(p.items())} for n, p in sorted(derived_fields.items()) if n in clean},
                "open_calls": open_calls,
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
