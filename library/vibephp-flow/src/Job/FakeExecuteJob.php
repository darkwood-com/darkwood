<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\VibeRequest;
use App\Model\VibeResponse;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @implements JobInterface<VibeRequest, VibeRequest>
 */
final class FakeExecuteJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
    ) {}

    public function __invoke($data): VibeRequest
    {
        $this->io->section('[execute] deterministic fake runtime');
        $start = hrtime(true);

        if (null === $data->script || null === $data->source) {
            $response = new VibeResponse(404, ['Content-Type' => 'text/plain'], 'VibePHP: No script found.');

            return new VibeRequest(
                $data->method,
                $data->path,
                $data->script,
                $data->source,
                $data->prompt,
                $response,
                $data->startedAt,
            );
        }

        $source = $data->source;
        $includeCount = preg_match_all('/\b(require|include)(_once)?\s+/', $source);
        $routeMatches = preg_match_all('/preg_match\s*\(/', $source);
        $isJson = str_contains($source, 'application/json') || str_contains($data->script, 'api/');

        if ($isJson) {
            $body = json_encode([
                'service' => 'vibe-php',
                'status' => 'ok',
                'script' => $data->script,
                'includes' => $includeCount,
            ], JSON_PRETTY_PRINT);
            $headers = ['Content-Type' => 'application/json'];
            $status = 200;
        } else {
            $title = 'VibePHP';
            if (preg_match('/\$pageTitle\s*=\s*[\'"]([^\'"]+)/', $source, $m)) {
                $title = $m[1];
            }

            $body = "<!DOCTYPE html><html><head><title>{$title}</title></head><body>";
            $body .= "<h1>Fake execution of {$data->script}</h1>";
            $body .= "<p>Includes discovered: {$includeCount}, route patterns: {$routeMatches}</p>";
            $body .= '<p class="muted">Rendered by VibePHP Flow demo.</p></body></html>';
            $headers = ['Content-Type' => 'text/html; charset=UTF-8'];
            $status = 200;
        }

        for ($i = 0; $i < 500; ++$i) {
            hash('sha256', $source.(string) $i);
        }

        $response = new VibeResponse($status, $headers, $body, $includeCount, $routeMatches);

        $this->io->writeln(sprintf(
            '  status %d, body %d bytes, %d includes in %.2f ms',
            $status,
            \strlen($body),
            $includeCount,
            (hrtime(true) - $start) / 1e6
        ));

        return new VibeRequest(
            $data->method,
            $data->path,
            $data->script,
            $data->source,
            $data->prompt,
            $response,
            $data->startedAt,
        );
    }
}
