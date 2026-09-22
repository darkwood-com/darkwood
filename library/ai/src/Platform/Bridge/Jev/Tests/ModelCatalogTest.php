<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darkwood\AI\Platform\Bridge\Jev\Tests;

use Darkwood\AI\Platform\Bridge\Jev\Jev;
use Darkwood\AI\Platform\Bridge\Jev\ModelCatalog;
use Darkwood\AI\Platform\Capability;
use Darkwood\AI\Platform\Exception\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class ModelCatalogTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string}>
     */
    public static function modelNamesProvider(): iterable
    {
        yield 'jev-latest' => ['jev-latest'];
        yield 'jev-preview' => ['jev-preview'];
        yield 'jev-1.13.0' => ['jev-1.13.0'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('modelNamesProvider')]
    public function testCatalogResolvesJevModels(string $name): void
    {
        $model = (new ModelCatalog())->getModel($name);

        $this->assertInstanceOf(Jev::class, $model);
        $this->assertSame($name, $model->getName());
        $this->assertTrue($model->supports(Capability::INPUT_TEXT));
    }

    public function testUnknownModelThrows(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new ModelCatalog())->getModel('unknown');
    }
}
