<?php

namespace App\Agent;

use App\Agent\Contract\AgentInterface;
use App\Agent\Definition\AgentDefinition;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;

abstract class AbstractAgent implements AgentInterface
{
    public function run(AgentInput $input, AgentContext $context): AgentOutput
    {
        return new AgentOutput(
            payload: [
                'agent' => $this->name(),
                'role' => $this->role(),
                'run_id' => $context->runId(),
                'requester' => $context->requester(),
                'input' => $input->payload,
                'status' => 'completed',
            ],
            metadata: [
                'produced_at' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            ],
        );
    }

    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: $this->name(),
            name: sprintf('%s Agent', ucwords(str_replace('_', ' ', $this->name()))),
            role: $this->role(),
            description: $this->role(),
            responsibilities: [
                $this->role(),
            ],
        );
    }
}
