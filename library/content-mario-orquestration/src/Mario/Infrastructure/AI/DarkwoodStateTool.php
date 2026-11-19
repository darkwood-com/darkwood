<?php

declare(strict_types=1);

namespace App\Mario\Infrastructure\AI;

use Mcp\Client\Client;
use Symfony\AI\Agent\Toolbox\ToolFactoryInterface;
use Symfony\AI\Platform\Tool\ExecutionReference;
use Symfony\AI\Platform\Tool\Tool;
use Symfony\AI\Platform\Tool\ToolEffectHints;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** One observation capability; the agent never receives Darkwood's action tool. */
#[Exclude]
final class DarkwoodStateTool implements ToolFactoryInterface
{
    private array $observations = [];

    public function __construct(private readonly Client $client)
    {
    }

    public function getTool(object|string $reference): iterable
    {
        foreach ($this->client->listTools()->tools as $remote) {
            if ('get_darkwood_state' === $remote->name && true === $remote->annotations?->readOnlyHint) {
                yield new Tool(new ExecutionReference(self::class), $remote->name, $remote->description ?? 'Observe the existing Darkwood player.', $remote->inputSchema, effectHints: new ToolEffectHints(readOnlyHint: true, destructiveHint: false, idempotentHint: true));

                return;
            }
        }
        throw new \RuntimeException('A qualified read-only Darkwood state tool is not available on this server.');
    }

    public function __invoke(): array
    {
        if ([] !== $this->observations) {
            throw new \RuntimeException('One observation is allowed per request.');
        }
        $result = $this->client->callTool('get_darkwood_state', []);
        if ($result->isError || !is_array($result->structuredContent) || !is_string($result->structuredContent['state'] ?? null)) {
            throw new \RuntimeException('The Darkwood server did not return a usable observation.');
        }
        $this->observations[] = $result->structuredContent;

        return $result->structuredContent;
    }

    public function observations(): array
    {
        return $this->observations;
    }
}
