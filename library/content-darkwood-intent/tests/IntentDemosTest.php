<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IntentDemosTest extends WebTestCase
{
    public function testAllPagesExposeThePedagogicalChain(): void
    {
        $client = static::createClient();
        foreach (['/', '/demo/darkwaar', '/demo/heroclip', '/demo/djmatyo', '/demo/flow', '/demo/uniflow'] as $url) {
            $client->request('GET', $url);
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('h1');
        }

        $client->request('GET', '/demo/flow');
        self::assertSelectorTextContains('.workbench', 'Minimiser la complexité');
        self::assertSelectorTextContains('.workbench', 'Plusieurs opérations I/O indépendantes');
        self::assertSelectorTextContains('.workbench', 'Simulation, pas benchmark');
        self::assertSelectorTextNotContains('.workbench', 'OpenSwoole est validé');
        $model = file_get_contents(static::getContainer()->getParameter('kernel.project_dir').'/public/intent-model.js');
        self::assertStringContainsString('PHP inline', $model);
        self::assertStringContainsString('Amp / Revolt (H1)', $model);

        $client->request('GET', '/demo/heroclip');
        self::assertSelectorTextContains('.workbench', 'Aucune génération vidéo');
        $client->request('GET', '/demo/djmatyo');
        self::assertSelectorTextContains('.workbench', 'sans morceau, sample tiers');
    }
}
