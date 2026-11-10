<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class CoreServicesCliCouplingTest extends TestCase
{
    public function testCoreCodeDoesNotUseForbiddenCliCoupling(): void
    {
        $root = '/path/to/nolife-agency/src';
        $targets = ['Service', 'Integration', 'Governance', 'Agent', 'Workflow'];
        $forbidden = ['shell_exec(', 'system(', 'passthru(', 'proc_open(', 'bin/console', 'Symfony\\Component\\Process\\Process'];

        foreach ($targets as $dir) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$dir));
            foreach ($it as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $content = file_get_contents((string) $file->getPathname()) ?: '';
                foreach ($forbidden as $needle) {
                    self::assertStringNotContainsString($needle, $content, sprintf('Forbidden coupling "%s" in %s', $needle, $file->getPathname()));
                }
            }
        }
    }
}
