<?php

declare(strict_types=1);

namespace App\Flow;

use App\Job\PrepareEtlExecutionJob;
use App\Job\RunEtlExtractJob;
use App\Job\RunEtlLoadJob;
use App\Job\RunEtlTransformJob;
use App\Model\OrderBatch;
use Flow\FlowFactory;
use Flow\FlowInterface as DarkwoodFlowInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Layer 2: darkwood/flow orchestrates Layer 1 ETL stages (App\Etl\FlowInterface).
 */
final class OrderEtlFlowFactory
{
    public function __construct(
        private readonly FlowFactory $flowFactory,
        private readonly string $projectDir,
    ) {}

    /**
     * @return DarkwoodFlowInterface<OrderBatch>
     */
    public function create(SymfonyStyle $io): DarkwoodFlowInterface
    {
        $outputDir = $this->projectDir.'/var/output';

        return $this->flowFactory->create(static function () use ($io, $outputDir) {
            yield new PrepareEtlExecutionJob($io, $outputDir);
            yield new RunEtlExtractJob($io);
            yield new RunEtlTransformJob($io);
            yield new RunEtlLoadJob($io);
            yield static function (array $result) use ($io): array {
                $io->section('[summary] ETL pipeline complete');
                $io->success(sprintf(
                    'Loaded %d order(s) to %s',
                    $result['count'],
                    $result['file']
                ));

                return $result;
            };
        });
    }
}
