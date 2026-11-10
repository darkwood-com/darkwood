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
final class CompileSqlite implements JobInterface
{
    public function __construct(
        private readonly Compiler $compiler,
        private readonly string $includeDir,
    ) {
    }

    public function __invoke($state): SqliteValidationState
    {
        $state->sqliteAsmPath = $state->workDir.'/sqlite3.s';
        $result = $this->compiler->compile(new CompileOptions(
            sourcePath: $state->amalgamationPath,
            outputAsmPath: $state->sqliteAsmPath,
            includePaths: [$this->includeDir],
        ));
        foreach ($result->timingsMs as $name => $ms) {
            $state->timings[$name] = $ms;
        }
        foreach ($result->metrics as $name => $value) {
            $state->metrics[$name] = $value;
        }
        if (!$result->success) {
            $sm = $this->compiler->sourceManager();
            $msgs = array_map(static fn ($d) => $d->format($sm), $result->diagnostics);
            throw new \RuntimeException("SQLite compile failed:\n".implode("\n", array_slice($msgs, 0, 5)));
        }

        return $state;
    }
}
