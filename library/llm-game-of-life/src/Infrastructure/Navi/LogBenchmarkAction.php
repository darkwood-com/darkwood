<?php

declare(strict_types=1);

namespace App\Infrastructure\Navi;

use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\Context;

final readonly class LogBenchmarkAction extends AbstractNdjsonLogAction
{
    public function name(): ActionName
    {
        return ActionName::fromString('life_llm.log_benchmark');
    }

    protected function buildRecord(Context $context): array
    {
        $payload = $context->get('payload', []);
        if (!\is_array($payload)) {
            $payload = [];
        }

        $event = ($payload['event'] ?? null) === 'benchmark_failed' ? 'benchmark_failed' : 'benchmark_completed';
        unset($payload['event']);

        return $this->record($context, $event, $payload);
    }
}
