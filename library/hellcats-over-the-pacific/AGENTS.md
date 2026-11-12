# Hellcats agent context

Preserve evidence first. Original binaries, resource forks, CODE payloads, Ghidra
`*.c` exports, and protected checksums are immutable. Semantic `*.decomp.c`
files are editable only for evidence-backed reconstruction; preserve function
boundaries, widths, fixed-point arithmetic, addresses, original `FUN_*` identities,
and unresolved annotations.

The game has three editions. **Original** is the faithful reconstruction and
the behavioural reference: translations of original functions (the Original
port) under `godot/original/`, and the emulated Macintosh around them under
`godot/original/mac/`, which maps around the port and never works around it.
**Native** (Godot facilities, same rules) and **Enhanced** (optional
improvements on Native) are planned and never change Original. The README's
roadmap gives the current phase. Keep the chain
`original .c -> semantic .decomp.c -> faithful .gd` explicit in
`godot/original/mappings/functions.json`. `docs/history/` (the removed MVP
prototype) is never a source of original behavior.

Start with `README.md`, then retrieve only what the task needs:

```sh
python3 tools/re_query.py function FUN_0000e792
python3 tools/re_query.py callers FUN_0000e792
python3 tools/re_query.py globals FUN_0000e792
python3 tools/re_query.py status FUN_0000e792
python3 tools/re_query.py source-map FUN_0001358c
python3 tools/resedit.py inspect DITL 137
python3 tools/resedit.py references ALRT 259
```

Do not preload whole CODE files, the full symbol registry, or all resource
metadata. Those are on-demand evidence. Before editing, inspect
Git status and preserve unrelated changes. Run targeted checks, then the
appropriate project-wide validation before claiming completion. Do not commit
or push unless explicitly asked.
