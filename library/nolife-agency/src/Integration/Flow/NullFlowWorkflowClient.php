<?php

namespace App\Integration\Flow;

class NullFlowWorkflowClient implements FlowClientInterface
{
    public function name(): string
    {
        return 'null';
    }

    public function dispatch(WorkflowDispatch $dispatch): WorkflowResult
    {
        return new WorkflowResult(
            workflowKey: $dispatch->workflowKey,
            runId: $dispatch->runId,
            finalState: 'human_approval_required',
            completedSteps: ['qualify_lead', 'define_architecture', 'generate_content', 'optimize_seo_geo', 'prepare_media_brief', 'review_delivery', 'request_human_approval'],
            outputArtifactPaths: [],
            status: 'noop',
            metadata: [
                'payload' => $dispatch->payload,
                'context' => $dispatch->context,
                'workflow_steps' => $dispatch->workflowSteps,
            ],
        );
    }
}
