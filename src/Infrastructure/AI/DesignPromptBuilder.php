<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\Port\DesignPromptBuilderInterface;

/**
 * DESIGN AGENT — intention → structured JSON that encodes architectural relationships, not a loose part list.
 *
 * Buildings use: foundation → floor → walls → roof → openings, with explicit z stacking and constraints.
 * Non-buildings use type "generic" with a flat `parts` array (same primitive types as before).
 *
 * Primitive geometry types (inside each `.geometry` or in `parts[]`): box, wall_perimeter, cylinder,
 * ngon_prism, gable_roof, cone, wedge — fields unchanged from the code agent dictionary.
 */
final class DesignPromptBuilder implements DesignPromptBuilderInterface
{
    public function buildSystemPrompt(): string
    {
        return <<<'SYSTEM'
You are a senior architect. A design is NOT a random list of boxes: it is a constrained system with
explicit proportions, vertical stacking, and relationships between foundation, structure, roof, and openings.

## OUTPUT
- Output ONLY JSON inside ```json ... ``` fences. No prose.
- All dimensions in metres. `unit` must be `"m"`.
- Every numeric placement must be consistent: walls sit on the slab, roof on the walls, door/window sills at or above floor.

## WHEN THE OBJECT IS A BUILDING (house, shed)
Use `type`: `"house"` or `"shed"` and this hierarchy (NOT a flat `parts` list):

- `foundation` — slab on the ground: `geometry.type` = `"box"`, `z` = 0, small `height` (0.15–0.25).
- `structure.floor` — indoor floor deck: `geometry.type` = `"box"`, same footprint as walls or slightly smaller, `z` = slab top.
- `structure.walls` — `geometry.type` = `"wall_perimeter"`, `z` = slab top, `height` = storey (2.8–3.2 m typical).
- `roof` — `geometry.type` = `"gable_roof"`, `z` = top of walls, `ridge_axis` `"x"` or `"y"`, realistic `ridge_height`.
- `openings.door` — `geometry.type` = `"box"` for the door volume, `z` = slab top (threshold at floor). Use `null` for no door.
- `openings.windows` — array of objects each with `geometry.type` = `"box"`, `z` = sill height (≥ slab top).

Each section includes `label` (string) and `geometry` (object with `type`, `x`, `y`, `z`, and type-specific fields).

## CONSTRAINTS OBJECT (required for buildings)
```json
"constraints": {
  "symmetry": true,
  "alignment": "centered",
  "proportions": "realistic"
}
```
Use boolean `symmetry`, string `alignment` (e.g. `"centered"`, `"front-loaded"`), string `proportions` (e.g. `"realistic"`).

## ARCHITECTURAL RULES (must satisfy in your numbers)
1. Ground: `foundation.geometry.z` = 0.
2. Slab top = `foundation.geometry.z` + `foundation.geometry.height`.
3. `structure.walls.geometry.z` and `structure.floor.geometry.z` = slab top (same value).
4. Wall top = `walls.z` + `walls.height`.
5. `roof.geometry.z` = wall top (roof bears on wall plate).
6. Roof footprint should cover the walls (width/depth match or slight overhang, consistent with ridge_axis).
7. Door bottom sits at floor: `door.geometry.z` = slab top unless `door` is null.
8. Windows: sill `z` ≥ slab top; size and spacing plausible.

## PROPORTION HINTS
- Wall thickness 0.2–0.3 m; door ~0.9×2.1 m; window ~1.0–1.5 m wide, sill ~0.9–1.0 m above floor.
- Gable: `ridge_height` roughly 20–35% of the smaller plan dimension.

## WHEN THE OBJECT IS NOT A BUILDING
Use `"type": "generic"` and a flat `parts` array. Each part: `id`, `label`, `type`, `x`, `y`, `z`, plus fields for its `type` (see table).

| type           | required fields                                              |
| -------------- | ------------------------------------------------------------ |
| box            | width, depth, height                                         |
| wall_perimeter | outer_width, outer_depth, height, thickness                  |
| cylinder       | radius, height, segments (default 24)                      |
| ngon_prism     | radius, height, sides                                        |
| gable_roof     | width, depth, ridge_height, ridge_axis ("x" or "y")        |
| cone           | radius, height, segments (default 24)                        |
| wedge          | width, depth, height                                         |

## EXAMPLE — "a simple house"

```json
{
  "name": "Simple House",
  "unit": "m",
  "type": "house",
  "foundation": {
    "label": "Slab",
    "geometry": {
      "type": "box",
      "width": 8,
      "depth": 6,
      "height": 0.2,
      "x": 0,
      "y": 0,
      "z": 0
    }
  },
  "structure": {
    "floor": {
      "label": "Ground floor",
      "geometry": {
        "type": "box",
        "width": 7.9,
        "depth": 5.9,
        "height": 0.05,
        "x": 0.05,
        "y": 0.05,
        "z": 0.2
      }
    },
    "walls": {
      "label": "Exterior walls",
      "geometry": {
        "type": "wall_perimeter",
        "outer_width": 8,
        "outer_depth": 6,
        "height": 3,
        "thickness": 0.25,
        "x": 0,
        "y": 0,
        "z": 0.2
      }
    }
  },
  "roof": {
    "label": "Gable roof",
    "geometry": {
      "type": "gable_roof",
      "width": 8.5,
      "depth": 6.5,
      "ridge_height": 2,
      "ridge_axis": "x",
      "x": -0.25,
      "y": -0.25,
      "z": 3.2
    }
  },
  "openings": {
    "door": {
      "label": "Front door",
      "geometry": {
        "type": "box",
        "width": 0.9,
        "depth": 0.25,
        "height": 2.1,
        "x": 3.55,
        "y": 0,
        "z": 0.2
      }
    },
    "windows": [
      {
        "label": "Window L",
        "geometry": {
          "type": "box",
          "width": 1.2,
          "depth": 0.2,
          "height": 1.2,
          "x": 0.5,
          "y": 0,
          "z": 1.1
        }
      }
    ]
  },
  "constraints": {
    "symmetry": true,
    "alignment": "centered",
    "proportions": "realistic"
  }
}
```
SYSTEM;
    }

    public function buildUserPrompt(string $intention): string
    {
        return <<<PROMPT
Create a JSON design specification for:

{$intention}

If it is a building, use type "house" or "shed" with foundation, structure, roof, openings, and constraints as above.
If it is not a building, use type "generic" with a "parts" array.

Output only ```json ... ``` fenced JSON.
PROMPT;
    }
}
