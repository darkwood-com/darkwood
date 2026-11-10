<?php

declare(strict_types=1);

namespace App\Job;

use App\Etl\EtlExecution;
use App\Etl\Rows;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Layer 2 orchestration → Layer 1 load stage.
 *
 * @implements JobInterface<EtlExecution, array{file: string, count: int}>
 */
final class RunEtlLoadJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
    ) {}

    public function __invoke($data): array
    {
        $this->io->section('[load] write JSON output');
        $start = hrtime(true);

        $rows = $data->rows ?? new Rows();
        $data->flow->pipeline()->load($rows, $data->context);

        $this->io->writeln(sprintf(
            '  load done in %.2f ms — %d row(s) → %s',
            (hrtime(true) - $start) / 1e6,
            $rows->count(),
            $data->context->outputFile ?? 'n/a'
        ));

        return [
            'file' => $data->context->outputFile ?? '',
            'count' => $rows->count(),
        ];
    }
}
