<?php

declare(strict_types=1);

namespace App\Flow\Event;

use Flow\Ip;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Success terminal for a job. Mutually exclusive with {@see ErrorEvent}.
 *
 * @template T
 */
final class CompleteEvent extends Event
{
    /**
     * @param Ip<T>                $ip
     * @param array<string, mixed> $metrics
     */
    public function __construct(
        private readonly Ip $ip,
        private readonly mixed $result = null,
        private readonly array $metrics = [],
    ) {}

    /**
     * @return Ip<T>
     */
    public function getIp(): Ip
    {
        return $this->ip;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
