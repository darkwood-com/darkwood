<?php

namespace App\Tests\Command;

use App\Agent\Builtin\ArchitectAgent;
use App\Agent\Builtin\CeoAgent;
use App\Agent\Builtin\ContentAgent;
use App\Agent\Builtin\CtoAgent;
use App\Agent\Builtin\DeliveryManagerAgent;
use App\Agent\Builtin\DeveloperAgent;
use App\Agent\Builtin\MediaMotionAgent;
use App\Agent\Builtin\ProductOwnerAgent;
use App\Agent\Builtin\QaAgent;
use App\Agent\Builtin\SeoGeoSpecialistAgent;
use App\Agent\Registry\ConfiguredAgentRegistry;
use App\Command\DemoClientWebsiteCommand;
use App\Demo\ClientWebsiteDemoInputFactory;
use App\Demo\ClientWebsiteRunWriter;
use App\Demo\DeliveryReportWriter;
use App\Governance\Audit\FilesystemAuditLogger;
use App\Governance\Policy\DefaultPolicyEngine;
use App\Governance\Rule\AgentMustExistRule;
use App\Integration\Ai\NullAiAgentClient;
use App\Integration\Flow\NullFlowWorkflowClient;
use App\Integration\Media\NullMediaGenerationClient;
use App\Integration\Navi\NullNaviTraceClient;
use App\Service\AgentRunner;
use App\Workflow\ClientWebsiteWorkflow;
use App\Workflow\WorkflowDefinitionLoader;
use App\Workflow\WorkflowDefinitionValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class DemoClientWebsiteCommandTest extends TestCase
{
    public function testCommandGeneratesExpectedFiles(): void
    {
        $runRoot = sys_get_temp_dir().'/nolife-command-'.uniqid('', true);
        $registry = new ConfiguredAgentRegistry([
            new CeoAgent(), new CtoAgent(), new ProductOwnerAgent(), new DeliveryManagerAgent(),
            new ArchitectAgent(), new DeveloperAgent(), new ContentAgent(), new SeoGeoSpecialistAgent(),
            new MediaMotionAgent(), new QaAgent(),
        ]);

        $runner = new AgentRunner(
            $registry,
            new FilesystemAuditLogger($runRoot.'/audit'),
            new DefaultPolicyEngine([new AgentMustExistRule($registry)]),
            new NullNaviTraceClient(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullMediaGenerationClient(),
            new ClientWebsiteWorkflow(
                new WorkflowDefinitionLoader('/path/to/nolife-agency/config/agency-workflows'),
                new WorkflowDefinitionValidator($registry),
            ),
            $runRoot,
        );

        $command = new DemoClientWebsiteCommand(
            $runner,
            new ClientWebsiteDemoInputFactory(),
            new ClientWebsiteRunWriter(),
            new DeliveryReportWriter(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullNaviTraceClient(),
            'null',
            $runRoot,
        );
        $tester = new CommandTester($command);
        $exitCode = $tester->execute(['client' => 'acme-saas']);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('run_id:', $tester->getDisplay());
        self::assertStringContainsString('final_state: human_approval_required', $tester->getDisplay());

        preg_match('/output_dir: (.+)$/m', $tester->getDisplay(), $matches);
        self::assertNotEmpty($matches[1] ?? null);
        $outputDir = trim((string) $matches[1]);
        foreach ([
            'brief.json',
            'audit.jsonl',
            'trace.json',
            'workflow-result.json',
            'delivery-report.md',
            'article-draft.md',
            'seo-geo.md',
            'media-brief.yaml',
        ] as $requiredFile) {
            self::assertFileExists($outputDir.'/'.$requiredFile);
        }
    }

    public function testDryRunOptionStillGeneratesPlannedArtifacts(): void
    {
        $runRoot = sys_get_temp_dir().'/nolife-command-'.uniqid('', true);
        $registry = new ConfiguredAgentRegistry([
            new CeoAgent(), new CtoAgent(), new ProductOwnerAgent(), new DeliveryManagerAgent(),
            new ArchitectAgent(), new DeveloperAgent(), new ContentAgent(), new SeoGeoSpecialistAgent(),
            new MediaMotionAgent(), new QaAgent(),
        ]);
        $runner = new AgentRunner(
            $registry,
            new FilesystemAuditLogger($runRoot.'/audit'),
            new DefaultPolicyEngine([new AgentMustExistRule($registry)]),
            new NullNaviTraceClient(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullMediaGenerationClient(),
            new ClientWebsiteWorkflow(
                new WorkflowDefinitionLoader('/path/to/nolife-agency/config/agency-workflows'),
                new WorkflowDefinitionValidator($registry),
            ),
            $runRoot,
        );
        $command = new DemoClientWebsiteCommand(
            $runner,
            new ClientWebsiteDemoInputFactory(),
            new ClientWebsiteRunWriter(),
            new DeliveryReportWriter(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullNaviTraceClient(),
            'null',
            $runRoot,
        );
        $tester = new CommandTester($command);
        self::assertSame(0, $tester->execute(['client' => 'acme-saas', '--dry-run' => true]));
        self::assertStringContainsString('workflow_status: completed', $tester->getDisplay());
    }

    public function testRealAiFailsWithNullAdapter(): void
    {
        $runRoot = sys_get_temp_dir().'/nolife-command-'.uniqid('', true);
        $registry = new ConfiguredAgentRegistry([
            new CeoAgent(), new CtoAgent(), new ProductOwnerAgent(), new DeliveryManagerAgent(),
            new ArchitectAgent(), new DeveloperAgent(), new ContentAgent(), new SeoGeoSpecialistAgent(),
            new MediaMotionAgent(), new QaAgent(),
        ]);
        $runner = new AgentRunner(
            $registry,
            new FilesystemAuditLogger($runRoot.'/audit'),
            new DefaultPolicyEngine([new AgentMustExistRule($registry)]),
            new NullNaviTraceClient(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullMediaGenerationClient(),
            new ClientWebsiteWorkflow(
                new WorkflowDefinitionLoader('/path/to/nolife-agency/config/agency-workflows'),
                new WorkflowDefinitionValidator($registry),
            ),
            $runRoot,
        );
        $command = new DemoClientWebsiteCommand(
            $runner,
            new ClientWebsiteDemoInputFactory(),
            new ClientWebsiteRunWriter(),
            new DeliveryReportWriter(),
            new NullAiAgentClient(),
            new NullFlowWorkflowClient(),
            new NullNaviTraceClient(),
            'null',
            $runRoot,
        );
        $tester = new CommandTester($command);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('--real-ai requires NOLIFE_AI_ADAPTER=symfony_ai');
        $tester->execute(['client' => 'acme-saas', '--real-ai' => true]);
    }
}
