<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Workflow\WorkflowRunner;
use App\Domain\Execution\Context;
use App\Infrastructure\Action\BuildPromptAction;
use App\Infrastructure\Action\ExtractScriptAction;
use App\Infrastructure\Action\GenerateRubyAction;
use App\Infrastructure\Action\ParseIntentAction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate-shape',
    description: 'Generate a SketchUp Ruby script from a natural language shape description',
)]
final class GenerateShapeCommand extends Command
{
    public function __construct(
        private readonly WorkflowRunner $runner,
        private readonly ParseIntentAction $parseIntent,
        private readonly BuildPromptAction $buildPrompt,
        private readonly GenerateRubyAction $generateRuby,
        private readonly ExtractScriptAction $extractScript,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('intention', InputArgument::REQUIRED, 'Natural language description of the shape to generate')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Write the Ruby script to this file path instead of stdout');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $context = Context::fromArray(['intention' => $input->getArgument('intention')]);

            $result = $this->runner->run($context, [
                $this->parseIntent,
                $this->buildPrompt,
                $this->generateRuby,
                $this->extractScript,
            ]);

            $rubyScript = (string) $result->state()->context()->get('ruby_script');

            $outputPath = $input->getOption('output');

            if ($outputPath) {
                $this->filesystem->dumpFile($outputPath, $rubyScript);
                $io->success(sprintf('Ruby script written to %s', $outputPath));
            } else {
                $output->writeln($rubyScript);
            }

            if ($output->isVerbose()) {
                $io->section('Workflow trace');
                foreach ($result->state()->completedActions() as $actionName) {
                    $io->text(sprintf('  ✓ %s', $actionName->toString()));
                }
                foreach ($result->state()->events() as $event) {
                    $io->text(sprintf('  · %s', $event->name()));
                }
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
