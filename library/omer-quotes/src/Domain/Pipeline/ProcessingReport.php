<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

final class ProcessingReport
{
    /**
     * @param list<array<string, mixed>> $steps
     * @param list<array<string, mixed>> $errors
     */
    public function __construct(
        public string $runId,
        public string $orchestration,
        public array $steps = [],
        public array $errors = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addStep(string $name, array $data = []): void
    {
        $this->steps[] = ['name' => $name, 'data' => $data, 'at' => (new \DateTimeImmutable())->format(DATE_ATOM)];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addError(string $name, string $message, array $data = []): void
    {
        $this->errors[] = ['name' => $name, 'message' => $message, 'data' => $data];
    }
}
