<?php

declare(strict_types=1);

namespace App\Flow\IO;

/**
 * Thin I/O multiplexing primitive for the thanks-agent demo.
 * Does not reimplement Amp/React — only wait-for-readiness.
 */
interface PollerInterface
{
    /**
     * @param list<resource> $read
     * @param list<resource> $write
     *
     * @return array{0: list<resource>, 1: list<resource>} ready read/write streams
     */
    public function poll(array $read, array $write, float $timeoutSeconds): array;

    public function name(): string;
}
