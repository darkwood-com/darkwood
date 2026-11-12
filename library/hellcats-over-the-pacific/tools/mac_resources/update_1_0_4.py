#!/usr/bin/env python3
"""Rebuild the Hellcats 1.0.4 resource fork from the original 1.0.3 disk and the original updater.

The updater on sources/mac-os-9/Version_1_0_4.dsk ("Hellcats 1.0.3 --> 1.0.4", APPL/PATa) is
self-describing (see docs/reference/RESOURCE_ANALYSIS.md, "Hellcats 1.0.4"):

- its data fork names the file it patches (Str31 "Hellcats", APPL/HELL, twice: before and after)
  and lists the resources it installs, one 10-byte record each: 01 03 80 00, type, id;
- its resource fork carries those payloads under their type XORed with 0x01 (BNED = CODE,
  roe! = snd, wdsr = vers, IDM0 = HEL1, RH[D = SIZE) and named "1-" + the installed name, beside
  the updater's own resources and a CHK~ table.

The rebuild replaces each listed resource of the 1.0.3 application's fork with the updater's
payload, adds the "1-" payloads the list leaves out (SIZE 0 and 1, which 1.0.3 lacks: how the
updater installs them, perhaps the record at 0x80, is not decoded, but the 1.0.4 copy holds them
byte for byte), and compares the result, resource by resource, with the independent 1.0.4 copy in
sources/mac-os-9/Hellcats_1_0_4.appledouble.

    python3 tools/mac_resources/update_1_0_4.py           # JSON report
    python3 tools/mac_resources/update_1_0_4.py --check   # fails unless the expected result holds
    python3 tools/mac_resources/update_1_0_4.py --write   # (re)writes sources/hellcats-1-0-4/

sources/hellcats-1-0-4/ is the 1.0.4 delta over sources/resedit-1-0-3/: the resources 1.0.4
changes or adds, as <TYPE>/<id>.bin (CODE 2 as CODE/ID02.bin), and manifest.json (their hashes,
the CODE 2 patch, the new routines). It is generated from the two floppies, never edited; --check
also fails when it differs from a fresh rebuild. CODE/ID02.delta.decomp.c, the semantic
reconstruction of the appended code, is the one hand-written file there.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
import hfs  # noqa: E402
from tools import resedit  # noqa: E402

DISK_1_0_3 = ROOT / "sources" / "mac-os-9" / "Hellcats_1_0_3.dsk"
APP_1_0_3 = "Hellcats:Hellcats:Hellcats"
DISK_UPDATER = ROOT / "sources" / "mac-os-9" / "Version_1_0_4.dsk"
UPDATER = "Update to v1.0.4:Hellcats 1.0.3 --> 1.0.4"
INSTALL_PREFIX = "1-"
SCRIPT_RECORDS = 0xB0
RECORD = b"\x01\x03\x80\x00"
# Where the rebuild and the AppleDouble copy may differ: resources the game itself writes (the saved
# settings HEL1 127, pilots and scores HEL1 128) and the caches it adds (HEL2).
WRITTEN_BY_PLAY = {("HEL1", 127), ("HEL1", 128)}
CACHE_TYPES = {"HEL2"}


DELTA = ROOT / "sources" / "hellcats-1-0-4"
FOLDER = {"snd ": "snds"}
# The code appended to CODE 2 (RESOURCE_ANALYSIS.md "Hellcats 1.0.4"): offset -> role, from its
# disassembly; the replaced routines are named after the 1.0.3 entries that jump to them.
NEW_ROUTINES = [
    ("0x4e1a", "sound_double_back (Pascal, RTD #8): mixes the four channels into the next buffer; base of the driver's data"),
    ("0x4fa6", "alloc_sound_buffer_node (1.0.4): pops and fills a node of a channel's free list"),
    ("0x5006", "init_sound_system (1.0.4): SndNewChannel, primes the buffers, SndPlayDoubleBuffer"),
    ("0x51fe", "play_sample_on_channel (1.0.4)"),
    ("0x52f4", "enqueue_sample_on_channel (1.0.4)"),
    ("0x541e", "set_sound_playback_rate (1.0.4)"),
    ("0x547c", "release_channel_loop (1.0.4)"),
    ("0x5506", "silence_sound_channel (1.0.4)"),
    ("0x5568", "is_channel_playing (1.0.4)"),
    ("0x55ba", "shutdown_sound (1.0.4): SndDisposeChannel, DisposePtr"),
    ("0x5634", "empty routine (RTS)"),
]
DATA_AREA = ("0x5636", "0x6100")


LISTED: set = set()


def delta_files(rebuilt: dict, report: dict) -> dict[Path, bytes]:
    """sources/hellcats-1-0-4/: the changed resources and their manifest."""
    files: dict[Path, bytes] = {}
    entries = []
    for r in report["resources"]:
        if not r["changed_from_1_0_3"]:
            continue
        key = (r["type"], r["id"])
        e = rebuilt[key]
        folder = FOLDER.get(key[0], key[0])
        path = DELTA / folder / (f"ID{key[1]:02d}.bin" if key[0] == "CODE" else f"{key[1]}.bin")
        files[path] = e["payload"]
        entries.append({"key": f"{key[0]}:{key[1]}", "path": str(path.relative_to(ROOT)), "name": e["name"],
                        "attributes": f"0x{e['attributes']:02X}", "size": len(e["payload"]),
                        "sha256": hashlib.sha256(e["payload"]).hexdigest(),
                        "change": "listed by the updater's script" if key in LISTED else "carried, not listed"})
    manifest = {"schema": "hellcats-version-delta-v1", "version": "1.0.4", "base_version": "1.0.3",
                "base": "sources/resedit-1-0-3", "generated_by": "tools/mac_resources/update_1_0_4.py --write",
                "sources": {str(p.relative_to(ROOT)): hashlib.sha256(p.read_bytes()).hexdigest()
                            for p in (DISK_1_0_3, DISK_UPDATER)},
                "resources": entries, "code2": report["code2"],
                "new_routines": [{"offset": o, "role": r} for o, r in NEW_ROUTINES],
                "data_area": {"start": DATA_AREA[0], "end": DATA_AREA[1]},
                "unchanged": "every other resource equals sources/resedit-1-0-3 (update_1_0_4.py --check)"}
    files[DELTA / "manifest.json"] = (json.dumps(manifest, indent=2) + "\n").encode()
    return files


def decode_type(t: str) -> str:
    return bytes(b ^ 1 for b in t.encode("mac_roman")).decode("mac_roman")


def pstring(b: bytes, at: int) -> str:
    return b[at + 1:at + 1 + b[at]].decode("mac_roman")


def script(data: bytes) -> dict:
    """The updater's data fork: the target file and the resources it installs."""
    records = []
    at = SCRIPT_RECORDS
    while at + 10 <= len(data):
        if data[at:at + 4] != RECORD:
            raise ValueError(f"unexpected script record at 0x{at:x}: {data[at:at + 10].hex()}")
        records.append((data[at + 4:at + 8].decode("mac_roman"), struct.unpack(">h", data[at + 8:at + 10])[0]))
        at += 10
    if at != len(data):
        raise ValueError("trailing bytes after the script records")
    return {"source_name": pstring(data, 0x00), "source_type": data[0x40:0x48].decode("mac_roman"),
            "target_name": pstring(data, 0x48), "target_type": data[0x68:0x70].decode("mac_roman"),
            "date_word_0x74": f"0x{struct.unpack('>I', data[0x74:0x78])[0]:08x}",
            "installs": records}


def rebuild() -> tuple[dict, dict, dict]:
    base = {(e["type"], e["id"]): e for e in resedit.parse_fork(hfs.find(DISK_1_0_3, APP_1_0_3).rsrc)}
    updater = hfs.find(DISK_UPDATER, UPDATER)
    plan = script(updater.data)
    payloads = {}
    for e in resedit.parse_fork(updater.rsrc):
        if e["name"] and e["name"].startswith(INSTALL_PREFIX):
            payloads[(decode_type(e["type"]), e["id"])] = e
    rebuilt = dict(base)
    for key in plan["installs"]:
        if key not in payloads:
            raise ValueError(f"the script installs {key} but the updater carries no payload for it")
        e = dict(payloads[key])
        e["type"], e["name"] = key[0], e["name"][len(INSTALL_PREFIX):] or base.get(key, {}).get("name")
        rebuilt[key] = e
    for key, payload in payloads.items():
        if key not in plan["installs"]:
            e = dict(payload)
            e["type"], e["name"] = key[0], e["name"][len(INSTALL_PREFIX):] or None
            rebuilt[key] = e
    return base, rebuilt, {"script": plan, "payloads": payloads}


def compare(base: dict, rebuilt: dict, payloads: dict) -> dict:
    fork, _ = resedit.extract_appledouble_resource_fork(resedit.APPLEDOUBLE)
    reference = {(e["type"], e["id"]): e for e in resedit.parse_fork(fork)}
    rows = []
    for key in sorted(set(rebuilt) | set(reference), key=lambda k: (k[0], k[1])):
        mine, theirs = rebuilt.get(key), reference.get(key)
        status = ("only_in_rebuild" if theirs is None else "only_in_appledouble" if mine is None
                  else "identical" if mine["payload"] == theirs["payload"] else "differs")
        rows.append({"type": key[0], "id": key[1], "status": status,
                     "installed_by_updater": key in [tuple(k) for k in payloads],
                     "changed_from_1_0_3": mine is not None and (key not in base or base[key]["payload"] != mine["payload"]),
                     "sha256": hashlib.sha256(mine["payload"]).hexdigest() if mine else None})
    return {"resources": rows}


def code2_patch(base: dict, rebuilt: dict) -> dict:
    """CODE 2 of 1.0.4 against 1.0.3, by the 1.0.3 functions (docs/DECOMPILATION_INVENTORY.json):
    which keep their bytes and offset, which entries were overwritten (and with what jump), and the
    code appended after the 1.0.3 segment's end."""
    old, new = base[("CODE", 2)]["payload"], rebuilt[("CODE", 2)]["payload"]
    inventory = json.loads((ROOT / "docs" / "DECOMPILATION_INVENTORY.json").read_text(encoding="utf-8"))["functions"]
    starts = sorted((int(f["original_address"], 16), f["semantic_name"] or f["original_function"])
                    for f in inventory if f["segment"] == "ID02")
    same, patched = [], []
    for k, (start, name) in enumerate(starts):
        end = starts[k + 1][0] if k + 1 < len(starts) else len(old)
        if old[start:end] == new[start:end]:
            same.append(name)
            continue
        changed = [i for i in range(start, end) if old[i] != new[i]]
        entry = {"function": name, "offset": f"0x{start:x}", "changed_bytes": len(changed)}
        if new[start:start + 2] == b"\x4e\xfa" and max(changed) < start + 4:   # JMP d16(PC)
            entry["jmp_to"] = f"0x{start + 2 + struct.unpack('>h', new[start + 2:start + 4])[0]:x}"
            entry["overwritten"] = old[start:start + 4].hex()
        patched.append(entry)
    return {"old_size": len(old), "new_size": len(new), "unchanged_functions": len(same),
            "prefix_unchanged": old[:starts[0][0]] == new[:starts[0][0]],
            "patched_functions": patched,
            "appended": {"start": f"0x{len(old):x}", "end": f"0x{len(new):x}", "size": len(new) - len(old),
                         "old_bytes_kept_below": old == new[:len(old)] or None}}


def expectations(report: dict) -> list[str]:
    errors = []
    for r in report["resources"]:
        key = (r["type"], r["id"])
        if r["status"] == "identical":
            continue
        if key in WRITTEN_BY_PLAY and r["status"] == "differs":
            continue
        if r["type"] in CACHE_TYPES and r["status"] == "only_in_appledouble":
            continue
        errors.append(f"{r['type']}:{r['id']} {r['status']}")
    code2 = report["code2"]
    appended = int(code2["appended"]["start"], 16), int(code2["appended"]["end"], 16)
    if not code2["prefix_unchanged"]:
        errors.append("CODE 2's bytes before its first function changed")
    for f in code2["patched_functions"]:
        if "jmp_to" not in f or not appended[0] <= int(f["jmp_to"], 16) < appended[1]:
            errors.append(f"CODE 2 {f['function']} changed beyond a jump into the appended code")
    return errors


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--check", action="store_true")
    ap.add_argument("--write", action="store_true")
    args = ap.parse_args()
    base, rebuilt, extra = rebuild()
    report = compare(base, rebuilt, extra["payloads"])
    plan = extra["script"]
    report["script"] = {**plan, "installs": [f"{t}:{i}" for t, i in plan["installs"]]}
    report["code2"] = code2_patch(base, rebuilt)
    report["carried_not_listed"] = sorted(f"{t}:{i}" for t, i in extra["payloads"] if (t, i) not in plan["installs"])
    errors = expectations(report)
    LISTED.update(plan["installs"])
    files = delta_files(rebuilt, report)
    if args.write:
        for path, data in files.items():
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_bytes(data)
        print(f"wrote {len(files)} files under {DELTA.relative_to(ROOT)}")
        return 0
    if args.check:
        present = {p for p in DELTA.rglob("*") if p.is_file()} if DELTA.is_dir() else set()
        for path, data in files.items():
            if not path.is_file() or path.read_bytes() != data:
                errors.append(f"{path.relative_to(ROOT)} missing or stale; rerun --write")
        for path in sorted(present - set(files)):
            if path.name.endswith(".decomp.c"):          # the reconstruction, written by hand
                continue
            errors.append(f"{path.relative_to(ROOT)} is not part of the 1.0.4 delta")
        changed = sorted(f"{r['type']}:{r['id']}" for r in report["resources"] if r["changed_from_1_0_3"])
        print(f"{'FAIL' if errors else 'PASS'} 1.0.4 rebuild: {len(plan['installs'])} resources installed, "
              f"{len(changed)} changed from 1.0.3 ({', '.join(changed)}); "
              f"{sum(r['status'] == 'identical' for r in report['resources'])} identical to the AppleDouble 1.0.4; CODE 2: "
              f"{report['code2']['unchanged_functions']} functions unchanged, "
              f"{len(report['code2']['patched_functions'])} entered by a JMP into "
              f"{report['code2']['appended']['size']} appended bytes")
        for e in errors:
            print(f" - {e}")
        return 1 if errors else 0
    print(json.dumps({k: v for k, v in report.items()}, indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())
