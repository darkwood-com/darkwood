<?php

namespace App\Tests\Service;

use App\Agent\Builtin\CeoAgent;
use App\Agent\Registry\ConfiguredAgentRegistry;
use App\Governance\Audit\FilesystemAuditLogger;
use App\Governance\Policy\DefaultPolicyEngine;
use App\Integration\Ai\NullAiAgentClient;
use App\Integration\Flow\NullFlowWorkflowClient;
use App\Integration\Media\NullMediaGenerationClient;
use App\Integration\Navi\NullNaviTraceClient;
use App\Service\AgentRunner;
use App\Workflow\ClientWebsiteWorkflow;
use App\Workflow\WorkflowDefinitionLoader;
use App\Workflow\WorkflowDefinitionValidator;
use PHPUnit\Framework\TestCase;

final class AgentRunnerBasicTest extends TestCase
{
    public function testCanExecuteSimpleRegisteredAgent(): void
    {
        $tmpDir = sys_get_temp_dir().'/nolife-audit-'.uniqid('', true);
        $runner = new AgentRunner(
            $registry = new ConfiguredAgentRegistry([new CeoAgent()]),
            new FilesystemAuditLogger($tmpDir),
            new DefaultPolicyEngine([]),
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

        $output = $runner->runSingle('ceo', ['brief' => 'x'], ['requester' => 'tester']);

        self::assertSame('ceo', $output->payload['agent']);
    }
}
