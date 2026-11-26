<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;

/** The qualified MCP subset, separate from the legacy HTTP API's flexible query map. */
final readonly class DarkwoodMcpActionInput
{
    public function __construct(
        #[ApiProperty(schema: [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['state'],
            'properties' => [
                'state' => ['type' => 'string', 'enum' => ['main', 'combat', 'daily-battle', 'info']],
                'mode' => ['type' => 'string', 'enum' => ['combat']],
                'actionBeginFight' => ['enum' => [true, 1, '1']],
                'actionFight' => ['enum' => [true, 1, '1']],
                'actionEndFight' => ['enum' => [true, 1, '1']],
                'actionUsePotion' => ['enum' => [true, 1, '1']],
                'actionAddPoint' => ['type' => 'string', 'enum' => ['strength', 'dexterity', 'vitality']],
            ],
            'description' => 'One existing game action; requires separate trusted-client transport approval. Not a credential or confirmation argument.',
        ])]
        public array $query = [],
    ) {}
}
