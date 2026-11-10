<?php
declare(strict_types=1);
namespace App\Tests\Unit;
use App\Application\Service\SimilarityService;
use PHPUnit\Framework\TestCase;

final class SimilarityServiceTest extends TestCase
{
    public function testCosineSimilarity(): void
    {
        $svc = new SimilarityService();
        $a = ['x' => 1.0, 'y' => 1.0];
        $b = ['x' => 1.0, 'y' => 1.0];
        self::assertEqualsWithDelta(1.0, $svc->cosine($a, $b), 0.0001);
    }
}
