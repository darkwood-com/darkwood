<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Catalog;

use App\NoLifeModels\Domain\Catalog\ModelCatalogRepositoryInterface;
use App\NoLifeModels\Domain\Model\ModelCatalogEntry;
use App\NoLifeModels\Domain\Model\ModelId;
use App\NoLifeModels\Domain\Model\ProviderId;

/**
 * Built-in sample rows for demos when JSON file is empty or missing entries.
 */
final class InMemoryModelCatalogRepository implements ModelCatalogRepositoryInterface
{
    public function findAll(): array
    {
        return [
            new ModelCatalogEntry(
                new ModelId('anthropic/claude-opus-4-6'),
                new ProviderId('anthropic'),
                'Claude Opus 4.6',
                200_000,
                ['text', 'image'],
                ['text'],
                ['reasoning', 'tool_call'],
            ),
            new ModelCatalogEntry(
                new ModelId('openai/gpt-5.2'),
                new ProviderId('openai'),
                'GPT-5.2',
                256_000,
                ['text'],
                ['text'],
                ['tool_call'],
            ),
            new ModelCatalogEntry(
                new ModelId('mistralai/mistral-small-3.2'),
                new ProviderId('mistralai'),
                'Mistral Small 3.2',
                128_000,
                ['text'],
                ['text'],
                ['tool_call'],
            ),
        ];
    }
}
