<?php

namespace App\Command;

use App\Governance\ApprovalGate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nolife:delivery:review', description: 'Review campaign delivery package.')]
final class DeliveryReviewCommand extends Command
{
    public function __construct(
        private readonly ApprovalGate $approvalGate,
        private readonly string $deliveriesDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('project_slug', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectSlug = (string) $input->getArgument('project_slug');
        $result = $this->approvalGate->review('content', 'delivery_manager');
        $status = $result['approved'] ? 'approved' : 'rejected';

        $dir = rtrim($this->deliveriesDir, '/').'/'.$projectSlug;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.'/delivery-report.md';
        file_put_contents($path, implode("\n", [
            '# Delivery Report',
            '',
            sprintf('Project: `%s`', $projectSlug),
            sprintf('Decision: **%s**', $status),
            '',
            '## Notes',
            '- Approval gate policy checked.',
            '- Trace and audit references must be attached by orchestrator.',
            '',
        ]));

        $output->writeln(sprintf('Delivery report written to %s', $path));

        return Command::SUCCESS;
    }
}
