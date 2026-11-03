<?php

namespace App\Terminal;

use Symfony\Component\Console\Output\OutputInterface;

final class JsonOutput
{
    /**
     * @param array<string, mixed> $data
     */
    public static function write(OutputInterface $output, array $data): void
    {
        $output->writeln(json_encode(
            ['schemaVersion' => 1, 'generatedAt' => gmdate('c')] + $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ));
    }
}
