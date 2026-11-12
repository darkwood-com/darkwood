#!/usr/bin/env python3
"""Read-only reader for the classic HFS floppy images in sources/mac-os-9/.

Pure Python, no dependencies: lists every file of an 800K HFS volume with its
type, creator and both forks, and can write the forks out. Fork extents come
from the catalog record; a fork longer than its three catalog extents would
need the extents-overflow file, which these floppies do not use, so such a fork
is rejected rather than read incompletely.

    python3 tools/mac_resources/hfs.py list sources/mac-os-9/Hellcats_1_0_3.dsk
    python3 tools/mac_resources/hfs.py extract sources/mac-os-9/LeyteGulf.dsk -o /tmp/leyte
"""

from __future__ import annotations

import argparse
import hashlib
import json
import struct
import sys
from dataclasses import dataclass
from pathlib import Path

NODE_SIZE = 512


@dataclass
class HfsFile:
    path: str          # "Volume:Folder:Name", HFS style
    type: str
    creator: str
    data: bytes
    rsrc: bytes


def _u16(b: bytes, o: int) -> int:
    return struct.unpack_from(">H", b, o)[0]


def _u32(b: bytes, o: int) -> int:
    return struct.unpack_from(">I", b, o)[0]


def read_volume(image: bytes) -> list[HfsFile]:
    mdb = image[0x400:0x600]
    if mdb[0:2] != b"BD":
        raise ValueError("not an HFS volume (no 'BD' signature at 0x400)")
    block_size = _u32(mdb, 0x14)
    first_block = _u16(mdb, 0x1C)

    def extents(raw: bytes, at: int) -> list[tuple[int, int]]:
        return [(_u16(raw, at + 4 * i), _u16(raw, at + 4 * i + 2)) for i in range(3)]

    def fork(ext: list[tuple[int, int]], length: int, what: str) -> bytes:
        out = b"".join(image[(first_block * 512 + s * block_size):(first_block * 512 + (s + c) * block_size)]
                       for s, c in ext if c)
        if len(out) < length:
            raise ValueError(f"{what}: fork needs the extents-overflow file ({len(out)} < {length} bytes)")
        return out[:length]

    catalog = fork(extents(mdb, 0x96), _u32(mdb, 0x92), "catalog")
    node = _u32(catalog, 14 + 10)  # header record: first leaf node
    folders: dict[int, tuple[int, str]] = {}
    records = []
    while node:
        raw = catalog[node * NODE_SIZE:(node + 1) * NODE_SIZE]
        count = _u16(raw, 10)
        for i in range(count):
            o = _u16(raw, NODE_SIZE - 2 * (i + 1))
            key_len = raw[o]
            parent = _u32(raw, o + 2)
            name = raw[o + 7:o + 7 + raw[o + 6]].decode("mac_roman")
            d = o + 1 + key_len
            d += d & 1
            kind = raw[d]
            if kind == 1:
                folders[_u32(raw, d + 6)] = (parent, name)
            elif kind == 2:
                records.append((parent, name, raw[d:d + 102]))
        node = _u32(raw, 0)

    def path(parent: int) -> str:
        parts = []
        while parent in folders:
            parent, name = folders[parent][0], folders[parent][1]
            parts.append(name)
        return ":".join(reversed(parts))

    files = []
    for parent, name, rec in records:
        full = f"{path(parent)}:{name}"
        files.append(HfsFile(
            path=full,
            type=rec[4:8].decode("mac_roman"),
            creator=rec[8:12].decode("mac_roman"),
            data=fork(extents(rec, 74), _u32(rec, 26), full + " data"),
            rsrc=fork(extents(rec, 86), _u32(rec, 36), full + " rsrc")))
    return sorted(files, key=lambda f: f.path)


def find(image_path: Path, hfs_path: str) -> HfsFile:
    for f in read_volume(image_path.read_bytes()):
        if f.path == hfs_path:
            return f
    raise KeyError(f"{hfs_path} is not on {image_path}")


def describe(f: HfsFile) -> dict:
    return {"path": f.path, "type": f.type, "creator": f.creator,
            "data_size": len(f.data), "data_sha256": hashlib.sha256(f.data).hexdigest(),
            "rsrc_size": len(f.rsrc), "rsrc_sha256": hashlib.sha256(f.rsrc).hexdigest()}


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    sub = ap.add_subparsers(dest="cmd", required=True)
    ls = sub.add_parser("list")
    ls.add_argument("image", type=Path)
    ex = sub.add_parser("extract")
    ex.add_argument("image", type=Path)
    ex.add_argument("-o", "--out", type=Path, required=True)
    args = ap.parse_args()
    files = read_volume(args.image.read_bytes())
    if args.cmd == "list":
        json.dump([describe(f) for f in files], sys.stdout, indent=2)
        print()
        return 0
    for f in files:
        base = args.out / f.path.replace(":", "/")
        base.parent.mkdir(parents=True, exist_ok=True)
        base.with_name(base.name + ".data").write_bytes(f.data)
        base.with_name(base.name + ".rsrc").write_bytes(f.rsrc)
    json.dump([describe(f) for f in files], sys.stdout, indent=2)
    print()
    return 0


if __name__ == "__main__":
    sys.exit(main())
