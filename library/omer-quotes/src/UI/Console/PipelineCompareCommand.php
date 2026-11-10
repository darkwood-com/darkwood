<?php
declare(strict_types=1);
namespace App\UI\Console;
use App\Application\Port\StoragePort;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:pipeline:compare')]
final class PipelineCompareCommand extends Command
{
    public function __construct(private readonly StoragePort $storage) { parent::__construct(); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reports = $this->storage->readAll('reports');
        $messenger = array_values(array_filter($reports, fn (array $r) => ($r['orchestration'] ?? '') === 'messenger'));
        $flow = array_values(array_filter($reports, fn (array $r) => ($r['orchestration'] ?? '') === 'flow'));
        $latestMsg = end($messenger) ?: ['steps' => [], 'errors' => []];
        $latestFlow = end($flow) ?: ['steps' => [], 'errors' => []];
        $comparison = [
            'messenger_steps' => count($latestMsg['steps']),
            'flow_steps' => count($latestFlow['steps']),
            'messenger_errors' => count($latestMsg['errors']),
            'flow_errors' => count($latestFlow['errors']),
        ];
        $this->storage->write('reports', 'comparison_latest', $comparison);
        $output->writeln(json_encode($comparison, JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}
