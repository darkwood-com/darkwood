<?php

declare(strict_types=1);

namespace App\Tests\Bad;

use App\Model\DocumentRequest;
use App\Model\ExcerptDraft;
use App\Model\ExcerptResult;
use App\Model\ParsedDocument;
use App\Model\RawDocument;
use Flow\Flow\Flow;
use Flow\Ip;
use Flow\JobInterface;
use PHPUnit\Framework\TestCase;

/**
 * NO-LIFE TEST — looks rigorous, asserts nothing about excerpt quality.
 *
 * It only verifies that mocked jobs were called in a certain order.
 * Swap ParseJob for a broken implementation and this suite stays green.
 */
final class ImplementationCoupledTest extends TestCase
{
    public function testItCallsJobsInExpectedOrder(): void
    {
        $request = new DocumentRequest('fixture://article');
        $raw = new RawDocument($request->uri, '<html></html>');
        $parsed = new ParsedDocument($request->uri, 'T', 'Body text that is long enough for validation rules here.');
        $draft = new ExcerptDraft($request->uri, 'T', 'Body text that is long enough for validation rules here.');
        $result = new ExcerptResult($request->uri, 'T', 'Body text that is long enough for validation rules here.');

        $fetch = $this->createMock(JobInterface::class);
        $parse = $this->createMock(JobInterface::class);
        $excerpt = $this->createMock(JobInterface::class);
        $validate = $this->createMock(JobInterface::class);

        $fetch->expects($this->once())->method('__invoke')->with($request)->willReturn($raw);
        $parse->expects($this->once())->method('__invoke')->with($raw)->willReturn($parsed);
        $excerpt->expects($this->once())->method('__invoke')->with($parsed)->willReturn($draft);
        $validate->expects($this->once())->method('__invoke')->with($draft)->willReturn($result);

        $flow = (new Flow($fetch))
            ->fn($parse)
            ->fn($excerpt)
            ->fn($validate);

        ($flow)(new Ip($request));
        $flow->await();

        // Green. We never asserted what the excerpt *means*.
        $this->assertTrue(true);
    }
}
