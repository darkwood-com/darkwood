<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SmokeTest extends WebTestCase
{
    public function testDashboardOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/dashboard');
        self::assertResponseIsSuccessful();
    }

    public function testHomeRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        self::assertResponseRedirects();
    }

    public function testCatalogPageRenders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/catalog');
        self::assertResponseIsSuccessful();
    }
}
