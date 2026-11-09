<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;

final class ChunkTextStep implements PipelineStepInterface
{
    public function __construct(
        private readonly int $size = 300,
    ) {
    }

    public static function name(): string
    {
        return 'chunk';
    }

    public function withArgs(array $args): self
    {
        return new self(isset($args['size']) ? (int) $args['size'] : $this->size);
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        $context->chunks = $this->splitIntoChunks($context->stream, $this->size);
        $context->recordStep($this->displayName());
        $context->addDebug(sprintf('chunk split into %d chunks size=%d', count($context->chunks), $this->size));

        return $context;
    }

    public function displayName(): string
    {
        return sprintf('chunk:%d', $this->size);
    }

    /**
     * @return list<string>
     */
    public function splitIntoChunks(string $text, ?int $size = null): array
    {
        $size ??= $this->size;
        if ($text === '') {
            return [];
        }

        $chunks = [];
        $offset = 0;
        $length = mb_strlen($text);

        while ($offset < $length) {
            $remaining = $length - $offset;
            if ($remaining <= $size) {
                $chunks[] = mb_substr($text, $offset);
                break;
            }

            $piece = mb_substr($text, $offset, $size);
            $breakAt = mb_strrpos($piece, ' ');
            if ($breakAt !== false && $breakAt > (int) ($size * 0.5)) {
                $piece = mb_substr($piece, 0, $breakAt);
            }

            $chunks[] = $piece;
            $offset += mb_strlen($piece);
            if ($offset < $length && mb_substr($text, $offset, 1) === ' ') {
                ++$offset;
            }
        }

        return $chunks;
    }
}
