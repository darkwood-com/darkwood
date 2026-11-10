<?php

declare(strict_types=1);

namespace App\Command;

use App\Bridge\NativeDepthBridge;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:depth-warmup', description: 'Load model and run one warm inference (optional dev/deploy convenience)')]
final class DepthWarmupCommand extends Command
{
    public function __construct(
        private readonly NativeDepthBridge $bridge,
        private readonly string $modelPathQ4k,
        private readonly string $modelPathF32,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('image', 'i', InputOption::VALUE_REQUIRED, 'Path to input image')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Model variant: q4_k or f32', 'q4_k');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('ffi.enable', '1');

        $path = (string) $input->getOption('image');
        if ($path === '') {
            $path = $this->projectDir.'/public/samples/mountains.jpg';
        }
        if (!is_file($path)) {
            $output->writeln('<error>Image not found.</error>');

            return Command::FAILURE;
        }

        $variant = (string) $input->getOption('model');
        $modelPath = $variant === 'f32' ? $this->modelPathF32 : $this->modelPathQ4k;

        $start = hrtime(true);
        $this->bridge->inferDense($modelPath, $path);
        $ms = (hrtime(true) - $start) / 1_000_000;

        $output->writeln(sprintf('Warmup OK variant=%s infer=%.1fms', $variant, $ms));

        return Command::SUCCESS;
    }
}
