<?php

declare(strict_types=1);

namespace App\Command;

use App\Io\FlowIoRunner;
use App\Io\PollRunner;
use App\Io\SequentialRunner;
use App\Io\StreamSelectRunner;
use App\Model\AgentId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'thanks-agent:bench-io', description: 'Compare sequential vs stream_select vs Poll vs Flow I/O')]
final class BenchIoCommand extends Command
{
    public function __construct(
        private readonly SequentialRunner $sequential,
        private readonly StreamSelectRunner $streamSelect,
        private readonly PollRunner $poll,
        private readonly FlowIoRunner $flowIo,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('without-thanks', null, InputOption::VALUE_NONE, 'Exclude ThanksAgent from the task set')
            ->addOption('save', null, InputOption::VALUE_NONE, 'Write JSON under var/reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $withThanks = !$input->getOption('without-thanks');
        $tasks = $this->buildTasks($withThanks);

        $io->title('I/O orchestration bench');
        $io->writeln('Same tasks through four runners. Orchestration guarantees execution, not value.');
        $io->writeln(sprintf('Tasks: %d (ThanksAgent: %s)', count($tasks), $withThanks ? 'included' : 'excluded'));
        $io->newLine();

        $results = [
            $this->sequential->run($tasks),
            $this->streamSelect->run($tasks),
            $this->poll->run($tasks),
            $this->flowIo->run($withThanks),
        ];

        $rows = [];
        foreach ($results as $result) {
            $rows[] = [
                $result->runner,
                sprintf('%.1f', $result->wallTimeMs),
                sprintf('%.1f', $result->busyTimeMs),
                sprintf('%.1f', $result->idleTimeMs),
                (string) $result->tasksCompleted,
                (string) $result->totalValue,
                sprintf('%.6f', $result->totalCost),
                (string) $result->totalMessages,
                isset($result->extra['skipped']) ? 'SKIPPED' : 'ok',
            ];
        }

        $io->table(
            ['Runner', 'Wall ms', 'Busy ms', 'Idle ms', 'Tasks', 'Value', 'Cost', 'Messages', 'Status'],
            $rows,
        );

        if (!$this->poll->isAvailable()) {
            $io->note('PHP 8.6 Io\\Poll is unavailable on '.PHP_VERSION.'; poll runner skipped (fallback documented).');
        }

        $io->warning('ThanksAgent goes through the same scheduler and still scores Value +0.');

        if ($input->getOption('save')) {
            $dir = $this->projectDir.'/var/reports';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $path = $dir.'/bench-io-'.date('Ymd-His').'.json';
            file_put_contents($path, json_encode(array_map(
                static fn ($r) => $r->toArray(),
                $results,
            ), JSON_PRETTY_PRINT));
            $io->success('Saved '.$path);
        }

        return Command::SUCCESS;
    }

    /**
     * @return list<array{id: string, delay: float, value: int, messages: int}>
     */
    private function buildTasks(bool $withThanks): array
    {
        $tasks = [
            ['id' => AgentId::FetchDocumentation->value, 'delay' => 0.12, 'value' => 25, 'messages' => 3],
            ['id' => AgentId::Github->value, 'delay' => 0.15, 'value' => 40, 'messages' => 4],
            ['id' => AgentId::Php->value, 'delay' => 0.10, 'value' => 30, 'messages' => 2],
            ['id' => AgentId::Mozilla->value, 'delay' => 0.11, 'value' => 20, 'messages' => 2],
            ['id' => AgentId::Cost->value, 'delay' => 0.06, 'value' => 15, 'messages' => 2],
        ];

        if ($withThanks) {
            $tasks[] = ['id' => AgentId::Thanks->value, 'delay' => 0.04, 'value' => 0, 'messages' => 28];
        }

        return $tasks;
    }
}
