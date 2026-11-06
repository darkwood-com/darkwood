<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/** Preserve semantic heading text while rendering color-font icons in monochrome. */
class DarkwoodInlineContent
{
    public function render(mixed $value): HtmlString
    {
        $html = $value instanceof Htmlable ? $value->toHtml() : e((string) $value);
        $emoji = '/[\x{2600}-\x{27BF}\x{1F000}-\x{1FAFF}]/u';
        if (! preg_match($emoji, $html)) {
            return new HtmlString($html);
        }

        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="dw-inline">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $dom->getElementById('dw-inline');
        $xp = new DOMXPath($dom);
        foreach (iterator_to_array($xp->query('.//text()[not(ancestor::code or ancestor::pre or ancestor::svg or ancestor::script or ancestor::style)]', $root)) as $node) {
            if (! preg_match($emoji, $node->textContent)) {
                continue;
            }
            preg_match_all('/\X/u', $node->textContent, $matches);
            $fragment = $dom->createDocumentFragment();
            $text = '';
            foreach ($matches[0] as $cluster) {
                if (! preg_match($emoji, $cluster)) {
                    $text .= $cluster;

                    continue;
                }
                if ($text !== '') {
                    $fragment->appendChild($dom->createTextNode($text));
                    $text = '';
                }
                $span = $dom->createElement('span');
                $span->setAttribute('class', 'dw-original-emoji');
                $span->appendChild($dom->createTextNode($cluster));
                $fragment->appendChild($span);
            }
            if ($text !== '') {
                $fragment->appendChild($dom->createTextNode($text));
            }
            $node->parentNode->replaceChild($fragment, $node);
        }
        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return new HtmlString($result);
    }
}
