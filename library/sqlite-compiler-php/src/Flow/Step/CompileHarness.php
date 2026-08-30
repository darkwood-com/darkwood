<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Driver\CompileOptions;
use App\Compiler\Driver\Compiler;
use App\Flow\Model\SqliteValidationState;
use Flow\JobInterface;

/**
 * @implements JobInterface<SqliteValidationState, SqliteValidationState>
 */
final class CompileHarness implements JobInterface
{
    public function __construct(
        private readonly Compiler $compiler,
        private readonly string $includeDir,
    ) {
    }

    public function __invoke($state): SqliteValidationState
    {
        $state->harnessAsmPath = $state->workDir.'/smoke.s';
        $result = $this->compiler->compile(new CompileOptions(
            sourcePath: $state->harnessPath,
            outputAsmPath: $state->harnessAsmPath,
            includePaths: [$this->includeDir],
        ));
        if (!$result->success) {
            throw new \RuntimeException('Harness compile failed');
        }

        return $state;
    }
}
