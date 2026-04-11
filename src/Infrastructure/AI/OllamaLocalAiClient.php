<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\Port\LocalAiClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Local Ollama HTTP API (no cloud fallback).
 */
final readonly class OllamaLocalAiClient implements LocalAiClientInterface
{
    public function __construct(
        private HttpClientInterface $http,
        private string $baseUrl,
        private string $model,
    ) {
    }

    public function generate(string $systemPrompt, string $userPrompt): string
    {
        $url = rtrim($this->baseUrl, '/') . '/api/chat';
        $response = $this->http->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'stream' => false,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Ollama request failed: ' . $response->getContent(false));
        }

        $data = $response->toArray();
        $content = $data['message']['content'] ?? '';

        return \is_string($content) ? $content : '';
    }
}
