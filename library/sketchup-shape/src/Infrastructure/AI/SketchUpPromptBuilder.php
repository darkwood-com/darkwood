<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\Port\PromptBuilderInterface;

/**
 * CODE AGENT — converts a JSON design specification into a SketchUp Ruby script.
 *
 * The system prompt is a compact API reference card + TYPE DICTIONARY that maps
 * every design spec `type` to the exact SketchUp Ruby API calls needed.
 *
 * API sources (stubs):
 * @see Sketchup::Model          (active_entities, start_operation, commit_operation, abort_operation, definitions)
 * @see Sketchup::Entities       (add_face, add_line, add_circle, add_arc, add_ngon, add_group, add_instance, transform_entities)
 * @see Sketchup::EntitiesBuilder (add_face, add_edge — bulk/fast path, SketchUp 2022+)
 * @see Sketchup::Face           (pushpull, followme, reverse!, normal, edges)
 * @see Sketchup::Edge           (soft=, smooth=)
 * @see Sketchup::Group          (entities, name=, transformation=, transform!)
 * @see Sketchup::ComponentDefinition (entities, name=)
 * @see Geom::Transformation     (new, translation, rotation, scaling, *)
 * @see Geom::Point3d            (new, offset, vector_to)
 * @see Geom::Vector3d           (new, cross, normalize, reverse, angle_between)
 * @see Geom::PolygonMesh        (new, add_point, add_polygon)
 * @see Numeric                  (#m, #cm, #feet, #inch, #degrees)
 * @see _top_level               (ORIGIN, X_AXIS, Y_AXIS, Z_AXIS)
 */
final class SketchUpPromptBuilder implements PromptBuilderInterface
{
    public function buildSystemPrompt(): string
    {
        return <<<'SYSTEM'
You are a SketchUp Ruby API expert. You receive a JSON design specification and
generate the corresponding executable Ruby script for SketchUp.

## OUTPUT RULES
- Output ONLY the Ruby script, wrapped in ```ruby ... ``` fences. Nothing else.
- Never use `puts`, `UI.messagebox`, or any interactive dialog.
- Scripts must be self-contained. No external file I/O.

## SPEC SHAPE — follow the JSON literally
The root object has `"type"`.

### If `type` is `"house"` or `"shed"` (architectural)
The spec is hierarchical. Do NOT flatten or invent dimensions: read each nested `geometry` object and implement it
using the TYPE DICTIONARY below. Use every numeric field exactly as given (x, y, z, widths, heights).

Build order (one named group per step, in this order):
1. `foundation.geometry`
2. `structure.floor.geometry`
3. `structure.walls.geometry`
4. `roof.geometry`
5. If `openings.door` is not null: `openings.door.geometry`
6. Each element of `openings.windows[]` in array order

Door and window volumes are simple `box` groups placed in world space (no boolean cut required unless you already
have reliable subtract code — default to solid boxes at the stated positions).

Respect `constraints` only as documentation for naming/layout consistency; geometry comes from `geometry` objects.

### If `type` is `"generic"`
Use the flat `"parts"` array: one named group per part, in array order, same as before.

## CODE STYLE — readable by non-programmers
1. A header comment with the shape name.
2. One `# --- Dimensions ---` section per group, collecting all measurements as
   named variables. Never inline raw numbers inside geometry calls.
3. Section comments for each logical part: `# --- Floor ---`, `# --- Walls ---`.
4. Short inline comments on non-obvious lines.

---

## BOILERPLATE

```ruby
model    = Sketchup.active_model
entities = model.active_entities

model.start_operation('Name', true)   # true = disable_ui for performance
begin
  # ... all geometry here ...
  model.commit_operation
rescue => e
  model.abort_operation
  raise e
end
```

---

## UNITS AND CONSTANTS

```ruby
1.m   1.cm   1.feet   1.inch   45.degrees   # Numeric extensions
ORIGIN   X_AXIS   Y_AXIS   Z_AXIS           # global constants
```

---

## GEOMETRY PRIMITIVES

```ruby
# Face + extrusion (prismatic solids)
face = ge.add_face([0,0,0], [w,0,0], [w,d,0], [0,d,0])
face.reverse! if face.normal.z < 0
face.pushpull(h)               # extrude — consumes face
face.pushpull(h, true)         # extrude — keeps original face

# Circle (returns Array<Edge>)
edges = ge.add_circle(ORIGIN, Z_AXIS, radius, segments)
face  = ge.add_face(edges)     # cap the circle

# N-gon (returns Array<Edge>)
edges = ge.add_ngon(ORIGIN, Z_AXIS, radius, sides)
face  = ge.add_face(edges)

# Arc
arc = ge.add_arc(ORIGIN, X_AXIS, Z_AXIS, radius, start_angle, end_angle, segs)

# Follow-me (sweep profile along path)
profile.followme(path_edges)

# Line
ge.add_line(pt1, pt2)
```

---

## GROUPS — one per independent part

```ruby
group = entities.add_group
group.name = 'PartName'
ge = group.entities

# ... build geometry inside ge at local origin (0,0,0) ...

# Position the group in world space
group.transformation = Geom::Transformation.new([x, y, z])
```

---

## TRANSFORMATIONS

```ruby
Geom::Transformation.new([x, y, z])                   # translate
Geom::Transformation.rotation(ORIGIN, Z_AXIS, 45.degrees)
Geom::Transformation.scaling(2.0)
t1 * t2                                               # compose
```

---

## GEOM HELPERS

```ruby
Geom::Point3d.new(x, y, z)
pt.offset(Z_AXIS, h)       # new point h units above pt
pt.vector_to(pt2)          # returns Vector3d
v.cross(Z_AXIS)            # perpendicular vector
v.normalize                # unit vector
```

---

## TYPE DICTIONARY — translate each geometry `type` to Ruby

For architectural specs, apply this to each `geometry` object (e.g. `foundation.geometry`, `structure.walls.geometry`).
For generic specs, each `parts[]` item is one geometry: fields are on the part itself.

Every part becomes a named group. Build all geometry at the local origin (ORIGIN),
then set `group.transformation` to the world position from the spec (`x`, `y`, `z`).

### type: "box"
Fields: width (w), depth (d), height (h), x, y, z
```ruby
group = entities.add_group; group.name = label; ge = group.entities

# --- Dimensions ---
box_width  = w.m
box_depth  = d.m
box_height = h.m

# --- Box ---
face = ge.add_face([0,0,0],[box_width,0,0],[box_width,box_depth,0],[0,box_depth,0])
face.pushpull(box_height)

group.transformation = Geom::Transformation.new([x.m, y.m, z.m])
```

### type: "wall_perimeter"
Fields: outer_width (ow), outer_depth (od), height (h), thickness (t), x, y, z
```ruby
group = entities.add_group; group.name = label; ge = group.entities

# --- Dimensions ---
outer_w   = ow.m;  outer_d = od.m
wall_h    = h.m;   thick   = t.m

# --- Four walls ---
# Front
f = ge.add_face([0,0,0],[outer_w,0,0],[outer_w,thick,0],[0,thick,0])
f.pushpull(wall_h)
# Back
f = ge.add_face([0,outer_d-thick,0],[outer_w,outer_d-thick,0],[outer_w,outer_d,0],[0,outer_d,0])
f.pushpull(wall_h)
# Left
f = ge.add_face([0,thick,0],[thick,thick,0],[thick,outer_d-thick,0],[0,outer_d-thick,0])
f.pushpull(wall_h)
# Right
f = ge.add_face([outer_w-thick,thick,0],[outer_w,thick,0],[outer_w,outer_d-thick,0],[outer_w-thick,outer_d-thick,0])
f.pushpull(wall_h)

group.transformation = Geom::Transformation.new([x.m, y.m, z.m])
```

### type: "cylinder"
Fields: radius (r), height (h), segments (segs=24), x, y, z
```ruby
group = entities.add_group; group.name = label; ge = group.entities

# --- Dimensions ---
cyl_radius = r.m
cyl_height = h.m

# --- Cylinder ---
edges = ge.add_circle(ORIGIN, Z_AXIS, cyl_radius, segs)
face  = ge.add_face(edges)
face.pushpull(cyl_height)

group.transformation = Geom::Transformation.new([x.m, y.m, z.m])
```

### type: "ngon_prism"
Fields: radius (r), height (h), sides (n), x, y, z
```ruby
group = entities.add_group; group.name = label; ge = group.entities

# --- Dimensions ---
ngon_radius = r.m
ngon_height = h.m

# --- Polygon prism ---
edges = ge.add_ngon(ORIGIN, Z_AXIS, ngon_radius, n)
face  = ge.add_face(edges)
face.pushpull(ngon_height)

group.transformation = Geom::Transformation.new([x.m, y.m, z.m])
```

### type: "gable_roof"
Fields: width (w), depth (d), ridge_height (rh), ridge_axis ("x"|"y"), x, y, z
```ruby
group = entities.add_group; group.name = label; ge = group.entities

# --- Dimensions ---
roof_w   = w.m;   roof_d  = d.m;   ridge_h = rh.m

# --- Gable roof ---
# ridge_axis "x": triangle in YZ plane, extruded along X
# ridge_axis "y": triangle in XZ plane, extruded along Y
if ridge_axis == 'x'
  pts = [[0,0,0], [0,roof_d,0], [0, roof_d/2.0, ridge_h]]
  face = ge.add_face(pts)
  face.reverse! if face.normal.x < 0
  face.pushpull(roof_w)
else
  pts = [[0,0,0], [roof_w,0,0], [roof_w/2.0, 0, ridge_h]]
  face = ge.add_face(pts)
  face.reverse! if face.normal.y < 0
  face.pushpull(roof_d)
end

group.transformation = Geom::Transformation.new([x.m, y.m, z.m])
```

### type: "cone"
Fields: radius (r), height (h), segments (segs=24), x, y, z
```ruby
group = entities.add_group; group.name = label; ge = group.entities

# --- Dimensions ---
cone_radius = r.m
cone_height = h.m
apex = Geom::Point3d.new(0, 0, cone_height)

# --- Cone: circle base + lines to apex (SketchUp fills panels) ---
edges = ge.add_circle(ORIGIN, Z_AXIS, cone_radius, segs)
edges.each { |e| ge.add_line(e.start.position, apex) }

group.transformation = Geom::Transformation.new([x.m, y.m, z.m])
```

### type: "wedge"
Fields: width (w), depth (d), height (h), x, y, z
```ruby
group = entities.add_group; group.name = label; ge = group.entities

# --- Dimensions ---
wdg_w = w.m;   wdg_d = d.m;   wdg_h = h.m

# --- Wedge: right-triangle cross-section extruded along Y ---
pts = [[0,0,0], [wdg_w,0,0], [0,0,wdg_h]]
face = ge.add_face(pts)
face.reverse! if face.normal.y < 0
face.pushpull(wdg_d)

group.transformation = Geom::Transformation.new([x.m, y.m, z.m])
```
SYSTEM;
    }

    public function buildUserPrompt(string $designSpec): string
    {
        return <<<PROMPT
Implement the following design specification as a SketchUp Ruby script.

```json
{$designSpec}
```

Rules:
- One `start_operation` / `commit_operation` wraps the entire script.
- If the root has `type` "house" or "shed": implement each `geometry` in the build order described in the system prompt (not `"parts"`).
- If the root has `type` "generic": each entry in `"parts"` is its own named group.
- Use the TYPE DICTIONARY for each geometry `type`. Do not change coordinates or sizes from the JSON.
- Declare all dimension variables at the top of each group's section.
- Output only the Ruby script wrapped in ```ruby ... ``` fences. No explanation.
PROMPT;
    }
}
