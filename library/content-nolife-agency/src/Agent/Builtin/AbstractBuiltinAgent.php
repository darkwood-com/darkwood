<?php

namespace App\Agent\Builtin;

use App\Agent\Contract\AgentInterface;
use App\Agent\Definition\AgentDefinition;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;

abstract class AbstractBuiltinAgent implements AgentInterface
{
    public function run(AgentInput $input, AgentContext $context): AgentOutput
    {
        return new AgentOutput(
            payload: [
                'agent' => $this->definition()->key,
                'run_id' => $context->runId(),
                'requester' => $context->requester(),
                'status' => 'ok',
                'input' => $input->payload,
            ],
            metadata: [
                'source' => 'builtin',
            ],
        );
    }
}
