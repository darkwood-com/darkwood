<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MemoryDemosTest extends WebTestCase
{
    public function testAllPagesAndDecisionLookup(): void
    {
        $client = static::createClient();
        foreach (['/', '/demo/darkwood', '/demo/flow', '/demo/uniflow', '/demo/darkwaar', '/demo/djmatyo', '/demo/heroclip'] as $url) {
            $client->request('GET', $url);
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('h1');
        }
        $client->request('GET', '/demo/darkwood?id=MEM-001');
        self::assertSelectorTextContains('#decision', 'v2');
        self::assertSelectorTextContains('#decision', 'Provenance');
        $client->request('GET', '/demo/darkwood?id=absent');
        self::assertSelectorNotExists('#decision');
        self::assertSelectorTextContains('.workbench [role=status]', 'Aucune décision');
    }

    public function testFlowPersistsAcrossRequestsAndValidatesInput(): void
    {
        $client = static::createClient();
        $client->request('GET', '/demo/flow');
        $client->submitForm('Conserver le résultat', ['form[n]' => 10]);
        self::assertResponseRedirects('/demo/flow', 303);
        $client->followRedirect();
        self::assertSelectorTextContains('.memory-lanes article:nth-child(3)', '55');
        $client->request('GET', '/demo/flow');
        self::assertSelectorTextContains('.memory-lanes article:nth-child(3)', '55');
        self::assertSelectorTextContains('.memory-lanes article:nth-child(2)', '1 calcul · 2 lectures');
        $client->submitForm('Conserver le résultat', ['form[n]' => -1]);
        self::assertResponseStatusCodeSame(422);
        $client->request('GET', '/demo/flow');
        $client->submitForm('Effacer le résultat');
        $client->followRedirect();
        self::assertSelectorTextContains('.memory-lanes article:nth-child(3)', 'Pas encore conservé');
    }

    public function testWorkflowResumesSameRunWithoutReplayingSteps(): void
    {
        $client = static::createClient();
        $client->request('GET', '/demo/uniflow');
        $client->submitForm('Lancer et mettre en pause');
        $crawler = $client->followRedirect();
        $id = $crawler->filter('.run .tags span')->first()->text();
        self::assertSelectorCount(2, '.run li');
        $client->submitForm('Reprendre la dernière exécution');
        $crawler = $client->followRedirect();
        self::assertSame($id, $crawler->filter('.run .tags span')->first()->text());
        self::assertSelectorCount(3, '.run li');
        self::assertSelectorTextContains('.run', 'Terminée');
        self::assertSelectorTextContains('.run', '25 points');
        self::assertSelectorNotExists('#form_resume');
        $client->submitForm('Lancer et mettre en pause');
        $client->followRedirect();
        self::assertSelectorCount(2, '.run');
    }

    public function testGameKnowledgeSurvivesWorldReset(): void
    {
        $client = static::createClient();
        $client->request('GET', '/demo/darkwaar');
        $client->submitForm('Lune');
        $client->followRedirect();
        self::assertSelectorTextContains('.room h2', 'reste close');
        $client->submitForm('Lire l’inscription');
        $client->followRedirect();
        $client->submitForm('Revenir au début');
        $client->followRedirect();
        self::assertSelectorTextContains('#knowledge', 'La lune ouvre');
        self::assertSelectorTextContains('.room', 'BOUCLE 1');
        $client->submitForm('Lune');
        $client->followRedirect();
        self::assertSelectorTextContains('.room h2', 's’ouvre');
        $client->submitForm('Tout oublier');
        $client->followRedirect();
        self::assertSelectorTextContains('#knowledge', 'encore rien appris');
    }

    public function testStoryboardContinuityCanBeDisabled(): void
    {
        $client = static::createClient();
        $client->request('GET', '/demo/heroclip');
        $client->submitForm('Créer le plan suivant', ['form[character]' => 'Ada', 'form[palette]' => 'vert']);
        $client->followRedirect();
        $client->submitForm('Créer le plan suivant');
        $client->followRedirect();
        self::assertSelectorCount(2, '.scene');
        self::assertSelectorTextContains('.scene:nth-child(2)', 'Ada · vert · lanterne');
        self::assertSelectorTextContains('.scene:nth-child(2)', 'CONTEXTE RAPPELÉ');
        $client->submitForm('Créer le plan suivant', ['form[memory]' => false]);
        $client->followRedirect();
        self::assertSelectorTextContains('.scene:nth-child(3)', 'Voyageur · bleu · boussole');
        $client->submitForm('Recommencer le storyboard');
        $client->followRedirect();
        self::assertSelectorCount(0, '.scene');
    }

    public function testPostWithoutCsrfTokenDoesNotMutateMemory(): void
    {
        $client = static::createClient();
        $client->request('POST', '/demo/flow', ['form' => ['n' => 10, 'save' => '']]);
        self::assertResponseStatusCodeSame(422);
        $client->request('GET', '/demo/flow');
        self::assertSelectorTextContains('.memory-lanes article:nth-child(3)', 'Pas encore conservé');
    }

    public function testNewVisitorDoesNotInheritAnotherSession(): void
    {
        $client = static::createClient();
        $client->request('GET', '/demo/flow');
        $client->submitForm('Conserver le résultat', ['form[n]' => 10]);
        $client->followRedirect();
        self::assertSelectorTextContains('.memory-lanes article:nth-child(3)', '55');
        $client->getCookieJar()->clear();
        $client->request('GET', '/demo/flow');
        self::assertSelectorTextContains('.memory-lanes article:nth-child(3)', 'Pas encore conservé');
    }
}
