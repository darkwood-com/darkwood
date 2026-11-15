#!/usr/bin/env python3
"""Leyte Gulf host ports from Hellcats 1.0.3's, where the binaries prove it: godot/original/leyte/port/
and sources/leyte-gulf-1-0/host_ports.json (what each segment's port is and why). --check compares.

- "reuse": every ported function of the segment is byte-identical in Leyte's CODE (correspondence
  "identical"): 1.0.3's port is Leyte's, unchanged.
- "remap": the segment's ported functions are the same instructions in both binaries (same count,
  mnemonics, registers, immediates), differing only in A5-relative operands; every such operand of
  1.0.3's is in the A5 map (a5_map.json) and maps to exactly the operand Leyte has at the same
  instruction (a global the map does not reach is paired by the function's own match, listed as
  "a5_local", and refused if it contradicts the map). The port is generated from 1.0.3's with its A5 constants (signed hex literals equal to
  one of those operands, in code, not comments) replaced by Leyte's. A reviewed immediate that Leyte
  changes (IMMEDIATES: listed with its meaning, found exactly once in the port's code) is rewritten
  too. Anything else refuses. tests/leyte/port/ replays the result against Leyte's own bytes run on
  the 68000 emulator (tools/leyte/gen_leyte_id07_cases.py).
- "partial": "remap" function by function. Each ported function is checked alone as "remap" checks a
  segment; those that pass are checked again together (their pairings must agree), and the port is
  generated as "remap" generates it, with every other function's body replaced by a refusal (an
  assert naming it: not Leyte's until reconstructed). host_ports.json lists those as "unverified",
  each with the check that refused it.

Comments in a remapped port keep 1.0.3's offsets (the header says so). Needs capstone (shell.nix).

    python3 tools/leyte/gen_host_ports.py [--check]
"""
from __future__ import annotations

import json
import re
import sys
from pathlib import Path

import capstone

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
sys.path.insert(0, str(ROOT / "tools" / "leyte"))
import hfs  # noqa: E402
from tools import resedit  # noqa: E402
from a5_map import a5_operands  # noqa: E402

PORTS = {"ID01": "reuse", "ID07": "remap", "ID05": "remap", "ID06": "remap", "ID02": "partial", "ID03": "partial", "ID04": "partial", "ID09": "partial"}
## Reviewed immediates a "remap" segment's Leyte code differs in besides A5 operands, each with what it
## means: [1.0.3 instruction operand, Leyte's, the port's literal before, after, why]. Any difference
## not listed here refuses.
IMMEDIATES = {"ID07": [["#$79", "#$7c", "0x79", "0x7c", "STR# 129's strings: 1.0.3 loads 120, Leyte 123 (cmpi.w at +0x182)"]],
              "ID02": [["#$160", "#$194", "0x160", "0x194", "the image table's record size (*(A5 - 0x13b2), indexed by A5 - 0x13ae; "
                        "ID02.decomp.c: records 0x160 bytes): 1.0.3 0x160, Leyte 0x194 (muls.w in blit_image_clipped_to_region +0x1902)"]]}
OUT_DIR = ROOT / "godot" / "original" / "leyte" / "port"
MANIFEST = ROOT / "sources" / "leyte-gulf-1-0" / "host_ports.json"
MD = capstone.Cs(capstone.CS_ARCH_M68K, capstone.CS_MODE_M68K_020)
MD.skipdata = True        # go on past inline data (switch tables): else the comparison stops there unseen
LITERAL = re.compile(r"(?<![\w$])(-?)0x([0-9a-fA-F]+)\b")


def _functions(seg: str, size: int) -> list:
    """The segment's decompiled functions as (start, end, name): each ends at the next routine entry,
    a decompiled function's, a jump-table target in the segment, or a routine the runtime implements
    itself (unexported.gd): routines the inventory lacks end their predecessor too."""
    inv = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text())["functions"]
    starts = sorted((int(f["original_address"], 16), f["semantic_name"] or f["original_function"])
                    for f in inv if f["segment"] == seg)
    code0 = (ROOT / "sources" / "resedit-1-0-3" / "CODE" / "ID00.bin").read_bytes()
    entries = {a for a, _ in starts}
    for k in range(int.from_bytes(code0[8:12], "big") // 8):
        e = code0[16 + 8 * k:24 + 8 * k]
        if int.from_bytes(e[4:6], "big") == int(seg[2:]):
            entries.add(int.from_bytes(e[0:2], "big") + 4)
    # The runtime's own routines in the segment (unexported.gd: "## IDnn 0xNNN"): entries too.
    unexported = (ROOT / "godot" / "original" / "mac" / "machine" / "unexported.gd").read_text()
    entries.update(int(m, 16) for m in re.findall(r"^## %s (0x[0-9a-f]+)" % seg, unexported, re.M))
    ordered = sorted(entries)
    out = []
    for a, name in starts:
        later = [e for e in ordered if e > a]
        out.append((a, later[0] if later else size, name))
    return out


## Per segment: the A5 globals paired by the segment's own verified functions only (not in the A5
## map), each with the functions it rests on (recorded in host_ports.json as "a5_local").
LOCAL: dict = {}


def _jt_target(code0: bytes, offset: int):
    """The (segment, .bin offset) a jump-table slot (A5 offset) reaches: CODE 0's entry, its routine
    offset + 4 (the segment's header), or None for an offset that is not an entry's."""
    if offset < 0x22 or (offset - 0x22) % 8:
        return None
    k = (offset - 0x22) // 8
    entry = code0[16 + 8 * k:16 + 8 * k + 8]
    if len(entry) < 8 or entry[2:4] != b"\x3f\x3c" or entry[6:8] != b"\xa9\xf0":
        return None
    return int.from_bytes(entry[4:6], "big"), int.from_bytes(entry[0:2], "big") + 4


def _same_routine(hv: int, lv: int, hcode0: bytes, lcode0: bytes, corr_by_place: dict) -> bool:
    """Whether jump-table slot hv (1.0.3) and lv (Leyte) reach the same function: 1.0.3's target is a
    decompiled function whose correspondence puts it at Leyte's target."""
    ht, lt = _jt_target(hcode0, hv), _jt_target(lcode0, lv)
    if ht is None or lt is None or ht[0] != lt[0]:
        return False
    row = corr_by_place.get(("ID%02d" % ht[0], ht[1]))
    if row is not None:
        # Which routine the slot reaches (its code may differ: that is its own port's concern); the
        # match kind is recorded (SLOT_MATCH, host_ports.json "jt_counterparts").
        if row["leyte"] is not None and int(row["leyte"], 16) == lt[1]:
            SLOT_MATCH[hv] = "%s (%s)" % (row["name"], row["match"])
            return True
        return False
    # Not a decompiled function (CODE 9's Toolbox glue, answered by the runtime's stand-ins): the same
    # bytes from the entry through its first RTS, JMP (an) or JMP d16(PC) (that one's displacement left
    # out: it reaches the segment's shared exit, placed elsewhere), at most 64 bytes, in both segments.
    return _glue_bytes(HCODE[ht[0]], ht[1]) is not None and _glue_bytes(HCODE[ht[0]], ht[1]) == _glue_bytes(LCODE[lt[0]], lt[1])


SLOT_MATCH: dict = {}
HCODE: dict = {}
LCODE: dict = {}


def _glue_bytes(code: bytes, at: int):
    for end in range(at, min(at + 64, len(code) - 1), 2):
        w = int.from_bytes(code[end:end + 2], "big")
        if w == 0x4E75 or (w & 0xFFF8) == 0x4ED0 or w == 0x4EFA:     # rts / jmp (an) / jmp d16(pc)
            return code[at:end + 2]
    return None


def _defined(source: str) -> set:
    """The functions a port file defines (func <name>(...))."""
    return set(re.findall(r"^func (\w+)\(", source, re.M))


def _remap_constants(seg: str, hcode: bytes, lcode: bytes, corr: dict, a5map: dict, codes0: tuple, corr_by_place: dict, defined: set = None,
                     whole: bool = True) -> dict:
    """1.0.3 A5 operand -> Leyte's, over the segment's ported functions, verified instruction by
    instruction: a global through the A5 map, a jump-table slot through the map or by both slots
    reaching the same function (CODE 0's entries and the correspondence); raises on anything else."""
    out = {}
    local = LOCAL.setdefault(seg, {})
    seen_immediates = set()
    for start, end, name in _functions(seg, len(hcode)):
        if defined is not None and name not in defined:
            continue                      # implemented elsewhere (the runtime's unexported.gd): not this port's
        row = corr.get((seg, name))
        assert row and row["leyte"], "%s %s: not found in Leyte" % (seg, name)
        l0 = int(row["leyte"], 16)
        his = list(MD.disasm(hcode[start:end], start))
        lis = list(MD.disasm(lcode[l0:l0 + (end - start)], l0))
        assert len(his) == len(lis), "%s %s: instruction counts differ" % (seg, name)
        for a, b in zip(his, lis):
            ha, la = a5_operands(a), a5_operands(b)
            assert a.mnemonic == b.mnemonic and len(ha) == len(la), "%s %s +0x%x: %s %s | %s" % (seg, name, a.address, a.mnemonic, a.op_str, b.op_str)
            masked = lambda ins: re.sub(r"-?\$[0-9a-f]+\(a5\)", "A5", re.sub(r"\$[0-9a-f]+\.l|\$[0-9a-f]+\(pc\)", "X", ins.op_str))
            if a.mnemonic.startswith(("b", "j", "db")) and a.mnemonic not in ("bset", "bclr", "btst", "bchg"):
                pass                                  # branch targets move with the code
            else:
                ma, mb = masked(a), masked(b)
                for imm in IMMEDIATES.get(seg, []):
                    if imm[0] in ma and imm[1] in mb:
                        ma, mb = ma.replace(imm[0], "IMM"), mb.replace(imm[1], "IMM")
                        seen_immediates.add(imm[0])
                assert ma == mb, "%s %s +0x%x: %s %s | %s" % (seg, name, a.address, a.mnemonic, a.op_str, b.op_str)
            for hv, lv in zip(ha, la):
                if hv in a5map:
                    assert a5map[hv] == lv, "%s %s: A5 %#x maps to %#x, Leyte has %#x" % (seg, name, hv, a5map[hv], lv)
                elif hv >= 0:
                    assert _same_routine(hv, lv, codes0[0], codes0[1], corr_by_place), \
                        "%s %s: jump-table slot %#x (Leyte %#x) does not reach the same routine" % (seg, name, hv, lv)
                else:
                    # A global the map does not reach: this pair rests on this function's own match (every
                    # instruction the same but A5 operands); it must not contradict the map or itself.
                    taken = [h for h, l in a5map.items() if l == lv and h != hv]
                    assert not taken, "%s %s: A5 %#x -> %#x, but the map gives %#x to %s" % (seg, name, hv, lv, lv, [hex(h) for h in taken])
                    assert out.get(hv, lv) == lv, "%s %s: A5 %#x paired with %#x and %#x" % (seg, name, hv, out[hv], lv)
                    local.setdefault(hv, set()).add(name)
                out[hv] = lv
    unseen = {imm[0] for imm in IMMEDIATES.get(seg, [])} - seen_immediates
    # A listed immediate must be met when the whole segment is checked; one function checked alone
    # ("partial") need not hold it (the port's literal is still required exactly once, below).
    assert not (whole and unseen), "%s: listed immediates not found: %s" % (seg, sorted(unseen))
    return out


FROM_MAP: dict = {}
RELOCATED: dict = {}


def _bodies(source: str) -> tuple[dict, dict]:
    """The file's top-level functions as name -> code (comments left out), and its integer constants."""
    bodies, consts, current = {}, {}, None
    for line in source.split("\n"):
        code = line.partition("#")[0]
        m = re.match(r"^(?:static )?func (\w+)\(", line)
        c = re.match(r"^const (\w+)\s*(?::\s*\w+)?\s*:?=\s*(-?)0x([0-9a-fA-F]+)\b", line)
        if m:
            current = m.group(1)
            bodies[current] = code
        elif c:
            consts[c.group(1)] = (-1 if c.group(2) else 1) * int(c.group(3), 16)
            current = None
        elif current and (line.startswith(("\t", " ")) or not line.strip()):
            bodies[current] += "\n" + code
        else:
            current = None
    return bodies, consts


def _reach(name: str, bodies: dict) -> set:
    seen, todo = set(), [name]
    while todo:
        b = todo.pop()
        if b in seen or b not in bodies:
            continue
        seen.add(b)
        todo += [c for c in re.findall(r"\b(\w+)\(", bodies[b]) if c in bodies and c != b]
    return seen


def _values(body: str, consts: dict) -> set:
    vals = {(-1 if m.group(1) else 1) * int(m.group(2), 16) for m in LITERAL.finditer(body)}
    return vals | {consts[w] for w in re.findall(r"\b([A-Z_][A-Z0-9_]*)\b", body) if w in consts}


DEFAULTS = {"int": "0", "bool": "false", "Array": "[]", "Dictionary": "{}", "String": "\"\"", "float": "0.0"}


def _refuse(source: str, refused: dict) -> str:
    """The port with each refused function's body replaced by an assert naming it (a typed default
    returned after it, so the class still parses); its signature and the comments above it kept."""
    out, skipping = [], False
    for line in source.split("\n"):
        m = re.match(r"^(?:static )?func (\w+)\(.*\)(?:\s*->\s*(\w+))?\s*:(.*)$", line)
        if m:
            skipping = m.group(1) in refused
            if skipping:
                assert not m.group(3).strip(), "%s: a one-line function cannot be refused in place" % m.group(1)
                out.append(line)
                out.append('\tassert(false, "%s: not verified for Leyte Gulf (sources/leyte-gulf-1-0/host_ports.json unverified)")' % m.group(1))
                if m.group(2) and m.group(2) != "void":
                    out.append("\treturn %s" % DEFAULTS.get(m.group(2), "null"))
                continue
        elif skipping and (line.startswith(("\t", " ")) or not line.strip()):
            if not line.strip():
                out.append(line)
            continue
        else:
            skipping = False
        out.append(line)
    return "\n".join(out)


def _signed_hex(v: int) -> str:
    return "-0x%x" % -v if v < 0 else "0x%x" % v


def build() -> dict:
    ley = hfs.find(ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk", "Leyte Gulf:Leyte Gulf")
    lcode = {e["id"]: e["payload"] for e in resedit.parse_fork(ley.rsrc) if e["type"] == "CODE"}
    corr = {(r["segment"], r["name"]): r for r in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "correspondence.json").read_text())["functions"]}
    a5map = {m["hellcats_1_0_3"]: m["leyte"] for m in json.loads((ROOT / "sources" / "leyte-gulf-1-0" / "a5_map.json").read_text())["mapped"]}
    codes0 = ((ROOT / "sources" / "resedit-1-0-3" / "CODE" / "ID00.bin").read_bytes(), lcode[0])
    for k in range(1, 10):
        HCODE[k] = (ROOT / "sources" / "resedit-1-0-3" / "CODE" / ("ID%02d.bin" % k)).read_bytes()
        LCODE[k] = lcode[k]
    corr_by_place = {(r["segment"], int(r["address"], 16)): r for r in corr.values()}
    files, manifest = {}, {}
    for seg, mode in PORTS.items():
        n = int(seg[2:])
        hcode = (ROOT / "sources" / "resedit-1-0-3" / "CODE" / ("%s.bin" % seg)).read_bytes()
        source = (ROOT / "godot" / "original" / "port" / ("%s.gd" % seg)).read_text()
        names = [f[2] for f in _functions(seg, len(hcode))]
        if mode == "reuse":
            for start, end, name in _functions(seg, len(hcode)):
                row = corr[(seg, name)]
                l0 = int(row["leyte"], 16)
                assert row["match"] == "identical" and lcode[n][l0:l0 + end - start] == hcode[start:end], "%s %s: not byte-identical" % (seg, name)
            manifest[seg] = {"mode": "reuse", "port": "original/port/%s.gd" % seg, "functions": names,
                             "evidence": "every ported function byte-identical in Leyte's CODE %d" % n}
            continue
        defined = _defined(source)
        elsewhere = [nm for nm in names if nm not in defined]
        names = [nm for nm in names if nm in defined]
        unverified = {}
        if mode == "partial":
            pairs = {}
            for nm in names:
                try:
                    pairs[nm] = _remap_constants(seg, hcode, lcode[n], corr, a5map, codes0, corr_by_place, {nm}, whole=False)
                except AssertionError as err:
                    unverified[nm] = str(err)
            def refuse_conflicts() -> None:
                # A global the map does not reach, paired differently by two functions: neither is taken
                # (each may then be found at its true Leyte routine below).
                seen = {}
                for nm, pr in pairs.items():
                    for hv, lv in pr.items():
                        if hv < 0 and hv not in a5map:
                            seen.setdefault(hv, {}).setdefault(lv, []).append(nm)
                for hv, by in seen.items():
                    if len(by) > 1:
                        for lv, users in by.items():
                            for nm in users:
                                unverified[nm] = "%s %s: A5 %s paired with %s here, %s by %s" % (
                                    seg, nm, _signed_hex(hv), _signed_hex(lv),
                                    " / ".join(_signed_hex(o) for o in by if o != lv),
                                    ", ".join(sorted({u for o, us in by.items() if o != lv for u in us})))
                for nm in list(pairs):
                    if nm in unverified:
                        del pairs[nm]
            refuse_conflicts()
            # A refused function placed where Leyte has another routine (the correspondence pairs tiny
            # wrappers by shape: hlock_a5safe's HLock call is through a slot that is HNoPurge in 1.0.3)
            # is looked for among Leyte's routine entries (link a6 or jump-table targets in the
            # segment) not taken by a verified function: exactly one passing every check relocates it
            # (host_ports.json "relocated"); none or several leave it refused.
            taken = {int(corr[(seg, nm)]["leyte"], 16) for nm in pairs}
            entries = sorted({k for k in range(0, len(lcode[n]) - 1, 2) if lcode[n][k:k + 2] == b"\x4e\x56"}
                             | {t for (sg, t) in (_jt_target(codes0[1], 0x22 + 8 * k) or (0, 0) for k in range(0x100)) if sg == n})
            for nm in sorted(unverified):
                if "its port reaches" in unverified[nm]:
                    continue
                found = []
                for cand in entries:
                    if cand in taken:
                        continue
                    trial = dict(corr)
                    trial[(seg, nm)] = dict(corr.get((seg, nm), {}), leyte="0x%x" % cand)
                    saved = {k: set(v) for k, v in LOCAL.get(seg, {}).items()}
                    try:
                        pr = _remap_constants(seg, hcode, lcode[n], trial, a5map, codes0, corr_by_place, {nm}, whole=False)
                        # and agreeing with the verified functions' own pairings
                        if all(other.get(hv, lv) == lv for hv, lv in pr.items() if hv < 0 and hv not in a5map
                               for other in pairs.values()):
                            found.append((cand, pr))
                    except (AssertionError, KeyError, IndexError):
                        pass
                    LOCAL[seg] = saved
                if len(found) == 1:
                    cand, pr = found[0]
                    if corr.get((seg, nm), {}).get("leyte") != "0x%x" % cand:    # found where it was: not moved
                        RELOCATED.setdefault(seg, {})[nm] = cand
                    corr[(seg, nm)] = dict(corr.get((seg, nm), {}), leyte="0x%x" % cand)
                    pairs[nm] = pr
                    taken.add(cand)
                    del unverified[nm]
                elif found:
                    unverified[nm] += " (and %d other Leyte routines pass: %s)" % (len(found), ", ".join("0x%x" % c for c, _ in found))
            refuse_conflicts()
            # "a5 - 0x149e" is the global -0x149e: written "a5 + -0x149e" (the same in GDScript) so the
            # rewrite and the checks below see it as the A5 operand it is.
            # The same for the other A5 bases the port writes: CurrentA5 (_current_a5()) and the local
            # holding it (world).
            original_source = re.sub(r"(\ba5|\bworld|\b_current_a5\(\)) - 0x", r"\1 + -0x", source)
            bodies, consts = _bodies(original_source)
            while True:
                LOCAL[seg] = {}                # the pairings again, from the verified functions together
                names = [nm for nm in names if nm not in unverified]
                remap = _remap_constants(seg, hcode, lcode[n], corr, a5map, codes0, corr_by_place, set(names), whole=False)
                # What each verified function's port reaches (its body and every function or helper of
                # the file it calls, transitively): a refused function refuses it; an A5 global (a
                # literal or constant from -0x2400, the A5 world's bottom, to -0x200) neither its instructions nor the A5 map pair
                # refuses it; one the map pairs is taken from the map.
                changed = False
                for nm in names:
                    reach = _reach(nm, bodies)
                    refused = sorted(reach & set(unverified))
                    values = {v for b in reach for v in _values(bodies[b], consts) if -0x2400 <= v <= -0x200}
                    unpaired = sorted(v for v in values if v not in remap and v not in a5map)
                    if refused or unpaired:
                        unverified[nm] = "%s %s: its port reaches %s" % (seg, nm, "; ".join(
                            (["refused " + ", ".join(refused)] if refused else []) +
                            (["A5 globals not paired: " + ", ".join(_signed_hex(v) for v in unpaired)] if unpaired else [])))
                        changed = True
                if changed:
                    continue
                for nm in names:
                    for b in _reach(nm, bodies):
                        for v in _values(bodies[b], consts):
                            if -0x2400 <= v <= -0x200 and v not in remap:
                                remap[v] = a5map[v]
                                FROM_MAP.setdefault(seg, set()).add(v)
                source = _refuse(original_source, unverified)
                # A global a verified function reaches that the port does not write as a constant (nor
                # derive from one, as below): that function is refused too, and the rest checked again.
                present = {(-1 if m.group(1) else 1) * int(m.group(2), 16)
                           for line in source.split("\n") for m in LITERAL.finditer(line.partition("#")[0])}
                kept = set(remap) & present
                absent = {v for v in set(remap) - kept if v < 0 and remap[v] != v
                          and not any(c < 0 and abs(v - c) < 0x40 and remap[c] - c == remap[v] - v for c in kept)}
                if not absent:
                    break
                for nm in names:
                    hit = sorted(set(pairs[nm]) & absent)
                    if hit:
                        unverified[nm] = "%s %s: A5 globals the port does not write as constants: %s" % (
                            seg, nm, ", ".join(_signed_hex(v) for v in hit))
        else:
            remap = _remap_constants(seg, hcode, lcode[n], corr, a5map, codes0, corr_by_place, defined)
        replaced = set()

        def sub(m):
            v = (-1 if m.group(1) else 1) * int(m.group(2), 16)
            if v in remap:
                replaced.add(v)
                return _signed_hex(remap[v])
            return m.group(0)

        lines = []
        source = re.sub(r"^class_name (\w+)", r"class_name Leyte\1", source, count=1, flags=re.M)   # its own global name
        for line in source.split("\n"):
            code, sep, comment = line.partition("#")
            lines.append(LITERAL.sub(sub, code) + sep + comment)
        # A jump-table slot the port does not hold as a constant (it calls the routine directly, as
        # ID07 calls ID02's memmove) is verified above and needs no rewrite; a global must be rewritten.
        # A global Leyte keeps at the same offset needs no rewrite, however the port writes it.
        missing = {v for v in set(remap) - replaced if v < 0 and remap[v] != v}
        # An address the port computes from a base constant (_g(A5_MATCHES) + 2 for A5 - 0xbe): right
        # when a rewritten constant within 0x40 of it moves by the same amount in Leyte. Listed as
        # "derived"; the emulator cases confirm the behaviour.
        derived = {}
        for v in sorted(missing):
            bases = [c for c in replaced if c < 0 and abs(v - c) < 0x40 and remap[c] - c == remap[v] - v]
            if bases:
                derived[v] = min(bases, key=lambda c: abs(v - c))
        missing -= set(derived)
        assert not missing, "%s: A5 globals not written as constants in the port: %s" % (seg, sorted(hex(v) for v in missing))
        applied_immediates = []
        for imm in IMMEDIATES.get(seg, []):
            pattern = re.compile(r"(?<![\w$])%s\b" % re.escape(imm[2]))
            hits = sum(len(pattern.findall(line.partition("#")[0])) for line in lines)
            # "partial": none when the function holding it is refused (its body gone); listed only if applied.
            if mode == "partial" and hits == 0:
                continue
            applied_immediates.append(imm)
            assert hits == 1, "%s: the literal %s for %s appears %d times in the port's code" % (seg, imm[2], imm[4], hits)
            lines = [pattern.sub(imm[3], line.partition("#")[0]) + "".join(line.partition("#")[1:]) for line in lines]
        header = ("## GENERATED by tools/leyte/gen_host_ports.py from original/port/%s.gd for Leyte Gulf: its A5\n"
                  "## constants remapped through sources/leyte-gulf-1-0/a5_map.json (%s), verified instruction by\n"
                  "## instruction against both binaries; do not edit. Comments keep 1.0.3's offsets.\n") % (
                      seg, ", ".join("%s -> %s" % (_signed_hex(k), _signed_hex(remap[k])) for k in sorted(remap)))
        files[OUT_DIR / ("%s.gd" % seg)] = header + "\n".join(lines)
        manifest[seg] = {"mode": "remap", "port": "original/leyte/port/%s.gd" % seg, "functions": names,
                         "a5": {_signed_hex(k): _signed_hex(v) for k, v in sorted(remap.items())},
                         "immediates": [{"1.0.3": i[2], "leyte": i[3], "meaning": i[4]} for i in applied_immediates],
                         "a5_local": {_signed_hex(k): sorted(v) for k, v in sorted(LOCAL.get(seg, {}).items())},
                         "elsewhere": elsewhere,
                         "jt_counterparts": {_signed_hex(k): SLOT_MATCH[k] for k in sorted(remap) if k in SLOT_MATCH},
                         "a5_derived": {_signed_hex(k): "from %s" % _signed_hex(v) for k, v in sorted(derived.items())},
                         "evidence": "same instructions in Leyte's CODE %d, A5 operands only differing, each through the A5 map" % n}
        if mode == "partial":
            manifest[seg]["mode"] = "partial"
            manifest[seg]["unverified"] = dict(sorted(unverified.items()))
            manifest[seg]["a5_from_map"] = sorted(_signed_hex(v) for v in FROM_MAP.get(seg, ()))
            manifest[seg]["relocated"] = {nm: "0x%x" % v for nm, v in sorted(RELOCATED.get(seg, {}).items())}
            manifest[seg]["evidence"] = ("per function: the listed functions are the same instructions in Leyte's CODE %d, "
                                         "A5 operands only differing, each through the A5 map; the unverified ones refuse" % n)
    files[MANIFEST] = json.dumps({"schema": 1, "generated_by": "tools/leyte/gen_host_ports.py", "segments": manifest}, indent=1) + "\n"
    return files


def main() -> int:
    files = build()
    if "--check" in sys.argv:
        bad = [p for p, text in files.items() if not p.is_file() or p.read_text() != text]
        for p in bad:
            print("FAIL Leyte host ports: %s is not what the generator gives" % p.relative_to(ROOT))
        if not bad:
            print("PASS Leyte host ports (%d files)" % len(files))
        return 1 if bad else 0
    for p, text in files.items():
        p.parent.mkdir(parents=True, exist_ok=True)
        p.write_text(text)
        print("wrote %s" % p.relative_to(ROOT))
    return 0


if __name__ == "__main__":
    sys.exit(main())
