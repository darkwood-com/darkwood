<?php

declare(strict_types=1);

namespace App\Agent;

use App\Model\AgentId;
use App\Model\AgentPacket;

final class GithubAgent extends AbstractAgent
{
    public function id(): AgentId
    {
        return AgentId::Github;
    }

    protected function ioSeconds(): float
    {
        return 0.15;
    }

    protected function produce(AgentPacket $packet): array
    {
        $fixture = $this->readFixture('symfony-ai-pr-2363.md');
        $repoMeta = [
            'repo' => 'symfony/ai',
            'pr' => 2363,
            'topic' => 'stream onError metering',
        ];

        // Prefer local git metadata when available (real work, offline-safe).
        $gitHead = @shell_exec('git -C '.escapeshellarg($this->projectDir).' rev-parse --short HEAD 2>/dev/null');
        if (is_string($gitHead) && '' !== trim($gitHead)) {
            $repoMeta['localHead'] = trim($gitHead);
        }

        $output = sprintf('GitHub/PR#%d: %s', $repoMeta['pr'], mb_substr(trim($fixture), 0, 120));

        return [
            'output' => $output,
            'tokens' => $this->estimateTokens($fixture) + 40,
            'messages' => 4,
            'didWork' => true,
            'payload' => $repoMeta,
        ];
    }
}
