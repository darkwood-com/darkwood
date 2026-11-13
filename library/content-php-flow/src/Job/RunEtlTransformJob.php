<?php

declare(strict_types=1);

namespace App\Job;

use App\Etl\EtlExecution;
use App\Etl\Rows;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Layer 2 orchestration → Layer 1 transform stage.
 *
 * @implements JobInterface<EtlExecution, EtlExecution>
 */
final class RunEtlTransformJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
    ) {}

    public function __invoke($data): EtlExecution
    {
        $this->io->section('[transform] normalize email, hash enrichment');
        $start = hrtime(true);

        $rows = $data->flow->pipeline()->transform($data->rows ?? new Rows(), $data->context);

        $this->io->writeln(sprintf(
            '  transform done in %.2f ms — %d → %d row(s)',
            (hrtime(true) - $start) / 1e6,
            $data->rows?->count() ?? 0,
            $rows->count()
        ));

        return new EtlExecution(
            $data->batch,
            $data->flow,
            $data->context,
            $rows,
            $rows->count(),
        );
    }
}
