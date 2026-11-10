<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\VibeRequest;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @implements JobInterface<VibeRequest, VibeRequest>
 */
final class ResolveScriptJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
        private readonly string $docroot,
    ) {}

    public function __invoke($data): VibeRequest
    {
        $this->io->section(sprintf('[resolve] %s %s', $data->method, $data->path));
        $start = hrtime(true);

        $path = trim($data->path, '/');
        $candidates = '' === $path
            ? ['index.php']
            : [$path, "{$path}.php", "{$path}/index.php", 'index.php'];

        $script = null;
        foreach ($candidates as $candidate) {
            if (is_file($this->docroot.DIRECTORY_SEPARATOR.$candidate)) {
                $script = $candidate;
                break;
            }
        }

        $this->io->writeln(sprintf(
            '  resolved to %s in %.2f ms',
            $script ?? 'none',
            (hrtime(true) - $start) / 1e6
        ));

        return new VibeRequest(
            $data->method,
            $data->path,
            $script,
            startedAt: microtime(true),
        );
    }
}
