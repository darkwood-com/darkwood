<?php

declare(strict_types=1);

namespace App\Token;

use App\Token\Optimizer\FileListOptimizer;
use App\Token\Optimizer\GitDiffOptimizer;
use App\Token\Optimizer\JsonOptimizer;
use App\Token\Optimizer\LogOptimizer;
use App\Token\Optimizer\TextOptimizer;

final class ContextOptimizer
{
    public function __construct(
        private readonly ContextStore $store = new ContextStore(),
        private readonly TokenEstimator $estimator = new TokenEstimator(),
        private readonly LogOptimizer $logOptimizer = new LogOptimizer(),
        private readonly JsonOptimizer $jsonOptimizer = new JsonOptimizer(),
        private readonly GitDiffOptimizer $gitDiffOptimizer = new GitDiffOptimizer(),
        private readonly FileListOptimizer $fileListOptimizer = new FileListOptimizer(),
        private readonly TextOptimizer $textOptimizer = new TextOptimizer(),
    ) {}

    public function optimize(ContextPacket $packet): void
    {
        assert($packet->type !== null);

        $result = match ($packet->type) {
            ContentType::Log => $this->logOptimizer->optimize($packet->raw, $packet->granularity),
            ContentType::Json => $this->jsonOptimizer->optimize($packet->raw),
            ContentType::GitDiff => $this->gitDiffOptimizer->optimize($packet->raw),
            ContentType::FileList => $this->fileListOptimizer->optimize($packet->raw),
            ContentType::Text => $this->textOptimizer->optimize($packet->raw),
        };

        $visible = $result->visible;
        $references = [];
        $markerText = '';
        $referencedTokens = 0;

        foreach ($result->omissions as $omission) {
            if ($omission['content'] === '') {
                continue;
            }

            $id = $this->store->makeId($packet->sourcePath, $omission['reason'], $omission['content']);
            $ref = new ContextReference(
                id: $id,
                source: $packet->sourcePath,
                type: $packet->type->value,
                reason: $omission['reason'],
                content: $omission['content'],
            );
            $this->store->put($ref);
            $references[] = $ref;

            $marker = $ref->marker();
            $markerText .= $marker . "\n";
            $referencedTokens += $this->estimator->measure($omission['content'])->estimatedTokens;

            $placeholder = $omission['marker_placeholder'] ?? null;
            if (is_string($placeholder) && $placeholder !== '') {
                $visible = str_replace($placeholder, $marker, $visible);
            } else {
                $visible .= ($visible !== '' ? "\n" : '') . $marker;
            }
        }

        $packet->optimized = $visible;
        $packet->references = $references;
        $packet->referenceMarkerTokens = $markerText === ''
            ? 0
            : $this->estimator->measure(trim($markerText))->estimatedTokens;
        $packet->referencedTokens = $referencedTokens;
    }
}
