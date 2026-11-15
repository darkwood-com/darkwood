#!/usr/bin/env python3
"""Extract Leyte Gulf's overlay (the application's data fork, `Leyte Gulf:Leyte Gulf` on
sources/mac-os-9/LeyteGulf.dsk) verbatim to sources/leyte-gulf-1-0/Leyte Gulf overlay, for Ghidra
(tools/leyte/ghidra/), and to the Godot mirror the runtime's Leyte profile serves it from
(godot/assets/leyte-gulf-1.0/data_fork.bin: the file the CODE 8 loader opens by the application's
name). The disk image is never written. --check: both exist and are the data fork byte for byte, with
the recorded SHA-256.
"""
import hashlib
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "mac_resources"))
import hfs  # noqa: E402

DISK = ROOT / "sources" / "mac-os-9" / "LeyteGulf.dsk"
TARGET = ROOT / "sources" / "leyte-gulf-1-0" / "Leyte Gulf overlay"
MIRROR = ROOT / "godot" / "assets" / "leyte-gulf-1.0" / "data_fork.bin"
SHA256 = "110906ae003abe1679713a60e48deb8391d1b2663c1dcfa0f6415cf54c844944"


def main() -> int:
    data = hfs.find(DISK, "Leyte Gulf:Leyte Gulf").data
    if hashlib.sha256(data).hexdigest() != SHA256:
        print("FAIL Leyte overlay: the disk's data fork is not the recorded one")
        return 1
    if "--check" in sys.argv:
        for path in (TARGET, MIRROR):
            if not path.exists() or path.read_bytes() != data:
                print("FAIL Leyte overlay: %s missing or not the data fork" % path.relative_to(ROOT))
                return 1
        print("PASS Leyte overlay: %d bytes, sha256 %s" % (len(data), SHA256[:12]))
        return 0
    TARGET.write_bytes(data)
    MIRROR.write_bytes(data)
    print("wrote %s and %s (%d bytes)" % (TARGET.relative_to(ROOT), MIRROR.relative_to(ROOT), len(data)))
    return 0


if __name__ == "__main__":
    sys.exit(main())
