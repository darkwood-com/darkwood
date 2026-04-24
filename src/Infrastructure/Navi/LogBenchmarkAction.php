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
        return [
            'at' => gmdate('c'),
            'run_id' => $context->get('run_id'),
            'channel' => 'cli',
            'event' => 'benchmark',
            'payload' => $context->get('payload', []),
        ];
    }
}
