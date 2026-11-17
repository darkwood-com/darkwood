#!/usr/bin/env python3
"""Where Leyte Gulf's overlay port stands, function by function, against Hellcats 1.0.3's port
(godot/original/port/pacific_conflict.gd), so the remaining work is planned from the whole call graph
rather than found one start probe at a time. Read-only: it prints, it writes nothing.

A 1.0.3 port function is, for Leyte:
- kept          the generated port keeps it (sources/leyte-gulf-1-0/overlay_port.json: verified by
                instruction, or a private helper taken as it is);
- reconstructed godot/original/leyte/port/pacific_conflict_reconstructed.gd defines it under the same
                name (written from Leyte's bytes; it overrides the generated stub);
- missing       neither: the generated stub refuses when reached. Shown with where Leyte has the
                routine (sources/leyte-gulf-1-0/overlay_placement.json: address, instruction counts,
                how many match) and the manifest's reason (an instruction that differs, a helper
                holding a moved field, ...).

The call graph is the 1.0.3 port's own (function names called in each body). Reports:
    python3 tools/leyte/overlay_status.py                 totals, the 22 hooks, the frontier
    python3 tools/leyte/overlay_status.py --hook 10       hook 10's whole closure, missing first
    python3 tools/leyte/overlay_status.py --reach NAME    one function's closure
    python3 tools/leyte/overlay_status.py --missing       every missing function, most-needed first
The frontier: missing functions that an available function calls directly (the next stubs a run can
reach), with how many available functions call each.
"""
from __future__ import annotations

import collections
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
PORT = ROOT / "godot" / "original" / "port" / "pacific_conflict.gd"
RECONSTRUCTED = ROOT / "godot" / "original" / "leyte" / "port" / "pacific_conflict_reconstructed.gd"
MANIFEST = ROOT / "sources" / "leyte-gulf-1-0" / "overlay_port.json"
PLACEMENT = ROOT / "sources" / "leyte-gulf-1-0" / "overlay_placement.json"
OVERLAY_METHODS = ROOT / "godot" / "original" / "mac" / "data" / "overlay_methods.json"
INFRASTRUCTURE = {"_init", "_jsr", "_bsr_file"}
FUNC = re.compile(r"^(?:static )?func (\w+)\(", re.M)


def _bodies(source: str) -> dict:
    """The file's top-level functions as name -> code, comments left out (gen_host_ports._bodies' walk,
    kept here so the report needs no disassembler)."""
    bodies, current = {}, None
    for line in source.split("\n"):
        code = line.partition("#")[0]
        m = re.match(r"^(?:static )?func (\w+)\(", line)
        if m:
            current = m.group(1)
            bodies[current] = code
        elif current and (line.startswith(("\t", " ")) or not line.strip()):
            bodies[current] += "\n" + code
        else:
            current = None
    return bodies


def load() -> dict:
    src = PORT.read_text()
    bodies = _bodies(src)
    manifest = json.loads(MANIFEST.read_text())
    kept = set(manifest["functions"]) | set(manifest["helpers"])
    rec_src = RECONSTRUCTED.read_text()
    reconstructed = set(FUNC.findall(rec_src))
    names = [n for n in bodies if n not in INFRASTRUCTURE]
    calls = {n: sorted({c for c in re.findall(r"\b(\w+)\(", bodies[n]) if c in bodies and c != n and c not in INFRASTRUCTURE})
             for n in names}
    hooks = [int(x, 16) for x in re.search(r"const OVERLAY_HOOKS := \[(.*?)\]", src, re.S).group(1).replace("\n", " ").split(",")]
    leyte_hooks = [int(x, 16) for x in re.search(r"const LEYTE_OVERLAY_HOOKS: Array = \[(.*?)\]", rec_src, re.S).group(1).replace("\n", " ").split(",")]
    methods = json.loads(OVERLAY_METHODS.read_text())
    methods = methods.get("methods", methods)
    registered = {int(a, 16) for a in re.findall(r"code_table\[(0x[0-9a-f]+)\]", rec_src)}
    registered |= {int(a, 16) for a in re.findall(r"\[(0x[0-9a-f]+), \"\w+\"\]", rec_src)}
    registered |= {int(a, 16) for a in re.findall(r"code_table\[(0x[0-9a-f]+)\]", (ROOT / "godot" / "original" / "leyte" / "port" / "pacific_conflict.gd").read_text())}
    # Code pointers the 1.0.3 port registers (handlers, factories, renderer routines): name by address.
    pointers = {}
    for a, rest in re.findall(r"^\tcode_table\[(0x[0-9a-f]+)\] = (.*)$", src, re.M):
        m = re.search(r"Callable\(self, \"(\w+)\"\)|return (\w+)\(", rest)
        if m:
            pointers[int(a, 16)] = m.group(1) or m.group(2)
    placed = {r["name"]: r for r in json.loads(PLACEMENT.read_text())["routines"]} if PLACEMENT.is_file() else {}
    return {"bodies": bodies, "names": names, "calls": calls, "kept": kept, "reconstructed": reconstructed, "placed": placed,
            "manifest": manifest, "hooks": hooks, "leyte_hooks": leyte_hooks, "methods": methods,
            "registered": registered, "pointers": pointers}


def state(d: dict, n: str) -> str:
    if n in d["reconstructed"]:
        return "reconstructed"
    if n in d["kept"]:
        return "kept"
    return "missing"


def reach(d: dict, n: str) -> set:
    seen, todo = set(), [n]
    while todo:
        b = todo.pop()
        if b in seen or b not in d["calls"]:
            continue
        seen.add(b)
        todo += d["calls"][b]
    return seen


def why(d: dict, n: str) -> str:
    """Where Leyte has the routine (overlay_placement.json: its address, the two instruction counts and how
    many match) and why the generated port refuses it."""
    p = d["placed"].get(n)
    where = "Leyte %-7s %4d/%4d instr, %4d match  " % (p["leyte"], p["instructions"][0], p["instructions"][1], p["matching"]) if p else (
        "a piece of other routines            " if n.startswith("_") else "not placed in Leyte                  ")
    return where + re.sub(r"^placed at 0x[0-9a-f]+ \(overlay_placement.json\), where ", "", d["manifest"]["refused"].get(n, "not in the manifest"))[:80]


def closure_report(d: dict, title: str, root: str) -> None:
    r = reach(d, root)
    by = collections.Counter(state(d, n) for n in r)
    print("%s: %s is %s; closure %d functions: %d kept, %d reconstructed, %d missing" % (
        title, root, state(d, root), len(r), by["kept"], by["reconstructed"], by["missing"]))
    for n in sorted(n for n in r if state(d, n) == "missing"):
        print("    missing %-40s %s" % (n, why(d, n)))


def main() -> int:
    d = load()
    args = sys.argv[1:]
    if "--hook" in args:
        k = int(args[args.index("--hook") + 1])
        closure_report(d, "hook %d" % k, d["methods"].get(hex(d["hooks"][k]), "?"))
        return 0
    if "--reach" in args:
        closure_report(d, "reach", args[args.index("--reach") + 1])
        return 0
    by = collections.Counter(state(d, n) for n in d["names"])
    addressed = [n for n in d["names"] if not n.startswith("_")]
    print("1.0.3 overlay port: %d functions (%d named routines, %d private pieces)" % (
        len(d["names"]), len(addressed), len(d["names"]) - len(addressed)))
    print("Leyte: %d kept by the generated port, %d reconstructed, %d missing; %d routines written for Leyte only" % (
        by["kept"], by["reconstructed"], by["missing"], len(d["reconstructed"] - set(d["names"]))))
    available = {n for n in d["names"] if state(d, n) != "missing"}
    needed = collections.Counter()
    for n in available:
        for c in d["calls"][n]:
            if c not in available:
                needed[c] += 1
    if "--missing" in args:
        callers = collections.Counter(c for n in d["names"] for c in d["calls"][n] if c not in available)
        for n in sorted((n for n in d["names"] if n not in available), key=lambda n: (-callers[n], n)):
            print("  %3d callers  %-42s %s" % (callers[n], n, why(d, n)))
        return 0
    print("\nHooks (the 22 the overlay stores in the host world):")
    for k, h in enumerate(d["hooks"]):
        name = d["methods"].get(hex(h), "?")
        if name not in d["calls"]:
            print("  %2d %-36s Leyte 0x%x: no 1.0.3 port function of that name" % (k, name, d["leyte_hooks"][k]))
            continue
        r = reach(d, name)
        miss = sorted(n for n in r if n not in available)
        live = "registered" if d["leyte_hooks"][k] in d["registered"] else "NOT registered"
        print("  %2d %-36s Leyte 0x%-5x %-14s %-13s closure %3d, missing %3d" % (
            k, name, d["leyte_hooks"][k], live, state(d, name), len(r), len(miss)))
    print("\nCode pointers the 1.0.3 port registers (handlers, factories, renderer routines): %d, of which missing:" % len(d["pointers"]))
    miss_ptr = sorted({n for n in d["pointers"].values() if n in d["calls"] and n not in available})
    print("  " + ", ".join(miss_ptr))
    print("\nFrontier (missing, called directly by an available function), most-needed first:")
    for n, c in needed.most_common():
        print("  %3d  %-42s %s" % (c, n, why(d, n)))
    return 0


if __name__ == "__main__":
    sys.exit(main())
