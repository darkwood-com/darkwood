<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Compiler\Driver\CompileOptions;
use App\Compiler\Driver\Compiler;
use App\Flow\Model\CompileState;
use Flow\JobInterface;

/**
 * @implements JobInterface<CompileState, CompileState>
 */
final class CompileSource implements JobInterface
{
    public function __construct(
        private readonly Compiler $compiler,
    ) {
    }

    public function __invoke($state): CompileState
    {
        $result = $this->compiler->compile(new CompileOptions(
            sourcePath: $state->sourcePath,
            outputAsmPath: $state->outputAsmPath,
            includePaths: $state->includePaths,
            defines: $state->defines,
        ));

        $state->compilation = $result;
        $state->assembly = $result->assembly;
        $state->timings = array_merge($state->timings, $result->timingsMs);

        if (!$result->success) {
            $sm = $this->compiler->sourceManager();
            $messages = array_map(
                static fn ($d) => $d->format($sm),
                $result->diagnostics,
            );
            throw new \RuntimeException("Compilation failed:\n".implode("\n", $messages));
        }

        return $state;
    }
}
