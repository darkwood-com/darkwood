#!/usr/bin/env python3
"""Extract Macintosh resources from Hellcats HFS floppy images.

Pipeline:
  1. Read classic HFS .dsk via machfs (data + resource forks).
  2. Optionally run resource_dasm / rsrcdump on application resource forks.

Does not modify original disk images. Writes under an explicit output directory.
"""

from __future__ import annotations

import argparse
import hashlib
import json
import os
import shutil
import subprocess
import sys
from pathlib import Path

try:
    import machfs
except ImportError:
    machfs = None  # type: ignore


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def extract_hfs(dsk: Path, out_dir: Path) -> dict:
    if machfs is None:
        raise SystemExit("machfs is required: pip3 install machfs")
    out_dir.mkdir(parents=True, exist_ok=True)
    vol = machfs.Volume()
    vol.read(dsk.read_bytes())
    entries: list[dict] = []

    def walk(folder, prefix: str = "") -> None:
        for name, item in folder.items():
            rel = f"{prefix}/{name}" if prefix else str(name)
            if isinstance(item, machfs.Folder):
                walk(item, rel)
                continue
            data = bytes(item.data)
            rsrc = bytes(item.rsrc)
            safe = (
                rel.replace("/", "__")
                .replace(":", "_")
                .replace("\x00", "")
            )
            rec: dict = {
                "path": rel,
                "data_size": len(data),
                "rsrc_size": len(rsrc),
                "data_sha256": sha256_bytes(data) if data else None,
                "rsrc_sha256": sha256_bytes(rsrc) if rsrc else None,
            }
            for attr in ("type", "creator"):
                v = getattr(item, attr, None)
                if isinstance(v, bytes):
                    rec[attr] = v.decode("mac_roman", "replace")
                elif v is not None:
                    rec[attr] = str(v)
            if data:
                (out_dir / f"{safe}.data").write_bytes(data)
                rec["data_file"] = f"{safe}.data"
            if rsrc:
                (out_dir / f"{safe}.rsrc").write_bytes(rsrc)
                rec["rsrc_file"] = f"{safe}.rsrc"
            entries.append(rec)

    walk(vol)
    entries.sort(key=lambda e: e["path"])
    inventory = {
        "schema": "hellcats-hfs-extract-v1",
        "source_dsk": str(dsk),
        "source_sha256": sha256_bytes(dsk.read_bytes()),
        "entry_count": len(entries),
        "entries": entries,
    }
    (out_dir / "inventory.json").write_text(
        json.dumps(inventory, indent=2, sort_keys=True) + "\n", encoding="utf-8"
    )
    return inventory


def run_resource_dasm(rsrc_file: Path, out_dir: Path, resource_dasm: Path) -> int:
    out_dir.mkdir(parents=True, exist_ok=True)
    # Exported forks are stored as data-fork files containing resource-map bytes.
    cmd = [str(resource_dasm), "--data-fork", str(rsrc_file), str(out_dir)]
    proc = subprocess.run(cmd, capture_output=True, text=True)
    (out_dir / "resource_dasm.stdout.txt").write_text(proc.stdout)
    (out_dir / "resource_dasm.stderr.txt").write_text(proc.stderr)
    return proc.returncode


def run_rsrcdump(rsrc_file: Path, out_dir: Path) -> int:
    out_dir.mkdir(parents=True, exist_ok=True)
    index = out_dir / "index.json"
    cmd = ["rsrcdump", "--extract", str(rsrc_file), "-o", str(index)]
    proc = subprocess.run(cmd, capture_output=True, text=True)
    (out_dir / "rsrcdump.stdout.txt").write_text(proc.stdout)
    (out_dir / "rsrcdump.stderr.txt").write_text(proc.stderr)
    return proc.returncode


def default_resource_dasm() -> Path | None:
    candidates = [
        Path(os.environ.get("RESOURCE_DASM", "")),
        Path("/Users/math/Sites/tests/_local_prefix/bin/resource_dasm"),
        Path(shutil.which("resource_dasm") or ""),
    ]
    for c in candidates:
        if c and c.is_file():
            return c
    return None


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--dsk", type=Path, required=True, help="Classic HFS .dsk image")
    ap.add_argument("-o", "--output", type=Path, required=True, help="Output directory")
    ap.add_argument(
        "--decode-app-rsrc",
        action="store_true",
        help="After HFS extract, run resource_dasm+rsrcdump on *.rsrc files that look like APPL",
    )
    ap.add_argument(
        "--resource-dasm",
        type=Path,
        default=None,
        help="Path to resource_dasm binary",
    )
    args = ap.parse_args()
    if not args.dsk.is_file():
        print(f"missing dsk: {args.dsk}", file=sys.stderr)
        return 1

    hfs_out = args.output / "hfs"
    inv = extract_hfs(args.dsk.resolve(), hfs_out)
    print(f"HFS entries: {inv['entry_count']} -> {hfs_out / 'inventory.json'}")

    if args.decode_app_rsrc:
        rd = args.resource_dasm or default_resource_dasm()
        for entry in inv["entries"]:
            if entry.get("rsrc_size", 0) <= 0:
                continue
            # Prefer application resource forks (APPL) or large rsrc
            if entry.get("type") != "APPL" and entry.get("rsrc_size", 0) < 10000:
                continue
            rsrc_path = hfs_out / entry["rsrc_file"]
            stem = Path(entry["rsrc_file"]).stem
            if rd:
                code = run_resource_dasm(rsrc_path, args.output / f"resource_dasm_{stem}", rd)
                print(f"resource_dasm {rsrc_path.name} exit={code}")
            else:
                print("resource_dasm not found; skip decode", file=sys.stderr)
            code = run_rsrcdump(rsrc_path, args.output / f"rsrcdump_{stem}")
            print(f"rsrcdump {rsrc_path.name} exit={code}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
