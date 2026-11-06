<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DarkwoodOriginalThemeTest extends TestCase
{
    public function test_each_pilot_slide_has_one_semantic_title_and_only_real_optional_context_and_subtitle(): void
    {
        $document = new \DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$this->get('/slides/symfony-ai-in-action')->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $frames = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " slidewire-frame ")]');
        $roles = json_decode(file_get_contents(base_path('tests/fixtures/darkwood-pilot-headings.json')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(count($roles), $frames);
        foreach ($frames as $i => $frame) {
            $titles = $xpath->query('.//h1', $frame);
            $subtitles = $xpath->query('.//h2', $frame);
            $context = $xpath->query('.//p[contains(concat(" ", @class, " "), " dw-overline ")]', $frame);
            $this->assertCount(1, $titles, 'Slide '.($i + 1));
            $this->assertSame($roles[$i]['title'], trim($titles->item(0)->textContent));
            $this->assertCount($roles[$i]['subtitle'] ? 1 : 0, $subtitles);
            $this->assertCount(isset($roles[$i]['overline']) ? 1 : 0, $context);
            if (isset($roles[$i]['overline'])) {
                $this->assertSame($roles[$i]['overline'], trim($context->item(0)->textContent));
            }
            foreach ($subtitles as $subtitle) {
                $this->assertNotSame('', trim($subtitle->textContent));
                $this->assertCount(1, $xpath->query('preceding-sibling::h1', $subtitle));
            }
        }
    }

    public function test_optional_heading_elements_are_omitted_instead_of_rendering_empty_tags(): void
    {
        $html = Blade::render('<x-darkwood.heading title="Titre" overline=" " subtitle=" " />');
        $this->assertStringContainsString('<h1', $html);
        $this->assertStringNotContainsString('<h2', $html);
        $this->assertStringNotContainsString('<p', $html);
    }

    public function test_footer_formats_only_numbered_editorial_labels(): void
    {
        $html = Blade::render('<x-darkwood.footer label="12 / memory" />');
        $this->assertStringContainsString('12 | memory', $html);
        $this->assertStringNotContainsString('dw-original-pagination', $html);
        $this->assertStringNotContainsString('<output', $html);
        $this->assertStringContainsString('https://darkwood.com/a/b', Blade::render('<x-darkwood.footer label="https://darkwood.com/a/b" />'));
        $this->assertStringContainsString('Flow / Navi / Uniflow', Blade::render('<x-darkwood.footer label="Flow / Navi / Uniflow" />'));
    }

    public function test_original_slidewire_highlighter_is_restored_without_theme_specific_overrides(): void
    {
        $highlighter = app(\WendellAdriel\SlideWire\Support\CodeHighlighter::class);
        $code = "final class Example { public const NAME = 'demo'; }";
        $darkwood = $highlighter->highlight($code, 'php', presentationTheme: 'darkwood-original')->toHtml();
        preg_match_all('/#[0-9a-f]{6}/i', $darkwood, $matches);
        $this->assertNotEmpty($matches[0]);
        $this->assertContains('#1E1E2E', array_map('strtoupper', $matches[0]));
        $this->assertStringContainsString('catppuccin-mocha', $highlighter->replaceCodeBlocks("```js\nconst answer = 42;\n```", presentationTheme: 'darkwood-original'));
        $original = new \WendellAdriel\SlideWire\Support\CodeHighlighter(app(\WendellAdriel\SlideWire\Support\ThemeResolver::class));
        $this->assertSame($original->highlight($code, 'php', presentationTheme: 'black')->toHtml(), $highlighter->highlight($code, 'php', presentationTheme: 'black')->toHtml());
        $this->assertSame($original->highlight($code, 'php', presentationTheme: 'darkwood-original')->toHtml(), $darkwood);
    }

    public function test_pilot_keeps_twenty_slides_and_selects_logos_by_surface(): void
    {
        $response = $this->get('/slides/symfony-ai-in-action');
        $response->assertOk();
        $html = $response->getContent();
        $this->assertSame(20, substr_count($html, 'class="dw-original-footer"'));
        $this->assertSame(5, preg_match_all('~<img src="[^"]*/dw512x512-uni-blue.png"~', $html));
        $this->assertSame(16, preg_match_all('~<img src="[^"]*/dw512x512-uni-dark.png"~', $html));
        $this->assertStringNotContainsString('/dw512x512-light.png', $html);
        $this->assertStringNotContainsString('dw-original-pagination', $html);
        $this->assertSame(3, preg_match_all('~<pre class="phiki language-php catppuccin-mocha~', $html));
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
