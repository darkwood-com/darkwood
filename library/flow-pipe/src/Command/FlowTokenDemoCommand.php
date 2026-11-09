<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Pipeline\TokenPipelineService;
use App\TokenFlow\Fixture\FlowFixtureProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:flow-token-demo',
    description: 'Demonstrate stream/chunk token discipline through a Flow pipeline',
)]
final class FlowTokenDemoCommand extends Command
{
    private const DEFAULT_PIPELINE = 'source |> strip_ansi |> remove_noise |> normalize_whitespace |> chunk:300 |> compress |> budget:1000 |> sink';

    public function __construct(
        private readonly TokenPipelineService $pipelineService,
        private readonly FlowFixtureProvider $fixtures,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('pipeline', null, InputOption::VALUE_REQUIRED, 'Pipeline expression', self::DEFAULT_PIPELINE)
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'Fixture key', 'flow-engine-log')
            ->addOption('show-chunks', null, InputOption::VALUE_NONE, 'Display final streamed chunks')
            ->addOption('show-debug', null, InputOption::VALUE_NONE, 'Display pipeline debug trace');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pipelineExpr = (string) $input->getOption('pipeline');
        $inputKey = (string) $input->getOption('input');
        $showChunks = (bool) $input->getOption('show-chunks');
        $showDebug = (bool) $input->getOption('show-debug');

        if (!in_array($inputKey, $this->fixtures->keys(), true)) {
            $io->error(sprintf(
                'Unknown input "%s". Available: %s',
                $inputKey,
                implode(', ', $this->fixtures->keys()),
            ));

            return Command::FAILURE;
        }

        try {
            $result = $this->pipelineService->run($pipelineExpr, $inputKey, $showDebug);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->title('Flow Token Demo');
        $io->writeln(sprintf('<info>Pipeline:</info> %s', $pipelineExpr));
        $io->writeln(sprintf('<info>Input:</info> %s', $inputKey));
        $io->writeln('<info>Orchestrator:</info> darkwood/flow (full pipeline)');
        $io->newLine();

        $io->section('Before');
        $io->listing([
            sprintf('Characters: %s', number_format($result->originalCharCount)),
            sprintf('Estimated tokens: %s', number_format($result->originalTokenEstimate)),
        ]);

        $io->section('After');
        $io->listing([
            sprintf('Characters: %s', number_format($result->compressedCharCount)),
            sprintf('Estimated tokens: %s', number_format($result->compressedTokenEstimate)),
            sprintf('Reduction: %s%%', number_format($result->reductionPercent(), 1)),
        ]);

        $io->section('Executed steps');
        foreach ($result->executedSteps as $index => $stepName) {
            $io->writeln(sprintf('  %d. %s', $index + 1, $stepName));
        }

        if ($showDebug) {
            $io->section('Debug trace');
            foreach ($result->debug as $line) {
                $io->writeln('  '.$line);
            }
        }

        if ($showChunks) {
            $io->section('Final streamed output');
            $chunks = $result->chunks !== [] ? $result->chunks : [$result->stream];
            foreach ($chunks as $index => $chunk) {
                $preview = mb_strlen($chunk) > 120 ? mb_substr($chunk, 0, 117).'…' : $chunk;
                $io->writeln(sprintf('[chunk %d] %s', $index + 1, $preview));
            }
        }

        return Command::SUCCESS;
    }
}
