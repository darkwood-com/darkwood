<?php

namespace Tests\Feature;

use App\Support\PresentationCatalog;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class CatalogueTest extends TestCase
{
    public function test_home_lists_the_canonical_registry_once_and_keeps_all_direct_routes(): void
    {
        $registry = app(PresentationCatalog::class)->entries();
        $this->assertCount(28, $registry);
        $dom = new \DOMDocument;
        $dom->loadHTML($this->get('/')->assertOk()->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xp = new \DOMXPath($dom);
        $links = $xp->query('//li[@data-presentation-id]/a');
        $this->assertCount(count($registry), $links);
        $actual = [];
        foreach ($links as $link) {
            $actual[$link->parentNode->getAttribute('data-presentation-id')] = ['url' => $link->getAttribute('href'), 'title' => trim($link->textContent)];
        }
        $this->assertCount(count($registry), $actual);
        foreach ($registry as $row) {
            $url = route('slidewire.'.$row['source_slug']);
            $this->assertSame(url('/slides/'.$row['source_slug']), $url);
            $this->assertSame(['url' => $url, 'title' => $row['title']], $actual[$row['id']]);
            $this->get('/slides/'.$row['source_slug'].'?catalogue=1')->assertOk();
        }
        $this->assertCount(0, $xp->query('//*[contains(@class,"slidewire-frame")]'));
    }

    public function test_portrait_keeps_dimensions_without_private_metadata_and_unknown_authors_keep_a_fallback(): void
    {
        $this->assertSame([640, 640], array_slice(getimagesize(public_path('images/authors/matyo91.jpg')), 0, 2));
        $this->assertStringNotContainsString("Exif\0\0", file_get_contents(public_path('images/authors/matyo91.jpg')));
        $this->assertStringNotContainsString('Photoshop 3.0', file_get_contents(public_path('images/authors/matyo91.jpg')));
        $html = Blade::render('<x-darkwood.conclusion title="Fin" contact="@other-author" />');
        $this->assertStringContainsString('data-author-fallback="logo"', $html);
        $this->assertStringNotContainsString('/images/authors/matyo91.jpg', $html);
        $html = Blade::render('<x-darkwood.conclusion title="Fin" author-id="matyo91" contact="@matyo91" />');
        $this->assertStringContainsString('/images/authors/matyo91.jpg', $html);
        $this->assertStringContainsString('Portrait de matyo91', $html);
    }

    public function test_only_the_two_identified_closing_marks_change_and_footer_logos_remain(): void
    {
        $compiler = app(PresentationCompiler::class);
        foreach (app(PresentationCatalog::class)->entries() as $row) {
            $slides = $compiler->flattenSlides($compiler->compile($row['source_slug'])['slides']);
            $expected = in_array($row['source_slug'], ['symfony-ai-in-action', 'holding-the-core-security-engineering-through-darkwaar40-breach']);
            foreach ($slides as $index => $slide) {
                $dom = new \DOMDocument;
                $dom->loadHTML('<?xml encoding="utf-8" ?>'.$slide->html, LIBXML_NOERROR | LIBXML_NOWARNING);
                $xp = new \DOMXPath($dom);
                $portraits = $xp->query('//img[@data-author-id="matyo91"]');
                $this->assertCount($expected && $index === count($slides) - 1 ? 1 : 0, $portraits, $row['source_slug'].' '.($index + 1));
                $this->assertCount(1, $xp->query('//footer//img[contains(@src,"/darkwood/logos/")]'));
                if ($portraits->length) {
                    $this->assertCount(0, $xp->query('//img[contains(@src,"/darkwood/logos/") and not(ancestor::footer)]'));
                }
            }
        }
    }
}
