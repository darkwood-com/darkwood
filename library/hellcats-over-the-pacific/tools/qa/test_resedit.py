#!/usr/bin/env python3
"""Regression tests for deterministic ResEdit enrichment."""

from __future__ import annotations

import hashlib
import json
import re
import struct
import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT))

from tools import resedit  # noqa: E402


class ResEditMetadataTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.records, cls.errors = resedit.build_records(write=False)
        cls.by_key = {r["resource"]["key"]: r for r in cls.records}

    def test_complete_curated_inventory(self) -> None:
        self.assertEqual([], self.errors)
        self.assertEqual(119, len(self.records))
        self.assertEqual(resedit.REQUIRED_TYPES, list(dict.fromkeys(
            r["resource"]["type"] for r in self.records)))
        self.assertEqual(30, len(resedit.REQUIRED_TYPES))
        inventory = json.loads((resedit.RESEDIT / "inventory.json").read_text(encoding="utf-8"))
        self.assertEqual(resedit.inventory_document(self.records), inventory)
        self.assertEqual(12, inventory["supporting_file_count"])

    def test_every_resource_has_matching_binary_and_metadata(self) -> None:
        for record in self.records:
            typ = record["resource"]["type"]
            rid = record["resource"]["id"]
            stem = resedit.output_stem(typ, rid)
            metadata = resedit.RESEDIT / typ / f"{stem}.json"
            binary = ROOT / record["evidence"]["binary_path"]
            self.assertTrue(metadata.is_file(), metadata)
            self.assertTrue(binary.is_file(), binary)
            self.assertEqual(record, json.loads(metadata.read_text(encoding="utf-8")))
            self.assertEqual(record["evidence"]["sha256"], hashlib.sha256(binary.read_bytes()).hexdigest())

    def test_decoders_and_claim_statuses(self) -> None:
        allowed = {"PROVEN", "VERIFIED", "INFERRED", "UNKNOWN"}
        for record in self.records:
            self.assertTrue(set(record["claims"].values()) <= allowed)
            decoded = record["decoded"]
            self.assertEqual(record["evidence"]["binary_size"],
                             decoded["consumed_bytes"] + decoded["trailing_bytes"])
        unknown = {r["resource"]["key"] for r in self.records
                   if r["claims"]["decoded_structure"] == "UNKNOWN"}
        self.assertEqual({"MDPL:128"}, unknown)

    def test_core_resource_decodes(self) -> None:
        self.assertEqual(259, self.by_key["ALRT:259"]["decoded"]["properties"]["ditl_id"])
        map_items = self.by_key["DITL:137"]["decoded"]["elements"]
        self.assertEqual(16, len(map_items))
        self.assertEqual("mission map custom drawing region", map_items[7]["descriptive_identity"])
        self.assertEqual(16, len(self.by_key["clut:999"]["decoded"]["elements"]))
        self.assertEqual("#217B31", self.by_key["clut:999"]["decoded"]["elements"][10]["hex_rgb8"])
        self.assertEqual("1.0.3", self.by_key["vers:1"]["decoded"]["properties"]["version"])
        code0 = self.by_key["CODE:0"]["decoded"]
        self.assertEqual(175, code0["properties"]["jump_table_entry_count"])
        self.assertEqual(0x20, code0["elements"][0]["a5_offset"])
        self.assertTrue(all(
            entry["target_file_offset"] == entry["stored_tail"] + 4
            for entry in code0["elements"]
            if entry["kind"] == "loadseg_jump_table_entry"
        ))
        file_menu = self.by_key["MENU:2"]["decoded"]["elements"]
        self.assertEqual("VERIFIED", file_menu[5]["command_handler_status"])
        self.assertIn("mission map", file_menu[5]["command_handler"])
        self.assertEqual("VERIFIED", file_menu[8]["command_handler_status"])

    def test_code_0_recreated_c_is_byte_exact(self) -> None:
        source = (resedit.RESEDIT / "CODE" / "ID00.c").read_text(encoding="utf-8")
        header = [int(value, 16) for value in re.findall(
            r"DAT_000000(?:00_above_a5_size|04_below_a5_size|08_jump_table_size|0c_jump_table_a5_offset) = 0x([0-9a-f]+)",
            source,
        )]
        rows = [tuple(int(value, 16) for value in match) for match in re.findall(
            r"\{0x([0-9a-f]{4}),0x([0-9a-f]{4}),0x([0-9a-f]{4}),0x([0-9a-f]{4})\}",
            source,
        )]
        rebuilt = b"".join(struct.pack(">I", value) for value in header)
        rebuilt += b"".join(struct.pack(">4H", *row) for row in rows)
        self.assertEqual((resedit.RESEDIT / "CODE" / "ID00.bin").read_bytes(), rebuilt)

        semantic = (resedit.RESEDIT / "CODE" / "ID00.decomp.c").read_text(encoding="utf-8")
        decoded_rows = [
            (int(match[0]), int(match[1], 16), int(match[2], 16), int(match[3]),
             int(match[4], 16), int(match[5], 16))
            for match in re.findall(
                r"\{\s*(\d+),0x([0-9a-f]+),0x([0-9a-f]+),(\d+),0x([0-9a-f]+),0x([0-9a-f]+),",
                semantic,
            )
        ]
        expected_rows = [
            (entry["index"], entry["record_file_offset"], entry["a5_offset"],
             entry["segment_id"], entry["stored_tail"], entry["target_file_offset"])
            for entry in self.by_key["CODE:0"]["decoded"]["elements"]
            if entry["kind"] == "loadseg_jump_table_entry"
        ]
        self.assertEqual(expected_rows, decoded_rows)

    def test_sound_and_color_icons_are_structural_not_heuristic(self) -> None:
        sound = self.by_key["snds:19330"]["decoded"]["properties"]
        self.assertEqual("none", sound["compression"])
        self.assertEqual(1, sound["channels"])
        self.assertEqual(8, sound["sample_width_bits"])
        self.assertGreater(sound["sample_rate_hz"], 22000)
        self.assertEqual({"release_sound_slots", "load_named_sound_into_slot",
                          "show_mission_summary_dialog", "play_named_sound_async"},
                         {x["function_or_context"] for x in self.by_key["snds:19330"]["type_code_references"]})
        for rid in list(range(256, 262)) + list(range(512, 520)):
            comparison = self.by_key[f"cicn:{rid}"]["decoded"]["properties"]["png_pixel_comparison"]
            self.assertEqual("pixel_exact", comparison["status"])
            self.assertEqual(0, comparison["differing_pixels"])
        for key in ("ICON:128", "ICN#:128", "ICN#:129", "ics#:128", "ics#:129"):
            comparison = self.by_key[key]["decoded"]["properties"]["png_pixel_comparison"]
            self.assertEqual("pixel_exact", comparison["status"])
            self.assertEqual(0, comparison["differing_pixels"])

    def test_bidirectional_relationships_resolve(self) -> None:
        for record in self.records:
            source = record["resource"]["key"]
            for link in record["relationships"]["outgoing"]:
                self.assertNotIn("target_status", link)
                target = self.by_key[link["target"]]
                self.assertTrue(any(back["source"] == source and back["kind"] == link["kind"]
                                    for back in target["relationships"]["incoming"]))

    def test_payloads_come_from_the_1_0_3_disk(self) -> None:
        for record in self.records:
            evidence = record["evidence"]
            self.assertEqual("1.0.3", evidence["source_version"])
            self.assertEqual("sources/mac-os-9/Hellcats_1_0_3.dsk", evidence["source_container"])
        exports = {r["resource"]["key"]: r["evidence"]["resedit_export_status"] for r in self.records
                   if r["evidence"]["resedit_export_status"] not in (None, "identical")}
        self.assertEqual({"DITL:135": "truncated: the first 536 of 544 bytes"}, exports)

    def test_ditl_135_is_complete_from_the_disk(self) -> None:
        ditl = self.by_key["DITL:135"]
        self.assertEqual(544, ditl["evidence"]["binary_size"])
        self.assertTrue(ditl["decoded"]["properties"]["parse_complete"])
        last = ditl["decoded"]["elements"][-1]
        self.assertEqual((9, "editable_text"), (last["index"], last["item_type"]))
        self.assertEqual({"top": 230, "left": 31, "bottom": 247, "right": 221, "width": 190, "height": 17},
                         last["bounds"])

    def test_1_0_4_differences_are_explicit(self) -> None:
        differs = {r["resource"]["key"] for r in self.records if r["version_1_0_4"]["status"] == "differs"}
        sounds = {f"snds:{rid}" for rid in (2186, 13493, 15490, 16059, 16815, 21266, 24100, 25233)}
        # The updater replaces CODE 2, vers 1 and the eight sounds; HEL1 127/128 are the saved settings
        # and scores the 1.0.4 copy had written by playing.
        self.assertEqual({"CODE:2", "HEL1:127", "HEL1:128", "vers:1"} | sounds, differs)
        self.assertEqual("1.0.4", self.by_key["vers:1"]["version_1_0_4"]["properties"]["version"])
        code = self.by_key["CODE:2"]
        self.assertEqual((19994, 24832), (code["evidence"]["binary_size"], code["version_1_0_4"]["size"]))
        gun = self.by_key["snds:25233"]
        self.assertEqual((2947, 6226), (gun["evidence"]["binary_size"], gun["version_1_0_4"]["size"]))

if __name__ == "__main__":
    unittest.main(verbosity=2)
