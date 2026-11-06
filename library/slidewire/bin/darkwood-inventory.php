<?php

use Illuminate\Contracts\Console\Kernel;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

// Local-only reference inventory. Run in nix develop before transforming decks.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$compiler = app(PresentationCompiler::class);
$root = storage_path('app/darkwood-migration/baseline');
if (file_exists($root.'/rendered.json')) {
    throw new RuntimeException('Baseline already exists; refusing to overwrite it.');
}
@mkdir($root.'/compiled', 0777, true);
$decks = [];
foreach (glob(resource_path('views/pages/slides/*.blade.php')) as $file) {
    $slug = basename($file, '.blade.php');
    $compiled = $compiler->compile($slug);
    $slides = $compiler->flattenSlides($compiled['slides']);
    $rows = [];
    foreach ($slides as $index => $slide) {
        file_put_contents($root.'/compiled/'.$slug.'-'.($index + 1).'.html', $slide->html);
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="inventory">'.$slide->html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $xp = new DOMXPath($dom);
        $texts = [];
        foreach ($xp->query('//text()[not(ancestor::style or ancestor::script)]') as $text) {
            $value = preg_replace('/\s+/u', ' ', trim($text->textContent));
            if ($value !== '') {
                $texts[] = $value;
            }
        }
        $headings = [];
        foreach ($xp->query('//h1|//h2|//p[contains(@class,"kicker")]') as $node) {
            $headings[] = [$node->nodeName, $node->getAttribute('class'), trim($node->textContent)];
        }
        $hooks = [];
        foreach ($xp->query('//*[@id or @data-dw-motion or @data-board or @data-pg-board]') as $node) {
            foreach ($node->attributes as $attr) {
                if ($attr->name === 'id' || str_starts_with($attr->name, 'data-')) {
                    $hooks[] = $attr->name.'='.$attr->value;
                }
            }
        }
        $rows[] = ['id' => $slide->id, 'class' => $slide->class, 'fragments' => $slide->fragments, 'headings' => $headings, 'textNodes' => $texts, 'hooks' => $hooks, 'meta' => $slide->meta];
    }
    $decks[$slug] = ['count' => count($slides), 'meta' => $compiled['deck_meta'], 'slides' => $rows];
    echo $slug.' '.count($slides).PHP_EOL;
}
file_put_contents($root.'/rendered.json', json_encode($decks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo 'TOTAL '.array_sum(array_column($decks, 'count')).PHP_EOL;
