<?php
declare(strict_types=1);
namespace App\UI\Console;
use App\Application\Service\PipelineService;
use App\Domain\Pipeline\ProcessingReport;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:features:build')]
final class FeaturesBuildCommand extends Command
{
    public function __construct(private readonly PipelineService $pipeline) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $report = new ProcessingReport(uniqid('feat_', true), 'direct');
        $this->pipeline->buildFeatures($report);
        $this->pipeline->persistReport($report);
        $output->writeln('Features complete');
        return Command::SUCCESS;
    }
}
