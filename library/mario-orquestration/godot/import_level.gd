extends Node2D

## Minimal importer for generated Mario-inspired level JSON.
## Expected path: res://../var/mario-levels/world_1_1_inspired.json

@export var level_path: String = "res://../var/mario-levels/world_1_1_inspired.json"
@export var terrain_layer_path: NodePath = NodePath("Terrain")
@export var objects_root_path: NodePath = NodePath("Objects")

func _ready() -> void:
    var level_data := _load_level_json(level_path)
    if level_data.is_empty():
        push_error("Could not load level JSON")
        return

    _paint_terrain(level_data.get("layers", {}).get("terrain", []))
    _spawn_objects(level_data.get("layers", {}).get("objects", []))

func _load_level_json(path: String) -> Dictionary:
    var file := FileAccess.open(path, FileAccess.READ)
    if file == null:
        return {}

    var parsed: Variant = JSON.parse_string(file.get_as_text())
    if parsed == null:
        return {}

    if parsed is Dictionary:
        return parsed

    return {}

func _paint_terrain(terrain: Array) -> void:
    var tile_map_layer: TileMapLayer = get_node_or_null(terrain_layer_path)
    if tile_map_layer == null:
        return

    var tile_set := tile_map_layer.tile_set
    if tile_set == null:
        push_warning("Terrain TileMapLayer has no TileSet configured.")
        return

    var source_ids: Array[int] = tile_set.get_source_ids()
    if source_ids.is_empty():
        push_warning("Terrain TileSet has no sources configured.")
        return

    var source_id: int = int(source_ids[0])

    for piece in terrain:
        var x: int = int(piece.get("x", 0))
        var y: int = int(piece.get("y", 0))
        var w: int = int(piece.get("w", 1))
        var h: int = int(piece.get("h", 1))

        for xx in range(x, x + w):
            for yy in range(y, y + h):
                # Atlas coordinates are placeholders until a real tileset is used.
                tile_map_layer.set_cell(Vector2i(xx, yy), source_id, Vector2i(0, 0))

func _spawn_objects(objects: Array) -> void:
    var objects_root := get_node_or_null(objects_root_path)
    if objects_root == null:
        return

    for object_data in objects:
        var marker := Node2D.new()
        marker.name = str(object_data.get("type", "object"))
        marker.position = Vector2(int(object_data.get("x", 0)) * 16, int(object_data.get("y", 0)) * 16)
        objects_root.add_child(marker)
