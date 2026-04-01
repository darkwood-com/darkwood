<?php

namespace App\Infrastructure\AI;

use App\Domain\Port\PromptBuilderInterface;

/**
 * Builds prompts tuned for SketchUp Ruby API code generation.
 *
 * System prompt establishes the LLM as a SketchUp expert and anchors it to
 * the Ruby API conventions. A one-shot example guides the output format.
 */
final class SketchUpPromptBuilder implements PromptBuilderInterface
{
    public function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert SketchUp Ruby API developer.
Your sole task is to generate valid Ruby scripts executable inside SketchUp via the SketchUp Ruby API.

Rules you must follow without exception:
- Output ONLY a Ruby script. No explanation, no prose, no markdown outside the code fence.
- Wrap the entire output in ```ruby ... ``` fences.
- Always wrap geometry operations inside model.start_operation / model.commit_operation for undo support.
- Use Sketchup.active_model to access the model.
- Use model.entities to access the entity collection.
- Create geometry using entities.add_face, entities.add_line, or entities.add_group.
- Express measurements with Ruby Length literals: 1.m, 0.5.m, 1.feet, 10.cm.
- Scripts must be self-contained and run without any external dependencies.
- Do not use puts, UI.messagebox, or any interactive dialogs.

One-shot example:

User: create a cube

```ruby
model = Sketchup.active_model
entities = model.entities

model.start_operation('Create Cube', true)

width  = 1.m
depth  = 1.m
height = 1.m

pts = [
  [0, 0, 0],
  [width, 0, 0],
  [width, depth, 0],
  [0, depth, 0]
]

face = entities.add_face(pts)
face.pushpull(height)

model.commit_operation
```
PROMPT;
    }

    public function buildUserPrompt(string $intention): string
    {
        return <<<PROMPT
Generate a SketchUp Ruby script for the following shape description:

{$intention}

Output only the Ruby script wrapped in ```ruby ... ``` fences. No explanation.
PROMPT;
    }
}
