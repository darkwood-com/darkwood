<?php

namespace App\Integration\Navi;

final readonly class TraceResult
{
    /**
     * @param list<TraceEvent> $events
     */
    public function __construct(
        public string $traceId,
        public string $runId,
        public array $events = [],
        public string $status = 'running',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'trace_id' => $this->traceId,
            'run_id' => $this->runId,
            'status' => $this->status,
            'events' => array_map(
                static fn (TraceEvent $event): array => [
                    'name' => $event->name,
                    'timestamp' => $event->timestamp,
                    'payload' => $event->payload,
                ],
                $this->events
            ),
        ];
    }
}
