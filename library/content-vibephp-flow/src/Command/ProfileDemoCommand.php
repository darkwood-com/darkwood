<?php

declare(strict_types=1);

namespace App\Command;

use App\Console\BlackfireSignalHandler;
use App\Console\InMemoryQueue;
use App\Flow\VibeRequestFlowFactory;
use Flow\Ip;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:flow:profile-demo',
    description: 'VibePHP request worker — resolve, read, prompt, execute, log',
)]
final class ProfileDemoCommand extends Command implements SignalableCommandInterface
{
    private bool $shouldStop = false;

    public function __construct(
        private readonly VibeRequestFlowFactory $flowFactory,
        private readonly BlackfireSignalHandler $blackfireHandler,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Process N messages then exit', null)
            ->addOption('sleep', 's', InputOption::VALUE_REQUIRED, 'Idle seconds when queue is empty', '1');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = null !== $input->getOption('limit') ? (int) $input->getOption('limit') : null;
        $sleep = (int) $input->getOption('sleep');
        $queue = new InMemoryQueue($this->projectDir.'/data/requests.jsonl');
        $processed = 0;

        $io->title('VibePHP Flow — request execution worker');
        $io->writeln('Press Ctrl+C to stop gracefully. Use kill -USR2 <pid> to toggle Blackfire profiling.');

        while (!$this->shouldStop) {
            if (null !== $limit && $processed >= $limit) {
                $io->note(sprintf('Limit of %d message(s) reached.', $limit));
                break;
            }

            $request = $queue->next();
            if (null === $request) {
                $io->writeln(sprintf('<comment>Queue empty, sleeping %ds…</comment>', $sleep));
                sleep($sleep);
                continue;
            }

            $iterationStart = hrtime(true);

            $io->writeln(sprintf(
                '<info>Processing %s %s</info>',
                $request->method,
                $request->path
            ));

            $flow = $this->flowFactory->create($io);
            $flow(new Ip($request));
            $flow->await();

            ++$processed;
            $io->writeln(sprintf(
                'Iteration %d finished in %.2f ms (peak memory: %s)',
                $processed,
                (hrtime(true) - $iterationStart) / 1e6,
                $this->formatBytes(memory_get_peak_usage(true))
            ));
        }

        $io->success(sprintf('Worker stopped after %d request(s).', $processed));

        return Command::SUCCESS;
    }

    public function getSubscribedSignals(): array
    {
        return $this->blackfireHandler->getSubscribedSignals();
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        return $this->blackfireHandler->handleSignal($signal, function (string $reason): void {
            $this->shouldStop = true;
        });
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        return round($bytes / 1024 / 1024, 2).' MB';
    }
}
