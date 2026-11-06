<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class DarkwoodMigrationTest extends TestCase
{
    public function test_heading_icons_keep_their_text_and_do_not_bypass_html_escaping(): void
    {
        $title = '😶‍🌫️ <img src=x onerror=alert(1)>';
        $html = Blade::render('<x-darkwood.heading :title="$title" />', compact('title'));
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xp = new DOMXPath($dom);
        $this->assertSame($title, $dom->getElementsByTagName('h1')->item(0)->textContent);
        $this->assertSame(0, $dom->getElementsByTagName('img')->length);
        $icons = $xp->query('//span[@class="dw-original-emoji"]');
        $this->assertCount(1, $icons);
        $this->assertSame('😶‍🌫️', $icons->item(0)->textContent);
    }

    public function test_future_decks_using_shared_components_are_not_composed_twice(): void
    {
        $directory = sys_get_temp_dir().'/darkwood-components-'.uniqid();
        mkdir($directory);
        $file = $directory.'/future.blade.php';
        file_put_contents($file, '<x-darkwood.deck><x-darkwood.slide><x-darkwood.heading title="Future deck" /><x-darkwood.footer /></x-darkwood.slide></x-darkwood.deck>');
        config(['slidewire.presentation_roots' => [$directory]]);
        try {
            $compiler = app(PresentationCompiler::class);
            $slides = $compiler->flattenSlides($compiler->compile('future')['slides']);
            $this->assertCount(1, $slides);
            $this->assertSame(1, substr_count($slides[0]->html, 'class="dw-original-canvas'));
            $this->assertStringNotContainsString('dw-migrated', $slides[0]->class);
            file_put_contents($directory.'/native.blade.php', '<x-slidewire::deck theme="default"><x-slidewire::slide><h1>Native scaffold</h1><p>Content</p></x-slidewire::slide></x-slidewire::deck>');
            $native = $compiler->compile('native');
            $nativeSlides = $compiler->flattenSlides($native['slides']);
            $this->assertSame('darkwood-original', $native['deck_meta']['theme']);
            $this->assertSame('darkwood-original', $nativeSlides[0]->effective['theme']);
            $this->assertStringContainsString('dw-migrated', $nativeSlides[0]->class);
        } finally {
            unlink($file);
            if (file_exists($directory.'/native.blade.php')) {
                unlink($directory.'/native.blade.php');
            }
            rmdir($directory);
        }
    }

    private function document(string $html): array
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="root">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $xp = new DOMXPath($dom);
        foreach (iterator_to_array($xp->query('//span[@class="dw-original-emoji"]')) as $icon) {
            $parent = $icon->parentNode;
            $parent->replaceChild($dom->createTextNode($icon->textContent), $icon);
            $parent->normalize();
        }

        return [$dom, new DOMXPath($dom)];
    }

    private function payload(string $html): array
    {
        [$dom, $xp] = $this->document($html);
        $text = [];
        foreach ($xp->query('//text()[not(ancestor::pre or ancestor::footer or ancestor::style or ancestor::script or ancestor::span[@class="sw-footer" or @class="sr-footer"])]') as $node) {
            $value = preg_replace('/\s+/u', ' ', trim($node->textContent));
            if ($node->parentNode instanceof \DOMElement && $node->parentNode->hasAttribute('data-pg-step')) {
                $value = str_replace(' / ', ' | ', $value);
            }
            if ($value !== '') {
                $text[] = $value;
            }
        }
        sort($text);
        $code = [];
        foreach ($xp->query('//pre') as $node) {
            // Phiki appends a terminal newline; indentation and all other bytes stay exact.
            $code[] = rtrim($node->textContent, "\r\n");
        }
        $links = [];
        foreach ($xp->query('//a[@href]|//iframe[@src]|//img[@src]') as $node) {
            $url = $node->getAttribute($node->nodeName === 'a' ? 'href' : 'src');
            if (! str_contains($url, '/darkwood/logos/dw512x512')) {
                $links[] = $url;
            }
        }
        sort($links);
        $hooks = [];
        foreach ($xp->query('//*') as $node) {
            foreach ($node->attributes as $attr) {
                if ($attr->name === 'id' && $attr->value !== 'root' || str_starts_with($attr->name, 'data-') && $attr->name !== 'data-background' && $attr->name !== 'data-language' || str_starts_with($attr->name, 'x-')) {
                    $hooks[] = $attr->name.'='.$attr->value;
                }
            }
        }
        sort($hooks);

        $footer = [];
        foreach ($xp->query('//footer//text() | //span[@class="sw-footer" or @class="sr-footer"]//text()') as $node) {
            $value = preg_replace('/\s+/u', ' ', trim($node->textContent));
            if ($value !== '' && $value !== '@matyo91') {
                $footer[] = preg_replace('/^(\d+)\s*\/\s*(.+)$/u', '$1 | $2', $value);
            }
        }

        return compact('text', 'code', 'links', 'hooks', 'footer');
    }

    public function test_all_migrated_decks_preserve_the_reference_payload_and_slide_count(): void
    {
        $baseline = json_decode(file_get_contents(base_path('tests/fixtures/darkwood-migration-baseline.json')), true)['decks'];
        $this->assertSame(['darkwood-original'], array_keys(config('slidewire.themes')));
        $this->assertSame('darkwood-original', config('slidewire.slides')->theme);
        $inventory = json_decode(file_get_contents(base_path('tests/fixtures/darkwood-migration-inventory.json')), true);
        $pilot = array_values(array_filter($inventory['decks'], fn ($deck) => $deck['slug'] === 'symfony-ai-in-action'))[0];
        $this->assertSame($pilot['sha256'], hash_file('sha256', resource_path('views/pages/slides/symfony-ai-in-action.blade.php')));
        $present = array_map(fn ($file) => basename($file, '.blade.php'), glob(resource_path('views/pages/slides/*.blade.php')));
        $this->assertSame(array_keys($baseline), $present);
        $compiler = app(PresentationCompiler::class);
        $total = 0;
        $failures = [];
        foreach ($baseline as $slug => $reference) {
            $compiled = $compiler->compile($slug);
            $slides = $compiler->flattenSlides($compiled['slides']);
            $this->assertSame('darkwood-original', $compiled['deck_meta']['theme'], $slug);
            $this->assertCount($reference['count'], $slides, $slug);
            $total += count($slides);
            foreach ($slides as $index => $slide) {
                $this->assertSame($reference['slides'][$index]['id'], $slide->id, $slug.' id');
                $this->assertSame($reference['slides'][$index]['fragments'], $slide->fragments, $slug.' fragments');
                $expected = $reference['slides'][$index]['payload'];
                $actual = $this->payload($slide->html);
                foreach ($expected['footer'] as $part) {
                    $this->assertStringContainsString($part, implode(' ', $actual['footer']), $slug.' footer '.($index + 1));
                }
                foreach (['text', 'code', 'links', 'hooks'] as $key) {
                    if ($expected[$key] !== $actual[$key]) {
                        $failures[] = $slug.' slide '.($index + 1).' '.$key.' missing='.json_encode(array_values(array_diff($expected[$key], $actual[$key])), JSON_UNESCAPED_UNICODE).' added='.json_encode(array_values(array_diff($actual[$key], $expected[$key])), JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }
        $this->assertSame(487, $total);
        $this->assertSame([], $failures);
    }
}
