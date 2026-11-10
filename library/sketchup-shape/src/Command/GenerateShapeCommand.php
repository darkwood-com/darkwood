<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Workflow\WorkflowRunner;
use App\Domain\Execution\Context;
use App\Infrastructure\Action\BuildCodePromptAction;
use App\Infrastructure\Action\BuildDesignPromptAction;
use App\Infrastructure\Action\ExtractDesignSpecAction;
use App\Infrastructure\Action\ExtractScriptAction;
use App\Infrastructure\Action\GenerateDesignAction;
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
        // --- Stage 1: parse ---
        private readonly ParseIntentAction $parseIntent,
        // --- Stage 2: design agent ---
        private readonly BuildDesignPromptAction $buildDesignPrompt,
        private readonly GenerateDesignAction $generateDesign,
        private readonly ExtractDesignSpecAction $extractDesignSpec,
        // --- Stage 3: code agent ---
        private readonly BuildCodePromptAction $buildCodePrompt,
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
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Write the Ruby script to this file path instead of stdout')
            ->addOption('spec', null, InputOption::VALUE_OPTIONAL, 'Also write the JSON design spec to this file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $context = Context::fromArray(['intention' => $input->getArgument('intention')]);

            $result = $this->runner->run($context, [
                // Stage 1 — validate intention
                $this->parseIntent,
                // Stage 2 — design agent: intention → JSON design spec
                $this->buildDesignPrompt,
                $this->generateDesign,
                $this->extractDesignSpec,
                // Stage 3 — code agent: JSON spec → Ruby script
                $this->buildCodePrompt,
                $this->generateRuby,
                $this->extractScript,
            ]);

            $finalContext = $result->state()->context();
            $rubyScript   = (string) $finalContext->get('ruby_script');
            $designSpec   = (string) $finalContext->get('design_spec');

            // --- Output: design spec (optional) ---
            if ($specPath = $input->getOption('spec')) {
                $this->filesystem->dumpFile($specPath, $designSpec);
                $io->success(sprintf('Design spec written to %s', $specPath));
            }

            // --- Output: Ruby script ---
            if ($outputPath = $input->getOption('output')) {
                $this->filesystem->dumpFile($outputPath, $rubyScript);
                $io->success(sprintf('Ruby script written to %s', $outputPath));
            } else {
                $output->writeln($rubyScript);
            }

            // --- Verbose: workflow trace ---
            if ($output->isVerbose()) {
                $io->section('Workflow trace');
                foreach ($result->state()->completedActions() as $actionName) {
                    $io->text(sprintf('  ✓ %s', $actionName->toString()));
                }
                $io->section('Design spec');
                $io->text($designSpec);
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
