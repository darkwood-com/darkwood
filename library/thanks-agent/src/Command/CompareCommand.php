<?php

declare(strict_types=1);

namespace App\Command;

use App\Orchestration\AgentPipelineFactory;
use App\Instrumentation\Scoreboard;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'thanks-agent:compare', description: 'Compare 5 useful agents vs 5+ThanksAgent on Flow')]
final class CompareCommand extends Command
{
    public function __construct(
        private readonly AgentPipelineFactory $pipeline,
        private readonly Scoreboard $scoreboard,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('with-thanks', null, InputOption::VALUE_NONE, 'Include the ThanksAgent (value +0)')
            ->addOption('both', null, InputOption::VALUE_NONE, 'Run without then with ThanksAgent and diff')
            ->addOption('driver', null, InputOption::VALUE_REQUIRED, 'fiber|stream_select', 'fiber')
            ->addOption('concurrency', null, InputOption::VALUE_REQUIRED, 'MaxIpStrategy concurrency', '6')
            ->addOption('save', null, InputOption::VALUE_NONE, 'Write JSON report under var/reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $driver = (string) $input->getOption('driver');
        $concurrency = max(1, (int) $input->getOption('concurrency'));
        $save = (bool) $input->getOption('save');

        if ($input->getOption('both')) {
            $without = $this->pipeline->compare(false, $driver, $concurrency);
            $with = $this->pipeline->compare(true, $driver, $concurrency);

            $io->section('Without ThanksAgent');
            $this->scoreboard->render($io, $without);

            $io->section('With ThanksAgent');
            $this->scoreboard->render($io, $with);

            $io->section('Delta (with − without)');
            $io->listing([
                sprintf('Wall time: %+.2f ms', $with->wallTimeMs - $without->wallTimeMs),
                sprintf('Cost: %+.6f', $with->totalCost - $without->totalCost),
                sprintf('Messages: %+d', $with->totalMessages - $without->totalMessages),
                sprintf('Tokens: %+d', $with->totalTokens - $without->totalTokens),
                sprintf('Value: %+d (should be 0)', $with->totalValue - $without->totalValue),
            ]);

            if ($save) {
                $this->save('compare-without', $without->toArray());
                $this->save('compare-with', $with->toArray());
                $this->save('compare-delta', [
                    'wallTimeMs' => $with->wallTimeMs - $without->wallTimeMs,
                    'cost' => $with->totalCost - $without->totalCost,
                    'messages' => $with->totalMessages - $without->totalMessages,
                    'tokens' => $with->totalTokens - $without->totalTokens,
                    'value' => $with->totalValue - $without->totalValue,
                ]);
                $io->success('Reports saved under var/reports/');
            }

            return Command::SUCCESS;
        }

        $withThanks = (bool) $input->getOption('with-thanks');
        $report = $this->pipeline->compare($withThanks, $driver, $concurrency);
        $this->scoreboard->render($io, $report);

        if ($save) {
            $name = $withThanks ? 'compare-with-thanks' : 'compare-five';
            $this->save($name, $report->toArray());
            $io->success(sprintf('Report saved: var/reports/%s-*.json', $name));
        }

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function save(string $prefix, array $data): void
    {
        $dir = $this->projectDir.'/var/reports';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = sprintf('%s/%s-%s.json', $dir, $prefix, date('Ymd-His'));
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
