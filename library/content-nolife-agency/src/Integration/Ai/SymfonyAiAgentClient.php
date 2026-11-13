<?php

namespace App\Integration\Ai;

use Psr\Container\ContainerInterface;

/**
 * Adapter boundary for Symfony AI.
 *
 * Symfony AI's current application shape is: inject an ai.agent.* service, build a MessageBag,
 * call AgentInterface::call(), pass tools/options there, and request structured output with
 * the response_format option. This adapter keeps those details out of the domain runner.
 */
final readonly class SymfonyAiAgentClient implements AiAgentClient
{
    /**
     * @param array<string, object> $agentServices
     * @param array<string, string> $agentServiceIds
     */
    public function __construct(
        private array $agentServices = [],
        private array $agentServiceIds = [],
        private ?ContainerInterface $container = null,
    ) {
    }

    public function run(AiAgentRequest $request): AiAgentResponse
    {
        $realMode = (bool) ($request->input->metadata['real_ai'] ?? false);
        error_log('Using Symfony AI adapter');
        $symfonyAgent = $this->resolveAgentService($request->agent->definition()->key);
        if (null === $symfonyAgent) {
            if ($realMode || !$request->allowFallback) {
                throw new \RuntimeException(sprintf('No Symfony AI agent service configured for "%s".', $request->agent->definition()->key));
            }

            return AiAgentResponse::fromAgentOutput($request->agent->run($request->input, $request->context));
        }

        if (!method_exists($symfonyAgent, 'call')) {
            throw new \LogicException('Configured Symfony AI agent must expose a call() method.');
        }

        $options = $request->options;
        if (null !== $request->responseFormat) {
            $options['response_format'] = $request->responseFormat;
        }

        try {
            $messages = $this->messageBag($request, $realMode);
        } catch (\RuntimeException) {
            if ($realMode || !$request->allowFallback) {
                throw new \RuntimeException('Symfony AI message classes unavailable and fallback disabled.');
            }

            return AiAgentResponse::fromAgentOutput($request->agent->run($request->input, $request->context));
        }

        error_log('Calling Symfony AI agent');
        $result = $symfonyAgent->call($messages, $options);
        $content = $this->resultContent($result);
        if ($realMode && $this->isEmptyContent($content)) {
            throw new \RuntimeException('Symfony AI returned empty content in real mode.');
        }

        $response = AiAgentResponse::fromSymfonyAiContent(
            $request->agent->definition()->key,
            $request->context,
            $content,
            [
                'result_type' => get_debug_type($result),
                'options' => $options,
            ],
        );
        error_log('Symfony AI call completed');

        return $response;
    }

    public function name(): string
    {
        return 'symfony_ai';
    }

    private function resolveAgentService(string $agentKey): ?object
    {
        $service = $this->agentServices[$agentKey] ?? $this->agentServices['default'] ?? null;
        if (!is_object($service)) {
            $serviceId = $this->agentServiceIds[$agentKey] ?? $this->agentServiceIds['default'] ?? null;
            if (is_string($serviceId) && $this->container !== null && $this->container->has($serviceId)) {
                $candidate = $this->container->get($serviceId);
                if (is_object($candidate)) {
                    return $candidate;
                }
            }

            return null;
        }

        return $service;
    }

    private function messageBag(AiAgentRequest $request, bool $requireSymfonyMessageClasses): object
    {
        $messageClass = 'Symfony\\AI\\Platform\\Message\\Message';
        $messageBagClass = 'Symfony\\AI\\Platform\\Message\\MessageBag';
        if (!class_exists($messageClass) || !class_exists($messageBagClass)) {
            if ($requireSymfonyMessageClasses) {
                throw new \RuntimeException('Symfony AI message classes are not available.');
            }

            return (object) [
                'system' => $request->systemPrompt,
                'user' => $request->userPrompt,
            ];
        }

        $messages = [];
        if ('' !== trim($request->systemPrompt)) {
            $messages[] = $messageClass::forSystem($request->systemPrompt);
        }
        $messages[] = $messageClass::ofUser($request->userPrompt);

        return new $messageBagClass(...$messages);
    }

    private function resultContent(object $result): mixed
    {
        if (method_exists($result, 'getContent')) {
            return $result->getContent();
        }

        if (method_exists($result, 'asText')) {
            return $result->asText();
        }

        if ($result instanceof \Stringable) {
            return (string) $result;
        }

        return [
            'result_type' => get_debug_type($result),
        ];
    }

    private function isEmptyContent(mixed $content): bool
    {
        if (is_string($content)) {
            return trim($content) === '';
        }

        if (is_array($content)) {
            return $content === [];
        }

        return $content === null;
    }
}
