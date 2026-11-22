<?php

declare(strict_types=1);

namespace App\Markdown;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class ArticleMarkdown implements RuntimeExtensionInterface
{
    public function __construct(private DarkwoodEmbedExtension $embeds) {}

    public function render(string $source): string
    {
        $converter = new CommonMarkConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $converter->getEnvironment()->addExtension(new TableExtension());
        $converter->getEnvironment()->addExtension(new MermaidExtension());
        $converter->getEnvironment()->addExtension($this->embeds);

        return $converter->convert($source)->getContent();
    }
}
