<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IntentDemosTest extends WebTestCase
{
    public function testAllPagesExposeThePedagogicalChain(): void
    {
        $client = static::createClient();
        foreach (['/', '/demo/darkwood', '/demo/flow', '/demo/uniflow', '/demo/darkwaar', '/demo/heroclip', '/demo/djmatyo'] as $url) {
            $client->request('GET', $url);
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('h1');
            self::assertSelectorTextNotContains('body', 'R&D');
            self::assertSelectorTextNotContains('body', 'Atelier associé');
            self::assertSelectorTextNotContains('body', 'Référentiel');
        }

        $client->request('GET', '/demo/darkwood');
        self::assertSelectorTextContains('.workbench', 'Créer un jeu');
        self::assertSelectorTextContains('.workbench', 'Orchestrer un traitement');
        self::assertSelectorTextContains('.workbench', 'ne connecte pas les services');

        $client->request('GET', '/demo/flow');
        self::assertSelectorTextContains('.sidebar-nav a[aria-current] span', 'Flow');
        self::assertSelectorTextContains('.demo-heading', 'Composer et exécuter des traitements');
        self::assertSelectorTextContains('.workbench', 'Minimiser la complexité');
        self::assertSelectorTextContains('.workbench', 'Plusieurs opérations I/O indépendantes');
        self::assertSelectorTextContains('.workbench', 'Simulation, pas benchmark');
        self::assertSelectorTextNotContains('.workbench', 'OpenSwoole est validé');
        $model = file_get_contents(static::getContainer()->getParameter('kernel.project_dir').'/public/intent-model.js');
        self::assertStringContainsString('PHP inline', $model);
        self::assertStringContainsString('Amp / Revolt (H1)', $model);
        self::assertStringContainsString("service: 'Darkwaar'", $model);

        $client->request('GET', '/demo/heroclip');
        self::assertSelectorTextContains('.workbench', 'Aucune génération vidéo');
        $client->request('GET', '/demo/djmatyo');
        self::assertSelectorTextContains('.workbench', 'sans morceau, sample tiers');
    }
}
