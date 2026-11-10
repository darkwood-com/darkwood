<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Platform\Toolchain;
use App\Flow\Model\ProbeState;
use Flow\JobInterface;

/**
 * @implements JobInterface<ProbeState, ProbeState>
 */
final class RunProbe implements JobInterface
{
    public function __construct(
        private readonly Toolchain $toolchain,
    ) {
    }

    public function __invoke($state): ProbeState
    {
        if (!is_file($state->sourcePath)) {
            throw new \RuntimeException('Fixture not found: '.$state->sourcePath);
        }

        $executable = $this->toolchain->makeWorkPath('probe');
        $compile = $this->toolchain->clangCompileProbe($state->sourcePath, $executable);
        $state->compileResult = $compile;

        if ($compile->exitCode !== 0) {
            throw new \RuntimeException("Compile failed:\n".$compile->stderr);
        }

        $run = $this->toolchain->runExecutable($executable);
        $state->runResult = $run;

        if ($run->exitCode !== $state->expectedExit) {
            throw new \RuntimeException(sprintf(
                'Exit code mismatch: got %d, expected %d',
                $run->exitCode,
                $state->expectedExit,
            ));
        }

        if ($run->stdout !== $state->expectedStdout) {
            throw new \RuntimeException(sprintf(
                "Stdout mismatch: got '%s', expected '%s'",
                $run->stdout,
                $state->expectedStdout,
            ));
        }

        return $state;
    }
}
