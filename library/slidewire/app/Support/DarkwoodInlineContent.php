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
            return new HtmlString($this->literalText($html));
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
                $span->setAttribute('class', 'sw-emoji');
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

        return new HtmlString($this->literalText($result));
    }

    /** Keep rendered title text literal when a component is compiled again. */
    private function literalText(string $html): string
    {
        $parts = preg_split('/(<(?:[^>\"\']|\"[^\"]*\"|\'[^\']*\')*>)/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $raw = null;
        foreach ($parts as $index => $part) {
            if ($index % 2 === 1) {
                if (preg_match('~^<(script|style)\\b~i', $part, $match)) {
                    $raw = strtolower($match[1]);
                } elseif ($raw !== null && preg_match('~^</'.$raw.'\\s*>~i', $part)) {
                    $raw = null;
                }
            } elseif ($raw === null) {
                $parts[$index] = strtr($part, ['@' => '&#64;', '{' => '&#123;', '}' => '&#125;']);
            }
        }

        return implode('', $parts);
    }
}
