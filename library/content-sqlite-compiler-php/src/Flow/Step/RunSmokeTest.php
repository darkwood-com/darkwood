<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Platform\Toolchain;
use App\Flow\Model\SqliteValidationState;
use Flow\JobInterface;

/**
 * @implements JobInterface<SqliteValidationState, SqliteValidationState>
 */
final class RunSmokeTest implements JobInterface
{
    public function __construct(
        private readonly Toolchain $toolchain,
    ) {
    }

    public function __invoke($state): SqliteValidationState
    {
        $r = $this->toolchain->runExecutable($state->executablePath);
        $state->exitCode = $r->exitCode;
        $state->stdout = trim($r->stdout);
        if ($state->exitCode !== 0) {
            throw new \RuntimeException("Smoke test exit {$r->exitCode}:\n".$r->stderr);
        }
        if ($state->stdout !== 'darkwood') {
            throw new \RuntimeException("Expected 'darkwood', got '{$state->stdout}'");
        }

        return $state;
    }
}
