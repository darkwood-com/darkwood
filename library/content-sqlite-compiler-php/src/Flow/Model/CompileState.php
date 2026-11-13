<?php

declare(strict_types=1);

namespace App\Flow\Model;

use App\Compiler\Driver\CompilationResult;

final class CompileState
{
    /** @var array<string, float> */
    public array $timings = [];

    public ?string $failure = null;

    public string $sourcePath;
    public string $outputAsmPath;
    public string $outputObjectPath = '';
    public string $outputExecutablePath = '';

    /** @var list<string> */
    public array $includePaths = [];

    /** @var array<string, string> */
    public array $defines = [];

    public ?string $assembly = null;
    public ?CompilationResult $compilation = null;

    public function __construct(string $sourcePath, string $outputAsmPath = '')
    {
        $this->sourcePath = $sourcePath;
        $this->outputAsmPath = $outputAsmPath;
    }
}
