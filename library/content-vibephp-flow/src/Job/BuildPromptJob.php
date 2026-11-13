<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\VibeRequest;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @implements JobInterface<VibeRequest, VibeRequest>
 */
final class BuildPromptJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
    ) {}

    public function __invoke($data): VibeRequest
    {
        $this->io->section('[prompt] build runtime context');
        $start = hrtime(true);

        $context = [
            'method' => $data->method,
            'uri' => $data->path,
            'script' => $data->script,
            'source' => $data->source,
            'instructions' => 'Simulate PHP execution and return status, headers, body.',
        ];

        $prompt = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (false === $prompt) {
            $prompt = '';
        }

        $this->io->writeln(sprintf(
            '  prompt %d bytes in %.2f ms',
            \strlen($prompt),
            (hrtime(true) - $start) / 1e6
        ));

        return new VibeRequest(
            $data->method,
            $data->path,
            $data->script,
            $data->source,
            $prompt,
            startedAt: $data->startedAt,
        );
    }
}
