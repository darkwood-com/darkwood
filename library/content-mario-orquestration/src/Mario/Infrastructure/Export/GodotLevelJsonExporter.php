<?php

namespace App\Mario\Infrastructure\Export;

final class GodotLevelJsonExporter
{
    /**
     * @param array<string, mixed> $payload
     */
    public function write(array $payload, string $outputPath): void
    {
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        file_put_contents($outputPath, $encoded."\n");
    }
}
