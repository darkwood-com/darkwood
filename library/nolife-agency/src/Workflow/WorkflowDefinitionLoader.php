<?php

namespace App\Workflow;

use Symfony\Component\Yaml\Yaml;

final readonly class WorkflowDefinitionLoader
{
    public function __construct(
        private string $workflowRoot,
    ) {
    }

    public function load(string $workflowKey): WorkflowDefinition
    {
        $path = rtrim($this->workflowRoot, '/').'/'.$workflowKey.'.yaml';
        if (!is_file($path)) {
            throw new WorkflowSchemaViolation(sprintf('Workflow file not found: %s', $path));
        }

        /** @var array<string, mixed> $raw */
        $raw = Yaml::parseFile($path);

        $states = array_map(
            static fn (string $name): WorkflowState => new WorkflowState($name),
            array_values($raw['states'] ?? [])
        );
        $steps = array_map(
            static fn (array $step): WorkflowStep => new WorkflowStep(
                key: (string) ($step['key'] ?? ''),
                agentKey: (string) ($step['agent'] ?? ''),
                fromState: (string) ($step['from'] ?? ''),
                toState: (string) ($step['to'] ?? ''),
            ),
            array_values($raw['steps'] ?? [])
        );

        return new WorkflowDefinition(
            key: (string) ($raw['key'] ?? $workflowKey),
            states: $states,
            steps: $steps,
        );
    }
}
