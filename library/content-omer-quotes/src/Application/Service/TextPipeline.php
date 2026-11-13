<?php

declare(strict_types=1);

namespace App\Application\Service;

final class TextPipeline
{
    /**
     * @var array<string, true>
     */
    private array $stopwords;

    public function __construct()
    {
        $words = ['a', 'an', 'and', 'the', 'of', 'for', 'to', 'in', 'on', 'is', 'it', 'this', 'that', 'de', 'la', 'le', 'les', 'et', 'un', 'une'];
        $this->stopwords = array_fill_keys($words, true);
    }

    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * @return list<string>
     */
    public function tokenize(string $normalized): array
    {
        if ($normalized === '') {
            return [];
        }

        $tokens = explode(' ', $normalized);
        $tokens = array_values(array_filter($tokens, fn (string $t): bool => strlen($t) > 1 && !isset($this->stopwords[$t])));

        return array_map($this->stem(...), $tokens);
    }

    private function stem(string $token): string
    {
        foreach (['ing', 'ed', 'es', 's'] as $suffix) {
            if (strlen($token) > 4 && str_ends_with($token, $suffix)) {
                return substr($token, 0, -strlen($suffix));
            }
        }

        return $token;
    }
}
