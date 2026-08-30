<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Platform\Toolchain;
use App\Flow\Model\SqliteValidationState;
use Flow\JobInterface;

/**
 * @implements JobInterface<SqliteValidationState, SqliteValidationState>
 */
final class AssembleSqlite implements JobInterface
{
    public function __construct(
        private readonly Toolchain $toolchain,
    ) {
    }

    public function __invoke($state): SqliteValidationState
    {
        $state->sqliteObjectPath = $state->workDir.'/sqlite3.o';
        $r = $this->toolchain->assemble($state->sqliteAsmPath, $state->sqliteObjectPath);
        if ($r->exitCode !== 0) {
            throw new \RuntimeException("SQLite assemble failed:\n".$r->stderr);
        }

        return $state;
    }
}
