<?php

declare(strict_types=1);

namespace App\Tests\Good;

use App\Model\DocumentRequest;
use App\Model\ExcerptResult;
use App\Port\InMemoryDocumentSource;
use App\Workflow\ExcerptWorkflowFactory;
use App\Workflow\FlowCollector;
use Flow\Driver\AmpDriver;
use Flow\Driver\FiberDriver;
use Flow\DriverInterface;
use Flow\Ip;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Same behavioral oracle, two runtimes.
 * If this file stays green after a driver swap, you tested business logic — not furniture.
 */
final class MultiDriverBehaviorTest extends TestCase
{
    /**
     * @param DriverInterface<mixed, mixed> $driver
     */
    #[DataProvider('provideDrivers')]
    public function testSameExcerptUnderDifferentDrivers(DriverInterface $driver): void
    {
        $html = (string) file_get_contents(dirname(__DIR__, 2) . '/fixtures/article.html');
        $source = new InMemoryDocumentSource(['fixture://article' => $html]);
        $flow = (new ExcerptWorkflowFactory())->create($driver, $source, maxExcerptLength: 120);

        $result = FlowCollector::run($flow, new Ip(new DocumentRequest('fixture://article')));

        $this->assertInstanceOf(ExcerptResult::class, $result);
        $this->assertSame('Darkwood Flow Notes', $result->title);
        $this->assertStringContainsString('Jobs transform information packets', $result->excerpt);
        $this->assertStringEndsWith('…', $result->excerpt);
    }

    /**
     * @return iterable<string, array{DriverInterface<mixed, mixed>}>
     */
    public static function provideDrivers(): iterable
    {
        yield 'fiber' => [new FiberDriver()];
        yield 'amp' => [new AmpDriver()];
    }
}
