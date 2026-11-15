<?php

namespace Tests\Feature;

use Tests\TestCase;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class IntentPresentationTest extends TestCase
{
    public function test_intent_deck_has_seventeen_slides_and_editorial_boundaries(): void
    {
        $this->get('/slides/intent')->assertOk();
        $compiler = app(PresentationCompiler::class);
        $slides = $compiler->flattenSlides($compiler->compile('intent')['slides']);
        $this->assertCount(17, $slides);
        $html = implode('', array_map(fn ($slide) => $slide->html, $slides));
        foreach (['15-11-2026', 'Aftertrace', 'Heroclip', 'DJ Matyo91', 'Amp / Revolt', 'Aucun moteur durable', 'pas benchmark', 'https://darkwaar.com/games/aftertrace', 'https://heroclip.darkwood.com/clips/6e42bdb6-7ba2-42cf-91a1-cdb3b40b4fdc', 'https://djmatyo91.darkwood.com/studio'] as $term) {
            $this->assertStringContainsString($term, $html);
        }
        $this->assertStringNotContainsString('OpenSwoole est validé', $html);
        $this->assertStringNotContainsString('génération vidéo réalisée', strtolower($html));
    }
}
