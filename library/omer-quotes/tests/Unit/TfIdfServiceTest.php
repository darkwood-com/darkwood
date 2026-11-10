<?php
declare(strict_types=1);
namespace App\Tests\Unit;
use App\Application\Service\TfIdfService;
use PHPUnit\Framework\TestCase;

final class TfIdfServiceTest extends TestCase
{
    public function testTfIdfTinyCorpus(): void
    {
        $svc = new TfIdfService();
        $result = $svc->compute(['d1' => ['apple', 'apple', 'banana'], 'd2' => ['banana', 'carrot']]);
        self::assertSame(2, $result['df']['banana']);
        self::assertGreaterThan($result['idf']['banana'], $result['idf']['apple']);
        self::assertArrayHasKey('apple', $result['vectors']['d1']);
    }
}
