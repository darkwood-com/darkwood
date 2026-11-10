<?php

declare(strict_types=1);

namespace App\Agent;

use App\Model\AgentId;
use App\Model\AgentPacket;

final class PHPAgent extends AbstractAgent
{
    public function id(): AgentId
    {
        return AgentId::Php;
    }

    protected function ioSeconds(): float
    {
        return 0.10;
    }

    protected function produce(AgentPacket $packet): array
    {
        $fixture = $this->readFixture('php-86-stitcher.md');
        $pollAvailable = class_exists(\Io\Poll\Context::class);
        $durationAvailable = class_exists(\Time\Duration::class);

        $findings = [
            'phpVersion' => PHP_VERSION,
            'partialApplication' => 'callable binding for pipeline steps',
            'pollApi' => $pollAvailable ? 'available' : 'unavailable on '.PHP_VERSION,
            'duration' => $durationAvailable ? 'available' : 'unavailable on '.PHP_VERSION,
            'clamp' => function_exists('clamp'),
        ];

        return [
            'output' => sprintf(
                'PHP 8.6 watch: Poll=%s Duration=%s clamp=%s',
                $findings['pollApi'],
                $findings['duration'],
                $findings['clamp'] ? 'yes' : 'no',
            ),
            'tokens' => $this->estimateTokens($fixture) + 20,
            'messages' => 2,
            'didWork' => true,
            'payload' => $findings,
        ];
    }
}
