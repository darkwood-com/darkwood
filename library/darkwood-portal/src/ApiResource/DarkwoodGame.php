<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\Post;
use App\Dto\DarkwoodActionInput;
use App\Dto\DarkwoodMcpActionInput;
use App\Dto\DarkwoodArchiveIdInput;
use App\State\DarkwoodActionHttpProcessor;
use App\State\DarkwoodActionProcessor;
use App\State\DarkwoodArchiveGetProcessor;
use App\State\DarkwoodArchivesProcessor;
use App\State\DarkwoodStateProcessor;
use App\State\Provider\DarkwoodArchiveProvider;
use App\State\Provider\DarkwoodArchivesProvider;
use App\State\Provider\DarkwoodStateProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/darkwood/state',
            stateless: false,
            name: 'api_darkwood_get_state',
            provider: DarkwoodStateProvider::class,
        ),
        new Post(
            uriTemplate: '/darkwood/action',
            inputFormats: ['json' => ['application/json', 'application/x-www-form-urlencoded']],
            stateless: false,
            input: DarkwoodActionInput::class,
            read: false,
            name: 'api_darkwood_post_action',
            processor: DarkwoodActionHttpProcessor::class,
        ),
        new Get(
            uriTemplate: '/darkwood/archives',
            stateless: false,
            name: 'api_darkwood_archives',
            provider: DarkwoodArchivesProvider::class,
        ),
        new Get(
            uriTemplate: '/darkwood/archives/{id}',
            stateless: false,
            name: 'api_darkwood_archive_get',
            provider: DarkwoodArchiveProvider::class,
        ),
    ],
    mcp: [
        'get_darkwood_state' => new McpTool(
            description: 'Observe the current state for an existing player; requires an active beta API key. Never initializes a player.',
            annotations: ['readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true],
            structuredContent: true,
            outputFormats: ['json' => ['application/json']],
            processor: DarkwoodStateProcessor::class,
        ),
        'darkwood_action' => new McpTool(
            description: 'Propose one validated game action. Execution requires authenticated player and exact, single-use transport-level client approval; never set approval from model arguments.',
            annotations: ['readOnlyHint' => false, 'destructiveHint' => true, 'idempotentHint' => false],
            structuredContent: true,
            outputFormats: ['json' => ['application/json']],
            input: DarkwoodMcpActionInput::class,
            processor: DarkwoodActionProcessor::class,
        ),
        'list_darkwood_archives' => new McpTool(
            description: 'List available Darkwood save archives (active beta + premium key). Shared historical snapshots, not a per-user save.',
            annotations: ['readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true],
            structuredContent: true,
            outputFormats: ['json' => ['application/json']],
            processor: DarkwoodArchivesProcessor::class,
        ),
        'get_darkwood_archive' => new McpTool(
            description: 'Read one shared historical archive by actual YYYY-MM-DD date. Active beta + premium key required.',
            annotations: ['readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true],
            structuredContent: true,
            outputFormats: ['json' => ['application/json']],
            input: DarkwoodArchiveIdInput::class,
            processor: DarkwoodArchiveGetProcessor::class,
        ),
    ],
)]
final class DarkwoodGame
{
}
