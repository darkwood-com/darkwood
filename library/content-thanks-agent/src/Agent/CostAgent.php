<?php

declare(strict_types=1);

namespace App\Agent;

use App\Instrumentation\CostLedger;
use App\Model\AgentId;
use App\Model\AgentPacket;

final class CostAgent extends AbstractAgent
{
    public function id(): AgentId
    {
        return AgentId::Cost;
    }

    protected function ioSeconds(): float
    {
        return 0.06;
    }

    protected function produce(AgentPacket $packet): array
    {
        $fixture = $this->readFixture('plais-ai-bill.md');
        // Illustrative outcome-cost math (Plais): rate × tokens × tokenizer variance.
        $rateCardEstimate = 0.01;
        $tokenizerVariance = 1.3;
        $realized = $rateCardEstimate * $tokenizerVariance;
        $output = sprintf(
            'Bill forecast: rate-card=%.4f realized≈%.4f (tokenizer ±30%%); unit=cost/successful outcome',
            $rateCardEstimate,
            $realized,
        );

        return [
            'output' => $output,
            'tokens' => $this->estimateTokens($fixture) + 15,
            'messages' => 2,
            'didWork' => true,
            'payload' => [
                'rateCardEstimate' => $rateCardEstimate,
                'realizedEstimate' => $realized,
                'ratePer1k' => CostLedger::RATE_PER_1K_TOKENS,
            ],
        ];
    }
}
