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
                family: 'claude-opus',
                costPerMillionInput: 5.0,
                costPerMillionOutput: 25.0,
                costPerMillionReasoning: null,
                limitMaxInput: null,
                limitMaxOutput: 64_000,
                reasoningEnabled: true,
                toolCallEnabled: true,
                structuredOutput: true,
                openWeights: false,
                knowledge: '2025-03-31',
                releaseDate: '2025-11-25',
            ),
            new ModelCatalogEntry(
                new ModelId('openai/gpt-5.2'),
                new ProviderId('openai'),
                'GPT-5.2',
                256_000,
                ['text'],
                ['text'],
                ['tool_call'],
                family: 'gpt',
                reasoningEnabled: false,
                toolCallEnabled: true,
                structuredOutput: true,
                openWeights: false,
                limitMaxOutput: 128_000,
            ),
            new ModelCatalogEntry(
                new ModelId('mistralai/mistral-small-3.2'),
                new ProviderId('mistralai'),
                'Mistral Small 3.2',
                128_000,
                ['text'],
                ['text'],
                ['tool_call'],
                family: 'mistral-small',
                reasoningEnabled: false,
                toolCallEnabled: true,
                openWeights: true,
                limitMaxOutput: 32_768,
            ),
        ];
    }
}
