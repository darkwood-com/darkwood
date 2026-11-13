<?php

namespace App\Governance\Policy;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;

interface PolicyEngineInterface
{
    public function evaluate(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): PolicyDecision;
}
