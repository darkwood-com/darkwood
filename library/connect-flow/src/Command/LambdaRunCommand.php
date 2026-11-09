<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Execution\LambdaExecutionContext;
use App\Infrastructure\Flow\LambdaFlowRuntime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'connect-flow:lambda:run',
    description: 'Evaluate a lambda expression through the Flow runtime (optionally with Composio context).',
)]
final class LambdaRunCommand extends Command
{
    public function __construct(
        private readonly LambdaFlowRuntime $flowRuntime,
        private readonly string $defaultComposioUserId,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('expression', InputArgument::REQUIRED, 'Lambda source, e.g. λx.x or λsend_email.λpayload.send_email(payload)')
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Composio user id (overrides COMPOSIO_USER_ID)')
            ->addOption('account', 'a', InputOption::VALUE_REQUIRED, 'Explicit connected account id')
            ->addOption('args', null, InputOption::VALUE_REQUIRED, 'JSON array of curried lambda arguments applied after configured primitive bindings', '[]')
            ->addOption('input', 'i', InputOption::VALUE_REQUIRED, 'JSON merged into Composio tool arguments', '{}');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $expr = $input->getArgument('expression');
        if (!\is_string($expr) || $expr === '') {
            $io->error('Pass a non-empty expression argument.');

            return Command::FAILURE;
        }

        $userId = $input->getOption('user');
        if (!\is_string($userId) || $userId === '') {
            $userId = $this->defaultComposioUserId;
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

        $argsJson = $input->getOption('args');
        if (!\is_string($argsJson)) {
            $argsJson = '[]';
        }

        try {
            $lambdaArguments = json_decode($argsJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $io->error('Invalid JSON for --args: ' . $e->getMessage());

            return Command::FAILURE;
        }

        if (!\is_array($lambdaArguments) || !array_is_list($lambdaArguments)) {
            $io->error('--args must decode to a JSON array.');

            return Command::FAILURE;
        }

        $account = $input->getOption('account');
        $accountStr = \is_string($account) && $account !== '' ? $account : null;

        $ctx = new LambdaExecutionContext($userId, $payload, $accountStr);

        try {
            $result = $this->flowRuntime->evaluateThroughFlow($expr, $ctx, $lambdaArguments);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->writeln(json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
