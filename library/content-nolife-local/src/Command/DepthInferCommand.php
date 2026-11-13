<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DepthAnythingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[AsCommand(name: 'app:depth-infer', description: 'Run local depth inference on an image file')]
final class DepthInferCommand extends Command
{
    public function __construct(
        private readonly DepthAnythingService $depthAnything,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('image', 'i', InputOption::VALUE_REQUIRED, 'Path to input image');
        $this->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'Model variant: q4_k or f32', 'q4_k');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('ffi.enable', '1');

        $path = (string) $input->getOption('image');
        if (!is_file($path)) {
            $output->writeln('<error>Image not found.</error>');

            return Command::FAILURE;
        }

        $upload = new UploadedFile($path, basename($path), test: true);
        $result = $this->depthAnything->process($upload, (string) $input->getOption('model'));

        $output->writeln(sprintf(
            'OK id=%s %dx%d infer=%.1fms total=%.1fms load=%.1fms glb=var/inference/%s/scene.glb',
            $result->id,
            $result->width,
            $result->height,
            $result->durationMs,
            $result->totalPipelineMs,
            $result->loadMs,
            $result->id,
        ));

        foreach ($result->pipelineTimings as $step => $ms) {
            $output->writeln(sprintf('  %s: %.2fms', $step, $ms));
        }

        return Command::SUCCESS;
    }
}
