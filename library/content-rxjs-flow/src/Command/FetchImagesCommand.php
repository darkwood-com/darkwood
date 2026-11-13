<?php

declare(strict_types=1);

namespace App\Command;

use App\Demo\FlowPipelineRunner;
use App\Demo\LocalFixtureServer;
use App\Demo\Trace;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fetch-images',
    description: 'Fetch→Hash→Save via darkwood/flow StreamSelectDriver + MaxIpStrategy',
)]
final class FetchImagesCommand extends Command
{
    public function __construct(
        private readonly FlowPipelineRunner $runner,
        private readonly string $projectDir,
        private readonly string $outputDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('concurrency', 'c', InputOption::VALUE_REQUIRED, 'MaxIpStrategy limit on fetch stage', '4')
            ->addOption('timeout', 't', InputOption::VALUE_REQUIRED, 'Per-request timeout in seconds', '5')
            ->addOption('base-url', null, InputOption::VALUE_REQUIRED, 'Base URL (default: spawn local fixture server)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $concurrency = max(1, (int) $input->getOption('concurrency'));
        $timeout = max(0.1, (float) $input->getOption('timeout'));
        $baseUrl = $input->getOption('base-url');

        $server = null;
        if (!is_string($baseUrl) || $baseUrl === '') {
            $server = new LocalFixtureServer(
                documentRoot: $this->projectDir.'/fixtures',
                port: 8765,
            );
            $server->start();
            $baseUrl = $server->baseUrl();
            $io->note(sprintf('Started fixture server at %s', $baseUrl));
        }

        $baseUrl = rtrim($baseUrl, '/');
        $urls = [
            $baseUrl.'/image-a.png',
            $baseUrl.'/image-b.png',
            $baseUrl.'/image-c.png',
            $baseUrl.'/image-d.png',
            $baseUrl.'/image-e.png',
            $baseUrl.'/fail.png',
        ];

        $this->cleanOutputDir();

        $trace = new Trace($output);
        try {
            $summary = $this->runner->run(
                urls: $urls,
                concurrency: $concurrency,
                timeoutSeconds: $timeout,
                trace: $trace,
            );
        } finally {
            $server?->stop();
        }

        $io->newLine();
        $io->writeln(json_encode($summary->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');

        return $summary->failed > 0 && $summary->ok === 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function cleanOutputDir(): void
    {
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0775, true);

            return;
        }

        foreach (glob($this->outputDir.'/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
