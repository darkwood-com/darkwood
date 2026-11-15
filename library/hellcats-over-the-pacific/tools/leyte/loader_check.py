#!/usr/bin/env python3
"""Leyte Gulf's overlay image, decoded independently of its loader and compared with what the loader built.

The data fork (sources/leyte-gulf-1-0/Leyte Gulf overlay) is an a.out file: a 32-byte exec header
(a_magic 0x107 OMAGIC, a_text, a_data, a_bss, a_syms, a_entry, a_trsize, a_drsize, big-endian longs),
the text and data, then the text and data relocation records (8 bytes each: r_address, then a long
packing r_symbolnum:24, r_pcrel:1, r_length:2, r_extern:1, 4 unused, most significant first). This
decoder reads the file from that format alone, not from the loader: the image is text + data, and each
record that is not pc-relative, not external, of length 2 (a long) adds the image's base to the long at
r_address (text records) or a_text + r_address (data records).

tools/leyte/loader_harness.py runs Leyte's own loader (CODE 8 FUN_00000006) on the same bytes; the
check compares the two byte for byte, with the allocation (NewPtrClear of text+data+bss+4, base
rounded up to 4) and the File Manager log, and proves the comparison sensitive: a changed image byte,
a dropped or misplaced relocation, an external record (the loader's error alert), and a truncated
fork must each be detected. The loader's acceptance rule (bit 7 skip, bit 4 error, bits 6-5 must be 2)
is its own code's (sources/leyte-gulf-1-0/CODE/ID08.c); the format's names for those bits are a.out's.

    python3 tools/leyte/loader_check.py
"""
from __future__ import annotations

import struct
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from loader_harness import DATA_FORK, loader_run  # noqa: E402

OMAGIC = 0x107


def decode(data: bytes, base: int, drop: int | None = None) -> dict:
    """The image the a.out format describes at base. drop: a record index left out (sensitivity check)."""
    magic, text, dat, bss, syms, entry, trsize, drsize = struct.unpack(">8I", data[:32])
    if magic != OMAGIC:
        raise ValueError("not an OMAGIC a.out: %#x" % magic)
    image = bytearray(data[32:32 + text + dat])
    if len(image) != text + dat:
        raise ValueError("fork shorter than its text and data")
    at = 32 + text + dat
    records = []
    for k in range((trsize + drsize) // 8):
        rec = data[at + 8 * k:at + 8 * k + 8]
        if len(rec) != 8:
            raise ValueError("fork shorter than its relocations")
        address, packed = struct.unpack(">II", rec)
        pcrel, length, extern = packed >> 7 & 1, packed >> 5 & 3, packed >> 4 & 1
        segment = "text" if k < trsize // 8 else "data"
        records.append((segment, address, pcrel, length, extern))
    applied = []
    for k, (segment, address, pcrel, length, extern) in enumerate(records):
        if pcrel or k == drop:
            continue
        if extern or length != 2:
            raise ValueError("record %d: extern %d length %d is not a long internal relocation" % (k, extern, length))
        off = address + (text if segment == "data" else 0)
        value = struct.unpack(">I", image[off:off + 4])[0]
        image[off:off + 4] = struct.pack(">I", (value + base) & 0xFFFFFFFF)
        applied.append(k)
    return {"text": text, "data": dat, "bss": bss, "entry": entry, "records": len(records),
            "applied": applied, "data_records": drsize // 8, "end": at + trsize + drsize, "image": bytes(image)}


def compare(run: dict, data: bytes) -> list[str]:
    """Every way the loader's run differs from the decoded image: empty when they agree."""
    bad = []
    if run["stopped"] != "after relocation (JT $51a)":
        bad.append("stopped at %r" % run["stopped"])
    if not run["base"]:
        return bad + ["no image base"]
    try:
        dec = decode(data, run["base"])
    except ValueError as err:
        return bad + ["the fork does not decode: %s" % err]
    alloc = [b for b in run["heap"] if b[2] == "NewPtrClear"]
    want = dec["text"] + dec["data"] + dec["bss"] + 4
    if not alloc or alloc[0][1] != want:
        bad.append("allocation %r, not NewPtrClear(%d)" % (alloc[:1], want))
    elif run["base"] != (alloc[0][0] + 3) & ~3:
        bad.append("base %#x not the block %#x rounded to 4" % (run["base"], alloc[0][0]))
    if run["header"] != data[:32]:
        bad.append("header differs")
    if run["image"] != dec["image"]:
        diff = [i for i in range(min(len(run["image"]), len(dec["image"]))) if run["image"][i] != dec["image"][i]]
        bad.append("image differs at %d bytes (first %s), sizes %d/%d" % (
            len(diff), hex(diff[0]) if diff else "-", len(run["image"]), len(dec["image"])))
    reads = [e for e in run["files"] if e[0] == "read"]
    expect = [32, dec["text"], dec["data"]] + [8] * dec["records"]
    if [e[3] for e in reads[:len(expect)]] != expect or any(e[5] != 0 for e in reads[:len(expect)]):
        bad.append("File Manager reads are not header, text, data, then %d records" % dec["records"])
    elif reads[len(expect)][2] != dec["end"]:
        bad.append("read after the relocations starts at %d, not %d" % (reads[len(expect)][2], dec["end"]))
    return bad


def main() -> int:
    data = DATA_FORK.read_bytes()
    run = loader_run(data)
    failures = ["pristine: " + m for m in compare(run, data)]
    dec = decode(data, run["base"]) if run["base"] else None

    # Sensitivity: each corruption must be caught.
    def caught(label: str, ok: bool) -> None:
        if not ok:
            failures.append("corruption not detected: " + label)
    if dec:
        flipped = dict(run, image=run["image"][:100] + bytes([run["image"][100] ^ 1]) + run["image"][101:])
        caught("one image byte", bool(compare(flipped, data)))
        # The first applied text record and the first applied data record (pc-relative ones are skipped).
        text_k = dec["applied"][0]
        data_k = next(k for k in dec["applied"] if k >= dec["records"] - dec["data_records"])
        for k in (text_k, data_k):
            caught("dropped relocation %d" % k, decode(data, run["base"], drop=k)["image"] != run["image"])
        moved = bytearray(data)
        rec = 32 + dec["text"] + dec["data"] + 8 * text_k
        struct.pack_into(">I", moved, rec, struct.unpack_from(">I", moved, rec)[0] + 2)
        caught("a misplaced relocation (run on the pristine image)", decode(bytes(moved), run["base"])["image"] != run["image"])
        moved_run = loader_run(bytes(moved))
        caught("a misplaced relocation (loader on the corrupt fork agrees with the decoder)",
               not compare(moved_run, bytes(moved)) and moved_run["image"] != run["image"])
        extern = bytearray(data)
        extern[rec + 7] |= 0x10
        caught("an external record (the loader's error alert)", loader_run(bytes(extern))["stopped"] == "loader error alert (JT $4da)")
        short = data[:32 + dec["text"] + dec["data"] + 4]
        short_run = loader_run(short)
        caught("a truncated fork", bool(compare(short_run, short)))
        print("truncated fork: the loader stopped at %r, last read %s" % (
            short_run["stopped"], [e for e in short_run["files"] if e[0] == "read"][-1]))
    for f in failures:
        print("FAIL Leyte loader: " + f)
    if not failures:
        print("PASS Leyte loader: image %d bytes at %#x (text %d, data %d, bss %d), %d records, %d relocated, "
              "7 corruptions detected" % (len(run["image"]), run["base"], dec["text"], dec["data"], dec["bss"],
                                          dec["records"], len(dec["applied"])))
    return 1 if failures else 0


if __name__ == "__main__":
    sys.exit(main())
