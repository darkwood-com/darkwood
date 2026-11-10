<?php

declare(strict_types=1);

namespace App\Tests\Good;

use App\Model\DocumentRequest;
use App\Model\ExcerptResult;
use App\Port\InMemoryDocumentSource;
use App\Workflow\ExcerptWorkflowFactory;
use App\Workflow\FlowCollector;
use Flow\Driver\FiberDriver;
use Flow\Ip;
use PHPUnit\Framework\TestCase;

/**
 * Behavioral workflow test: input document → observable excerpt.
 * Oracle = business outcome. Driver is an implementation detail.
 */
final class WorkflowBehaviorTest extends TestCase
{
    public function testExcerptWorkflowProducesFaithfulResult(): void
    {
        $html = (string) file_get_contents(dirname(__DIR__, 2) . '/fixtures/article.html');
        $source = new InMemoryDocumentSource(['fixture://article' => $html]);
        $flow = (new ExcerptWorkflowFactory())->create(
            new FiberDriver(),
            $source,
            maxExcerptLength: 120,
            minExcerptLength: 20,
        );

        $result = FlowCollector::run($flow, new Ip(new DocumentRequest('fixture://article')));

        $this->assertInstanceOf(ExcerptResult::class, $result);
        $this->assertSame('Darkwood Flow Notes', $result->title);
        $this->assertStringContainsString('Jobs transform information packets', $result->excerpt);
        $this->assertStringContainsString('Drivers schedule', $result->excerpt);
        $this->assertLessThanOrEqual(121, mb_strlen($result->excerpt)); // 120 + ellipsis
        $this->assertSame('fixture://article', $result->uri);
    }

    public function testExcerptWorkflowFailsWhenDocumentIsTooThin(): void
    {
        $source = new InMemoryDocumentSource([
            'fixture://thin' => '<html><head><title>X</title></head><body><p>Hi</p></body></html>',
        ]);
        $flow = (new ExcerptWorkflowFactory())->create(new FiberDriver(), $source);
        $errors = [];

        $flow->fn([
            'job' => static fn (mixed $data): mixed => $data,
            'errorJob' => static function (\Flow\ExceptionInterface $exception) use (&$errors): void {
                $errors[] = $exception->getPrevious()?->getMessage() ?? $exception->getMessage();
            },
        ]);

        ($flow)(new Ip(new DocumentRequest('fixture://thin')));
        $flow->await();

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('too short', $errors[0]);
    }
}
