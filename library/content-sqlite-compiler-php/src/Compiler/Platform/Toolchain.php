<?php

declare(strict_types=1);

namespace App\Compiler\Platform;

final class Toolchain
{
    public function __construct(
        private readonly string $workDir,
    ) {
    }

    /**
     * @param list<string> $extraArgs
     */
    public function clangCompileProbe(string $sourcePath, string $outputExecutable, array $extraArgs = []): ProcessResult
    {
        $args = array_merge(['/usr/bin/clang', $sourcePath, '-o', $outputExecutable], $extraArgs);

        return $this->run($args);
    }

    /**
     * @param list<string> $extraArgs
     */
    public function clangEmitAssembly(string $sourcePath, string $outputAsm, array $extraArgs = []): ProcessResult
    {
        $args = array_merge(['/usr/bin/clang', '-S', $sourcePath, '-o', $outputAsm], $extraArgs);

        return $this->run($args);
    }

    public function assemble(string $asmPath, string $objectPath): ProcessResult
    {
        return $this->run(['/usr/bin/as', $asmPath, '-o', $objectPath]);
    }

    /**
     * @param list<string> $objectPaths
     * @param list<string> $extraArgs
     */
    public function link(array $objectPaths, string $outputExecutable, array $extraArgs = []): ProcessResult
    {
        $args = array_merge(['/usr/bin/clang', ...$objectPaths, '-o', $outputExecutable, '-lm', '-lpthread', '-ldl'], $extraArgs);

        return $this->run($args);
    }

    /**
     * @param list<string> $args
     */
    public function runExecutable(string $executable, array $args = []): ProcessResult
    {
        return $this->run(array_merge([$executable], $args));
    }

    public function makeWorkPath(string $basename): string
    {
        if (!is_dir($this->workDir) && !mkdir($this->workDir, 0775, true) && !is_dir($this->workDir)) {
            throw new \RuntimeException('Unable to create compiler work directory.');
        }

        return $this->workDir.'/'.uniqid($basename.'_', true);
    }

    /**
     * @param list<string> $command
     */
    private function run(array $command): ProcessResult
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);
        if (!\is_resource($process)) {
            throw new \RuntimeException('Failed to start process: '.implode(' ', $command));
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return new ProcessResult($exitCode, $stdout, $stderr);
    }
}
