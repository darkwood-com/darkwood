<?php

declare(strict_types=1);

namespace App\Study;

use Symfony\AI\Platform\Bridge\Jev\Output\EvaluationResult;
use Symfony\AI\Platform\PlatformInterface;

final class JevInvoke
{
    /**
     * @param array<string, array<string, mixed>> $questions
     */
    public static function call(PlatformInterface $platform, mixed $state, array $questions): EvaluationResult
    {
        $object = $platform->invoke('jev-latest', $state, [
            'questions' => $questions,
        ])->asObject();
        if (!$object instanceof EvaluationResult) {
            throw new \RuntimeException('Jev Platform did not return an EvaluationResult.');
        }

        return $object;
    }
}
