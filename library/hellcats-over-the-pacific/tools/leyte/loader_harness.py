#!/usr/bin/env python3
"""Leyte Gulf's overlay loader (CODE 8 FUN_00000006) run on its own 68000 bytes, with its real data fork.

The machine: Leyte's CODE 1..9 (sources/leyte-gulf-1-0/CODE/, tools/leyte/extract_resources.py) each at
its own base; its jump table (CODE 0) in the A5 world with every slot a JMP to its routine; low memory
CurApName (0x910) = "Leyte Gulf" and CurrentA5 (0x904); a deterministic heap for NewPtr. The File
Manager traps the loader reaches are implemented on the application's data fork (sources/leyte-gulf-
1-0/Leyte Gulf overlay, verbatim, tools/leyte/extract_overlay.py) as parameter-block operations, each
call logged (trap, reference, position, count asked, count read, result); an unknown name or reference
answers the File Manager's error. The run stops at the first of STOP_SLOTS reached: the relocated image
is what the loader built, before the host's own state (not set up here) or the protection come in.

loader_run() returns the log, the image's base and bytes; tools/leyte/loader_check.py compares them
with the image decoded independently from the data fork.
"""
from __future__ import annotations

import struct
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "tools" / "original"))
from m68k_emu import Emulator  # noqa: E402

BASE = ROOT / "sources" / "leyte-gulf-1-0"
DATA_FORK = BASE / "Leyte Gulf overlay"
APP_NAME = b"Leyte Gulf"
MEMORY_SIZE = 0x400000
A5 = 0x200000
STACK_TOP = 0x1F0000
HEAP = 0x300000
SEG_BASE = {n: 0x40000 + 0x10000 * n for n in range(1, 10)}
CUR_AP_NAME, CURRENT_A5 = 0x910, 0x904
## Where the run stops: the first of these slots reached. JT $51a (a CODE 2 routine) follows the
## relocation loop and FUN_00000350's read of the fork's remaining bytes; it reads host state Leyte's
## main program sets up (a nil pointer here), so the image is taken there, complete. JT $592
## (poison_overlay_bytes) is the copy protection's first step, should a path reach it first.
## JT $4da is the loader's error alert (a relocation it refuses, a failed read or allocation): the run
## stops there too, the alert's text reference (the A5 global the loader pushes) is what tells which.
STOP_SLOTS = {0x51A: "after relocation (JT $51a)", 0x592: "copy protection (JT $592)",
              0x4DA: "loader error alert (JT $4da)"}
NO_ERR, FNF_ERR, RF_NUM_ERR, EOF_ERR = 0, -43, -51, -39


class Stop(Exception):
    pass


class FileManager:
    """The data fork as the File Manager serves it: one file, opened by name, read sequentially."""

    def __init__(self, data: bytes):
        self.data = data
        self.open_refs: dict[int, int] = {}       # refNum -> mark
        self.next_ref = 3
        self.log: list[list] = []

    def open(self, name: bytes) -> tuple[int, int]:
        if name != APP_NAME:
            self.log.append(["open", name.decode("mac_roman"), None, FNF_ERR])
            return FNF_ERR, 0
        ref = self.next_ref
        self.next_ref += 1
        self.open_refs[ref] = 0
        self.log.append(["open", name.decode("mac_roman"), ref, NO_ERR])
        return NO_ERR, ref

    def read(self, ref: int, count: int) -> tuple[int, bytes]:
        if ref not in self.open_refs:
            self.log.append(["read", ref, None, count, 0, RF_NUM_ERR])
            return RF_NUM_ERR, b""
        mark = self.open_refs[ref]
        chunk = self.data[mark:mark + count]
        self.open_refs[ref] = mark + len(chunk)
        err = NO_ERR if len(chunk) == count else EOF_ERR
        self.log.append(["read", ref, mark, count, len(chunk), err])
        return err, chunk

    def close(self, ref: int) -> int:
        err = NO_ERR if self.open_refs.pop(ref, None) is not None else RF_NUM_ERR
        self.log.append(["close", ref, err])
        return err


def _pascal(emu, at: int) -> bytes:
    n = emu.read(at, 1)
    return bytes(emu.mem[at + 1:at + 1 + n])


def build(data: bytes):
    emu = Emulator(MEMORY_SIZE, str(DATA_FORK), file_delta=0x7FFFFFFF)
    for n, base in SEG_BASE.items():
        code = (BASE / "CODE" / ("ID%02d.bin" % n)).read_bytes()
        emu.mem[base:base + len(code)] = code
    code0 = (BASE / "CODE" / "ID00.bin").read_bytes()
    size, offset = struct.unpack(">II", code0[8:16])
    for k in range(size // 8):
        e = code0[16 + 8 * k:24 + 8 * k]
        target = struct.unpack(">H", e[0:2])[0] + 4
        segment = struct.unpack(">H", e[4:6])[0]
        slot = A5 + offset + 8 * k + 2
        emu.write(slot, 2, 0x4EF9)                       # JMP abs.l to the routine (the segment loaded)
        emu.write(slot + 2, 4, SEG_BASE[segment] + target)
    emu.mem[CUR_AP_NAME:CUR_AP_NAME + 1 + len(APP_NAME)] = bytes([len(APP_NAME)]) + APP_NAME
    emu.write(CURRENT_A5, 4, A5)
    fm = FileManager(data)
    heap = {"next": HEAP, "blocks": []}

    for slot, why in STOP_SLOTS.items():
        emu.stubs[A5 + slot] = (lambda w: lambda _emu: (_ for _ in ()).throw(Stop(w)))(why)
    return emu, fm, heap


def loader_run(data: bytes | None = None) -> dict:
    data = DATA_FORK.read_bytes() if data is None else data
    emu, fm, heap = build(data)
    install_traps(emu, fm, heap)
    emu.a[5] = A5
    stopped = None
    try:
        emu.call(SEG_BASE[8] + 6, [], STACK_TOP)
    except Stop as why:
        stopped = str(why)
    base = emu.read(A5 - 0x1048, 4)
    header = bytes(emu.mem[A5 - 0x1068:A5 - 0x1048])
    code, dat = struct.unpack(">II", header[4:12])
    payload = emu.read(A5 - 0x137A, 4)            # FUN_00000350's block: the fork's bytes past the relocations
    sizes = {b[0]: b[1] for b in heap["blocks"]}
    return {"stopped": stopped, "files": fm.log, "heap": heap["blocks"], "base": base,
            "header": header, "image": bytes(emu.mem[base:base + code + dat]) if base else b"",
            "payload_ptr": payload, "payload": bytes(emu.mem[payload:payload + sizes.get(payload, 0)])}


## Parameter-block fields (Inside Macintosh, File Manager): ioResult +0x10, ioNamePtr +0x12,
## ioVRefNum +0x16, ioRefNum +0x18, ioPermssn +0x1b, ioBuffer +0x20, ioReqCount +0x24, ioActCount
## +0x28, ioPosMode +0x2c, ioPosOffset +0x2e.
IO_RESULT, IO_NAME, IO_REF, IO_BUFFER, IO_REQ, IO_ACT, IO_POS_MODE, IO_POS_OFF = 0x10, 0x12, 0x18, 0x20, 0x24, 0x28, 0x2c, 0x2e


def _result(emu, pb: int, err: int) -> None:
    emu.write(pb + IO_RESULT, 2, err & 0xFFFF)
    emu.d[0] = err & 0xFFFFFFFF


def install_traps(emu, fm: FileManager, heap: dict) -> None:
    """The traps the loader reaches: the File Manager's (on the data fork), the Memory Manager's NewPtr."""

    def open_(e):                                   # _Open: the file named by ioNamePtr, its data fork
        pb = e.a[0]
        err, ref = fm.open(_pascal(e, e.read(pb + IO_NAME, 4)))
        e.write(pb + IO_REF, 2, ref)
        _result(e, pb, err)

    def read(e):                                    # _Read: ioReqCount bytes at the mark into ioBuffer
        pb = e.a[0]
        if e.read(pb + IO_POS_MODE, 2) & 3 not in (0,):   # fsAtMark only: any other mode is not modelled
            raise NotImplementedError("_Read position mode %d" % (e.read(pb + IO_POS_MODE, 2) & 3))
        err, chunk = fm.read(e.read(pb + IO_REF, 2), e.read(pb + IO_REQ, 4))
        buf = e.read(pb + IO_BUFFER, 4)
        e.mem[buf:buf + len(chunk)] = chunk
        e.write(pb + IO_ACT, 4, len(chunk))
        _result(e, pb, err)

    def close(e):                                   # _Close
        pb = e.a[0]
        _result(e, pb, fm.close(e.read(pb + IO_REF, 2)))

    def new_ptr(e, clear: bool = False):            # _NewPtr / _NewPtrClear: D0 bytes; A0 the block, D0 noErr
        size = e.d[0]
        at = (heap["next"] + 3) & ~3
        heap["next"] = at + size
        heap["blocks"].append([at, size, "NewPtrClear" if clear else "NewPtr"])
        if clear:
            e.mem[at:at + size] = bytes(size)
        e.a[0] = at
        e.d[0] = 0

    def get_fpos(e):                                # _GetFPos: ioPosOffset = the mark
        pb = e.a[0]
        ref = e.read(pb + IO_REF, 2)
        err = NO_ERR if ref in fm.open_refs else RF_NUM_ERR
        e.write(pb + IO_POS_OFF, 4, fm.open_refs.get(ref, 0))
        fm.log.append(["getfpos", ref, fm.open_refs.get(ref), err])
        _result(e, pb, err)

    def get_eof(e):                                 # _GetEOF: ioMisc (+0x1c) = the fork's length
        pb = e.a[0]
        ref = e.read(pb + IO_REF, 2)
        err = NO_ERR if ref in fm.open_refs else RF_NUM_ERR
        e.write(pb + 0x1C, 4, len(fm.data))
        fm.log.append(["geteof", ref, len(fm.data), err])
        _result(e, pb, err)

    def set_fpos(e):                                # _SetFPos: mode 1 from start, 2 from EOF, 3 from mark
        pb = e.a[0]
        ref = e.read(pb + IO_REF, 2)
        mode = e.read(pb + IO_POS_MODE, 2) & 3
        off = e.read(pb + IO_POS_OFF, 4)
        off = off - 0x100000000 if off & 0x80000000 else off
        if ref not in fm.open_refs:
            err = RF_NUM_ERR
        else:
            origin = {1: 0, 2: len(fm.data), 3: fm.open_refs[ref]}.get(mode, fm.open_refs[ref])
            mark = origin + off
            err = NO_ERR if 0 <= mark <= len(fm.data) else EOF_ERR
            fm.open_refs[ref] = max(0, min(mark, len(fm.data)))
        fm.log.append(["setfpos", ref, mode, off, err])
        _result(e, pb, err)

    def get_ptr_size(e):                            # _GetPtrSize: A0 a block NewPtr gave; D0 its size
        sizes = {b[0]: b[1] for b in heap["blocks"]}
        if e.a[0] not in sizes:
            raise NotImplementedError("_GetPtrSize of %#x: not a block this heap gave" % e.a[0])
        e.d[0] = sizes[e.a[0]]

    emu.traps.update({0xA021: get_ptr_size})
    emu.traps.update({0xA000: open_, 0xA002: read, 0xA001: close, 0xA11E: new_ptr,
                      0xA018: get_fpos, 0xA011: get_eof, 0xA044: set_fpos,
                      0xA31E: lambda e: new_ptr(e, True)})


if __name__ == "__main__":
    r = loader_run()
    print({k: (v if k not in ("image", "header", "payload") else len(v)) for k, v in r.items()})
