<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:depth-cleanup', description: 'Remove old inference run directories from var/inference/')]
final class DepthCleanupCommand extends Command
{
    public function __construct(
        private readonly string $inferenceDir,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('keep', 'k', InputOption::VALUE_REQUIRED, 'Number of newest runs to retain', '20')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List directories that would be removed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keep = max(0, (int) $input->getOption('keep'));
        $dryRun = (bool) $input->getOption('dry-run');

        if (!is_dir($this->inferenceDir)) {
            $output->writeln('No inference directory.');

            return Command::SUCCESS;
        }

        $dirs = [];
        foreach (scandir($this->inferenceDir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..' || str_contains($entry, '/')) {
                continue;
            }
            $path = $this->inferenceDir.'/'.$entry;
            if (!is_dir($path)) {
                continue;
            }
            $real = realpath($path);
            $root = realpath($this->inferenceDir);
            if ($real === false || $root === false || !str_starts_with($real, $root.DIRECTORY_SEPARATOR)) {
                continue;
            }
            $dirs[$real] = filemtime($real) ?: 0;
        }

        arsort($dirs);
        $toRemove = array_slice(array_keys($dirs), $keep);

        if ($toRemove === []) {
            $output->writeln(sprintf('Nothing to remove (%d run(s) retained).', count($dirs)));

            return Command::SUCCESS;
        }

        foreach ($toRemove as $path) {
            if ($dryRun) {
                $output->writeln('would remove: '.$path);
                continue;
            }
            $this->filesystem->remove($path);
            $output->writeln('removed: '.$path);
        }

        $output->writeln(sprintf(
            'Cleanup complete: removed %d, kept %d.',
            count($toRemove),
            min($keep, count($dirs)),
        ));

        return Command::SUCCESS;
    }
}
