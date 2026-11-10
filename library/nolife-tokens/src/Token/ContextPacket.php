<?php

declare(strict_types=1);

namespace App\Token;

/**
 * Mutable pipeline payload. Jobs mutate and return the same instance.
 */
final class ContextPacket
{
    public string $optimized = '';

    public ?ContentType $type = null;

    public ?ContextMetrics $rawMetrics = null;

    public ?ContextMetrics $optimizedMetrics = null;

    /** @var list<array{stage: string, detail?: string, bytes?: int, tokens?: int}> */
    public array $trace = [];

    /** @var list<string> */
    public array $expectedSignals = [];

    /** @var list<string> */
    public array $missingSignals = [];

    public bool $signalPass = false;

    /** @var list<string> */
    public array $retainedHighlights = [];

    /** @var list<ContextReference> */
    public array $references = [];

    public int $referenceMarkerTokens = 0;

    public int $referencedTokens = 0;

    public bool $recoveryPass = true;

    /** @var list<string> */
    public array $missingRecoveries = [];

    /** coarse | fine */
    public string $granularity = 'coarse';

    public function __construct(
        public string $raw,
        public string $sourcePath = '',
    ) {}

    public function addTrace(string $stage, ?string $detail = null, ?int $bytes = null, ?int $tokens = null): void
    {
        $row = ['stage' => $stage];
        if ($detail !== null) {
            $row['detail'] = $detail;
        }
        if ($bytes !== null) {
            $row['bytes'] = $bytes;
        }
        if ($tokens !== null) {
            $row['tokens'] = $tokens;
        }
        $this->trace[] = $row;
    }
}
