<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\VibeRequest;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @implements JobInterface<VibeRequest, VibeRequest>
 */
final class ReadSourceJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
        private readonly string $docroot,
    ) {}

    public function __invoke($data): VibeRequest
    {
        $this->io->section('[read] load script source');
        $start = hrtime(true);

        if (null === $data->script) {
            $this->io->warning('  no script to read');

            return $data;
        }

        $source = file_get_contents($this->docroot.DIRECTORY_SEPARATOR.$data->script);
        if (false === $source) {
            $source = '';
        }

        $this->io->writeln(sprintf(
            '  read %d bytes in %.2f ms',
            \strlen($source),
            (hrtime(true) - $start) / 1e6
        ));

        return new VibeRequest(
            $data->method,
            $data->path,
            $data->script,
            $source,
            startedAt: $data->startedAt,
        );
    }
}
