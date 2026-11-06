<?php

namespace App\Support;

use WendellAdriel\SlideWire\DTOs\Slide;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class DarkwoodPresentationCompiler extends PresentationCompiler
{
    public function compile(string $presentation): array
    {
        $compiled = parent::compile($presentation);
        // The approved pilot remains on its native R6 components, byte for byte.
        if ($presentation === 'symfony-ai-in-action') {
            return $compiled;
        }
        // Native scaffolds and Markdown inherit the sole supported identity too.
        $compiled['deck_meta']['theme'] = 'darkwood-original';
        $index = 0;
        foreach ($compiled['slides'] as &$column) {
            foreach ($column as &$slide) {
                // New decks written with the common components already own their
                // composition. Compatibility normalization must never wrap them twice.
                if (str_contains($slide->html, 'class="dw-original-canvas')) {
                    $html = $slide->html;
                    $class = $slide->class;
                } else {
                    [$html, $variant] = app(DarkwoodSlideLayout::class)->render($slide->html, $presentation, $index);
                    $class = $slide->class.' dw-original-slide dw-migrated dw-original-slide--'.$variant;
                }
                $index++;
                $effective = $slide->effective;
                $effective['theme'] = 'darkwood-original';
                $slide = new Slide(
                    id: $slide->id, html: $html, meta: $slide->meta,
                    fragments: $slide->fragments,
                    class: $class,
                    h: $slide->h, v: $slide->v, effective: $effective,
                );
            }
        }

        return $compiled;
    }
}
