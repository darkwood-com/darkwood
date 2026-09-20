<?php

declare(strict_types=1);

namespace App\Navi;

use App\AiCostTable;
use App\SecretRedactor;
use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\Event;
use Navi\Domain\Execution\ExecutionState;
use Symfony\AI\Platform\Bridge\Anthropic\Factory as AnthropicFactory;
use Symfony\AI\Platform\Bridge\OpenAi\Factory as OpenAiFactory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Navi step: build a Symfony AI platform with a request-scoped key and send a tiny prompt.
 *
 * The key is constructor-only. It must never be copied into Context.
 */
final class TestAiConnectionAction implements Action
{
    public function __construct(
        #[\SensitiveParameter] private string $apiKey,
        private string $provider,
        private string $model,
        private ?string $endpoint,
        private HttpClientInterface $httpClient,
        private AiCostTable $costTable,
    ) {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('ai.connection.test');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        try {
            $deferred = $this->invoke();
            $reply = $deferred->asText();
        } catch (\Throwable $e) {
            $safe = SecretRedactor::message($e->getMessage(), $this->apiKey);

            return ActionResult::continueWith(
                $context->merge([
                    'test_ok' => false,
                    'test_error' => $safe,
                ]),
                Event::fromName('ai.connection.failed', [
                    'provider' => $this->provider,
                    'model' => $this->model,
                ]),
            );
        }

        $usage = $deferred->getMetadata()->get('token_usage');
        $tokens = $this->tokensFrom($usage instanceof TokenUsageInterface ? $usage : null);
        $cost = $this->costTable->estimate(
            $this->model,
            $tokens['input'],
            $tokens['output'],
            $tokens['cache_write'],
            $tokens['cache_read'],
        );

        $payload = [
            'provider' => $this->provider,
            'model' => $this->model,
            'endpoint' => $this->endpoint,
            'tokens' => $tokens,
            'cost' => $cost,
        ];

        return ActionResult::continueWith(
            $context->merge([
                'test_ok' => true,
                'test_reply' => $reply,
                ...$payload,
            ]),
            Event::fromName('ai.connection.tested', $payload),
        );
    }

    private function invoke(): \Symfony\AI\Platform\Result\DeferredResult
    {
        $messages = new MessageBag(Message::ofUser('Reply with the single word pong.'));

        $platform = match ($this->provider) {
            'anthropic' => AnthropicFactory::createPlatform(
                $this->apiKey,
                $this->httpClient,
                baseUrl: $this->endpoint ?: 'https://api.anthropic.com',
            ),
            'openai' => OpenAiFactory::createPlatform($this->apiKey, $this->httpClient),
            default => throw new \InvalidArgumentException(\sprintf('Unsupported provider "%s".', $this->provider)),
        };

        return $platform->invoke($this->model, $messages);
    }

    /**
     * @return array{input: ?int, output: ?int, cache_write: ?int, cache_read: ?int, cached: ?int}
     */
    private function tokensFrom(?TokenUsageInterface $usage): array
    {
        if ($usage === null) {
            return [
                'input' => null,
                'output' => null,
                'cache_write' => null,
                'cache_read' => null,
                'cached' => null,
            ];
        }

        return [
            'input' => $usage->getPromptTokens(),
            'output' => $usage->getCompletionTokens(),
            'cache_write' => $usage->getCacheCreationTokens(),
            'cache_read' => $usage->getCacheReadTokens(),
            'cached' => $usage->getCachedTokens(),
        ];
    }
}
