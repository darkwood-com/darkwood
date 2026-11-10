<?php

declare(strict_types=1);

namespace App\Tests\Bad;

use App\Model\DocumentRequest;
use App\Model\ExcerptResult;
use App\Port\InMemoryDocumentSource;
use App\Workflow\ExcerptWorkflowFactory;
use App\Workflow\FlowCollector;
use Flow\Driver\FiberDriver;
use Flow\Ip;
use PHPUnit\Framework\TestCase;

/**
 * NO-LIFE TEST — coverage theater.
 *
 * Asserts shape, not meaning. An AI agent loves this: high coverage, zero oracle.
 * Change the excerpt algorithm to return "lorem ipsum" and this still passes.
 */
final class CoverageTheaterTest extends TestCase
{
    public function testWorkflowReturnsSomethingShapedLikeAResult(): void
    {
        $html = (string) file_get_contents(dirname(__DIR__, 2) . '/fixtures/article.html');
        $source = new InMemoryDocumentSource(['fixture://article' => $html]);
        $flow = (new ExcerptWorkflowFactory())->create(new FiberDriver(), $source);

        $result = FlowCollector::run($flow, new Ip(new DocumentRequest('fixture://article')));

        $this->assertInstanceOf(ExcerptResult::class, $result);
        $this->assertNotNull($result->title);
        $this->assertNotNull($result->excerpt);
        $this->assertIsString($result->excerpt);
        // Never asked: is the excerpt faithful to the document?
    }
}
