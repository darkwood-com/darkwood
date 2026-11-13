<?php

namespace App\Tests\Demo;

use App\Demo\ClientWebsiteRunWriter;
use App\Demo\RunDirectory;
use PHPUnit\Framework\TestCase;

final class ClientWebsiteRunWriterTest extends TestCase
{
    public function testWritesRequiredRunFiles(): void
    {
        $runId = 'run-abc';
        $path = sys_get_temp_dir().'/nolife-demo-'.$runId;
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        (new ClientWebsiteRunWriter())->write(
            new RunDirectory($runId, $path),
            ['client' => 'acme-saas'],
            ['status' => 'completed', 'final_state' => 'human_approval_required']
        );

        self::assertFileExists($path.'/brief.json');
        self::assertFileExists($path.'/landing-page.md');
        self::assertFileExists($path.'/seo-geo.md');
        self::assertFileExists($path.'/article-draft.md');
        self::assertFileExists($path.'/media-brief.yaml');
        self::assertFileExists($path.'/trace.json');
        self::assertFileExists($path.'/workflow-result.json');
    }
}
