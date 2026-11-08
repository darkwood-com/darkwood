<?php

namespace Tests\Feature;

use Tests\TestCase;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class MemoryPresentationTest extends TestCase
{
    public function test_memory_deck_keeps_editorial_boundaries_and_real_game_assets(): void
    {
        $this->get('/slides/memory')->assertOk();
        $compiler = app(PresentationCompiler::class);
        $slides = $compiler->flattenSlides($compiler->compile('memory')['slides']);
        $this->assertCount(22, $slides);
        $html = implode('', array_map(fn ($slide) => $slide->html, $slides));
        foreach (['MEM-001', '8.1.6', 'atelier-bilan', 'Aftertrace', 'Happy Hardcore', 'Voyageur', 'storyboards'] as $term) {
            $this->assertStringContainsString($term, $html);
        }
        $dom = new \DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        foreach ((new \DOMXPath($dom))->query('//a[@href]') as $link) {
            $this->assertContains(parse_url($link->getAttribute('href'), PHP_URL_HOST), ['memory.content.darkwood.com', 'slides.darkwood.com']);
        }
        foreach (['aftertrace-failure.png', 'aftertrace-route.png', 'aftertrace-delivery.png'] as $image) {
            $this->assertFileExists(public_path('images/memory/'.$image));
            $this->assertStringContainsString('/images/memory/'.$image, $html);
        }
        foreach (['GamePix', 'itch.io', 'youtube.com', 'github.com', 'x.com/'] as $external) {
            $this->assertStringNotContainsString($external, $html);
        }
    }
}
