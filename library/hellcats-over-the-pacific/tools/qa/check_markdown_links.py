#!/usr/bin/env python3
"""Report broken repository-local Markdown links."""

from __future__ import annotations

import re
import sys
from pathlib import Path
from urllib.parse import unquote


ROOT = Path(__file__).resolve().parents[2]
LINK = re.compile(r"(?<!!)\[[^\]]*\]\(([^)]+)\)")


def main() -> int:
    failures: list[str] = []
    for source in sorted(ROOT.rglob("*.md")):
        if ".git" in source.parts:
            continue
        for number, line in enumerate(source.read_text(encoding="utf-8", errors="replace").splitlines(), 1):
            for raw in LINK.findall(line):
                target = raw.strip().strip("<>").split("#", 1)[0]
                if not target or re.match(r"^[A-Za-z][A-Za-z0-9+.-]*:", target):
                    continue
                path = (source.parent / unquote(target)).resolve()
                if not path.exists():
                    failures.append(f"{source.relative_to(ROOT)}:{number}: {raw}")
    for failure in failures:
        print(f"BROKEN {failure}")
    print(f"{'PASS' if not failures else 'FAIL'} markdown links: {len(failures)} broken")
    return 0 if not failures else 1


if __name__ == "__main__":
    raise SystemExit(main())
