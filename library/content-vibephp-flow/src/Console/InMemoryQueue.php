<?php

declare(strict_types=1);

namespace App\Console;

use App\Model\VibeRequest;

final class InMemoryQueue
{
    /** @var list<VibeRequest> */
    private array $messages = [];

    private int $position = 0;

    public function __construct(string $queuePath)
    {
        if (!is_file($queuePath)) {
            return;
        }

        $handle = fopen($queuePath, 'r');
        if (false === $handle) {
            return;
        }

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ('' === $line) {
                continue;
            }

            /** @var array{method: string, path: string} $data */
            $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            $this->messages[] = new VibeRequest($data['method'], $data['path']);
        }

        fclose($handle);
    }

    public function next(): ?VibeRequest
    {
        if ([] === $this->messages) {
            return null;
        }

        $message = $this->messages[$this->position];
        $this->position = ($this->position + 1) % \count($this->messages);

        return $message;
    }
}
