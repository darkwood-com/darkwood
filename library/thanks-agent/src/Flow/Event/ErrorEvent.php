<?php

declare(strict_types=1);

namespace App\Flow\Event;

use Flow\Ip;
use Throwable;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Failure terminal for a job. Resources may still have been consumed.
 *
 * @template T
 */
final class ErrorEvent extends Event
{
    /**
     * @param Ip<T>                $ip
     * @param array<string, mixed> $metrics
     */
    public function __construct(
        private readonly Ip $ip,
        private readonly Throwable $error,
        private readonly array $metrics = [],
    ) {}

    /**
     * @return Ip<T>
     */
    public function getIp(): Ip
    {
        return $this->ip;
    }

    public function getError(): Throwable
    {
        return $this->error;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
