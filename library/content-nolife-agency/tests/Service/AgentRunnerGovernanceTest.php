<?php

namespace App\Tests\Service;

use App\Agent\Builtin\DeveloperAgent;
use App\Agent\Runtime\AgentOutput;
use App\Agent\Registry\ConfiguredAgentRegistry;
use App\Governance\Audit\FilesystemAuditLogger;
use App\Governance\Policy\DefaultPolicyEngine;
use App\Governance\Rule\AgentMustExistRule;
use App\Governance\Rule\ArtifactPathMustStayInsideRunDirectoryRule;
use App\Governance\Rule\DeliveryRequiresHumanApprovalRule;
use App\Governance\Rule\DeliverySeparationOfDutiesRule;
use App\Governance\Rule\HumanApprovalRequiredRule;
use App\Governance\Rule\NoDirectCrossRepoCliCouplingRule;
use App\Integration\Ai\NullAiAgentClient;
use App\Integration\Flow\NullFlowWorkflowClient;
use App\Integration\Media\NullMediaGenerationClient;
use App\Integration\Navi\NullNaviTraceClient;
use App\Integration\Ai\AiAgentClient;
use App\Integration\Ai\AiAgentRequest;
use App\Integration\Ai\AiAgentResponse;
use App\Service\AgentRunner;
use App\Workflow\ClientWebsiteWorkflow;
use App\Workflow\WorkflowDefinitionLoader;
use App\Workflow\WorkflowDefinitionValidator;
use PHPUnit\Framework\TestCase;

final class AgentRunnerGovernanceTest extends TestCase
{
    public function testPolicyFailurePreventsExecution(): void
    {
        $tmpDir = sys_get_temp_dir().'/nolife-audit-'.uniqid('', true);
        $registry = new ConfiguredAgentRegistry([new DeveloperAgent()]);
        $engine = new DefaultPolicyEngine([
            new AgentMustExistRule($registry),
            new DeliverySeparationOfDutiesRule(),
            new NoDirectCrossRepoCliCouplingRule(),
            new HumanApprovalRequiredRule(),
            new DeliveryRequiresHumanApprovalRule(),
            new ArtifactPathMustStayInsideRunDirectoryRule(),
        ]);

        $runner = new AgentRunner(
            $registry,
            new FilesystemAuditLogger($tmpDir),
            $engine,
            new NullNaviTraceClient(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullMediaGenerationClient(),
            new ClientWebsiteWorkflow(
                new WorkflowDefinitionLoader(dirname(__DIR__, 2).'/config/agency-workflows'),
                new WorkflowDefinitionValidator($registry),
            ),
            $tmpDir.'/runs',
        );

        $this->expectException(\RuntimeException::class);
        $runner->runSingle('developer', ['x' => 1], [
            'requester' => 'tester',
            'metadata' => ['action' => 'deploy', 'approved_by_human' => false],
        ]);
    }

    public function testRejectedPolicyPreventsAiAdapterInvocation(): void
    {
        $tmpDir = sys_get_temp_dir().'/nolife-audit-'.uniqid('', true);
        $registry = new ConfiguredAgentRegistry([new DeveloperAgent()]);
        $engine = new DefaultPolicyEngine([new HumanApprovalRequiredRule()]);
        $fakeAi = new class implements AiAgentClient {
            public int $calls = 0;
            public function name(): string
            {
                return 'fake';
            }

            public function run(AiAgentRequest $request): AiAgentResponse
            {
                $this->calls++;

                return AiAgentResponse::fromAgentOutput(new AgentOutput(['ok' => true]));
            }
        };

        $runner = new AgentRunner(
            $registry,
            new FilesystemAuditLogger($tmpDir),
            $engine,
            new NullNaviTraceClient(),
            $fakeAi,
            new NullFlowWorkflowClient(),
            new NullMediaGenerationClient(),
            new ClientWebsiteWorkflow(
                new WorkflowDefinitionLoader(dirname(__DIR__, 2).'/config/agency-workflows'),
                new WorkflowDefinitionValidator($registry),
            ),
            $tmpDir.'/runs',
        );

        try {
            $runner->runSingle('developer', ['x' => 1], [
                'requester' => 'tester',
                'metadata' => ['action' => 'deploy', 'approved_by_human' => false],
            ]);
            self::fail('Expected runtime exception for policy rejection.');
        } catch (\RuntimeException) {
            self::assertSame(0, $fakeAi->calls);
        }
    }

    public function testApprovedExecutionEmitsAuditJsonl(): void
    {
        $tmpDir = sys_get_temp_dir().'/nolife-audit-'.uniqid('', true);
        $registry = new ConfiguredAgentRegistry([new DeveloperAgent()]);
        $runner = new AgentRunner(
            $registry,
            new FilesystemAuditLogger($tmpDir),
            new DefaultPolicyEngine([new AgentMustExistRule($registry)]),
            new NullNaviTraceClient(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullMediaGenerationClient(),
            new ClientWebsiteWorkflow(
                new WorkflowDefinitionLoader(dirname(__DIR__, 2).'/config/agency-workflows'),
                new WorkflowDefinitionValidator($registry),
            ),
            $tmpDir.'/runs',
        );

        $runner->runSingle('developer', ['x' => 1], ['run_id' => 'run-approved', 'requester' => 'tester']);
        $auditPath = $tmpDir.'/agent-runs/run-approved.jsonl';
        self::assertFileExists($auditPath);
        $content = (string) file_get_contents($auditPath);
        self::assertStringContainsString('agent.run.completed', $content);
        self::assertStringContainsString('run-approved', $content);
    }
}
