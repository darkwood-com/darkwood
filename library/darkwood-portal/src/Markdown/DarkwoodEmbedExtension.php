<?php

declare(strict_types=1);

namespace App\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DarkwoodEmbedExtension implements ExtensionInterface, NodeRendererInterface
{
    public const string FRAME_POLICY = "frame-src https://darkwaar.com https://heroclip.darkwood.com https://djmatyo91.darkwood.com; object-src 'none'";

    private const array PRODUCTS = [
        'game' => ['Darkwaar', 'darkwaar.com', 'games', '[a-z0-9]+(?:-[a-z0-9]+)*', 'assets/docs/(?:blog|games)/(?:[A-Za-z0-9_-]+/)+[A-Za-z0-9_-]+\.(?:png|jpe?g|webp)'],
        'clip' => ['HeroClip', 'heroclip.darkwood.com', 'clips', '[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}', 'media/clips/[a-z0-9-]+/thumbnail\.(?:jpe?g|webp|png)'],
        'set' => ['DJ Matyo', 'djmatyo91.darkwood.com', 'sets', '[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}', 'covers/[a-zA-Z0-9_-]+\.(?:svg|png|jpe?g|webp)'],
    ];

    public function __construct(private TranslatorInterface $translator) {}

    public static function usedIn(string $markdown): bool
    {
        return str_contains($markdown, 'darkwood-embed');
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addRenderer(FencedCode::class, $this, 20);
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): ?HtmlElement
    {
        if (!$node instanceof FencedCode || $node->getInfo() !== 'darkwood-embed') {
            return null;
        }

        $data = json_decode($node->getLiteral(), true, 4);
        if (!is_array($data) || !is_string($data['type'] ?? null) || !isset(self::PRODUCTS[$data['type']])) {
            return null;
        }

        $type = $data['type'];
        [$product, $host, $path, $id, $coverPath] = self::PRODUCTS[$type];
        $origin = 'https://' . $host;
        $url = $data['url'] ?? null;
        $title = $data['title'] ?? null;
        if (!is_string($url) || preg_match('~\A' . preg_quote($origin, '~') . '/' . $path . '/(' . $id . ')\z~', $url, $matches) !== 1
            || !is_string($title) || trim($title) === '' || mb_strlen($title) > 160) {
            return null;
        }

        $coverUrl = $data['cover'] ?? null;
        $cover = is_string($coverUrl) && preg_match('~\A' . preg_quote($origin, '~') . '/' . $coverPath . '\z~', $coverUrl) === 1
            ? new HtmlElement('img', ['src' => $coverUrl, 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async', 'referrerpolicy' => 'no-referrer'], '', true)
            : '';
        $description = is_string($data['description'] ?? null) ? mb_substr($data['description'], 0, 320) : '';
        $label = $this->translator->trans('blog.embed.' . $type);
        $ratio = $type === 'set' ? 'audio' : ($type === 'game' ? 'portrait' : 'landscape');
        if ($type !== 'set' && in_array($data['orientation'] ?? null, ['portrait', 'landscape'], true)) {
            $ratio = $data['orientation'];
        }

        $iframe = new HtmlElement('iframe', [
            'src' => $origin . '/embed/' . $path . '/' . $matches[1],
            'title' => $product . ' — ' . $title,
            'sandbox' => $type === 'clip' ? '' : 'allow-scripts allow-same-origin',
            'allow' => $type === 'set' ? "autoplay 'none'" : "fullscreen; autoplay 'none'",
            'loading' => 'lazy',
            'referrerpolicy' => 'no-referrer',
        ]);

        return new HtmlElement('section', ['class' => 'dw-embed dw-embed--' . $ratio, 'data-darkwood-embed' => '', 'aria-label' => $title], [
            new HtmlElement('div', ['class' => 'dw-embed-intro'], [
                $cover,
                new HtmlElement('div', [], [
                    new HtmlElement('div', ['class' => 'dw-embed-product'], $product),
                    new HtmlElement('div', ['class' => 'dw-embed-title'], Xml::escape($title)),
                    $description === '' ? '' : new HtmlElement('p', [], Xml::escape($description)),
                ]),
            ]),
            new HtmlElement('div', ['class' => 'dw-embed-actions'], [
                new HtmlElement('button', [
                    'type' => 'button', 'hidden' => '', 'data-open-player' => '',
                    'data-close-label' => $this->translator->trans('blog.embed.close'),
                    'aria-expanded' => 'false',
                ], Xml::escape($label)),
                new HtmlElement('a', ['href' => $url], Xml::escape($this->translator->trans('blog.embed.original'))),
            ]),
            new HtmlElement('div', ['class' => 'dw-embed-player', 'hidden' => '']),
            new HtmlElement('template', [], $iframe),
        ]);
    }
}
