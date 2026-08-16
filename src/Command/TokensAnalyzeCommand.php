<?php

declare(strict_types=1);

namespace App\Command;

use App\Flow\TokenPipelineFactory;
use App\Token\ContextPacket;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tokens:analyze',
    description: 'Classify, optimize, and measure a context file through the token pipeline',
)]
final class TokensAnalyzeCommand extends Command
{
    public function __construct(
        private readonly TokenPipelineFactory $pipelineFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to a context fixture or raw file')
            ->addOption('signals', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Expected signal substrings (repeatable)', [])
            ->addOption('granularity', null, InputOption::VALUE_REQUIRED, 'Reference granularity: coarse|fine', 'coarse')
            ->addOption('show-optimized', null, InputOption::VALUE_NONE, 'Print optimized content');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = (string) $input->getArgument('file');
        $granularity = (string) $input->getOption('granularity');
        if (!in_array($granularity, ['coarse', 'fine'], true)) {
            $io->error('granularity must be coarse or fine');

            return Command::FAILURE;
        }

        if (!is_readable($file)) {
            $io->error(sprintf('Cannot read file: %s', $file));

            return Command::FAILURE;
        }

        $rawContent = file_get_contents($file);
        if ($rawContent === false) {
            $io->error(sprintf('Failed to read file: %s', $file));

            return Command::FAILURE;
        }

        /** @var list<string> $cliSignals */
        $cliSignals = $input->getOption('signals');
        $metaSignals = $this->loadMetaSignals($file);
        $signals = $cliSignals !== [] ? $cliSignals : $metaSignals;

        $packet = new ContextPacket($rawContent, $file);
        $packet->expectedSignals = $signals;
        $packet->granularity = $granularity;
        $packet = $this->pipelineFactory->run($packet);

        assert($packet->rawMetrics !== null && $packet->optimizedMetrics !== null && $packet->type !== null);

        $raw = $packet->rawMetrics;
        $opt = $packet->optimizedMetrics;
        $saved = $raw->reductionPercent($opt);

        $io->section('INPUT');
        $io->listing([
            sprintf('bytes: %s', number_format($raw->bytes)),
            sprintf('characters: %s', number_format($raw->characters)),
            sprintf('lines: %s', number_format($raw->lines)),
            sprintf('estimated tokens: %s (approx bytes/4, not billed)', number_format($raw->estimatedTokens)),
            sprintf('type: %s', $packet->type->value),
            sprintf('granularity: %s', $packet->granularity),
        ]);

        $io->section('OPTIMIZED (visible)');
        $io->listing([
            sprintf('bytes: %s', number_format($opt->bytes)),
            sprintf('lines: %s', number_format($opt->lines)),
            sprintf('estimated tokens: %s', number_format($opt->estimatedTokens)),
            sprintf('reduction vs raw: %s%%', $saved),
        ]);

        $io->section('REFERENCES');
        $io->listing([
            sprintf('count: %d', count($packet->references)),
            sprintf('reference marker tokens: %s', number_format($packet->referenceMarkerTokens)),
            sprintf('referenced out-of-context tokens: %s', number_format($packet->referencedTokens)),
            sprintf('effective active context tokens: %s', number_format($opt->estimatedTokens)),
        ]);
        foreach ($packet->references as $ref) {
            $io->writeln(sprintf('  %s  (%s)', $ref->marker(), $ref->reason));
        }

        $io->section('STAGE TRACE');
        foreach ($packet->trace as $row) {
            $parts = [$row['stage']];
            if (isset($row['detail'])) {
                $parts[] = $row['detail'];
            }
            if (isset($row['bytes'])) {
                $parts[] = number_format($row['bytes']) . ' bytes';
            }
            if (isset($row['tokens'])) {
                $parts[] = '~' . number_format($row['tokens']) . ' tokens';
            }
            $io->writeln('  ' . implode('  |  ', $parts));
        }

        if ($packet->expectedSignals !== []) {
            $io->section('IMPORTANT INFORMATION RETAINED');
            if ($packet->retainedHighlights !== []) {
                $io->listing($packet->retainedHighlights);
            }
            if ($packet->missingSignals !== []) {
                $io->warning('Missing signals: ' . implode(' | ', $packet->missingSignals));
            } else {
                $io->success('SIGNAL PASS');
            }
        }

        if ($packet->recoveryPass) {
            $io->success('REF_RECOVERY PASS');
        } else {
            $io->error('REF_RECOVERY FAIL: ' . implode(', ', $packet->missingRecoveries));
        }

        if ($input->getOption('show-optimized')) {
            $io->section('OPTIMIZED CONTENT');
            $io->writeln($packet->optimized);
        }

        return ($packet->signalPass && $packet->recoveryPass) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * @return list<string>
     */
    private function loadMetaSignals(string $file): array
    {
        $metaPath = $file . '.meta.json';
        if (!is_readable($metaPath)) {
            return [];
        }

        $json = file_get_contents($metaPath);
        if ($json === false) {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['signals']) || !is_array($data['signals'])) {
            return [];
        }

        return array_values(array_filter($data['signals'], is_string(...)));
    }
}
