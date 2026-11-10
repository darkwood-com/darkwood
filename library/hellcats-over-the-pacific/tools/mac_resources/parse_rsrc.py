#!/usr/bin/env python3
"""Attempt a classic Macintosh resource-map parse.

Does not invent resource types/IDs when the map cannot be validated.
Returns a dict with parse_status and either resources[] or an error reason.
"""

from __future__ import annotations

import struct
from pathlib import Path
from typing import Any


def _u16(data: bytes, off: int) -> int:
    return struct.unpack_from(">H", data, off)[0]


def _u32(data: bytes, off: int) -> int:
    return struct.unpack_from(">I", data, off)[0]


def _i16(data: bytes, off: int) -> int:
    return struct.unpack_from(">h", data, off)[0]


def parse_classic_resource_fork(data: bytes) -> dict[str, Any]:
    """Parse an in-memory classic Mac resource fork (data fork layout).

    Layout (Inside Macintosh: Resource Manager):
      offset 0:  dataOffset (u32), mapOffset (u32), dataLength (u32), mapLength (u32)
    """
    result: dict[str, Any] = {
        "parse_status": "failed",
        "format": "classic-resource-fork",
        "resources": [],
        "error": None,
    }
    if len(data) < 16:
        result["error"] = "file shorter than 16-byte resource fork header"
        return result

    data_offset = _u32(data, 0)
    map_offset = _u32(data, 4)
    data_length = _u32(data, 8)
    map_length = _u32(data, 12)

    header = {
        "data_offset": data_offset,
        "map_offset": map_offset,
        "data_length": data_length,
        "map_length": map_length,
    }
    result["header"] = header

    if map_offset + 16 > len(data):
        result["error"] = "map_offset beyond file end"
        return result
    if data_offset + data_length > len(data) and data_length != 0:
        # Allow slight slack only if lengths are nonsense; still fail clearly.
        if data_offset >= len(data):
            result["error"] = "data_offset beyond file end"
            return result

    # Map begins with copy of 16-byte header, then reserved, then type-list offset.
    if map_offset + 28 > len(data):
        result["error"] = "resource map too short for type-list fields"
        return result

    map_data_offset = _u32(data, map_offset + 0)
    map_map_offset = _u32(data, map_offset + 4)
    # Sanity: embedded header should echo outer offsets for a well-formed fork.
    if map_data_offset != data_offset or map_map_offset != map_offset:
        # Not necessarily fatal for some dumps, but treat as failed validation
        # rather than inventing entries.
        result["error"] = (
            "resource map header does not echo fork header "
            f"(map data_offset={map_data_offset:#x} map_offset={map_map_offset:#x})"
        )
        result["header_echo"] = {
            "map_data_offset": map_data_offset,
            "map_map_offset": map_map_offset,
        }
        return result

    type_list_offset_in_map = _u16(data, map_offset + 24)
    name_list_offset_in_map = _u16(data, map_offset + 26)
    type_list_abs = map_offset + type_list_offset_in_map
    name_list_abs = map_offset + name_list_offset_in_map

    if type_list_abs + 2 > len(data):
        result["error"] = "type list offset out of range"
        return result

    type_count_minus_one = _u16(data, type_list_abs)
    type_count = type_count_minus_one + 1
    if type_count > 4096:
        result["error"] = f"implausible type count {type_count}"
        return result

    resources: list[dict[str, Any]] = []
    type_entry_base = type_list_abs + 2

    for ti in range(type_count):
        te = type_entry_base + ti * 8
        if te + 8 > len(data):
            result["error"] = f"type entry {ti} truncated"
            return result
        type_fourcc = data[te : te + 4]
        try:
            type_str = type_fourcc.decode("mac_roman")
        except Exception:
            type_str = type_fourcc.hex()
        # Prefer printable ASCII fourcc when possible.
        if all(32 <= b < 127 for b in type_fourcc):
            type_str = type_fourcc.decode("ascii")

        ref_count_minus_one = _u16(data, te + 4)
        ref_count = ref_count_minus_one + 1
        ref_list_offset_in_type_list = _u16(data, te + 6)
        ref_list_abs = type_list_abs + ref_list_offset_in_type_list

        for ri in range(ref_count):
            re = ref_list_abs + ri * 12
            if re + 12 > len(data):
                result["error"] = f"ref entry {type_str}/{ri} truncated"
                return result
            res_id = _i16(data, re)
            name_offset = _u16(data, re + 2)
            attrs_and_offset = _u32(data, re + 4)
            # attrs in high byte; 24-bit offset into resource data section
            attrs = (attrs_and_offset >> 24) & 0xFF
            data_rel = attrs_and_offset & 0xFFFFFF
            res_data_abs = data_offset + data_rel
            if res_data_abs + 4 > len(data):
                result["error"] = (
                    f"resource {type_str}/{res_id} data pointer out of range "
                    f"(abs={res_data_abs:#x})"
                )
                return result
            res_size = _u32(data, res_data_abs)
            payload_off = res_data_abs + 4
            if payload_off + res_size > len(data):
                result["error"] = (
                    f"resource {type_str}/{res_id} size {res_size} exceeds file"
                )
                return result

            name: str | None = None
            if name_offset != 0xFFFF:
                nabs = name_list_abs + name_offset
                if nabs < len(data):
                    nlen = data[nabs]
                    if nabs + 1 + nlen <= len(data):
                        raw_name = data[nabs + 1 : nabs + 1 + nlen]
                        try:
                            name = raw_name.decode("mac_roman")
                        except Exception:
                            name = raw_name.hex()

            resources.append(
                {
                    "type": type_str,
                    "id": res_id,
                    "name": name,
                    "attrs": attrs,
                    "size": res_size,
                    "data_file_offset": payload_off,
                }
            )

    result["parse_status"] = "ok"
    result["type_count"] = type_count
    result["resource_count"] = len(resources)
    result["resources"] = resources
    result["name_list_offset"] = name_list_abs
    return result


def parse_rsrc_file(path: Path) -> dict[str, Any]:
    data = path.read_bytes()
    out = parse_classic_resource_fork(data)
    out["path"] = str(path)
    out["file_size"] = len(data)
    return out


if __name__ == "__main__":
    import json
    import sys

    if len(sys.argv) != 2:
        print(f"usage: {sys.argv[0]} PATH", file=sys.stderr)
        sys.exit(2)
    print(json.dumps(parse_rsrc_file(Path(sys.argv[1])), indent=2, sort_keys=True))
