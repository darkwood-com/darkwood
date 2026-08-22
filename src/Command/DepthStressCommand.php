<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DepthAnythingService;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(name: 'app:depth-stress', description: 'Run repeated in-process inference to validate native result lifecycle')]
final class DepthStressCommand extends Command
{
    public function __construct(
        private readonly DepthAnythingService $depthAnything,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('image', 'i', InputOption::VALUE_REQUIRED, 'Path to input image')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Model variant: q4_k or f32', 'q4_k')
            ->addOption('runs', 'r', InputOption::VALUE_REQUIRED, 'Number of consecutive runs in this PHP process', '10');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('ffi.enable', '1');

        $path = (string) $input->getOption('image');
        if (!is_file($path)) {
            $output->writeln('<error>Image not found.</error>');

            return Command::FAILURE;
        }

        $runs = max(1, (int) $input->getOption('runs'));
        $variant = (string) $input->getOption('model');
        $failures = 0;
        $inferMs = [];
        $totalMs = [];
        $glbMs = [];
        $glbValid = 0;

        for ($i = 1; $i <= $runs; ++$i) {
            try {
                $upload = new UploadedFile($path, basename($path), test: true);
                $result = $this->depthAnything->process($upload, $variant);

                if ($result->glbPath === null || !is_file($result->glbPath)) {
                    throw new RuntimeException('Missing GLB output.');
                }

                $magic = file_get_contents($result->glbPath, false, null, 0, 4);
                if ($magic !== 'glTF') {
                    throw new RuntimeException('Invalid GLB magic bytes.');
                }

                ++$glbValid;
                $infer = $result->durationMs;
                $total = $result->totalPipelineMs;
                $glb = $result->pipelineTimings['glb'] ?? 0.0;
                $inferMs[] = $infer;
                $totalMs[] = $total;
                $glbMs[] = $glb;

                $output->writeln(sprintf(
                    'run %d/%d OK infer=%.1fms total=%.1fms glb=%.2fms',
                    $i,
                    $runs,
                    $infer,
                    $total,
                    $glb,
                ));
            } catch (RuntimeException $e) {
                ++$failures;
                $output->writeln(sprintf('<error>run %d/%d FAIL: %s</error>', $i, $runs, $e->getMessage()));
            }
        }

        if ($inferMs !== []) {
            sort($inferMs);
            sort($glbMs);
            $n = count($inferMs);
            $output->writeln(sprintf(
                'infer ms:  min=%.1f median=%.1f mean=%.1f max=%.1f',
                $inferMs[0],
                $inferMs[(int) floor(($n - 1) / 2)],
                array_sum($inferMs) / $n,
                $inferMs[$n - 1],
            ));
            sort($totalMs);
            $output->writeln(sprintf(
                'total ms: min=%.1f median=%.1f mean=%.1f max=%.1f',
                $totalMs[0],
                $totalMs[(int) floor(($n - 1) / 2)],
                array_sum($totalMs) / $n,
                $totalMs[$n - 1],
            ));
            $output->writeln(sprintf(
                'glb ms:   min=%.2f median=%.2f mean=%.2f max=%.2f',
                $glbMs[0],
                $glbMs[(int) floor(($n - 1) / 2)],
                array_sum($glbMs) / $n,
                $glbMs[$n - 1],
            ));
            $output->writeln(sprintf('GLB validated: %d/%d', $glbValid, $runs));
        }

        if ($failures > 0) {
            $output->writeln(sprintf('<error>Stress test failed: %d/%d runs</error>', $failures, $runs));

            return Command::FAILURE;
        }

        $output->writeln(sprintf('Stress test passed: %d consecutive in-process run(s).', $runs));

        return Command::SUCCESS;
    }
}
