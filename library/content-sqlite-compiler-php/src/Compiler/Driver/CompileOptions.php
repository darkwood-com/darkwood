<?php

declare(strict_types=1);

namespace App\Compiler\Driver;

final readonly class CompileOptions
{
    /**
     * @param list<string> $includePaths
     * @param array<string, string> $defines
     */
    public function __construct(
        public string $sourcePath,
        public string $outputAsmPath,
        public array $includePaths = [],
        public array $defines = [],
    ) {
    }
}
