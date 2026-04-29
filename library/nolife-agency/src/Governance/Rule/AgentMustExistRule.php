<?php

namespace App\Governance\Rule;

use App\Agent\Contract\AgentRegistryInterface;
use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final readonly class AgentMustExistRule implements GovernanceRuleInterface
{
    public function __construct(private AgentRegistryInterface $registry)
    {
    }

    public function name(): string
    {
        return 'agent_must_exist';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        if ($this->registry->has($context->agentKey)) {
            return [];
        }

        return [new PolicyViolation($this->name(), 'Agent key is not registered.')];
    }
}
