<?php

declare(strict_types=1);

namespace App\Command;

use App\Flow\TokenPipelineFactory;
use App\Token\ContextPacket;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tokens:benchmark',
    description: 'Run the token pipeline against fixtures and report signal + recovery',
)]
final class TokensBenchmarkCommand extends Command
{
    public function __construct(
        private readonly TokenPipelineFactory $pipelineFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Fixtures directory',
                dirname(__DIR__, 2) . '/fixtures',
            )
            ->addOption('granularity', null, InputOption::VALUE_REQUIRED, 'Default granularity for non-log cases: coarse|fine', 'coarse');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dir = (string) $input->getOption('dir');
        $defaultGranularity = (string) $input->getOption('granularity');
        if (!in_array($defaultGranularity, ['coarse', 'fine'], true)) {
            $io->error('granularity must be coarse or fine');

            return Command::FAILURE;
        }

        if (!is_dir($dir)) {
            $io->error(sprintf('Fixtures directory not found: %s', $dir));

            return Command::FAILURE;
        }

        $metas = glob($dir . '/*.meta.json') ?: [];
        sort($metas);

        if ($metas === []) {
            $io->warning('No *.meta.json fixtures found.');

            return Command::FAILURE;
        }

        $rows = [];
        $allPass = true;

        foreach ($metas as $metaPath) {
            $base = preg_replace('/\.meta\.json$/', '', $metaPath);
            assert(is_string($base));

            if (!is_readable($base)) {
                $io->warning(sprintf('Missing fixture for meta: %s', $metaPath));
                $allPass = false;
                continue;
            }

            $raw = file_get_contents($base);
            $metaJson = file_get_contents($metaPath);
            if ($raw === false || $metaJson === false) {
                $allPass = false;
                continue;
            }

            $meta = json_decode($metaJson, true);
            $signals = [];
            if (is_array($meta) && isset($meta['signals']) && is_array($meta['signals'])) {
                $signals = array_values(array_filter($meta['signals'], is_string(...)));
            }

            $isLog = str_ends_with($base, '.log');
            $modes = $isLog ? ['coarse', 'fine'] : [$defaultGranularity];

            foreach ($modes as $granularity) {
                $packet = new ContextPacket($raw, $base);
                $packet->expectedSignals = $signals;
                $packet->granularity = $granularity;
                $packet = $this->pipelineFactory->run($packet);

                assert($packet->rawMetrics !== null && $packet->optimizedMetrics !== null && $packet->type !== null);

                $saved = $packet->rawMetrics->reductionPercent($packet->optimizedMetrics);
                $signal = $packet->signalPass ? 'PASS' : 'FAIL';
                $recovery = $packet->recoveryPass ? 'PASS' : 'FAIL';
                if (!$packet->signalPass || !$packet->recoveryPass) {
                    $allPass = false;
                }

                $case = basename($base);
                if ($isLog) {
                    $case .= '@' . $granularity;
                }

                $rows[] = [
                    $case,
                    $packet->type->value,
                    number_format($packet->rawMetrics->estimatedTokens),
                    number_format($packet->optimizedMetrics->estimatedTokens),
                    number_format($packet->referenceMarkerTokens),
                    number_format($packet->referencedTokens),
                    sprintf('%.1f%%', $saved),
                    (string) count($packet->references),
                    $signal,
                    $recovery,
                ];
            }
        }

        $io->section('BENCHMARK');
        $io->table(
            ['CASE', 'TYPE', 'RAW', 'VISIBLE', 'MARKERS', 'REFERENCED', 'SAVED', 'REFS', 'SIGNAL', 'RECOVERY'],
            $rows,
        );
        $io->note('Token counts are estimated (bytes/4), not provider-billed tokens. VISIBLE includes #ref markers.');

        return $allPass ? Command::SUCCESS : Command::FAILURE;
    }
}
