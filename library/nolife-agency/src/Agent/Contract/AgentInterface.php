<?php

namespace App\Agent\Contract;

use App\Agent\Definition\AgentDefinition;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;

interface AgentInterface
{
    public function definition(): AgentDefinition;

    public function run(AgentInput $input, AgentContext $context): AgentOutput;
}
