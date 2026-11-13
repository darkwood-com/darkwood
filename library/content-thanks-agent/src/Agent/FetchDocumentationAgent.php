<?php

declare(strict_types=1);

namespace App\Agent;

use App\Model\AgentId;
use App\Model\AgentPacket;

final class FetchDocumentationAgent extends AbstractAgent
{
    public function id(): AgentId
    {
        return AgentId::FetchDocumentation;
    }

    protected function ioSeconds(): float
    {
        return 0.12;
    }

    protected function produce(AgentPacket $packet): array
    {
        $fabien = $this->readFixture('fabien-five-agents.md');
        $bouchery = $this->readFixture('bouchery-stopped-agents.md');
        $combined = $fabien."\n".$bouchery;
        $excerpt = mb_substr(trim(preg_replace('/\s+/', ' ', $combined) ?? $combined), 0, 180);

        return [
            'output' => 'Docs: '.$excerpt,
            'tokens' => $this->estimateTokens($combined),
            'messages' => 3,
            'didWork' => true,
            'payload' => ['sources' => ['fabien', 'bouchery'], 'bytes' => strlen($combined)],
        ];
    }
}
