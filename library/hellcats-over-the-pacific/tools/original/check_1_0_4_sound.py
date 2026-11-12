#!/usr/bin/env python3
"""Check sources/hellcats-1-0-4/CODE/ID02.delta.decomp.c against the 1.0.4 bytes.

Runs each reconstructed routine of the 1.0.4 sound driver on the 68020 interpreter (m68k_emu.py),
with the rebuilt 1.0.4 CODE 2 (sources/hellcats-1-0-4/CODE/ID02.bin) loaded at address 0 (its offsets
are the addresses), over a set of cases, and compares the result and every driver variable and node
field the C says it writes with the C's own reading of the case (the model functions below, line for
line the reconstruction). The node helper 0x4fa6 is a stub returning a prepared node.

Usage: python3 tools/original/check_1_0_4_sound.py   (exit 1 on any difference)
"""
from __future__ import annotations

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from m68k_emu import Emulator  # noqa: E402

ROOT = Path(__file__).resolve().parents[2]
CODE = ROOT / "sources" / "hellcats-1-0-4" / "CODE" / "ID02.bin"
DRIVER = 0x4E1A
ACTIVE, STEP_INT, STEP_FRAC, SILENCE = DRIVER + 0x81C, DRIVER + 0x81E, DRIVER + 0x822, DRIVER + 0x826
RECORDS, RECORD_SIZE = DRIVER + 0x82A, 0x2A8
NEW_NODE = 0x4FA6
STACK = 0x3F000
SILENCE_BUFFER, SAMPLE = 0x30000, 0x31000
NODES = [0x38000 + 0x40 * i for i in range(6)]
ROUTINES = {"sound_double_back": 0x4E1A, "init_sound_system": 0x5006, "shutdown_sound": 0x55BA, "play_sample_on_channel": 0x51FE, "enqueue_sample_on_channel": 0x52F4,
            "alloc_sound_buffer_node": 0x4FA6, "set_sound_playback_rate": 0x541E, "release_channel_loop": 0x547C,
            "silence_sound_channel": 0x5506, "is_channel_playing": 0x5568}


def record(ch: int) -> int:
    return RECORDS + ch * RECORD_SIZE


def machine(active: int) -> Emulator:
    emu = Emulator(memory_size=0x40000, binary_path=str(CODE), file_delta=0)
    emu.write(ACTIVE, 2, active)
    emu.write(SILENCE, 4, SILENCE_BUFFER)
    return emu


def chain(emu: Emulator, ch: int, length: int, buffer: int) -> None:
    """The channel's head and `length` nodes linked by +0x14, each looping on itself (+0x18)."""
    for i in range(length):
        n = NODES[i]
        emu.write(n + 0x08, 4, buffer)
        emu.write(n + 0x14, 4, NODES[i + 1])
        emu.write(n + 0x18, 4, n)
    emu.write(record(ch), 4, NODES[0])


def run(emu: Emulator, name: str, arg: int, new_node: int | None = None) -> tuple[int, list]:
    calls = []
    if new_node is not None:
        def stub(e: Emulator) -> int:
            calls.append([e.read(e.a[7] + 4 * k, 4) for k in range(4)] + [e.read(e.a[7] + 16, 2)])
            return new_node
        emu.stubs[NEW_NODE] = stub
    return emu.call(ROUTINES[name], [arg & 0xFFFFFFFF], STACK), calls


def model_queue(mem: dict, name: str, args: list, active: int) -> int:
    """ID02.delta.decomp.c's play / enqueue on a dictionary of longs (missing = 0)."""
    ch, buffer, start, end, loop_start, loop_end = args
    get = lambda a: mem.get(a, 0)
    if not active or ch < 0 or ch >= 4:
        return 0
    if ch != 0:
        end -= 0x73A

    def node(buf, s, e, advance):
        n = get(record(ch) + 4)
        mem[record(ch) + 4] = get(n + 0x14)
        mem[n] = s & 0xFFFFFFFF
        mem[n + 4] = 0
        mem[n + 8] = buf
        mem[n + 0xC] = s & 0xFFFFFFFF
        mem[n + 0x10] = e & 0xFFFFFFFF
        mem[n + 0x18] = get(n + 0x14) if advance else n
        return n
    if ((loop_end - loop_start) & 0xFFFFFFFF) < 0x172:
        first = node(buffer, start, end, 1)
        node(SILENCE_BUFFER, 0, 0xE74, 0)
    else:
        first = node(buffer, start, loop_start, 1)
        node(buffer, loop_start, loop_end, 0)
        node(buffer, loop_end, end, 1)
    if name == "play_sample_on_channel":
        mem[record(ch)] = first
    else:
        free_head = get(record(ch) + 4)
        n = get(record(ch))
        while get(n + 0x14) != free_head:
            mem[n + 0x18] = get(n + 0x14)
            n = get(n + 0x14)
    return 1


def model_mix(mem: dict, data: bytearray, step_int: int, step_frac: int, out: int) -> None:
    """ID02.delta.decomp.c's sound_double_back_104 on node fields (mem) and sample bytes (data)."""
    M = 0xFFFFFFFF
    s32 = lambda v: v - (1 << 32) if v & 0x80000000 else v
    reads = []
    for ch in (1, 2, 3):
        node = mem[record(ch)]
        cursor = mem[node]
        reads.append(mem[node + 8] + cursor)
        cursor = (cursor + 0x73A) & M
        if s32(cursor) >= s32(mem[node + 0x10]):
            node = mem[node + 0x18]
            cursor = mem[node + 0xC]
            mem[record(ch)] = node
        mem[node] = cursor
    node = mem[record(0)]
    pos, frac, end, samples = mem[node], mem[node + 4], mem[node + 0x10], mem[node + 8]
    checked = s32((end - pos) & M) <= 0xE74
    for n in range(0x73A):
        if checked and s32(end) <= s32(pos):
            node = mem[node + 0x18]
            samples, pos, frac, end = mem[node + 8], mem[node + 0xC], 0, mem[node + 0x10]
            mem[record(0)] = node
        v = data[(samples + pos) & M] + sum(data[r + n] for r in reads) - 0x180
        data[out + 0x10 + n] = v & 0xFF
        total = frac + step_frac
        pos = (pos + step_int + (total >> 32)) & M
        frac = total & M
    mem[node], mem[node + 4] = pos, frac


def check() -> list[str]:
    errors = []

    def expect(label, got, want):
        if got != want:
            errors.append(f"{label}: bytes give {got!r}, the C {want!r}")

    # set_sound_playback_rate
    for active in (0, 1):
        for rate in (0, 0x4000, 0x7FFF, 0x8000, 0x10000, 0x18000, 0x30000, 0x30001, 0x80000000):
            emu = machine(active)
            emu.write(STEP_INT, 4, 0x11111111)
            emu.write(STEP_FRAC, 4, 0x22222222)
            d0, _ = run(emu, "set_sound_playback_rate", rate)
            want_int, want_frac = 0x11111111, 0x22222222
            if active:
                r = min(max(rate, 0x8000), 0x30000)
                want_int, want_frac = r >> 16, (r << 16) & 0xFFFFFFFF
            expect(f"rate {rate:#x} active {active}", (d0, emu.read(STEP_INT, 4), emu.read(STEP_FRAC, 4)),
                   (0, want_int, want_frac))

    # silence_sound_channel and release_channel_loop (the new node is NODES[3])
    for active in (0, 1):
        for ch in (-1, 0, 1, 3, 4):
            for name in ("silence_sound_channel", "release_channel_loop"):
                emu = machine(active)
                for c in range(4):
                    chain(emu, c, 3, SAMPLE)
                emu.write(NODES[3] + 0x14, 4, 0xABCD0000)
                d0, calls = run(emu, name, ch, NODES[3])
                valid = active and 0 <= ch < 4
                want_calls = [[ch & 0xFFFFFFFF, SILENCE_BUFFER, 0, 0xE74, 0]] if valid else []
                expect(f"{name}({ch}) active {active} calls", calls, want_calls)
                heads = [emu.read(record(c), 4) for c in range(4)]
                links = [emu.read(NODES[i] + 0x18, 4) for i in range(3)]
                tails = [emu.read(record(c) + 4, 4) for c in range(4)]
                want_heads, want_links, want_tails = [NODES[0]] * 4, list(NODES[:3]), [0] * 4
                if valid and name == "silence_sound_channel":
                    want_heads[ch] = NODES[3]
                if valid and name == "release_channel_loop":
                    want_tails[ch] = 0xABCD0000
                    want_links = [NODES[i + 1] for i in range(3)]
                expect(f"{name}({ch}) active {active}", (d0, heads, links, tails), (0, want_heads, want_links, want_tails))

    # alloc_sound_buffer_node (called with A4 = the driver: its callers' LEA)
    for ch in (0, 2, 3):
        for advance in (0, 1, 0x100):
            emu = machine(1)
            emu.write(record(ch) + 4, 4, NODES[1])
            emu.write(NODES[1] + 0x14, 4, NODES[2])
            emu.a[4] = DRIVER
            emu.a[7] = STACK
            emu.push(advance)                            # the word flag: its low half stays on the stack
            emu.a[7] += 2
            for value in (0x2000, 0x10, SAMPLE, ch):
                emu.push(value)
            emu.push(0xFFFFFFF0)
            emu.pc = NEW_NODE
            while emu.pc != 0xFFFFFFF0:
                emu.step()
            fields = [emu.read(NODES[1] + o, 4) for o in (0, 4, 8, 0xC, 0x10, 0x18)]
            want = [0x10, 0, SAMPLE, 0x10, 0x2000, NODES[2] if advance & 0xFFFF else NODES[1]]
            expect(f"alloc_sound_buffer_node({ch}, advance {advance:#x})",
                   (emu.d[0], emu.read(record(ch) + 4, 4), fields), (NODES[1], NODES[2], want))

    # play_sample_on_channel and enqueue_sample_on_channel, with the real node helper: the C's
    # reading (model below) of the same records, node pool and free lists
    pool = [0x38000 + 0x20 * i for i in range(40)]
    for name in ("play_sample_on_channel", "enqueue_sample_on_channel"):
        for active in (0, 1):
            for ch in (-1, 0, 1, 3, 4):
                for loop in ((0, 0), (0x100, 0x200), (0x100, 0x271), (0x100, 0x272), (0x300, 0x100)):
                    emu = machine(active)
                    model = {}

                    def put(addr, value):
                        emu.write(addr, 4, value)
                        model[addr] = value
                    for c in range(4):                      # each channel: a looping head node, 6 free
                        nodes = pool[c * 10:(c + 1) * 10]
                        put(record(c), nodes[0])
                        put(nodes[0] + 0x08, SAMPLE)
                        put(nodes[0] + 0x14, nodes[1])
                        put(nodes[0] + 0x18, nodes[0])
                        put(record(c) + 4, nodes[1])
                        for k in range(1, 9):
                            put(nodes[k] + 0x14, nodes[k + 1])
                    for n in pool:                          # every field compared, written or not
                        for o in range(0, 0x1C, 4):
                            model.setdefault(n + o, 0)
                    for c in range(4):
                        model.setdefault(record(c), 0)
                        model.setdefault(record(c) + 4, 0)
                    args = [ch, SAMPLE, 0x10, 0x4000, loop[0], loop[1]]
                    d0 = emu.call(ROUTINES[name], [a & 0xFFFFFFFF for a in args], STACK)
                    want = model_queue(model, name, args, active)
                    got = {addr: emu.read(addr, 4) for addr in model}
                    for addr in sorted(model):
                        if got[addr] != model[addr]:
                            expect(f"{name}{tuple(args)} active {active} at {addr:#x}", got[addr], model[addr])
                            break
                    expect(f"{name}{tuple(args)} active {active} result", d0, want)

    # shutdown_sound: SndDisposeChannel and DisposePtr stubbed and logged; the silencing is real
    CHAN, VARS = DRIVER + 0x12CA, (DRIVER + 0x12DA, DRIVER + 0x12DE)
    for active in (0, 1):
        for buffers in ((0x33000, 0x34000), (0, 0x34000), (0, 0)):
            emu = machine(active)
            log = []

            def dispose_channel(e):
                log.append(("SndDisposeChannel", e.read(e.a[7] + 2, 4), e.read(e.a[7], 1)))
                e.a[7] += 6
                e.write(e.a[7], 2, 0)

            def dispose_ptr(e):
                log.append(("DisposePtr", e.a[0]))
                e.d[0] = 0
            emu.traps[0xA801] = dispose_channel
            emu.traps[0xA01F] = dispose_ptr
            emu.write(CHAN, 4, 0x35000)
            for at, value in zip(VARS, buffers):
                emu.write(at, 4, value)
            for c in range(4):
                nodes = pool[c * 10:(c + 1) * 10]
                emu.write(record(c), 4, nodes[0])
                emu.write(record(c) + 4, 4, nodes[1])
                for k in range(1, 9):
                    emu.write(nodes[k] + 0x14, 4, nodes[k + 1])
            d0 = emu.call(ROUTINES["shutdown_sound"], [], STACK)
            heads = [emu.read(record(c), 4) for c in range(4)]
            state = (emu.read(ACTIVE, 2), emu.read(CHAN, 4), [emu.read(v, 4) for v in VARS], emu.read(SILENCE, 4))
            if active:
                want_log = [("SndDisposeChannel", 0x35000, 1)] + [("DisposePtr", b) for b in buffers if b] + [("DisposePtr", SILENCE_BUFFER)]
                want_heads = [pool[c * 10 + 1] for c in range(3)] + [pool[30]]
                want_state = (0, 0, [0, 0], 0)
            else:
                want_log, want_heads = [], [pool[c * 10] for c in range(4)]
                want_state = (0, 0x35000, list(buffers), SILENCE_BUFFER)
            expect(f"shutdown_sound active {active} buffers {buffers}", (d0, log, heads, state), (0, want_log, want_heads, want_state))

    # init_sound_system: NewPtrClear, SndNewChannel, SndPlayDoubleBuffer and the double-back procedure
    # (0x4e1a, its own entry) stubbed and logged; `fail` makes the n-th allocation or a Sound Manager
    # call fail to follow each exit
    for fail in (None, "ptr0", "ptr1", "ptr2", "channel", "play"):
        emu = machine(0)
        emu.write(SILENCE, 4, 0)
        log = []
        heap = [0x33000, 0x34000, 0x34800]

        def new_ptr(e):
            k = sum(1 for x in log if x[0] == "NewPtrClear")
            ok = fail != f"ptr{k}"
            log.append(("NewPtrClear", e.d[0]))
            e.a[0] = heap[k] if ok else 0
            e.d[0] = 0 if ok else (-108 & 0xFFFFFFFF)

        def new_channel(e):
            sp = e.a[7]
            log.append(("SndNewChannel", e.read(sp + 10, 4), e.read(sp + 8, 2), e.read(sp + 4, 4), e.read(sp, 4)))
            e.write(e.read(sp + 10, 4), 4, 0x35000)
            e.a[7] = sp + 14
            e.write(e.a[7], 2, 0xFFFF if fail == "channel" else 0)

        def dispatch(e):
            sp = e.a[7]
            log.append(("SoundDispatch", e.d[0], e.read(sp + 4, 4), e.read(sp, 4)))
            e.a[7] = sp + 8
            e.write(e.a[7], 2, 0xFFFF if fail == "play" else 0)

        def double_back(e):
            log.append(("double_back", e.read(e.a[7] + 4, 4), e.read(e.a[7], 4)))
            e.a[7] += 8
            return 0
        emu.traps.update({0xA31E: new_ptr, 0xA807: new_channel, 0xA800: dispatch})
        emu.stubs[DRIVER] = double_back
        d0 = emu.call(ROUTINES["init_sound_system"], [], STACK)
        steps = [("NewPtrClear", 0xE74)]
        if fail != "ptr0":
            steps.append(("SndNewChannel", DRIVER + 0x12CA, 5, 0x8C, 0))
            if fail != "channel":
                steps.append(("NewPtrClear", 0x74C))
                if fail != "ptr1":
                    steps += [("double_back", 0x35000, 0x34000), ("NewPtrClear", 0x74C)]
                    if fail != "ptr2":
                        steps += [("double_back", 0x35000, 0x34800), ("SoundDispatch", 0x200008, 0x35000, DRIVER + 0x12CE)]
        expect(f"init_sound_system fail={fail} calls", log, steps)
        expect(f"init_sound_system fail={fail} result", (d0, emu.read(ACTIVE, 2)), (1, 1) if fail is None else (0, 0))
        for c in range(4):
            nodes = record(c) + 8
            got = [emu.read(record(c) + 0x2A0, 4)] + [emu.read(nodes + i * 0x1C + 0x14, 4) for i in range(24)]
            want = [nodes] + [nodes + (i + 1) * 0x1C for i in range(23)] + [nodes]   # a ring
            expect(f"init records {c}", got, want)
            if fail != "ptr0":
                expect(f"init silence node {c}", ([emu.read(record(c) + o, 4) for o in (0, 4)],
                       [emu.read(nodes + o, 4) for o in (0, 4, 8, 0xC, 0x10, 0x18)]),
                       ([nodes, nodes + 0x1C], [0, 0, 0x33000, 0, 0xE74, nodes]))
        if fail != "ptr0":
            expect("silence bytes", bytes(emu.mem[0x33000:0x33000 + 0xE75]), b"\x80" * 0xE74 + b"\x00")
            expect("header", [emu.read(DRIVER + 0x12CE + o, n) for o, n in ((0, 2), (2, 2), (4, 2), (6, 2), (8, 4), (0x14, 4))],
                   [1, 8, 0, 0, 0x56EE8BA3, DRIVER])
        expect("steps", (emu.read(STEP_INT, 4), emu.read(STEP_FRAC, 4)), (1, 0))

    # sound_double_back (RTD #8): four channels of known bytes; channel 0 at several rates and node
    # positions (the unchecked path, the checked path, a wrap inside the frame); 1..3 across their ends
    import random
    rng = random.Random(104)
    OUT = 0x36000
    for rate in (0x8000, 0x10000, 0x18000, 0x30000):
        for (pos0, end0) in ((0, 0x4000), (0x100, 0x1000), (0x300, 0x500), (0x3F0, 0x400)):
            emu = machine(1)
            mem = {}

            def node_at(addr, buf, cursor, frac, start, end, cont):
                for o, v in ((0, cursor), (4, frac), (8, buf), (0xC, start), (0x10, end), (0x18, cont)):
                    emu.write(addr + o, 4, v)
                    mem[addr + o] = v
            bufs = [0x20000 + 0x2000 * c for c in range(4)] + [0x28000]
            for b in bufs:
                emu.mem[b:b + 0x2000] = bytes(rng.randrange(256) for _ in range(0x2000))
            for c in range(4):
                a, b = 0x38000 + 0x40 * c, 0x38200 + 0x40 * c     # head and its continuation
                if c == 0:
                    node_at(a, bufs[0], pos0, 0x40000000, 0, end0, b)
                    node_at(b, bufs[4], 0x777, 0x123, 0x20, 0x1800, b)
                else:
                    node_at(a, bufs[c], 0x200 * c, 0, 0, 0x200 * c + 0x73A * (c - 1), b)
                    node_at(b, bufs[4], 0, 0, 0x40 * c, 0x900, b)
                emu.write(record(c), 4, a)
                mem[record(c)] = a
            emu.write(STEP_INT, 4, rate >> 16)
            emu.write(STEP_FRAC, 4, (rate << 16) & 0xFFFFFFFF)
            emu.write(OUT + 4, 4, 0x00010000)
            data = bytearray(emu.mem)
            model_mix(mem, data, rate >> 16, (rate << 16) & 0xFFFFFFFF, OUT)
            regs = (list(emu.d), list(emu.a[:7]))
            emu.call(ROUTINES["sound_double_back"], [OUT, 0x35000], STACK)
            label = f"double_back rate {rate:#x} ch0 {pos0:#x}..{end0:#x}"
            expect(label + " samples", bytes(emu.mem[OUT + 0x10:OUT + 0x10 + 0x73A]), bytes(data[OUT + 0x10:OUT + 0x10 + 0x73A]))
            expect(label + " nodes", {a: emu.read(a, 4) for a in mem}, mem)
            expect(label + " header", (emu.read(OUT, 4), emu.read(OUT + 4, 4)), (0x73A, 0x00010001))
            regs[1][0] = OUT                                # A0: the buffer, loaded after the MOVEM restore
            expect(label + " registers (all kept but A0)", (list(emu.d), list(emu.a[:7])), regs)

    # is_channel_playing
    for active in (0, 1):
        for ch in (-1, 0, 2, 3, 4):
            for buffer in (SILENCE_BUFFER, SAMPLE):
                emu = machine(active)
                for c in range(4):
                    chain(emu, c, 1, buffer)
                d0, _ = run(emu, "is_channel_playing", ch)
                want = 1 if active and 0 <= ch < 4 and buffer != SILENCE_BUFFER else 0
                expect(f"is_channel_playing({ch}) active {active} buffer {buffer:#x}", d0, want)
    return errors


def main() -> int:
    errors = check()
    for e in errors[:20]:
        print(f"FAIL {e}")
    if errors:
        return 1
    print(f"PASS 1.0.4 sound driver: {', '.join(ROUTINES)} match ID02.delta.decomp.c on the 1.0.4 bytes")
    return 0


if __name__ == "__main__":
    sys.exit(main())
