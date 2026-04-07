<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use App\Domain\Capability\ComposioToolCapability;
use App\Domain\Execution\Action;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\ActionResult;
use App\Domain\Execution\Context;
use App\Domain\Execution\Event;
use App\Domain\Execution\ExecutionState;
use App\Infrastructure\Composio\ComposioClient;
use App\Infrastructure\Composio\ConnectedAccountResolver;

/**
 * Navi Action: for each configured Composio tool name (comma-separated in COMPOSIO_ACTION), loads metadata,
 * resolves connected account, executes via Composio HTTP API.
 */
final readonly class ExecuteComposioToolAction implements Action
{
    public function __construct(
        private ComposioToolCapability $capability,
        private ComposioClient $client,
        private ConnectedAccountResolver $resolver,
    ) {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('composio.execute');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $userId = $context->get('composio_user_id');
        if (!\is_string($userId) || $userId === '') {
            throw new \InvalidArgumentException('Context key composio_user_id must be a non-empty string.');
        }

        $rawInput = $context->get('composio_input');
        if ($rawInput !== null && !\is_array($rawInput)) {
            throw new \InvalidArgumentException('Context key composio_input must be an array if set.');
        }
        /** @var array<string, mixed> $input */
        $input = $rawInput ?? [];

        $forced = $context->get('composio_connected_account_id');
        $forcedId = \is_string($forced) && $forced !== '' ? $forced : null;

        $toolNames = $this->toolNamesFromCapability();
        $results = [];
        foreach ($toolNames as $toolName) {
            $meta = $this->client->getTool($toolName);
            $accountId = $this->resolver->resolve($userId, $meta, $forcedId);
            $results[$toolName] = $this->client->executeTool($toolName, $accountId, $input, $userId);
        }

        $payload = \count($results) === 1 ? reset($results) : $results;

        $next = $context->merge([
            'composio_result' => $payload,
        ]);

        return ActionResult::continueWith(
            $next,
            Event::fromName('composio.executed', ['tool_names' => array_keys($results)])
        );
    }

    /**
     * Composio tool names from COMPOSIO_ACTION (one or comma-separated).
     *
     * @return list<string>
     */
    private function toolNamesFromCapability(): array
    {
        $raw = trim($this->capability->actionName());
        if ($raw === '') {
            throw new \RuntimeException('COMPOSIO_ACTION is not set.');
        }

        $toolNames = array_values(array_filter(array_map(trim(...), explode(',', $raw))));

        if ($toolNames === []) {
            throw new \RuntimeException('COMPOSIO_ACTION is not set.');
        }

        return $toolNames;
    }
}
