<?php
declare(strict_types=1);
namespace App\UI\Console;
use App\Application\Service\PipelineService;
use App\Domain\Pipeline\ProcessingReport;
use App\Infrastructure\Orchestration\Flow\PipelineStageAction;
use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\Context;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:pipeline:flow:run')]
final class FlowPipelineRunCommand extends Command
{
    public function __construct(private readonly WorkflowRunner $runner, private readonly PipelineService $pipeline) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = new ProcessingReport(uniqid('flow_', true), 'flow');
        $actions = [
            new PipelineStageAction('ingest', fn (Context $c) => ($this->pipeline->ingest($report) || true) ? $c : $c),
            new PipelineStageAction('preprocess', fn (Context $c) => ($this->pipeline->preprocess($report) || true) ? $c : $c),
            new PipelineStageAction('features', fn (Context $c) => ($this->pipeline->buildFeatures($report) || true) ? $c : $c),
            new PipelineStageAction('classification', fn (Context $c) => ($this->pipeline->classify($report) || true) ? $c : $c),
            new PipelineStageAction('clustering', fn (Context $c) => ($this->pipeline->cluster($report) || true) ? $c : $c),
        ];
        $this->runner->run(Context::fromArray(['runId' => $report->runId]), $actions);
        $this->pipeline->persistReport($report);
        $output->writeln('Flow pipeline complete');
        return Command::SUCCESS;
    }
}
