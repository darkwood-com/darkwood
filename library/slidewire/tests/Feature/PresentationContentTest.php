<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class PresentationContentTest extends TestCase
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
        $icons = $xp->query('//span[@class="sw-emoji"]');
        $this->assertCount(1, $icons);
        $this->assertSame('😶‍🌫️', $icons->item(0)->textContent);
    }

    private function document(string $html): array
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="root">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $xp = new DOMXPath($dom);
        foreach (iterator_to_array($xp->query('//span[@class="sw-emoji"]')) as $icon) {
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
                if ($attr->name === 'id' && $attr->value !== 'root' || str_starts_with($attr->name, 'data-') && ! in_array($attr->name, ['data-background', 'data-language', 'data-code-surface']) || str_starts_with($attr->name, 'x-')) {
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

    public function test_all_presentations_preserve_their_canonical_payload_and_slide_count(): void
    {
        $baseline = json_decode(file_get_contents(base_path('tests/fixtures/presentation-content.json')), true)['decks'];
        $this->assertSame(['darkwood'], array_keys(config('slidewire.themes')));
        $this->assertSame('darkwood', config('slidewire.slides')->theme);
        $present = array_map(fn ($file) => basename($file, '.blade.php'), glob(resource_path('views/pages/slides/*.blade.php')));
        $this->assertSame(array_keys($baseline), $present);
        $compiler = app(PresentationCompiler::class);
        $total = 0;
        $failures = [];
        foreach ($baseline as $slug => $reference) {
            $compiled = $compiler->compile($slug);
            $slides = $compiler->flattenSlides($compiled['slides']);
            $this->assertSame('darkwood', $compiled['deck_meta']['theme'], $slug);
            $this->assertCount($reference['count'], $slides, $slug);
            $total += count($slides);
            foreach ($slides as $index => $slide) {
                $this->assertSame($reference['slides'][$index]['id'], $slide->id, $slug.' id');
                $this->assertSame($reference['slides'][$index]['fragments'], $slide->fragments, $slug.' fragments');
                $expected = $reference['slides'][$index]['payload'];
                $actual = $this->payload($slide->html);
                // The two approved closing marks are now an author portrait.
                if ($index === count($slides) - 1 && in_array($slug, ['symfony-ai-in-action', 'holding-the-core-security-engineering-through-darkwaar40-breach'])) {
                    $portrait = asset('images/authors/matyo91.jpg');
                    $this->assertSame(1, count(array_keys($actual['links'], $portrait, true)));
                    $actual['links'] = array_values(array_diff($actual['links'], [$portrait]));
                    $actual['hooks'] = array_values(array_diff($actual['hooks'], ['data-author-id=matyo91']));
                }
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
