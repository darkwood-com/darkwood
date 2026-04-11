<?php

declare(strict_types=1);

namespace App\Infrastructure\Composio;

use App\Domain\Execution\LambdaExecutionContext;
use App\Domain\Primitive\PrimitiveInvocation;
use App\Domain\Primitive\PrimitiveInvokerInterface;

/**
 * Maps primitive names to Composio tool slugs and executes via HTTP API.
 *
 * @phpstan-import-type Arguments from PrimitiveInvocation
 */
final readonly class ComposioPrimitiveInvoker implements PrimitiveInvokerInterface
{
    /**
     * @param array<string, string> $primitiveToToolSlug empty = identity map (primitive name === tool slug)
     */
    public function __construct(
        private ComposioClient $client,
        private ConnectedAccountResolver $resolver,
        private array $primitiveToToolSlug = [],
    ) {
    }

    public function invoke(PrimitiveInvocation $invocation, LambdaExecutionContext $context): mixed
    {
        $name = $invocation->primitiveName;
        $toolSlug = $this->primitiveToToolSlug[$name] ?? $name;

        if ($context->composioUserId === '') {
            throw new \InvalidArgumentException('composio_user_id is required for primitive invocation.');
        }

        $meta = $this->client->getTool($toolSlug);
        $accountId = $this->resolver->resolve($context->composioUserId, $meta, $context->composioConnectedAccountId);

        $input = array_merge($context->composioInput, $invocation->arguments);

        return $this->client->executeTool($toolSlug, $accountId, $input, $context->composioUserId);
    }
}
