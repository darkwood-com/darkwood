<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Workflow\WorkflowRunner;
use App\Domain\Execution\Context;
use App\Infrastructure\Action\ExecuteComposioToolAction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'connect-flow:composio:execute',
    description: 'Run the minimal Navi workflow: execute one Composio tool (COMPOSIO_ACTION) for a user.',
)]
final class ExecuteComposioCommand extends Command
{
    public function __construct(
        private readonly WorkflowRunner $runner,
        private readonly ExecuteComposioToolAction $executeComposioTool,
        private readonly string $defaultUserId,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Composio user id (overrides COMPOSIO_USER_ID)')
            ->addOption('account', 'a', InputOption::VALUE_REQUIRED, 'Explicit connected account id')
            ->addOption('input', 'i', InputOption::VALUE_REQUIRED, 'JSON object for tool arguments', '{}');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $input->getOption('user');
        if (!\is_string($userId) || $userId === '') {
            $userId = $this->defaultUserId;
        }
        if ($userId === '') {
            $io->error('Set COMPOSIO_USER_ID or pass --user.');

            return Command::FAILURE;
        }

        $json = $input->getOption('input');
        if (!\is_string($json)) {
            $json = '{}';
        }

        try {
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $io->error('Invalid JSON for --input: ' . $e->getMessage());

            return Command::FAILURE;
        }

        if (!\is_array($payload)) {
            $io->error('--input must decode to a JSON object.');

            return Command::FAILURE;
        }

        $account = $input->getOption('account');
        $accountStr = \is_string($account) && $account !== '' ? $account : null;

        $context = Context::fromArray([
            'composio_user_id' => $userId,
            'composio_input' => $payload,
            'composio_connected_account_id' => $accountStr,
        ]);

        try {
            $result = $this->runner->run($context, [$this->executeComposioTool]);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $final = $result->state()->context()->get('composio_result');
        $io->writeln(json_encode($final, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
