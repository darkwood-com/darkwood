<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OidcDiscoveryTest extends WebTestCase
{
    public function testDiscoveryAdvertisesHeroClipCompatibleCapabilities(): void
    {
        $client = self::createClient();
        $client->request('GET', '/.well-known/openid-configuration');

        self::assertResponseIsSuccessful();
        $metadata = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['S256'], $metadata['code_challenge_methods_supported']);
        self::assertContains('authorization_code', $metadata['grant_types_supported']);
        self::assertContains('openid', $metadata['scopes_supported']);
        self::assertStringEndsWith('/userinfo', $metadata['userinfo_endpoint']);
    }
}
