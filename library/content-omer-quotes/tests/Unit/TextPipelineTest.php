<?php
declare(strict_types=1);
namespace App\Tests\Unit;
use App\Application\Service\TextPipeline;
use PHPUnit\Framework\TestCase;

final class TextPipelineTest extends TestCase
{
    public function testNormalizeAndTokenize(): void
    {
        $pipeline = new TextPipeline();
        $normalized = $pipeline->normalize('Hello, THE world! Running tests.');
        self::assertSame('hello the world running tests', $normalized);
        self::assertSame(['hello', 'world', 'runn', 'test'], $pipeline->tokenize($normalized));
    }
}
