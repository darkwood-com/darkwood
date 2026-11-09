<?php

namespace App\Mario\Application\Command;

use App\Mario\Application\Orchestration\MarioLevelOrchestrator;
use App\Mario\Infrastructure\Export\GodotLevelJsonExporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:mario:generate-level', description: 'Generate a Godot-friendly inspired platformer level.')]
final class GenerateMarioLevelCommand extends Command
{
    public function __construct(
        private readonly MarioLevelOrchestrator $orchestrator,
        private readonly GodotLevelJsonExporter $exporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('reference', null, InputOption::VALUE_REQUIRED, 'Design grammar reference', 'world-1-1')
            ->addOption('target', null, InputOption::VALUE_REQUIRED, 'Target engine', 'godot')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Output path', 'var/mario-levels/world_1_1_inspired.json')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format', 'json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $reference = (string) $input->getOption('reference');
        $target = (string) $input->getOption('target');
        $format = (string) $input->getOption('format');
        $outputPath = (string) $input->getOption('output');

        $io->title('Mario Orchestration');
        $io->text('orchestration started');
        $io->text('agents executed');

        $result = $this->orchestrator->generate($reference, $target);
        $validation = $result['validation'];

        $io->text($validation->valid() ? 'validation result: valid' : 'validation result: invalid');

        if (!$validation->valid()) {
            $io->error($validation->errors());

            return Command::FAILURE;
        }

        if ('json' !== $format) {
            $io->error(sprintf('Unsupported format "%s". Only json is supported.', $format));

            return Command::FAILURE;
        }

        $this->exporter->write($result['payload'], $outputPath);
        $io->success(sprintf('output path: %s', $outputPath));

        return Command::SUCCESS;
    }
}
