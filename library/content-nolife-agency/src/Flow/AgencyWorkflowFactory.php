<?php

namespace App\Flow;

use App\Integration\Flow\FlowWorkflowClient;
use App\Integration\Flow\WorkflowDispatch;

final readonly class AgencyWorkflowFactory
{
    public function __construct(
        private FlowWorkflowClient $workflowClient,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function campaignDelivery(array $payload = [], array $context = []): array
    {
        $runId = isset($context['run_id']) ? (string) $context['run_id'] : 'workflow-preview';

        return $this->workflowClient->dispatch(new WorkflowDispatch('campaign-delivery', $runId, $payload, $context))->toArray();
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function clientWebsite(array $payload = [], array $context = []): array
    {
        $runId = isset($context['run_id']) ? (string) $context['run_id'] : 'workflow-preview';

        return $this->workflowClient->dispatch(new WorkflowDispatch('client_website', $runId, $payload, $context))->toArray();
    }
}
