<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\DarkwoodMcpActionInput;
use App\Service\DarkwoodMcpForwarderService;

final readonly class DarkwoodActionProcessor implements ProcessorInterface
{
    public function __construct(
        private DarkwoodMcpForwarderService $forwarder,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof DarkwoodMcpActionInput || !is_array($data->query)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('A query object is required.');
        }
        if ([] !== array_diff(array_keys($context['mcp_data'] ?? []), ['query'])) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Unknown MCP action argument.');
        }
        $query = $data->query;
        $states = ['main', 'combat', 'daily-battle', 'info'];
        if (!isset($query['state']) || !in_array($query['state'], $states, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Supported MCP states: main, combat, daily-battle, info.');
        }
        $flags = ['actionBeginFight', 'actionFight', 'actionEndFight', 'actionUsePotion'];
        if ([] !== array_diff(array_keys($query), ['state', 'mode', 'actionAddPoint', ...$flags])) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Unknown MCP game action parameter.');
        }
        if (isset($query['mode']) && 'combat' !== $query['mode']) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('The only supported action mode is combat.');
        }
        if (isset($query['mode']) && !in_array($query['state'], ['combat', 'daily-battle'], true)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Combat mode requires a combat state.');
        }
        $actions = array_intersect(array_keys($query), ['actionAddPoint', ...$flags]);
        if (count($actions) > 1) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Propose one game action at a time.');
        }
        foreach ($flags as $flag) {
            if (isset($query[$flag]) && !in_array($query[$flag], [true, 1, '1'], true)) {
                throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Action flags must be true, 1 or "1".');
            }
            if (isset($query[$flag]) && 'actionBeginFight' !== $flag && 'combat' !== ($query['mode'] ?? null)) {
                throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('An active fight action requires mode combat.');
            }
            if (isset($query[$flag]) && (!in_array($query['state'], ['combat', 'daily-battle'], true) || ('actionUsePotion' === $flag && 'combat' !== $query['state']))) {
                throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Action is incompatible with the selected game state.');
            }
        }
        if (isset($query['actionAddPoint']) && ('info' !== $query['state'] || !in_array($query['actionAddPoint'], ['strength', 'dexterity', 'vitality'], true))) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Choose strength, dexterity or vitality in the info state.');
        }

        return $this->forwarder->forward('api_darkwood_post_action', 'POST', ['query' => $query]);
    }
}
