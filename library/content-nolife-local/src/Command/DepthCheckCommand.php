<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:depth-check', description: 'Verify local depth inference prerequisites')]
final class DepthCheckCommand extends Command
{
    public function __construct(
        private readonly string $libraryPath,
        private readonly string $modelPathQ4k,
        private readonly string $modelPathF32,
        private readonly string $inferenceDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $failures = 0;
        $this->line($output, 'FFI', $this->checkFfi(), $failures);
        $this->line($output, 'GD', extension_loaded('gd') ? 'OK' : 'missing ext-gd', $failures);
        $this->line($output, 'Native library', is_file($this->libraryPath) ? 'OK' : 'not found', $failures);
        $this->line($output, 'q4_k model', is_file($this->modelPathQ4k) ? 'OK' : 'not found', $failures);
        $this->line($output, 'f32 model', is_file($this->modelPathF32) ? 'OK' : 'not found', $failures);
        $this->line($output, 'Inference dir', $this->checkInferenceDir(), $failures);

        $output->writeln('');
        if ($failures === 0) {
            $output->writeln('<info>READY</info>');

            return Command::SUCCESS;
        }

        $output->writeln('<error>NOT READY</error>');

        return Command::FAILURE;
    }

    private function checkFfi(): string
    {
        if (!extension_loaded('ffi')) {
            return 'missing ext-ffi';
        }

        return ini_get('ffi.enable') ? 'OK' : 'disabled (use ffi.enable=1)';
    }

    private function checkInferenceDir(): string
    {
        if (is_dir($this->inferenceDir) && is_writable($this->inferenceDir)) {
            return 'OK';
        }

        if (@mkdir($this->inferenceDir, 0775, true) && is_writable($this->inferenceDir)) {
            return 'OK (created)';
        }

        return 'not writable';
    }

    private function line(OutputInterface $output, string $label, string $status, int &$failures): void
    {
        if (!str_starts_with($status, 'OK')) {
            ++$failures;
        }
        $output->writeln(sprintf('%-18s %s', $label, $status));
    }
}
