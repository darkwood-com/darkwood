<?php

namespace App\Tests\Demo;

use App\Demo\DeliveryReportWriter;
use App\Demo\RunDirectory;
use PHPUnit\Framework\TestCase;

final class DeliveryReportWriterTest extends TestCase
{
    public function testWritesDeliveryReport(): void
    {
        $runId = 'run-report';
        $path = sys_get_temp_dir().'/nolife-demo-'.$runId;
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        (new DeliveryReportWriter())->write(new RunDirectory($runId, $path), [
            'status' => 'completed',
            'final_state' => 'human_approval_required',
        ]);

        self::assertFileExists($path.'/delivery-report.md');
    }
}
