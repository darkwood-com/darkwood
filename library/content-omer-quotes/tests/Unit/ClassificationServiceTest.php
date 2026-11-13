<?php
declare(strict_types=1);
namespace App\Tests\Unit;
use App\Application\Service\ClassificationService;
use PHPUnit\Framework\TestCase;

final class ClassificationServiceTest extends TestCase
{
    public function testNaiveBayesBasics(): void
    {
        $svc = new ClassificationService();
        $vectors = ['r1' => ['php' => 1.0], 'r2' => ['game' => 1.0], 'u' => ['php' => 0.9]];
        $labels = ['r1' => 'web', 'r2' => 'game'];
        $result = $svc->classify($vectors, $labels);
        self::assertSame('web', $result['u']['label']);
    }
}
