<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\AiConnection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AiConnectionControllerTest extends WebTestCase
{
    public function testFormPersistsEncryptedCredentialWithoutPlaintext(): void
    {
        $client = static::createClient();
        $this->resetSchema();

        $plaintext = 'sk-ant-test-plaintext-key-do-not-store';

        $crawler = $client->request('GET', '/connections/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Store encrypted key')->form([
            'ai_connection_input[label]' => 'Lab Claude',
            'ai_connection_input[provider]' => 'anthropic',
            'ai_connection_input[model]' => 'claude-sonnet-4-5',
            'ai_connection_input[apiKey]' => $plaintext,
        ]);
        $client->submit($form);
        self::assertResponseRedirects('/');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $em->getRepository(AiConnection::class)->findOneBy(['label' => 'Lab Claude']);
        self::assertInstanceOf(AiConnection::class, $connection);
        self::assertStringNotContainsString($plaintext, $connection->getCiphertextBlob());

        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'encrypted');
        self::assertSelectorTextContains('body', 'Sodium');
        self::assertStringNotContainsString($plaintext, $client->getResponse()->getContent() ?: '');
    }

    private function resetSchema(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }
}
