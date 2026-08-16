<?php

declare(strict_types=1);

namespace App\Token\Optimizer;

use App\Token\OptimizeResult;

/**
 * Collapse path listings into a compact directory tree with counts.
 */
final class FileListOptimizer
{
    public function optimize(string $content): OptimizeResult
    {
        $lines = preg_split('/\R/', $content) ?: [];
        $paths = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $paths[] = $line;
            }
        }

        if ($paths === []) {
            return new OptimizeResult('');
        }

        $tree = [];
        foreach ($paths as $path) {
            $parts = explode('/', $path);
            $node = &$tree;
            foreach ($parts as $i => $part) {
                if ($part === '') {
                    continue;
                }
                $isLeaf = $i === count($parts) - 1;
                if ($isLeaf) {
                    $node['__files'][] = $part;
                } else {
                    $node['__dirs'][$part] ??= [];
                    $node = &$node['__dirs'][$part];
                }
            }
            unset($node);
        }

        return new OptimizeResult(implode("\n", $this->render($tree, '')));
    }

    /**
     * @param array{__files?: list<string>, __dirs?: array<string, array>} $node
     *
     * @return list<string>
     */
    private function render(array $node, string $indent): array
    {
        $lines = [];
        foreach ($node['__dirs'] ?? [] as $name => $child) {
            $fileCount = $this->countFiles($child);
            $lines[] = sprintf('%s%s/ (%d)', $indent, $name, $fileCount);
            foreach ($this->render($child, $indent . '  ') as $childLine) {
                $lines[] = $childLine;
            }
        }

        $files = $node['__files'] ?? [];
        if (count($files) <= 8) {
            foreach ($files as $file) {
                $lines[] = $indent . $file;
            }
        } else {
            foreach (array_slice($files, 0, 5) as $file) {
                $lines[] = $indent . $file;
            }
            $lines[] = sprintf('%s… +%d more files', $indent, count($files) - 5);
        }

        return $lines;
    }

    /**
     * @param array{__files?: list<string>, __dirs?: array<string, array>} $node
     */
    private function countFiles(array $node): int
    {
        $n = count($node['__files'] ?? []);
        foreach ($node['__dirs'] ?? [] as $child) {
            $n += $this->countFiles($child);
        }

        return $n;
    }
}
