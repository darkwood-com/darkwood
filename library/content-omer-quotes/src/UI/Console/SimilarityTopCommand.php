<?php
declare(strict_types=1);
namespace App\UI\Console;
use App\Application\Service\PipelineService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:similarity:top')]
final class SimilarityTopCommand extends Command
{
    public function __construct(private readonly PipelineService $pipeline) { parent::__construct(); }
    protected function configure(): void
    {
        $this->addArgument('repoId', InputArgument::REQUIRED)->addArgument('top', InputArgument::OPTIONAL, default: 5);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = $this->pipeline->topSimilarity((string) $input->getArgument('repoId'), (int) $input->getArgument('top'));
        $output->writeln(json_encode($rows, JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}
