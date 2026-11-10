<?php

declare(strict_types=1);

namespace App\NoLifeModels\Application\Comparison;

use App\NoLifeModels\Domain\Model\ModelInferenceResult;
use App\NoLifeModels\Domain\Model\PromptComparisonResult;
use App\NoLifeModels\Domain\Runtime\GeneratePromptCommand;
use App\NoLifeModels\Domain\Runtime\LocalModelRuntimeInterface;

final class RunPromptComparisonHandler
{
    public function __construct(
        private readonly LocalModelRuntimeInterface $runtime,
    ) {
    }

    public function handle(RunPromptComparisonInput $input): PromptComparisonResult
    {
        $id = bin2hex(random_bytes(16));
        $comparedAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $results = [];
        foreach ([$input->modelA, $input->modelB] as $modelName) {
            $cmd = new GeneratePromptCommand(
                $modelName,
                $input->prompt,
                $input->system,
                $input->temperature,
                $input->topP,
            );
            $results[] = $this->runtime->generate($cmd);
        }

        if (!$results[0] instanceof ModelInferenceResult || !$results[1] instanceof ModelInferenceResult) {
            throw new \LogicException('Expected two inference results.');
        }

        return new PromptComparisonResult(
            $id,
            $input->prompt,
            $input->system,
            $comparedAt,
            $results,
        );
    }
}
