<?php

declare(strict_types=1);

namespace App\Job;

use App\Etl\EtlExecution;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Layer 2 orchestration → Layer 1 extract stage.
 *
 * @implements JobInterface<EtlExecution, EtlExecution>
 */
final class RunEtlExtractJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
    ) {}

    public function __invoke($data): EtlExecution
    {
        $this->io->section(sprintf('[extract] batch %s (%d rows)', $data->batch->id, \count($data->batch->rows)));
        $start = hrtime(true);

        $rows = $data->flow->pipeline()->extract($data->context);

        $this->io->writeln(sprintf(
            '  extract done in %.2f ms — %d row(s)',
            (hrtime(true) - $start) / 1e6,
            $rows->count()
        ));

        return new EtlExecution(
            $data->batch,
            $data->flow,
            $data->context,
            $rows,
        );
    }
}
