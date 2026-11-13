<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Platform\Toolchain;
use App\Flow\Model\CompileState;
use Flow\JobInterface;

/**
 * @implements JobInterface<CompileState, CompileState>
 */
final class LinkExecutable implements JobInterface
{
    public function __construct(
        private readonly Toolchain $toolchain,
    ) {
    }

    public function __invoke($state): CompileState
    {
        if ($state->outputObjectPath === '' || $state->outputExecutablePath === '') {
            throw new \RuntimeException('Object and executable paths required.');
        }

        $result = $this->toolchain->link([$state->outputObjectPath], $state->outputExecutablePath);
        if ($result->exitCode !== 0) {
            throw new \RuntimeException("Link failed:\n".$result->stderr);
        }

        return $state;
    }
}
