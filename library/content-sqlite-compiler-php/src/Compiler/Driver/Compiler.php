<?php

declare(strict_types=1);

namespace App\Compiler\Driver;

use App\Compiler\CodeGen\Codegen;
use App\Compiler\Common\Decl;
use App\Compiler\Common\DiagnosticEngine;
use App\Compiler\Common\SourceManager;
use App\Compiler\Parser\Parser;
use App\Compiler\Preproc\Lexer;
use App\Compiler\Preproc\Preprocessor;
use App\Compiler\Sema\Sema;

final class Compiler
{
    public function __construct(
        private readonly SourceManager $sourceManager = new SourceManager(),
        private readonly DiagnosticEngine $diagnostics = new DiagnosticEngine(),
    ) {
    }

    public function diagnostics(): DiagnosticEngine
    {
        return $this->diagnostics;
    }

    public function sourceManager(): SourceManager
    {
        return $this->sourceManager;
    }

    /**
     * @param array<string, string> $defines
     */
    public function preprocessFile(string $path, array $includePaths = [], array $defines = []): array
    {
        $source = file_get_contents($path);
        if ($source === false) {
            throw new \RuntimeException('Unable to read source: '.$path);
        }

        $this->diagnostics->clear();
        $this->sourceManager->reset();
        $fileId = $this->sourceManager->register($path, $source);
        $preprocessor = new Preprocessor($this->sourceManager, $includePaths, $defines);

        return $preprocessor->preprocess($fileId);
    }

    /**
     * @param list<Decl> $decls
     *
     * @return list<Decl>
     */
    public function parseTokens(array $tokens): array
    {
        $parser = new Parser($tokens, $this->diagnostics);

        return $parser->parse();
    }

    /**
     * @param list<Decl> $decls
     *
     * @return list<Decl>
     */
    public function analyze(array $decls): array
    {
        $sema = new Sema($this->diagnostics);

        return $sema->analyze($decls);
    }

    public function compile(CompileOptions $options): CompilationResult
    {
        $this->diagnostics->clear();
        $this->sourceManager->reset();
        $timings = [];
        $metrics = [];

        $source = file_get_contents($options->sourcePath);
        if ($source === false) {
            return new CompilationResult(false, diagnostics: [
                ...$this->diagnostics->diagnostics,
            ]);
        }

        $metrics['sourceBytes'] = strlen($source);
        $peakBefore = memory_get_peak_usage(true);

        $t0 = hrtime(true);
        $fileId = $this->sourceManager->register($options->sourcePath, $source);
        unset($source);
        $preprocessor = new Preprocessor($this->sourceManager, $options->includePaths, $options->defines);
        $tokens = $preprocessor->preprocess($fileId);
        $timings['preprocess'] = (hrtime(true) - $t0) / 1_000_000;
        $metrics['tokenCount'] = count($tokens);

        if ($this->diagnostics->hasErrors()) {
            return new CompilationResult(false, diagnostics: $this->diagnostics->diagnostics, timingsMs: $timings, metrics: $metrics);
        }

        $t0 = hrtime(true);
        $parser = new Parser($tokens, $this->diagnostics);
        $decls = $parser->parse();
        unset($parser, $tokens);
        $timings['parse'] = (hrtime(true) - $t0) / 1_000_000;

        if ($this->diagnostics->hasErrors()) {
            return new CompilationResult(false, diagnostics: $this->diagnostics->diagnostics, timingsMs: $timings, metrics: $metrics);
        }

        $t0 = hrtime(true);
        $sema = new Sema($this->diagnostics);
        $tast = $sema->analyze($decls);
        $enumConstants = $sema->enumConstants;
        unset($sema);
        $timings['sema'] = (hrtime(true) - $t0) / 1_000_000;

        if ($this->diagnostics->hasErrors()) {
            return new CompilationResult(false, diagnostics: $this->diagnostics->diagnostics, timingsMs: $timings, metrics: $metrics);
        }

        $t0 = hrtime(true);
        $codegen = new Codegen($enumConstants);
        $assembly = $codegen->generate($tast);
        unset($codegen);
        $timings['codegen'] = (hrtime(true) - $t0) / 1_000_000;
        $metrics['assemblyBytes'] = strlen($assembly);
        $metrics['peakMemoryBytes'] = memory_get_peak_usage(true) - $peakBefore;

        $retainAssembly = true;
        if ($options->outputAsmPath !== '') {
            if (file_put_contents($options->outputAsmPath, $assembly) === false) {
                throw new \RuntimeException('Unable to write assembly: '.$options->outputAsmPath);
            }
            // Callers that need the text re-read from disk; keep one less 12MB copy alive.
            $retainAssembly = false;
        }

        $timings['total'] = array_sum($timings);

        $result = new CompilationResult(
            success: !$this->diagnostics->hasErrors(),
            assembly: $retainAssembly ? $assembly : null,
            diagnostics: $this->diagnostics->diagnostics,
            timingsMs: $timings,
            metrics: $metrics,
        );
        unset($assembly);

        return $result;
    }
}
