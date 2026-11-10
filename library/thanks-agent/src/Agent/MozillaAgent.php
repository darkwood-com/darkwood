<?php

declare(strict_types=1);

namespace App\Agent;

use App\Model\AgentId;
use App\Model\AgentPacket;

final class MozillaAgent extends AbstractAgent
{
    public function id(): AgentId
    {
        return AgentId::Mozilla;
    }

    protected function ioSeconds(): float
    {
        return 0.11;
    }

    protected function produce(AgentPacket $packet): array
    {
        $fixture = $this->readFixture('mozilla-open-source-ai.md');
        $excerpt = mb_substr(trim(preg_replace('/\s+/', ' ', $fixture) ?? $fixture), 0, 160);

        return [
            'output' => 'Mozilla: '.$excerpt,
            'tokens' => $this->estimateTokens($fixture),
            'messages' => 2,
            'didWork' => true,
            'payload' => [
                'principles' => ['openness', 'sovereignty', 'transparency', 'reproducibility', 'trust'],
            ],
        ];
    }
}
