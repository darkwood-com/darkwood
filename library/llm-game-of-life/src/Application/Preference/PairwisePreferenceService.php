<?php

declare(strict_types=1);

namespace App\Application\Preference;

use App\Domain\Tokenization\ProgramTokenizer;

final class PairwisePreferenceService
{
    public function __construct(private readonly ProgramTokenizer $tokenizer)
    {
    }

    /**
     * @param array<string,mixed> $preferredAst
     * @param array<string,mixed> $rejectedAst
     * @param array<string,mixed> $context
     *
     * @return array<string,mixed>
     */
    public function record(
        array $preferredAst,
        array $rejectedAst,
        float $preferredScore,
        float $rejectedScore,
        array $context,
        ?string $id = null,
    ): array {
        $preferredTokens = $this->tokenizer->tokenizeProgram($preferredAst);
        $rejectedTokens = $this->tokenizer->tokenizeProgram($rejectedAst);

        return [
            'schema_version' => 1,
            'id' => $id ?? 'preference_' . bin2hex(random_bytes(6)),
            'created_at' => gmdate('c'),
            'context' => $context,
            'preferred_ast' => $preferredAst,
            'rejected_ast' => $rejectedAst,
            'score_preferred' => $preferredScore,
            'score_rejected' => $rejectedScore,
            'margin' => $preferredScore - $rejectedScore,
            'objective' => 'maximize(score_preferred - score_rejected)',
            'token_digest' => [
                'preferred' => $this->digest($preferredTokens),
                'rejected' => $this->digest($rejectedTokens),
            ],
        ];
    }

    /**
     * @param list<string> $tokens
     */
    private function digest(array $tokens): string
    {
        return hash('sha256', json_encode($tokens, JSON_THROW_ON_ERROR));
    }
}
