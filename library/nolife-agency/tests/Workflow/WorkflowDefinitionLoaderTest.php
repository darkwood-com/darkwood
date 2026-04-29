<?php

namespace App\Tests\Workflow;

use App\Workflow\WorkflowDefinitionLoader;
use PHPUnit\Framework\TestCase;

final class WorkflowDefinitionLoaderTest extends TestCase
{
    public function testLoadsClientWebsiteWorkflow(): void
    {
        $loader = new WorkflowDefinitionLoader('/path/to/nolife-agency/config/agency-workflows');
        $definition = $loader->load('client-website');
        self::assertSame('client_website', $definition->key);
        self::assertNotEmpty($definition->steps);
    }
}
