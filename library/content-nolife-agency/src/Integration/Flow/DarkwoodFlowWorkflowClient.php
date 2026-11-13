<?php

namespace App\Integration\Flow;

final class DarkwoodFlowWorkflowClient implements FlowWorkflowClient
{
    public function name(): string
    {
        return 'darkwood_flow';
    }

    public function dispatch(WorkflowDispatch $dispatch): WorkflowResult
    {
        error_log('Dispatching workflow to Flow');
        $steps = $this->resolveSteps($dispatch);
        $state = 'lead_qualified';
        $completed = [];
        foreach ($steps as $step) {
            $stepKey = (string) ($step['key'] ?? '');
            if ($stepKey === '') {
                continue;
            }
            $state = (string) ($step['to'] ?? $state);
            $completed[] = $stepKey;
        }

        $result = new WorkflowResult(
            workflowKey: $dispatch->workflowKey,
            runId: $dispatch->runId,
            finalState: $state,
            completedSteps: $completed,
            outputArtifactPaths: [],
            status: 'completed',
            metadata: [
                'bridge' => 'darkwood_flow_php',
                'step_count' => count($completed),
            ],
        );
        error_log('Flow workflow completed');

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function resolveSteps(WorkflowDispatch $dispatch): array
    {
        if ($dispatch->workflowSteps !== []) {
            return array_values(array_filter($dispatch->workflowSteps, 'is_array'));
        }

        return [
            ['key' => 'qualify_lead', 'to' => 'brief_created'],
            ['key' => 'define_architecture', 'to' => 'architecture_defined'],
            ['key' => 'generate_content', 'to' => 'content_generated'],
            ['key' => 'optimize_seo_geo', 'to' => 'seo_optimized'],
            ['key' => 'prepare_media_brief', 'to' => 'media_brief_created'],
            ['key' => 'review_delivery', 'to' => 'delivery_reviewed'],
            ['key' => 'request_human_approval', 'to' => 'human_approval_required'],
        ];
    }
}
