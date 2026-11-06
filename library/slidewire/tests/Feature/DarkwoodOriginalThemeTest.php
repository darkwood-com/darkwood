<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DarkwoodOriginalThemeTest extends TestCase
{
    public function test_pilot_keeps_twenty_slides_and_selects_logos_by_surface(): void
    {
        $response = $this->get('/slides/symfony-ai-in-action');
        $response->assertOk();
        $html = $response->getContent();
        $this->assertSame(20, substr_count($html, 'class="dw-original-footer"'));
        $this->assertSame(5, preg_match_all('~<img src="[^"]*/dw512x512-uni-blue.png"~', $html));
        $this->assertSame(16, preg_match_all('~<img src="[^"]*/dw512x512-uni-dark.png"~', $html));
        $this->assertStringNotContainsString('/dw512x512-light.png', $html);
        $this->assertStringContainsString('dw-original-pagination', $html);
    }

    public function test_black_cover_inherits_the_light_logo_without_slide_specific_logic(): void
    {
        $html = Blade::render('<x-darkwood.cover background="black" title="Darkwood" />');
        $this->assertStringContainsString('dw-original-slide--cover-black', $html);
        $this->assertStringContainsString('/dw512x512-uni-light.png', $html);
        $this->assertStringNotContainsString('/dw512x512-uni-blue.png', $html);
    }

    public function test_html_and_generated_lists_keep_their_nested_structure(): void
    {
        $html = Blade::render(file_get_contents(base_path('tests/fixtures/darkwood-original.blade.php')));
        $this->assertStringContainsString('<ul>', $html);
        $this->assertStringContainsString('Generated level two', $html);
        $this->assertStringContainsString('HTML level three', $html);
    }
}
