<?php

declare(strict_types=1);

namespace App\Token;

/**
 * A slice of content removed from active LLM context but kept on disk.
 */
final class ContextReference
{
    public function __construct(
        public readonly string $id,
        public readonly string $source,
        public readonly string $type,
        public readonly string $reason,
        public readonly string $content,
    ) {}

    public function marker(): string
    {
        return '#ref:' . $this->id;
    }

    /**
     * @return array{id: string, source: string, type: string, reason: string, content: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'type' => $this->type,
            'reason' => $this->reason,
            'content' => $this->content,
        ];
    }

    /**
     * @param array{id?: mixed, source?: mixed, type?: mixed, reason?: mixed, content?: mixed} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            source: (string) ($data['source'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            reason: (string) ($data['reason'] ?? ''),
            content: (string) ($data['content'] ?? ''),
        );
    }
}
