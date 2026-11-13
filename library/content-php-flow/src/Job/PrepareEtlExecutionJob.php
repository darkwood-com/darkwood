<?php

declare(strict_types=1);

namespace App\Job;

use App\Etl\EtlExecution;
use App\Etl\FlowContext;
use App\Etl\OrderEtlFlow;
use App\Model\OrderBatch;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Layer 2 (darkwood/flow): prepare Layer 1 ETL execution from an incoming batch.
 *
 * @implements JobInterface<OrderBatch, EtlExecution>
 */
final class PrepareEtlExecutionJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
        private readonly string $outputDir,
    ) {}

    public function __invoke($data): EtlExecution
    {
        $this->io->section(sprintf('[prepare] ETL flow for batch %s', $data->id));

        $context = new FlowContext($this->outputDir, $data);

        return new EtlExecution(
            $data,
            OrderEtlFlow::create(),
            $context,
        );
    }
}
