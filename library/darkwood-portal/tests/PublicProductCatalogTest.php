<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\PublicProductCatalog;
use PHPUnit\Framework\TestCase;

use function count;

final class PublicProductCatalogTest extends TestCase
{
    public function testCanonicalPublicProductsAreDistinctAndResolvable(): void
    {
        $catalog = new PublicProductCatalog();
        $products = $catalog->all();

        self::assertCount(count(array_unique(array_column($products, 'id'))), $products);
        self::assertSame('https://flow.darkwood.com/', $catalog->find('Darkwood Flow')['url']);
        self::assertSame('https://uniflow.io/', $catalog->find('uniflow')['url']);
        self::assertSame('https://geo.darkwood.com/', $catalog->find('geo')['url']);
    }
}
