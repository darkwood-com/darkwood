<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Export;

use App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport;
use App\NoLifeModels\Domain\Model\PromptComparisonResult;

/**
 * Serializes comparison or benchmark matrix for download.
 */
interface ResultExporterInterface
{
    public function supports(ExportFormat $format): bool;

    /**
     * @throws \InvalidArgumentException when format unsupported
     */
    public function exportPromptComparison(PromptComparisonResult $result, ExportFormat $format): string;

    /**
     * @throws \InvalidArgumentException when format unsupported
     */
    public function exportBenchmarkReport(BenchmarkRunReport $report, ExportFormat $format): string;
}
