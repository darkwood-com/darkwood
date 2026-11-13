<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Platform\Toolchain;
use App\Flow\Model\SqliteValidationState;
use Flow\JobInterface;

/**
 * @implements JobInterface<SqliteValidationState, SqliteValidationState>
 */
final class LinkSmokeExecutable implements JobInterface
{
    public function __construct(
        private readonly Toolchain $toolchain,
    ) {
    }

    public function __invoke($state): SqliteValidationState
    {
        $state->executablePath = $state->workDir.'/sqlite_smoke';
        $r = $this->toolchain->link(
            [$state->sqliteObjectPath, $state->harnessObjectPath],
            $state->executablePath,
        );
        if ($r->exitCode !== 0) {
            throw new \RuntimeException("Link failed:\n".$r->stderr);
        }

        return $state;
    }
}
