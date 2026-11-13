<?php

namespace App\Service;

use App\Agent\Contract\AgentRegistryInterface;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Agent\Runtime\AgentRunId;
use App\Governance\Approval\ApprovalGate;
use App\Integration\Ai\AiAgentClient;
use App\Integration\Ai\AiAgentRequest;
use App\Governance\Audit\AuditContext;
use App\Governance\Audit\AuditEvent;
use App\Governance\Audit\AuditEventType;
use App\Governance\Audit\AuditLoggerInterface;
use App\Governance\Policy\PolicyDecisionType;
use App\Governance\Policy\PolicyEngineInterface;
use App\Integration\Flow\FlowWorkflowClient;
use App\Integration\Flow\WorkflowDispatch;
use App\Integration\Flow\WorkflowResult;
use App\Integration\Media\MediaGenerationClient;
use App\Integration\Media\MediaGenerationRequest;
use App\Integration\Navi\NaviTraceClient;
use App\Workflow\ClientWebsiteWorkflow;

final readonly class AgentRunner
{
    public function __construct(
        private AgentRegistryInterface $registry,
        private AuditLoggerInterface $auditLogger,
        private PolicyEngineInterface $policyEngine,
        private NaviTraceClient $traceClient,
        private AiAgentClient $aiClient,
        private FlowWorkflowClient $flowWorkflowClient,
        private MediaGenerationClient $mediaGenerationClient,
        private ClientWebsiteWorkflow $clientWebsiteWorkflow,
        private string $runRootDir = '',
        private ApprovalGate $approvalGate = new ApprovalGate(),
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $runContext
     */
    public function runSingle(string $agentKey, array $payload, array $runContext): AgentOutput
    {
        $runId = (string) ($runContext['run_id'] ?? AgentRunId::generate()->value);
        $runDir = $this->runDirectory($runId);
        $requester = (string) ($runContext['requester'] ?? 'system');
        $projectKey = isset($runContext['project_key']) ? (string) $runContext['project_key'] : null;
        $inputMetadata = isset($runContext['metadata']) && is_array($runContext['metadata']) ? $runContext['metadata'] : [];
        $inputMetadata['run_dir'] = $runDir;
        $input = new AgentInput($payload, $inputMetadata);

        $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::AgentRunRequested, null, 'Agent run requested.');
        $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::PolicyCheckStarted, null, 'Pre-flight policy checks started.');

        $preContext = new AuditContext($runId, $agentKey, $requester, $projectKey, null);
        $preDecision = $this->policyEngine->evaluate($preContext, $input);
        if ($preDecision->isRejected()) {
            $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::PolicyCheckFailed, null, 'Pre-flight policy checks failed.', $this->violationMeta($preDecision));
            throw new \RuntimeException('Pre-flight policy checks failed.');
        }

        if ($preDecision->requiresApproval()) {
            $decision = $this->approvalGate->evaluate(false, ['High-risk action requires explicit human approval.']);
            $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::ApprovalRejected, null, 'Approval denied.', ['status' => $decision->status->value, 'reasons' => $decision->reasons]);
            throw new \RuntimeException('Approval required before execution.');
        }

        $traceId = $this->traceClient->startOrAttachTrace($runId, $runContext);
        $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::TraceAttached, $traceId, 'Trace attached.');
        $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::AgentRunStarted, $traceId, 'Agent run started.');

        $agent = $this->registry->get($agentKey);
        $context = new AgentContext($runId, $requester, $projectKey, $traceId, $runContext);
        $this->traceClient->recordStep($traceId, 'agent.execution.started', ['agent_key' => $agentKey]);
        $output = $this->aiClient->run(AiAgentRequest::fromAgentExecution($agent, $input, $context))->toAgentOutput();
        $this->traceClient->recordStep($traceId, 'agent.execution.completed', ['agent_key' => $agentKey]);
        $this->writeTraceLink($runDir, $traceId);

        $inputHash = hash('sha256', json_encode($input->payload, \JSON_THROW_ON_ERROR));
        $outputHash = hash('sha256', json_encode($output->payload, \JSON_THROW_ON_ERROR));

        $postContext = new AuditContext($runId, $agentKey, $requester, $projectKey, $traceId);
        $postDecision = $this->policyEngine->evaluate($postContext, $input, $output);
        if ($postDecision->type === PolicyDecisionType::Reject) {
            $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::PolicyCheckFailed, $traceId, 'Post-flight policy checks failed.', $this->violationMeta($postDecision), $inputHash, $outputHash);
            throw new \RuntimeException('Post-flight policy checks failed.');
        }

        $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::PolicyCheckPassed, $traceId, 'Policy checks passed.', [], $inputHash, $outputHash);
        $this->audit($runId, $agentKey, $requester, $projectKey, AuditEventType::AgentRunCompleted, $traceId, 'Agent run completed.', [], $inputHash, $outputHash);
        $this->traceClient->finishTrace($traceId, ['status' => 'completed', 'agent_key' => $agentKey]);

        return $output;
    }

    /**
     * @param array<string, mixed> $inputs
     * @param array<string, mixed> $runContext
     * @return array<string, AgentOutput>
     */
    public function runWorkflow(array $inputs, array $runContext): array
    {
        $workflow = $this->clientWebsiteWorkflow->definition();
        $runId = (string) ($runContext['run_id'] ?? AgentRunId::generate()->value);
        $runContext['run_id'] = $runId;

        $result = $this->flowWorkflowClient->dispatch(new WorkflowDispatch(
            workflowKey: $workflow->key,
            runId: $runId,
            payload: $inputs,
            context: $runContext,
            workflowSteps: array_map(
                static fn ($step): array => [
                    'key' => $step->key,
                    'agent' => $step->agentKey,
                    'from' => $step->fromState,
                    'to' => $step->toState,
                ],
                $workflow->steps
            ),
        ));
        $this->audit($runId, 'workflow', (string) ($runContext['requester'] ?? 'system'), $runContext['project_key'] ?? null, AuditEventType::WorkflowStarted, null, 'Workflow started.', $result->toArray());
        if ($result->finalState !== 'human_approval_required') {
            throw new \RuntimeException(sprintf('Workflow ended in unexpected state "%s".', $result->finalState));
        }

        $results = [];
        foreach ($workflow->steps as $step) {
            $stepInput = $inputs;
            $runContext['metadata']['workflow_state'] = $step->toState;
            $runContext['metadata']['known_workflow_states'] = array_map(static fn ($state): string => $state->name, $workflow->states);
            $results[$step->key] = $this->runSingle($step->agentKey, $stepInput, $runContext);
            $this->audit($runId, $step->agentKey, (string) ($runContext['requester'] ?? 'system'), $runContext['project_key'] ?? null, AuditEventType::WorkflowStateChanged, null, 'Workflow state changed.', ['to_state' => $step->toState, 'step' => $step->key]);
        }

        $this->audit($runId, 'delivery_manager', (string) ($runContext['requester'] ?? 'system'), $runContext['project_key'] ?? null, AuditEventType::DeliveryReviewed, null, 'Delivery reviewed.');
        $this->audit($runId, 'delivery_manager', (string) ($runContext['requester'] ?? 'system'), $runContext['project_key'] ?? null, AuditEventType::HumanApprovalRequired, null, 'Human approval required for final delivery state.');

        return $results;
    }

    public function generateMediaBrief(string $runId, array $payload, array $context = []): array
    {
        return $this->mediaGenerationClient->request(new MediaGenerationRequest(
            kind: 'video_brief',
            runId: $runId,
            payload: $payload,
            context: $context,
        ))->toArray();
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function audit(
        string $runId,
        string $agentKey,
        string $requester,
        ?string $projectKey,
        AuditEventType $type,
        ?string $traceId,
        string $message,
        array $metadata = [],
        ?string $inputHash = null,
        ?string $outputHash = null,
    ): void {
        $this->auditLogger->log(new AuditEvent(
            eventId: bin2hex(random_bytes(8)),
            eventType: $type,
            runId: $runId,
            traceId: $traceId,
            agentKey: $agentKey,
            requester: $requester,
            projectKey: $projectKey,
            timestamp: (new \DateTimeImmutable())->format(\DATE_ATOM),
            inputHash: $inputHash,
            outputHash: $outputHash,
            decision: null,
            message: $message,
            metadata: $metadata,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function violationMeta(\App\Governance\Policy\PolicyDecision $decision): array
    {
        return [
            'decision' => $decision->type->value,
            'violations' => array_map(
                static fn (\App\Governance\Policy\PolicyViolation $violation): array => [
                    'rule' => $violation->rule,
                    'message' => $violation->message,
                ],
                $decision->violations
            ),
        ];
    }

    private function runDirectory(string $runId): string
    {
        $root = $this->runRootDir !== '' ? $this->runRootDir : getcwd().'/var/nolife-agency/runs';
        $dir = rtrim($root, '/').'/'.$runId;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private function writeTraceLink(string $runDir, string $traceId): void
    {
        file_put_contents($runDir.'/trace.json', json_encode(['trace_id' => $traceId], \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
    }
}
