<?php

declare(strict_types=1);

namespace App\Tests\Good;

use App\Job\ExcerptJob;
use App\Job\ParseJob;
use App\Job\ValidateJob;
use App\Model\ExcerptDraft;
use App\Model\ParsedDocument;
use App\Model\RawDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Behavioral unit tests: Jobs are the business. No Flow, no Driver.
 */
final class JobBehaviorTest extends TestCase
{
    public function testParseJobExtractsTitleAndPlainBody(): void
    {
        $html = <<<'HTML'
            <html><head><title>Hello Flow</title></head>
            <body><p>Jobs transform packets.</p><p>Drivers schedule them.</p></body></html>
            HTML;

        $parsed = (new ParseJob())(new RawDocument('doc://1', $html));

        $this->assertSame('Hello Flow', $parsed->title);
        $this->assertSame('Jobs transform packets. Drivers schedule them.', $parsed->body);
    }

    #[DataProvider('provideExcerptCases')]
    public function testExcerptJobRespectsMaxLength(string $body, int $max, string $expected): void
    {
        $draft = (new ExcerptJob($max))(new ParsedDocument('doc://1', 'T', $body));

        $this->assertSame($expected, $draft->excerpt);
    }

    /**
     * @return iterable<string, array{string, int, string}>
     */
    public static function provideExcerptCases(): iterable
    {
        yield 'short text is unchanged' => [
            'Short body.',
            120,
            'Short body.',
        ];

        yield 'long text is truncated on a word boundary' => [
            'Jobs transform information packets into readable excerpts for humans.',
            40,
            'Jobs transform information packets into…',
        ];
    }

    public function testValidateJobRejectsTooShortExcerpt(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('too short');

        (new ValidateJob(20))(new ExcerptDraft('doc://1', 'T', 'too short'));
    }

    public function testValidateJobAcceptsLongEnoughExcerpt(): void
    {
        $result = (new ValidateJob(20))(new ExcerptDraft(
            'doc://1',
            'Title',
            'This excerpt is definitely long enough.',
        ));

        $this->assertSame('Title', $result->title);
        $this->assertSame('This excerpt is definitely long enough.', $result->excerpt);
    }
}
