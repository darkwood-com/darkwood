<?php

declare(strict_types=1);

namespace App\Flow\Model;

final class SqliteValidationState
{
    /** @var array<string, float> */
    public array $timings = [];

    /** @var array<string, int|string> */
    public array $metrics = [];

    public ?string $failure = null;
    public ?string $stdout = null;
    public ?int $exitCode = null;

    public string $sqliteAsmPath = '';
    public string $sqliteObjectPath = '';
    public string $harnessAsmPath = '';
    public string $harnessObjectPath = '';
    public string $executablePath = '';

    public function __construct(
        public readonly string $amalgamationPath,
        public readonly string $harnessPath,
        public readonly string $workDir,
    ) {
    }
}
