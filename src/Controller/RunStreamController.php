<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\EventStreamResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ServerEvent;
use Symfony\Component\Routing\Attribute\Route;

final class RunStreamController extends AbstractController
{
    public function __construct(private readonly RunStreamChunkNormalizer $chunkNormalizer)
    {
    }

    #[Route('/runs', name: 'app_runs_index', methods: ['GET'])]
    public function index(): Response
    {
        $runFiles = glob($this->getRunsDirectory() . '/*.ndjson') ?: [];
        $runs = array_map(
            static fn (string $file): string => pathinfo($file, \PATHINFO_FILENAME),
            $runFiles,
        );
        sort($runs);

        return $this->render('runs/index.html.twig', [
            'runs' => $runs,
        ]);
    }

    #[Route('/runs/{id}/stream', name: 'app_runs_stream', methods: ['GET'])]
    public function streamRun(string $id): EventStreamResponse
    {
        $safeId = preg_replace('/[^a-zA-Z0-9._-]/', '', $id) ?? '';
        $file = sprintf('%s/%s.ndjson', $this->getRunsDirectory(), $safeId);

        return new EventStreamResponse(function () use ($file) {
            if (!is_file($file)) {
                yield new ServerEvent(
                    json_encode(['message' => sprintf('Run "%s" not found.', basename($file, '.ndjson'))], JSON_THROW_ON_ERROR),
                    'error'
                );
                yield new ServerEvent('done', 'done');

                return;
            }

            $handle = fopen($file, 'rb');
            if (false === $handle) {
                yield new ServerEvent(json_encode(['message' => 'Unable to open run file.'], JSON_THROW_ON_ERROR), 'error');
                yield new ServerEvent('done', 'done');

                return;
            }

            $eventIndex = 0;
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                try {
                    $decoded = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                    if (!\is_array($decoded)) {
                        continue;
                    }

                    yield new ServerEvent(
                        json_encode($this->chunkNormalizer->normalize($decoded), JSON_THROW_ON_ERROR),
                        'chunk',
                        id: (string) $eventIndex++
                    );
                } catch (\Throwable) {
                    continue;
                }
            }

            fclose($handle);

            yield new ServerEvent('done', 'done');
        });
    }

    private function getRunsDirectory(): string
    {
        return $this->getParameter('kernel.project_dir') . '/var/runs';
    }
}
