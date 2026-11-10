<?php

declare(strict_types=1);

namespace App\Application\Service;

final class WordClassTransformer
{
    /**
     * @param array<string, float> $vector
     * @return array<string, float>
     */
    public function transform(array $vector): array
    {
        $out = [];
        foreach ($vector as $token => $weight) {
            $tokenString = (string) $token;
            $weightFloat = (float) $weight;
            $class = ctype_digit($tokenString) ? 'num' : (strlen($tokenString) > 8 ? 'long' : 'short');
            $out[$class] = ($out[$class] ?? 0.0) + $weightFloat;
        }

        return $out;
    }
}
