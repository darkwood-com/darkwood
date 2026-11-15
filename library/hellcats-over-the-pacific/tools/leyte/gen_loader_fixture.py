#!/usr/bin/env python3
"""The fixture Leyte's GDScript loader port is checked against: what Leyte's own CODE 8 loader built on
the 68000 emulator with the real data fork (tools/leyte/loader_harness.py), as
godot/tests/leyte/port/fixtures/loader_image.json: the image base, the header, the image's size and
SHA-256, the payload block (FUN_00000350) likewise, the heap blocks and the File Manager log.
tests/leyte/port/test_leyte_loader.gd runs the port (original/leyte/port/ID08.gd) on the same fork
and must produce the same. --check compares the fixture with a fresh run.

    python3 tools/leyte/gen_loader_fixture.py [--check]
"""
from __future__ import annotations

import hashlib
import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from loader_harness import ROOT, loader_run  # noqa: E402

FIXTURE = ROOT / "godot" / "tests" / "leyte" / "port" / "fixtures" / "loader_image.json"


def build() -> str:
    r = loader_run()
    doc = {"schema": 1, "generated_by": "tools/leyte/gen_loader_fixture.py",
           "source": "Leyte Gulf CODE 8 FUN_00000006 on tools/original/m68k_emu.py, data fork sources/leyte-gulf-1-0/Leyte Gulf overlay",
           "stopped": r["stopped"], "base": r["base"], "header": r["header"].hex(),
           "image_size": len(r["image"]), "image_sha256": hashlib.sha256(r["image"]).hexdigest(),
           "payload_ptr": r["payload_ptr"], "payload_size": len(r["payload"]),
           "payload_sha256": hashlib.sha256(r["payload"]).hexdigest(),
           "heap": r["heap"], "files": r["files"]}
    return json.dumps(doc, separators=(",", ":")) + "\n"


def main() -> int:
    text = build()
    if "--check" in sys.argv:
        ok = FIXTURE.is_file() and FIXTURE.read_text() == text
        print(("PASS" if ok else "FAIL") + " Leyte loader fixture: %s" % FIXTURE.relative_to(ROOT))
        return 0 if ok else 1
    FIXTURE.write_text(text)
    print("wrote %s" % FIXTURE.relative_to(ROOT))
    return 0


if __name__ == "__main__":
    sys.exit(main())
