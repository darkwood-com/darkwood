<?php

namespace App\Workflow;

use App\Agent\Contract\AgentRegistryInterface;

final readonly class WorkflowDefinitionValidator
{
    private const REQUIRED_STATES = [
        'lead_qualified',
        'brief_created',
        'architecture_defined',
        'content_generated',
        'seo_optimized',
        'media_brief_created',
        'delivery_reviewed',
        'human_approval_required',
    ];

    public function __construct(
        private AgentRegistryInterface $registry,
    ) {
    }

    public function validate(WorkflowDefinition $definition): void
    {
        $stateNames = array_map(static fn (WorkflowState $s): string => $s->name, $definition->states);
        foreach (self::REQUIRED_STATES as $requiredState) {
            if (!in_array($requiredState, $stateNames, true)) {
                throw new WorkflowSchemaViolation(sprintf('Missing required state "%s".', $requiredState));
            }
        }

        foreach ($definition->steps as $step) {
            if (!$this->registry->has($step->agentKey)) {
                throw new WorkflowSchemaViolation(sprintf('Unknown agent key "%s" in step "%s".', $step->agentKey, $step->key));
            }
            if (!in_array($step->fromState, $stateNames, true) || !in_array($step->toState, $stateNames, true)) {
                throw new WorkflowSchemaViolation(sprintf('Unknown transition state in step "%s".', $step->key));
            }
        }
    }
}
