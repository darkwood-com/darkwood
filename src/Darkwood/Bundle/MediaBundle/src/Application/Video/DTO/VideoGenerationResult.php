<?php

declare(strict_types=1);

namespace App\Application\Video\DTO;

use App\Domain\Video\VideoProject;

final class VideoGenerationResult
{
    /**
     * @param null|array{json: string, markdown: string} $benchmarkReportPaths
     */
    public function __construct(
        public VideoProject $project,
        public ?string $renderOutputPath = null,
        public ?array $benchmarkReportPaths = null,
        public ?string $scenarioOutputPath = null,
        public ?string $scenarioSkipReason = null,
    ) {}
}
