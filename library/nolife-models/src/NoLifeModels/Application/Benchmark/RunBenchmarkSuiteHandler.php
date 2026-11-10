<?php

declare(strict_types=1);

namespace App\NoLifeModels\Application\Benchmark;

use App\NoLifeModels\Domain\Benchmark\BenchmarkCellResult;
use App\NoLifeModels\Domain\Benchmark\BenchmarkRepositoryInterface;
use App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport;
use App\NoLifeModels\Domain\Runtime\GeneratePromptCommand;
use App\NoLifeModels\Domain\Runtime\LocalModelRuntimeInterface;

final class RunBenchmarkSuiteHandler
{
    public function __construct(
        private readonly BenchmarkRepositoryInterface $benchmarks,
        private readonly LocalModelRuntimeInterface $runtime,
    ) {
    }

    /**
     * @param list<string> $modelNames
     */
    public function handle(array $modelNames): BenchmarkRunReport
    {
        if ($modelNames === []) {
            throw new \InvalidArgumentException('At least one model is required.');
        }

        $id = bin2hex(random_bytes(16));
        $started = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $cells = [];

        foreach ($this->benchmarks->findAll() as $case) {
            foreach ($modelNames as $modelName) {
                $result = $this->runtime->generate(new GeneratePromptCommand(
                    $modelName,
                    $case->prompt,
                    $case->system,
                    0.3,
                    0.9,
                ));
                $cells[] = new BenchmarkCellResult(
                    $case->id,
                    $modelName,
                    $result->responseText,
                    $result->error,
                    $result->metrics,
                );
            }
        }

        $finished = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return new BenchmarkRunReport($id, $started, $finished, $cells);
    }
}
