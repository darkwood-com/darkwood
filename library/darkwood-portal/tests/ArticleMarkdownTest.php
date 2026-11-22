<?php

declare(strict_types=1);

namespace App\Tests;

use App\Markdown\ArticleMarkdown;
use App\Markdown\DarkwoodEmbedExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;

final class ArticleMarkdownTest extends TestCase
{
    public function testOnlySupportedPublicUrlsCreateInertPlayersInLongMarkdown(): void
    {
        $renderer = new ArticleMarkdown(new DarkwoodEmbedExtension(new Translator('fr')));
        $body = str_repeat("Une phrase de l'article.\n\n", 400) . "## Créations\n";
        foreach ([
            ['game', 'https://darkwaar.com/games/prime-lanterns'],
            ['clip', 'https://heroclip.darkwood.com/clips/01a20241-c293-748f-9dd4-ccfead5f65d8'],
            ['set', 'https://djmatyo91.darkwood.com/sets/01a20241-c177-7ce9-a9a8-af260e305622'],
            ['game', 'https://example.org/games/prime-lanterns'],
            ['clip', 'https://heroclip.darkwood.com/clips/not-an-id'],
            ['set', 'data:text/html,untrusted'],
            ['game', 'https://darkwaar.com/games/admin/extra'],
            ['game', 'https://darkwaar.com@another.example/games/prime-lanterns'],
        ] as [$type, $url]) {
            $body .= self::block($type, $url);
        }
        $body .= "\n**Markdown ordinaire** [Source](https://example.org)\n\n"
            . '<script>alert(1)</script><img src=x onerror=alert(1)><iframe src="https://example.org"></iframe>'
            . "\n\n[unsafe](javascript:alert%281%29)\n\n"
            . "\x60\x60\x60mermaid\ngraph TD\n\x60\x60\x60\n";

        $html = $renderer->render($body);
        self::assertSame(3, substr_count($html, '<template><iframe'));
        self::assertSame(5, substr_count($html, '<code class="language-darkwood-embed">'));
        self::assertStringContainsString('https://darkwaar.com/embed/games/prime-lanterns', $html);
        self::assertStringContainsString('https://heroclip.darkwood.com/embed/clips/', $html);
        self::assertStringContainsString('https://djmatyo91.darkwood.com/embed/sets/', $html);
        self::assertStringContainsString('href="https://darkwaar.com/games/prime-lanterns"', $html);
        self::assertStringContainsString('&lt;em&gt;Titre&lt;/em&gt;', $html);
        self::assertStringContainsString('<strong>Markdown ordinaire</strong>', $html);
        self::assertStringContainsString('<pre class="mermaid">', $html);
        self::assertStringNotContainsString('<script>', $html);
        self::assertStringNotContainsString('onerror=', $html);
        self::assertStringNotContainsString('href="javascript:', $html);
        self::assertStringNotContainsString('src="https://example.org"', $html);
    }

    private static function block(string $type, string $url): string
    {
        return "\n\x60\x60\x60darkwood-embed\n" . json_encode([
            'type' => $type,
            'url' => $url,
            'title' => '<em>Titre</em>',
            'cover' => 'https://example.org/image.png',
        ], JSON_THROW_ON_ERROR) . "\n\x60\x60\x60\n";
    }
}
