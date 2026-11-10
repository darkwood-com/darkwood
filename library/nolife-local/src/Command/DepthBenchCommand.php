<?php

declare(strict_types=1);

namespace App\Command;

use App\Bridge\NativeDepthBridge;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:depth-bench', description: 'Warm inference benchmark (native inferDense only, same process)')]
final class DepthBenchCommand extends Command
{
    public function __construct(
        private readonly NativeDepthBridge $bridge,
        private readonly string $modelPathQ4k,
        private readonly string $modelPathF32,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('image', 'i', InputOption::VALUE_REQUIRED, 'Path to input image')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Model variant: q4_k or f32', 'q4_k')
            ->addOption('warmup', 'w', InputOption::VALUE_REQUIRED, 'Warmup runs', '1')
            ->addOption('repeat', 'r', InputOption::VALUE_REQUIRED, 'Timed runs', '10')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Emit machine-readable JSON');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('ffi.enable', '1');

        $path = (string) $input->getOption('image');
        if (!is_file($path)) {
            $output->writeln('<error>Image not found.</error>');

            return Command::FAILURE;
        }

        $variant = (string) $input->getOption('model');
        $modelPath = $variant === 'f32' ? $this->modelPathF32 : $this->modelPathQ4k;
        $warmup = max(0, (int) $input->getOption('warmup'));
        $repeat = max(1, (int) $input->getOption('repeat'));

        $processedWidth = 0;
        $processedHeight = 0;
        for ($i = 0; $i < max(1, $warmup); ++$i) {
            $warm = $this->bridge->inferDense($modelPath, $path);
            $processedWidth = $warm['width'];
            $processedHeight = $warm['height'];
        }

        // When warmup was 0, the loop above still ran once to discover resolution;
        // do not count that discovery run in timed samples.
        $discoveryOnly = $warmup === 0;

        $samples = [];
        for ($i = 0; $i < $repeat; ++$i) {
            $start = hrtime(true);
            $this->bridge->inferDense($modelPath, $path);
            $samples[] = (hrtime(true) - $start) / 1_000_000;
        }

        sort($samples);
        $count = count($samples);
        $median = $samples[(int) floor(($count - 1) / 2)];
        $p95 = $samples[(int) floor(0.95 * ($count - 1))];
        $mean = array_sum($samples) / $count;

        if ($input->getOption('json')) {
            $output->writeln(json_encode([
                'variant' => $variant,
                'processed' => ['width' => $processedWidth, 'height' => $processedHeight],
                'warmup' => $warmup,
                'repeat' => $repeat,
                'discoveryRunWhenWarmupZero' => $discoveryOnly,
                'ms' => [
                    'min' => $samples[0],
                    'median' => $median,
                    'mean' => $mean,
                    'p95' => $p95,
                ],
                'boundary' => 'native_infer_dense_only',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            'BENCH variant=%s processed=%dx%d warmup=%d repeat=%d min=%.1fms median=%.1fms mean=%.1fms p95=%.1fms',
            $variant,
            $processedWidth,
            $processedHeight,
            $warmup,
            $repeat,
            $samples[0],
            $median,
            $mean,
            $p95,
        ));

        return Command::SUCCESS;
    }
}
