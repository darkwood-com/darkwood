<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Platform\Toolchain;
use App\Flow\Model\SqliteValidationState;
use Flow\JobInterface;

/**
 * @implements JobInterface<SqliteValidationState, SqliteValidationState>
 */
final class AssembleHarness implements JobInterface
{
    public function __construct(
        private readonly Toolchain $toolchain,
    ) {
    }

    public function __invoke($state): SqliteValidationState
    {
        $state->harnessObjectPath = $state->workDir.'/smoke.o';
        $r = $this->toolchain->assemble($state->harnessAsmPath, $state->harnessObjectPath);
        if ($r->exitCode !== 0) {
            throw new \RuntimeException("Harness assemble failed:\n".$r->stderr);
        }

        return $state;
    }
}
