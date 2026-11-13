<?php

namespace App\Integration\Ai;

use App\Agent\Contract\AgentInterface;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;

final readonly class AiAgentRequest
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public AgentInterface $agent,
        public AgentInput $input,
        public AgentContext $context,
        public string $systemPrompt,
        public string $userPrompt,
        public array $options = [],
        public ?string $responseFormat = null,
        public bool $allowFallback = true,
    ) {
    }

    public static function fromAgentExecution(AgentInterface $agent, AgentInput $input, AgentContext $context): self
    {
        $definition = $agent->definition();
        $systemPrompt = self::mappedSystemPrompt($definition->key, $definition->name, $definition->role, $definition->responsibilities);

        $encodedPayload = json_encode($input->payload, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR);
        $userPrompt = sprintf(
            "Run id: %s\nRequester: %s\nProject: %s\nInput payload:\n%s",
            $context->runId(),
            $context->requester(),
            $context->projectKey() ?? 'none',
            $encodedPayload,
        );

        $options = [];
        if (isset($input->metadata['ai_options']) && is_array($input->metadata['ai_options'])) {
            /** @var array<string, mixed> $metadataOptions */
            $metadataOptions = $input->metadata['ai_options'];
            $options = $metadataOptions;
        }

        $responseFormat = isset($input->metadata['response_format']) && is_string($input->metadata['response_format'])
            ? $input->metadata['response_format']
            : null;
        $allowFallback = isset($input->metadata['allow_ai_fallback']) ? (bool) $input->metadata['allow_ai_fallback'] : true;

        return new self(
            agent: $agent,
            input: $input,
            context: $context,
            systemPrompt: $systemPrompt,
            userPrompt: $userPrompt,
            options: $options,
            responseFormat: $responseFormat,
            allowFallback: $allowFallback,
        );
    }

    /**
     * @param list<string> $responsibilities
     */
    private static function mappedSystemPrompt(string $agentKey, string $name, string $role, array $responsibilities): string
    {
        $base = sprintf(
            "You are %s.\nRole: %s\nResponsibilities:\n%s",
            $name,
            $role,
            implode('', array_map(static fn (string $responsibility): string => sprintf("- %s\n", $responsibility), $responsibilities)),
        );

        return match ($agentKey) {
            'ceo' => $base."\nFocus on strategic business direction and risk framing.",
            'cto' => $base."\nFocus on architecture risk, standards, and platform constraints.",
            'product_owner' => $base."\nFocus on scoped requirements and acceptance criteria.",
            'architect' => $base."\nFocus on service boundaries, contracts, and implementation sequencing.",
            'developer' => $base."\nFocus on implementation details, testability, and change safety.",
            'content' => $base."\nFocus on clear, conversion-oriented website copy and consistency.",
            'seo_geo_specialist' => $base."\nFocus on SEO/GEO discoverability, metadata, and structured data.",
            'media_motion' => $base."\nFocus on concise media and motion brief generation.",
            'qa' => $base."\nFocus on quality gates, test evidence, and release risk.",
            'delivery_manager' => $base."\nFocus on delivery readiness, traceability, and approvals.",
            default => $base,
        };
    }
}
