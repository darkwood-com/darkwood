<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;

final class CompressChunkStep implements PipelineStepInterface
{
    public static function name(): string
    {
        return 'compress';
    }

    public function withArgs(array $args): self
    {
        return $this;
    }

    public function apply(PipelineContext $context): PipelineContext
    {
        if ($context->chunks !== []) {
            $context->chunks = array_map(
                fn (string $chunk): string => $this->applyToChunk($chunk),
                $context->chunks,
            );
        } else {
            $context->stream = $this->compressText($context->stream);
        }

        $context->recordStep($this->displayName());
        $context->addDebug('compress applied deterministic local algorithm');

        return $context;
    }

    public function displayName(): string
    {
        return 'compress';
    }

    public function applyToChunk(string $chunk): string
    {
        return $this->compressText($chunk);
    }

    public function compressText(string $text): string
    {
        $lines = preg_split('/\R/u', $text) ?: [];
        $seen = [];
        $compressed = [];

        foreach ($lines as $line) {
            $normalized = trim($line);
            if ($normalized === '') {
                continue;
            }

            $key = (string) preg_replace('/^\[\d{4}-\d{2}-\d{2}[^\]]*\]\s*/', '', $normalized);
            $key = (string) preg_replace('/\s+/', ' ', $key);

            if (isset($seen[$key])) {
                ++$seen[$key];
                continue;
            }

            $seen[$key] = 1;
            $compressed[] = $normalized;
        }

        $result = implode("\n", $compressed);

        foreach ($seen as $line => $count) {
            if ($count > 1) {
                $short = mb_strlen($line) > 60 ? mb_substr($line, 0, 57).'…' : $line;
                $result = str_replace($line, sprintf('%s [×%d collapsed]', $short, $count), $result);
            }
        }

        return trim($result);
    }
}
