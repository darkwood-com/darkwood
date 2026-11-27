<?php

declare(strict_types=1);

namespace App\Service;

use function in_array;

final class PublicProductCatalog
{
    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return [
            ['id' => 'darkwood', 'name' => 'Darkwood', 'category' => 'ecosystem', 'url' => 'https://darkwood.com/', 'status' => 'public', 'function' => 'Portal and public catalogue for the Darkwood ecosystem.', 'technologies' => ['PHP', 'Symfony']],
            ['id' => 'flow', 'name' => 'Darkwood Flow', 'category' => 'automation', 'url' => 'https://flow.darkwood.com/', 'status' => 'public', 'function' => 'PHP engine for asynchronous orchestration.', 'technologies' => ['PHP', 'Symfony']],
            ['id' => 'uniflow', 'name' => 'Uniflow', 'category' => 'automation', 'url' => 'https://uniflow.io/', 'status' => 'public', 'function' => 'Workflow automation and creation platform.', 'technologies' => ['workflows', 'automation']],
            ['id' => 'geo', 'name' => 'Darkwood Geo', 'category' => 'geographic-data', 'url' => 'https://geo.darkwood.com/', 'status' => 'public', 'function' => 'Published European maps and sourced indicators.', 'technologies' => ['PHP', 'Symfony', 'Eurostat']],
            ['id' => 'darkwaar', 'name' => 'Darkwaar Games', 'category' => 'games', 'url' => 'https://darkwaar.com/', 'status' => 'public', 'function' => 'Catalogue of independent browser games.', 'technologies' => ['PHP', 'Symfony', 'Godot']],
            ['id' => 'dj-matyo', 'name' => 'DJ Matyo', 'category' => 'media', 'url' => 'https://djmatyo91.darkwood.com/', 'status' => 'public', 'function' => 'Published original DJ sets and replayable musical timelines.', 'technologies' => ['PHP', 'Symfony', 'Web Audio']],
            ['id' => 'job', 'name' => 'Darkwood Job', 'category' => 'employment', 'url' => 'https://job.darkwood.com/', 'status' => 'public-product', 'function' => 'Job and freelance opportunity search.', 'technologies' => ['PHP', 'Symfony']],
            ['id' => 'heroclip', 'name' => 'HeroClip', 'category' => 'creative-ai', 'url' => 'https://heroclip.darkwood.com/', 'status' => 'public-product', 'function' => 'Creative studio for storyboards and video clips.', 'technologies' => ['PHP', 'Symfony', 'AI']],
        ];
    }

    public function find(string $reference): ?array
    {
        $needle = strtolower(trim($reference));
        foreach ($this->all() as $product) {
            if (in_array($needle, [strtolower($product['id']), strtolower($product['name'])], true)) {
                return $product;
            }
        }

        return null;
    }
}
