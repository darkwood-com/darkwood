<?php
declare(strict_types=1);
namespace App\UI\Console;
use App\Application\Service\PipelineService;
use App\Domain\Pipeline\ProcessingReport;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:corpus:preprocess')]
final class CorpusPreprocessCommand extends Command
{
    public function __construct(private readonly PipelineService $pipeline) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = new ProcessingReport(uniqid('pre_', true), 'direct');
        $this->pipeline->preprocess($report);
        $this->pipeline->persistReport($report);
        $output->writeln('Preprocess complete');
        return Command::SUCCESS;
    }
}
