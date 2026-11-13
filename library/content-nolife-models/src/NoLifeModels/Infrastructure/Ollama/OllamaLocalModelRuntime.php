<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Ollama;

use App\NoLifeModels\Domain\Model\InferenceMetrics;
use App\NoLifeModels\Domain\Model\LocalModel;
use App\NoLifeModels\Domain\Model\ModelInferenceResult;
use App\NoLifeModels\Domain\Runtime\GeneratePromptCommand;
use App\NoLifeModels\Domain\Runtime\LocalModelRuntimeInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OllamaLocalModelRuntime implements LocalModelRuntimeInterface
{
    public function __construct(
        private readonly HttpClientInterface $ollamaClient,
    ) {
    }

    public function listLocalModels(): array
    {
        $response = $this->ollamaClient->request('GET', '/api/tags');
        $data = $response->toArray();
        $models = [];
        foreach ($data['models'] ?? [] as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $name = (string) ($row['name'] ?? $row['model'] ?? '');
            if ($name === '') {
                continue;
            }
            $models[] = new LocalModel(
                $name,
                isset($row['size']) ? (int) $row['size'] : null,
                isset($row['modified_at']) ? (string) $row['modified_at'] : null,
                isset($row['digest']) ? (string) $row['digest'] : null,
            );
        }

        return $models;
    }

    public function generate(GeneratePromptCommand $command): ModelInferenceResult
    {
        $payload = [
            'model' => $command->model,
            'prompt' => $command->prompt,
            'stream' => false,
            'options' => [
                'temperature' => $command->temperature,
                'top_p' => $command->topP,
            ],
        ];
        if (null !== $command->system && $command->system !== '') {
            $payload['system'] = $command->system;
        }

        $t0 = microtime(true);
        try {
            $response = $this->ollamaClient->request('POST', '/api/generate', [
                'json' => $payload,
            ]);
            $wall = microtime(true) - $t0;
            $body = $response->toArray(false);
            if (($body['error'] ?? null) !== null && $body['error'] !== '') {
                return self::errorResult($command->model, (string) $body['error'], $wall, $body);
            }

            $text = (string) ($body['response'] ?? '');
            $metrics = self::metricsFromBody($body, $wall);

            return new ModelInferenceResult($command->model, $text, null, $metrics, $body);
        } catch (\Throwable $e) {
            $wall = microtime(true) - $t0;
            $metrics = new InferenceMetrics($wall, null, null, null, null, null, null, null, null);

            return new ModelInferenceResult(
                $command->model,
                '',
                $e->getMessage(),
                $metrics,
                null,
            );
        }
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function errorResult(string $model, string $message, float $wall, array $body): ModelInferenceResult
    {
        $metrics = self::metricsFromBody($body, $wall);

        return new ModelInferenceResult($model, '', $message, $metrics, $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function metricsFromBody(array $body, float $wallClockSeconds): InferenceMetrics
    {
        $evalCount = isset($body['eval_count']) ? (int) $body['eval_count'] : null;
        $evalDurationNs = isset($body['eval_duration']) ? (int) $body['eval_duration'] : null;
        $tokensPerSecond = null;
        if (null !== $evalCount && $evalCount > 0 && null !== $evalDurationNs && $evalDurationNs > 0) {
            $seconds = $evalDurationNs / 1_000_000_000;
            $tokensPerSecond = $seconds > 0 ? $evalCount / $seconds : null;
        }

        return new InferenceMetrics(
            $wallClockSeconds,
            self::nsToSeconds($body['total_duration'] ?? null),
            self::nsToSeconds($body['load_duration'] ?? null),
            self::nsToSeconds($body['prompt_eval_duration'] ?? null),
            self::nsToSeconds($body['eval_duration'] ?? null),
            isset($body['prompt_eval_count']) ? (int) $body['prompt_eval_count'] : null,
            $evalCount,
            $tokensPerSecond,
            isset($body['done_reason']) ? (string) $body['done_reason'] : null,
        );
    }

    private static function nsToSeconds(mixed $ns): ?float
    {
        if (!\is_int($ns) && !\is_float($ns)) {
            return null;
        }
        $n = (float) $ns;

        return $n > 0 ? $n / 1_000_000_000 : null;
    }
}
