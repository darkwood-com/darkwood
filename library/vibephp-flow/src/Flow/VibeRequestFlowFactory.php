<?php

declare(strict_types=1);

namespace App\Flow;

use App\Job\BuildPromptJob;
use App\Job\FakeExecuteJob;
use App\Job\LogMetricsJob;
use App\Job\ReadSourceJob;
use App\Job\ResolveScriptJob;
use App\Model\VibeRequest;
use Flow\FlowFactory;
use Flow\FlowInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class VibeRequestFlowFactory
{
    public function __construct(
        private readonly FlowFactory $flowFactory,
        private readonly string $projectDir,
    ) {}

    /**
     * @return FlowInterface<VibeRequest>
     */
    public function create(SymfonyStyle $io): FlowInterface
    {
        $docroot = $this->projectDir.'/vibe';

        return $this->flowFactory->create(static function () use ($io, $docroot) {
            yield new ResolveScriptJob($io, $docroot);
            yield new ReadSourceJob($io, $docroot);
            yield new BuildPromptJob($io);
            yield new FakeExecuteJob($io);
            yield new LogMetricsJob($io);
        });
    }
}
