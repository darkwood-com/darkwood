<?php

declare(strict_types=1);

namespace App\Infrastructure\RepositoryContent;

use App\Application\Port\RepositoryContentReaderPort;
use App\Domain\Repository\RepositoryMetadata;
use Symfony\Component\Process\Process;

final readonly class GitRepositoryContentReader implements RepositoryContentReaderPort
{
    public function __construct(private string $cachePath = __DIR__ . '/../../../var/app/ingested')
    {
    }

    public function readTextMaterial(RepositoryMetadata $metadata): array
    {
        $repoDir = $this->cachePath . '/' . $metadata->id->toString();
        if (!is_dir($repoDir . '/.git')) {
            if (!is_dir($this->cachePath)) {
                mkdir($this->cachePath, 0777, true);
            }
            $clone = new Process(['git', 'clone', '--depth', '1', '--branch', $metadata->defaultBranch, $metadata->cloneUrl, $repoDir]);
            $clone->setTimeout(40);
            $clone->run();
            if (!$clone->isSuccessful()) {
                return [];
            }
        }

        $fragments = [];
        foreach (['README.md', 'README', 'readme.md'] as $candidate) {
            $file = $repoDir . '/' . $candidate;
            if (is_file($file)) {
                $fragments[] = (string) file_get_contents($file);
                break;
            }
        }

        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($repoDir));
        foreach ($iter as $file) {
            if (count($fragments) >= 4 || !$file->isFile()) {
                continue;
            }
            $name = $file->getFilename();
            if (preg_match('/\.(md|txt|rst)$/i', $name) !== 1) {
                continue;
            }
            $path = $file->getPathname();
            if (str_contains($path, '/.git/')) {
                continue;
            }
            $fragments[] = (string) file_get_contents($path);
        }

        return $fragments;
    }
}
