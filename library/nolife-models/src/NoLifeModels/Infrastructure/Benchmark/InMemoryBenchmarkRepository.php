<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Benchmark;

use App\NoLifeModels\Domain\Benchmark\BenchmarkCase;
use App\NoLifeModels\Domain\Benchmark\BenchmarkRepositoryInterface;

final class InMemoryBenchmarkRepository implements BenchmarkRepositoryInterface
{
    /**
     * @var list<BenchmarkCase>
     */
    private array $cases;

    public function __construct()
    {
        $this->cases = [
            new BenchmarkCase(
                'greeting-en',
                'Greeting (EN)',
                'Reply with exactly one short sentence introducing yourself as a concise assistant.',
                'You answer in English only.',
                'en',
            ),
            new BenchmarkCase(
                'greeting-fr',
                'Salutation (FR)',
                'Répondez par une seule phrase courte en vous présentant comme assistant concis.',
                'Vous répondez uniquement en français.',
                'fr',
            ),
        ];
    }

    public function findAll(): array
    {
        return $this->cases;
    }

    public function find(string $id): ?BenchmarkCase
    {
        foreach ($this->cases as $case) {
            if ($case->id === $id) {
                return $case;
            }
        }

        return null;
    }
}
