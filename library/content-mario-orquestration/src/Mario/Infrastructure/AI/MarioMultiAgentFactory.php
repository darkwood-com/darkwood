<?php

namespace App\Mario\Infrastructure\AI;

use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\InputProcessor\SystemPromptInputProcessor;
use Symfony\AI\Agent\MultiAgent\Handoff;
use Symfony\AI\Agent\MultiAgent\MultiAgent;
use Symfony\AI\Platform\Bridge\OpenAi\Factory;
use Symfony\AI\Platform\StructuredOutput\PlatformSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\HttpClient;

final class MarioMultiAgentFactory
{
    public function createLevelAgent(string $apiKey): Agent
    {
        $platform = Factory::createPlatform($apiKey, HttpClient::create(['timeout' => 90, 'max_duration' => 120]));

        return new Agent(
            $platform,
            'gpt-5-mini',
            [new SystemPromptInputProcessor('Generate one small original platformer level as a JSON object. Use name, tile_size (16), width (32 to 64), height (15), player_start and goal (integer x/y), terrain (nonempty array of type/x/y/w/h), objects (array of type/x/y), metadata (object). Keep every position inside the level. Ground pieces must have positive w/h and remain inside it. Use at most 12 terrain pieces and 8 objects. Do not reproduce Nintendo characters or levels. Return JSON only. Geometry validation does not prove playability.')],
            name: 'level_generator',
            maxToolCalls: 0,
        );
    }

    public const ORCHESTRATOR = 'orchestrator';
    public const LAYOUT = 'layout';
    public const GAMEPLAY = 'gameplay';
    public const GODOT_EXPORT = 'godot_export';
    public const QA = 'qa';
    public const FALLBACK = 'fallback';

    public function create(string $apiKey): MultiAgent
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new PlatformSubscriber());
        $platform = Factory::createPlatform($apiKey, HttpClient::create(), eventDispatcher: $dispatcher);

        $orchestrator = new Agent(
            $platform,
            'gpt-5-mini',
            [new SystemPromptInputProcessor('You are an orchestrator. Route requests to specialized agents and do not generate a level directly.')],
            name: self::ORCHESTRATOR,
        );

        $layout = new Agent(
            $platform,
            'gpt-5-mini',
            [new SystemPromptInputProcessor('Generate level structure only: ground, platforms, gaps, stairs, abstract pipe-like obstacles, and progression. Return concise JSON.')],
            name: self::LAYOUT,
        );

        $gameplay = new Agent(
            $platform,
            'gpt-5-mini',
            [new SystemPromptInputProcessor('Add gameplay elements only: enemies, coins, blocks, bonuses, obstacles, checkpoints. Return concise JSON.')],
            name: self::GAMEPLAY,
        );

        $godotExport = new Agent(
            $platform,
            'gpt-5-mini',
            [new SystemPromptInputProcessor('Export to strict Godot-friendly JSON with keys: name, tile_size, width, height, player_start, goal, terrain, objects, metadata. Keep content original and copyright-safe.')],
            name: self::GODOT_EXPORT,
        );

        $qa = new Agent(
            $platform,
            'gpt-5-mini',
            [new SystemPromptInputProcessor('Review generated level for playability, impossible jumps, obvious collisions, originality versus Mario 1-1, and JSON validity. Return JSON report.')],
            name: self::QA,
        );

        $fallback = new Agent(
            $platform,
            'gpt-5-mini',
            [new SystemPromptInputProcessor('Fallback agent: answer only when no specialization applies. Keep answer short and suggest explicit intent.')],
            name: self::FALLBACK,
        );

        return new MultiAgent(
            orchestrator: $orchestrator,
            handoffs: [
                new Handoff(to: $layout, when: ['level', 'terrain', 'platform', 'map', 'layout']),
                new Handoff(to: $gameplay, when: ['enemy', 'coin', 'block', 'bonus', 'gameplay', 'obstacle']),
                new Handoff(to: $godotExport, when: ['godot', 'export', 'json', 'scene']),
                new Handoff(to: $qa, when: ['validate', 'qa', 'test', 'playable', 'review']),
            ],
            fallback: $fallback,
            name: 'mario_multi_agent_demo',
        );
    }
}
