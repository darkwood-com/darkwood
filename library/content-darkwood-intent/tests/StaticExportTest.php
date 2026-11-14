<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\IntentExamples;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DomCrawler\Crawler;

final class StaticExportTest extends KernelTestCase
{
    public function testExportHasSixPagesAndWorksAtBothBasePaths(): void
    {
        self::bootKernel();
        $tester = new CommandTester((new Application(self::$kernel))->find('app:export-static'));
        foreach (['', '/darkwood-intent'] as $prefix) {
            $tester->execute(['--base-path' => $prefix]);
            self::assertSame(Command::SUCCESS, $tester->getStatusCode());
            $files = ['index.html'];
            foreach (array_keys(IntentExamples::DEMOS) as $slug) {
                $files[] = 'demo/'.$slug.'/index.html';
            }
            foreach ($files as $file) {
                $html = file_get_contents(self::$kernel->getProjectDir().'/dist/'.$file);
                $crawler = new Crawler($html);
                self::assertCount(1, $crawler->filter('body.theme-light[data-mode="static"]'));
                self::assertCount(1, $crawler->filter('header img[alt="Darkwood"]'));
                self::assertCount(1, $crawler->filter('#theme-switcher'));
                self::assertStringContainsString('href="'.$prefix.'/style.css"', $html);
                self::assertStringContainsString('href="'.$prefix.'/demo/heroclip/"', $html);
                self::assertStringNotContainsString('method="post"', strtolower($html));
                self::assertStringNotContainsString('app_secret', strtolower($html));
            }
            self::assertFileExists(self::$kernel->getProjectDir().'/dist/intent-model.js');
            self::assertFileExists(self::$kernel->getProjectDir().'/dist/aftertrace/teaser.mp4');
        }
        self::assertFileDoesNotExist(self::$kernel->getProjectDir().'/dist/index.php');
        self::assertFileDoesNotExist(self::$kernel->getProjectDir().'/dist/.env');
    }

    public function testExportRejectsUnsafeUrlPrefixes(): void
    {
        self::bootKernel();
        $tester = new CommandTester((new Application(self::$kernel))->find('app:export-static'));
        foreach (['/../private', '//external.example', '/x?token=foo', 'https://example.org'] as $path) {
            $tester->execute(['--base-path' => $path]);
            self::assertSame(Command::INVALID, $tester->getStatusCode());
        }
    }
}
