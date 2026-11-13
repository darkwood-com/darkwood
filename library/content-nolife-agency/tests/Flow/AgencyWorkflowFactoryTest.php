<?php

namespace App\Tests\Flow;

use App\Flow\AgencyWorkflowFactory;
use App\Integration\Flow\NullFlowWorkflowClient;
use PHPUnit\Framework\TestCase;

final class AgencyWorkflowFactoryTest extends TestCase
{
    public function testClientWebsiteReturnsWorkflowArray(): void
    {
        $factory = new AgencyWorkflowFactory(new NullFlowWorkflowClient());
        $result = $factory->clientWebsite([], ['run_id' => 'run-44']);
        self::assertSame('run-44', $result['run_id']);
    }
}
