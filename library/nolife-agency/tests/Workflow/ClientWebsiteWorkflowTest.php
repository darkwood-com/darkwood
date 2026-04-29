<?php

namespace App\Tests\Workflow;

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
use App\Workflow\ClientWebsiteWorkflow;
use App\Workflow\WorkflowDefinitionLoader;
use App\Workflow\WorkflowDefinitionValidator;
use PHPUnit\Framework\TestCase;

final class ClientWebsiteWorkflowTest extends TestCase
{
    public function testReturnsValidatedWorkflow(): void
    {
        $registry = new ConfiguredAgentRegistry([
            new CeoAgent(), new CtoAgent(), new ProductOwnerAgent(), new DeliveryManagerAgent(),
            new ArchitectAgent(), new DeveloperAgent(), new ContentAgent(), new SeoGeoSpecialistAgent(),
            new MediaMotionAgent(), new QaAgent(),
        ]);
        $workflow = new ClientWebsiteWorkflow(
            new WorkflowDefinitionLoader('/path/to/nolife-agency/config/agency-workflows'),
            new WorkflowDefinitionValidator($registry),
        );
        self::assertSame('client_website', $workflow->definition()->key);
    }
}
