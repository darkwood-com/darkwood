<?php

declare(strict_types=1);

namespace App\Tests\NoLifeModels;

use App\NoLifeModels\Domain\Export\ExportFormat;
use App\NoLifeModels\Domain\Model\InferenceMetrics;
use App\NoLifeModels\Domain\Model\ModelInferenceResult;
use App\NoLifeModels\Domain\Model\PromptComparisonResult;
use App\NoLifeModels\Infrastructure\Export\MultiFormatResultExporter;
use PHPUnit\Framework\TestCase;

final class MultiFormatResultExporterTest extends TestCase
{
    public function testJsonExportSchema(): void
    {
        $exporter = new MultiFormatResultExporter();
        $comparison = new PromptComparisonResult(
            'abc',
            'Hello',
            null,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
            [
                new ModelInferenceResult('m1', 'r1', null, new InferenceMetrics(1.0, 0.5, 0.1, 0.2, 0.3, 10, 20, 40.0, 'stop'), ['eval_count' => 20]),
                new ModelInferenceResult('m2', 'r2', 'boom', new InferenceMetrics(2.0, null, null, null, null, null, null, null, null), null),
            ],
        );
        $json = $exporter->exportPromptComparison($comparison, ExportFormat::Json);
        $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('no_life_models.comparison', $data['schema']);
        self::assertSame(1, $data['version']);
        self::assertCount(2, $data['models']);
        self::assertSame('boom', $data['models'][1]['error']);
    }
}
