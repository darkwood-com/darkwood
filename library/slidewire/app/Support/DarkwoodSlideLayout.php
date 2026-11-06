<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\ComponentAttributeBag;
use WendellAdriel\SlideWire\Support\CodeHighlighter;

/** Compose existing, rendered slide content without re-tokenizing code or fragments. */
class DarkwoodSlideLayout
{
    private function inner(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    private function attributes(?DOMElement $node): ComponentAttributeBag
    {
        $attrs = [];
        if ($node) {
            foreach ($node->attributes as $attr) {
                if ($attr->name !== 'style') {
                    $attrs[$attr->name] = $attr->value;
                }
            }
        }

        return new ComponentAttributeBag($attrs);
    }

    public function render(string $html, string $slug, int $index): array
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="dw-layout-root">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $xp = new DOMXPath($dom);
        $root = $dom->getElementById('dw-layout-root');
        $interactive = preg_match('/darkwaar|strata-relay|holding-the-core/', $slug) === 1;
        $title = $xp->query('.//h1', $root)->item(0) ?? $xp->query('.//h2', $root)->item(0);
        // Historical standard-cover typography is used for long technical titles;
        // Hero is reserved for compact names. Neither font size is scaled to fit.
        $variant = ! $interactive && $index === 0 && $title?->nodeName === 'h1'
            ? (mb_strlen(trim($title->textContent)) <= 32 ? 'cover' : 'section')
            : 'content';
        $overline = null;
        foreach ($xp->query('.//p', $root) as $p) {
            if (str_contains($p->getAttribute('class'), 'kicker')) {
                $overline = $p;
                break;
            }
        }
        if (! $overline && $title?->parentNode instanceof DOMElement && $title->parentNode->nodeName === 'header') {
            $overline = $xp->query('./p', $title->parentNode)->item(0);
        }
        if (! $title) {
            $overline = null;
        }
        $subtitle = null;
        if ($title) {
            $next = $title->nextSibling;
            while ($next && ! ($next instanceof DOMElement)) {
                $next = $next->nextSibling;
            }
            if ($next instanceof DOMElement && $next->nodeName === 'p' && preg_match('/(?:lead|sub|text-(?:lg|xl|2xl))/', $next->getAttribute('class'))) {
                $subtitle = $next;
            }
        }
        $label = '';
        foreach (iterator_to_array($xp->query('.//footer | .//span[@class="sw-footer" or @class="sr-footer"]', $root)) as $footer) {
            $parts = [];
            foreach ($footer->childNodes as $child) {
                $part = trim($child->textContent);
                if ($part !== '' && $part !== '@matyo91') {
                    $parts[] = $part;
                }
            }
            $label .= implode(' · ', $parts);
            $footer->parentNode->removeChild($footer);
        }
        if ($label === '@matyo91') {
            $label = '';
        }
        // Remove superseded paint, retain layout declarations and every event/data hook.
        // Functional legacy rules remain available during phased migration.
        // The scoped common skin overrides their paint, not their behavior.
        foreach ($xp->query('.//*[@style]', $root) as $node) {
            if ($xp->query('ancestor-or-self::pre', $node)->length) {
                continue;
            }
            $style = preg_replace('/(?:^|;)\s*(?:color|background(?:-[\w-]+)?|box-shadow|text-shadow|backdrop-filter|border-color)\s*:[^;]*(?:;|$)/i', ';', $node->getAttribute('style'));
            $node->setAttribute('style', trim($style, '; '));
        }
        foreach (iterator_to_array($xp->query('.//pre[not(contains(@class,"phiki")) and not(contains(@class,"mermaid")) and not(@data-slidewire-diagram)]', $root)) as $pre) {
            $code = $pre->textContent;
            $language = preg_match('/\$[a-zA-Z_]|<\?php|\b(?:function|class|public|private|namespace)\b/', $code) ? 'php' : 'text';
            $highlighted = app(CodeHighlighter::class)->highlight($code, $language, presentationTheme: 'darkwood-original')->toHtml();
            $snippet = new DOMDocument;
            $snippet->loadHTML('<?xml encoding="utf-8" ?>'.$highlighted, LIBXML_NOERROR | LIBXML_NOWARNING);
            $replacement = $dom->importNode($snippet->getElementsByTagName('pre')->item(0), true);
            foreach ($pre->attributes as $attr) {
                if (str_starts_with($attr->name, 'data-') || $attr->name === 'id') {
                    $replacement->setAttribute($attr->name, $attr->value);
                }
            }
            $pre->parentNode->replaceChild($replacement, $pre);
        }
        $motion = $xp->query('.//*[@data-dw-motion]', $root)->item(0);
        $motionName = $motion?->getAttribute('data-dw-motion');
        if ($motion) {
            $motion->removeAttribute('data-dw-motion');
        }
        $headingData = ['variant' => $variant];
        if ($title?->parentNode instanceof DOMElement && preg_match('/\b[\w-]*copy\b/', $title->parentNode->getAttribute('class'))) {
            $headingData['headerAttributes'] = new ComponentAttributeBag(['class' => $title->parentNode->getAttribute('class')]);
        }
        foreach (['title' => $title, 'overline' => $overline, 'subtitle' => $subtitle] as $role => $node) {
            $headingData[$role] = $node ? new HtmlString($this->inner($node)) : null;
            $headingData[$role.'Attributes'] = $this->attributes($node);
        }
        if ($title) {
            $title->parentNode->removeChild($title);
        }
        if ($overline) {
            $overline->parentNode->removeChild($overline);
        }
        if ($subtitle) {
            $subtitle->parentNode->removeChild($subtitle);
        }
        // Secondary content headings are subordinates, never competing slide titles.
        foreach (iterator_to_array($xp->query('.//h1|.//h2', $root)) as $heading) {
            $h3 = $dom->createElement('h3');
            foreach ($heading->attributes as $a) {
                $h3->setAttribute($a->name, $a->value);
            }
            while ($heading->firstChild) {
                $h3->appendChild($heading->firstChild);
            }
            $heading->parentNode->replaceChild($h3, $heading);
        }
        $surface = in_array($variant, ['cover', 'section'], true) ? 'blue' : 'white';
        foreach ($xp->query('.//img[contains(@src,"/darkwood/logos/dw512x512")]', $root) as $logo) {
            $logo->setAttribute('src', '/darkwood/logos/dw512x512-uni-'.($surface === 'blue' ? 'blue' : 'dark').'.png');
            $logo->setAttribute('class', $logo->getAttribute('class').' dw-original-logo');
            $logo->setAttribute('data-background', $surface);
        }
        $heading = $title ? view('components.darkwood.heading', $headingData)->render() : '';
        // Anonymous @aware only sees an explicit component ancestor; supply the logo
        // surface through a real slide component when rendering the common footer.
        $footer = Blade::render('<x-darkwood.slide :variant="$variant"><x-darkwood.footer :label="$label" /></x-darkwood.slide>', compact('variant', 'label'));
        $fd = new DOMDocument;
        $fd->loadHTML('<?xml encoding="utf-8" ?>'.$footer, LIBXML_NOERROR | LIBXML_NOWARNING);
        $footer = $fd->saveHTML($fd->getElementsByTagName('footer')->item(0));
        $untitledClass = $title ? '' : ' dw-original-canvas--untitled';
        $motionAttr = $motionName ? ' data-dw-motion="'.e($motionName).'"' : '';

        return ['<section class="dw-original-canvas'.$untitledClass.'"'.$motionAttr.'>'.$heading.'<div class="dw-original-body"><div class="dw-original-body-inner dw-legacy-content">'.$this->inner($root).'</div></div>'.$footer.'</section>', $variant];
    }
}
