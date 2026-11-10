<?php

declare(strict_types=1);

namespace App\Application\Service;

final class TfIdfService
{
    /**
     * @param array<string, list<string>> $tokenByDoc
     * @return array{vectors: array<string, array<string, float>>, df: array<string, int>, idf: array<string, float>}
     */
    public function compute(array $tokenByDoc): array
    {
        $df = [];
        $tfByDoc = [];
        $docCount = max(count($tokenByDoc), 1);

        foreach ($tokenByDoc as $docId => $tokens) {
            $tf = [];
            foreach ($tokens as $token) {
                $tf[$token] = ($tf[$token] ?? 0) + 1;
            }
            $tfByDoc[$docId] = $tf;

            foreach (array_keys($tf) as $token) {
                $df[$token] = ($df[$token] ?? 0) + 1;
            }
        }

        $idf = [];
        foreach ($df as $token => $freq) {
            $idf[$token] = log(($docCount + 1) / ($freq + 1)) + 1.0;
        }

        $vectors = [];
        foreach ($tfByDoc as $docId => $tf) {
            $total = max(array_sum($tf), 1);
            $vector = [];
            foreach ($tf as $token => $count) {
                $vector[$token] = ($count / $total) * ($idf[$token] ?? 0.0);
            }
            $vectors[$docId] = $vector;
        }

        return ['vectors' => $vectors, 'df' => $df, 'idf' => $idf];
    }
}
