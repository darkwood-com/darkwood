<?php

declare(strict_types=1);

namespace App\Token;

/**
 * Deterministic content-type detection. Input is treated as data only.
 */
final class ContentClassifier
{
    public function classify(string $content): ContentType
    {
        $trimmed = ltrim($content);
        if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return ContentType::Json;
            }
        }

        if (str_contains($content, 'diff --git') || preg_match('/^@@ -\d+/m', $content) === 1) {
            return ContentType::GitDiff;
        }

        $lines = preg_split('/\R/', $content) ?: [];
        $logHits = 0;
        $pathHits = 0;
        $nonEmpty = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            ++$nonEmpty;

            if ($this->looksLikeLogLine($line)) {
                ++$logHits;
            }
            if ($this->looksLikePathLine($line)) {
                ++$pathHits;
            }
        }

        if ($logHits >= 3) {
            return ContentType::Log;
        }

        if ($nonEmpty >= 5 && $pathHits / $nonEmpty >= 0.8) {
            return ContentType::FileList;
        }

        return ContentType::Text;
    }

    private function looksLikeLogLine(string $line): bool
    {
        if (preg_match('/^\[?(DEBUG|INFO|NOTICE|WARNING|WARN|ERROR|CRITICAL|ALERT|EMERGENCY)\]?\b/i', $line) === 1) {
            return true;
        }

        // ISO-ish timestamp prefix, optionally followed by a level.
        return preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/', $line) === 1;
    }

    private function looksLikePathLine(string $line): bool
    {
        if (str_contains($line, ' ')) {
            return false;
        }

        return (bool) preg_match('#^[\w./@+-]+\.\w{1,10}$|^[\w./@+-]+/$#', $line);
    }
}
