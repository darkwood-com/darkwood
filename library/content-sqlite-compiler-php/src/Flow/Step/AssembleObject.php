<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Platform\Toolchain;
use App\Flow\Model\CompileState;
use Flow\JobInterface;

/**
 * @implements JobInterface<CompileState, CompileState>
 */
final class AssembleObject implements JobInterface
{
    public function __construct(
        private readonly Toolchain $toolchain,
    ) {
    }

    public function __invoke($state): CompileState
    {
        if ($state->outputAsmPath === '' || $state->outputObjectPath === '') {
            throw new \RuntimeException('Assembly and object paths required.');
        }

        $result = $this->toolchain->assemble($state->outputAsmPath, $state->outputObjectPath);
        if ($result->exitCode !== 0) {
            throw new \RuntimeException("Assemble failed:\n".$result->stderr);
        }

        return $state;
    }
}
