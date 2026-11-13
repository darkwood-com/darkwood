<?php

declare(strict_types=1);

namespace App\Tests\Bad;

use App\Model\DocumentRequest;
use App\Port\InMemoryDocumentSource;
use App\Workflow\ExcerptWorkflowFactory;
use App\Workflow\FlowCollector;
use Flow\Driver\FiberDriver;
use Flow\DriverInterface;
use Flow\Ip;
use Flow\JobInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * NO-LIFE TEST — couples the suite to the execution engine.
 *
 * Counting await() calls proves the driver was exercised, not that
 * the workflow produced a correct excerpt. ReactPHP does not need your tests.
 */
final class RuntimeCoupledTest extends TestCase
{
    public function testItWaitsOnTheDriverExactlyOnce(): void
    {
        $inner = new FiberDriver();
        $counter = new stdClass();
        $counter->awaitCalls = 0;

        $driver = new class($inner, $counter) implements DriverInterface {
            public function __construct(
                private DriverInterface $inner,
                private stdClass $counter,
            ) {}

            public function async(\Closure|JobInterface $callback): \Closure
            {
                return $this->inner->async($callback);
            }

            public function defer(\Closure $callback): mixed
            {
                return $this->inner->defer($callback);
            }

            public function await(array &$stream): void
            {
                ++$this->counter->awaitCalls;
                $this->inner->await($stream);
            }

            public function delay(float $seconds): void
            {
                $this->inner->delay($seconds);
            }

            public function tick(float $interval, \Closure $callback): \Closure
            {
                return $this->inner->tick($interval, $callback);
            }
        };

        $html = (string) file_get_contents(dirname(__DIR__, 2) . '/fixtures/article.html');
        $source = new InMemoryDocumentSource(['fixture://article' => $html]);
        $flow = (new ExcerptWorkflowFactory())->create($driver, $source);

        $result = FlowCollector::run($flow, new Ip(new DocumentRequest('fixture://article')));

        $this->assertSame(1, $counter->awaitCalls);
        $this->assertNotNull($result);
        // Green even if title/excerpt content is nonsense — runtime was “tested.”
    }
}
