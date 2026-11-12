#!/usr/bin/env python3
"""Generate replay cases for the 1.0.4 sound driver (godot/original/port/ID02_104.gd) by running the
ORIGINAL 1.0.4 bytes: sources/hellcats-1-0-4/CODE/ID02.bin at address 0 on the 68020 interpreter
(m68k_emu.py), only the Sound Manager and Memory Manager traps stubbed (their answers in the case,
their calls logged). Each case records its set-up (long writes and patterned byte blocks), the
routine and arguments, and what the bytes left: D0, the trap log, every long the set-up wrote or the
driver variables hold, and the mixer's output bytes. test_id02_104_sound.gd replays them.

Byte blocks are filled with the pattern (i * 37 + seed * 101 + (i >> 5)) & 0xff, computed again by
the replay. Usage: python3 tools/original/gen_id02_104_sound_cases.py [--check]
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from m68k_emu import Emulator  # noqa: E402

ROOT = Path(__file__).resolve().parents[2]
CODE = ROOT / "sources" / "hellcats-1-0-4" / "CODE" / "ID02.bin"
FIXTURE = ROOT / "godot" / "tests" / "original" / "port" / "fixtures" / "id02_104_sound_cases.json"
DRIVER = 0x4E1A
ACTIVE, STEP_INT, STEP_FRAC, SILENCE = DRIVER + 0x81C, DRIVER + 0x81E, DRIVER + 0x822, DRIVER + 0x826
CHANNEL, HEADER, BUF0, BUF1 = DRIVER + 0x12CA, DRIVER + 0x12CE, DRIVER + 0x12DA, DRIVER + 0x12DE
STACK = 0x3F000
ENTRY = {"alloc_sound_buffer_node_104": 0x4FA6, "init_sound_system_104": 0x5006,
         "play_sample_on_channel_104": 0x51FE, "enqueue_sample_on_channel_104": 0x52F4,
         "set_sound_playback_rate_104": 0x541E, "release_channel_loop_104": 0x547C,
         "silence_sound_channel_104": 0x5506, "is_channel_playing_104": 0x5568,
         "shutdown_sound_104": 0x55BA, "sound_double_back_104": 0x4E1A}
SILENCE_BUF, OUT = 0x30000, 0x36000
VARS = [(ACTIVE, 2), (STEP_INT, 4), (STEP_FRAC, 4), (SILENCE, 4), (CHANNEL, 4), (BUF0, 4), (BUF1, 4)]


def record(ch: int) -> int:
    return DRIVER + 0x82A + ch * 0x2A8


def pattern(n: int, seed: int) -> bytes:
    return bytes(((i * 37 + seed * 101 + (i >> 5)) & 0xFF) for i in range(n))


def run(name: str, routine: str, args: list, writes: list, blocks: list, answers: dict, bytes_out: list) -> dict:
    emu = Emulator(memory_size=0x40000, binary_path=str(CODE), file_delta=0)
    for addr, size, value in writes:
        emu.write(addr, size, value)
    for addr, length, seed in blocks:
        emu.mem[addr:addr + length] = pattern(length, seed)
    log = []
    ptrs = list(answers.get("NewPtrClear", []))

    def new_ptr(e):
        log.append(["NewPtrClear", e.d[0]])
        e.a[0] = ptrs.pop(0) if ptrs else 0
        e.d[0] = 0 if e.a[0] else (-108 & 0xFFFFFFFF)

    def new_channel(e):
        sp = e.a[7]
        log.append(["SndNewChannel", e.read(sp + 10, 4), e.read(sp + 8, 2), e.read(sp + 4, 4), e.read(sp, 4)])
        e.write(e.read(sp + 10, 4), 4, 0x35000)
        e.a[7] = sp + 14
        e.write(e.a[7], 2, answers.get("SndNewChannel", 0))

    def dispatch(e):
        sp = e.a[7]
        log.append(["SoundDispatch", e.d[0], e.read(sp + 4, 4), e.read(sp, 4)])
        e.a[7] = sp + 8
        e.write(e.a[7], 2, answers.get("SoundDispatch", 0))

    def dispose_channel(e):
        log.append(["SndDisposeChannel", e.read(e.a[7] + 2, 4), e.read(e.a[7], 1)])
        e.a[7] += 6
        e.write(e.a[7], 2, 0)

    def dispose_ptr(e):
        log.append(["DisposePtr", e.a[0]])
        e.d[0] = 0
    emu.traps.update({0xA31E: new_ptr, 0xA807: new_channel, 0xA800: dispatch, 0xA801: dispose_channel,
                      0xA01F: dispose_ptr})
    emu.a[4] = DRIVER                           # alloc_sound_buffer_node runs on its caller's A4
    if routine == "alloc_sound_buffer_node_104":
        emu.a[7] = STACK
        emu.push(args[4] & 0xFFFF)              # the word flag
        emu.a[7] += 2
        for value in reversed(args[:4]):
            emu.push(value & 0xFFFFFFFF)
        emu.push(0xFFFFFFF0)
        emu.pc = ENTRY[routine]
        while emu.pc != 0xFFFFFFF0:
            emu.step()
        d0 = emu.d[0]
    elif routine == "sound_double_back_104":   # Pascal: channel pushed first, buffer last
        d0 = emu.call(ENTRY[routine], [args[1], args[0]], STACK)
    else:
        d0 = emu.call(ENTRY[routine], [a & 0xFFFFFFFF for a in args], STACK)
    watched = sorted({(a, s) for a, s, _ in writes} | set(VARS)
                     | {(record(c) + o, 4) for c in range(4) for o in (0, 4)})
    return {"name": name, "routine": routine, "args": args, "writes": writes, "blocks": blocks,
            "answers": answers, "d0": d0 & 0xFFFFFFFF if routine not in ("sound_double_back_104",) else None,
            "log": log, "reads": [[a, s, emu.read(a, s)] for a, s in watched],
            "bytes": [[a, n, bytes(emu.mem[a:a + n]).hex()] for a, n in bytes_out]}


def channels(free: int = 8) -> list:
    """Each channel: a looping head node and `free` free nodes (pool at 0x38000)."""
    w = [(SILENCE, 4, SILENCE_BUF)]
    for c in range(4):
        nodes = [0x38000 + 0x20 * (c * 10 + k) for k in range(10)]
        w += [(record(c), 4, nodes[0]), (nodes[0] + 8, 4, 0x31000), (nodes[0] + 0x14, 4, nodes[1]),
              (nodes[0] + 0x18, 4, nodes[0]), (record(c) + 4, 4, nodes[1])]
        w += [(nodes[k] + 0x14, 4, nodes[k + 1]) for k in range(1, free + 1)]
        w += [(nodes[k] + o, 4, 0) for k in range(1, free + 1) for o in (0, 4, 8, 0xC, 0x10, 0x18)]
    return [list(x) for x in w]


def cases() -> list:
    out = []
    for active in (0, 1):
        for rate in (0x4000, 0x10000, 0x40000, 0x80000000):
            out.append(run(f"rate {rate:#x} active {active}", "set_sound_playback_rate_104", [rate],
                           [[ACTIVE, 2, active], [STEP_INT, 4, 7], [STEP_FRAC, 4, 9]], [], {}, []))
        for ch in (-1, 0, 3, 4):
            base = [[ACTIVE, 2, active]] + channels()
            for r in ("silence_sound_channel_104", "release_channel_loop_104", "is_channel_playing_104"):
                out.append(run(f"{r}({ch}) active {active}", r, [ch], base, [], {}, []))
            for r in ("play_sample_on_channel_104", "enqueue_sample_on_channel_104"):
                for loop in ((0, 0), (0x100, 0x272), (0x300, 0x100)):
                    out.append(run(f"{r}({ch}, loop {loop}) active {active}", r, [ch, 0x31000, 0x10, 0x4000, *loop], base, [], {}, []))
    out.append(run("alloc advance 1", "alloc_sound_buffer_node_104", [2, 0x31000, 0x10, 0x2000, 1], channels(), [], {}, []))
    out.append(run("alloc advance 0", "alloc_sound_buffer_node_104", [3, 0x31000, 0x10, 0x2000, 0], channels(), [], {}, []))
    for buffers in ((0x33000, 0x34000), (0, 0)):
        out.append(run(f"shutdown {buffers}", "shutdown_sound_104", [],
                       [[ACTIVE, 2, 1], [CHANNEL, 4, 0x35000], [BUF0, 4, buffers[0]], [BUF1, 4, buffers[1]]] + channels(), [], {}, []))
    for label, answers in (("ok", {"NewPtrClear": [0x30000, 0x33000, 0x34000]}),
                           ("no silence", {"NewPtrClear": []}),
                           ("no buffer 1", {"NewPtrClear": [0x30000, 0x33000]}),
                           ("channel error", {"NewPtrClear": [0x30000], "SndNewChannel": 0xFFFF}),
                           ("play error", {"NewPtrClear": [0x30000, 0x33000, 0x34000], "SoundDispatch": 0xFFFF})):
        out.append(run(f"init {label}", "init_sound_system_104", [], [], [], answers,
                       [[0x30000, 0xE74], [0x33000, 0x74C], [0x34000, 0x74C]]))
    for rate in (0x8000, 0x18000, 0x30000):
        for pos0, end0 in ((0, 0x4000), (0x300, 0x500), (0x3F0, 0x400)):
            w = [[ACTIVE, 2, 1], [STEP_INT, 4, rate >> 16], [STEP_FRAC, 4, (rate << 16) & 0xFFFFFFFF], [OUT + 4, 4, 0x10000]]
            for c in range(4):
                a, b = 0x38000 + 0x40 * c, 0x38200 + 0x40 * c
                if c == 0:
                    fields = ((a, 0x20000, pos0, 0x40000000, 0, end0, b), (b, 0x28000, 0x777, 0x123, 0x20, 0x1800, b))
                else:
                    fields = ((a, 0x20000 + 0x2000 * c, 0x200 * c, 0, 0, 0x200 * c + 0x73A * (c - 1), b),
                              (b, 0x28000, 0, 0, 0x40 * c, 0x900, b))
                for node, buf, cur, frac, start, end, cont in fields:
                    w += [[node, 4, cur], [node + 4, 4, frac], [node + 8, 4, buf], [node + 0xC, 4, start],
                          [node + 0x10, 4, end], [node + 0x18, 4, cont]]
                w.append([record(c), 4, a])
            blocks = [[0x20000 + 0x2000 * c, 0x2000, c + 1] for c in range(4)] + [[0x28000, 0x2000, 9]]
            out.append(run(f"double_back rate {rate:#x} ch0 {pos0:#x}..{end0:#x}", "sound_double_back_104",
                           [0x35000, OUT], w, blocks, {}, [[OUT + 0x10, 0x73A]]))
    return out


def main() -> int:
    text = json.dumps({"code": str(CODE.relative_to(ROOT)), "cases": cases()}, separators=(",", ":")) + "\n"
    if "--check" in sys.argv:
        ok = FIXTURE.is_file() and FIXTURE.read_text() == text
        print(("PASS" if ok else "FAIL") + f" {FIXTURE.relative_to(ROOT)} is {'' if ok else 'not '}what the 1.0.4 bytes give")
        return 0 if ok else 1
    FIXTURE.write_text(text)
    print(f"wrote {FIXTURE.relative_to(ROOT)}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
