<?php

declare(strict_types=1);

namespace App\Infrastructure\Navi;

use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\Context;

final readonly class LifeRunLogger
{
    public function __construct(
        private WorkflowRunner $runner,
        private LogRunEventAction $action,
    ) {
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function log(string $runId, string $event, array $payload = []): void
    {
        $this->runner->run(
            Context::fromArray([
                'run_id' => $runId,
                'channel' => 'cli',
                'event' => $event,
                'payload' => $payload,
            ]),
            [$this->action],
        );
    }
}
