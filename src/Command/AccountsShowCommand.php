<?php

namespace App\Command;

use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Provider\Capability;
use App\Provider\ProviderCatalog;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'accounts:show', description: 'Inspect a locally registered account')]
final class AccountsShowCommand extends Command
{
    public function __construct(
        private readonly AccountService $accounts,
        private readonly ProviderCatalog $catalog,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'Local account identifier')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json', 'table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!\in_array($format, ['table', 'json'], true)) {
            $io->error('Format must be "table" or "json".');

            return ExitCode::InvalidInput->value;
        }

        try {
            $account = $this->accounts->get((string) $input->getArgument('id'));
        } catch (AccountOperationException $error) {
            $io->error($error->getMessage());

            return $error->exitCode->value;
        }
        $defaults = $this->accounts->defaultsFor($account);
        $product = $this->catalog->product($account->providerKey, $account->productKey);
        $data = $account->toSafeArray(true) + [
            'providerDefault' => $defaults['provider'],
            'productDefault' => $defaults['product'],
            'quotaRetrieval' => $product?->capability(Capability::QuotaRetrieval)->value ?? 'unknown',
        ];

        if ('json' === $format) {
            JsonOutput::write($output, ['account' => $data]);

            return ExitCode::Success->value;
        }

        $io->title(sprintf('%s%s', $account->id, null === $account->alias ? '' : ' — '.$account->alias));
        $io->definitionList(
            ['Provider / product' => $account->providerKey.' / '.$account->productKey],
            ['Local registration' => 'registered'],
            ['Monitoring' => $account->monitoringEnabled ? 'enabled' : 'disabled'],
            ['Authentication method' => $account->authenticationMethod->label()],
            ['Authentication configured' => $account->authenticationConfigured() ? 'yes' : 'no'],
            ['Authentication reference' => $account->redactedAuthenticationReference() ?? 'none'],
            ['Authentication verified' => $account->authenticationVerificationState->label()],
            ['Quota retrieval' => $product?->capability(Capability::QuotaRetrieval)->label() ?? 'Unknown'],
            ['Provider default' => $defaults['provider'] ? 'yes' : 'no'],
            ['Product default' => $defaults['product'] ? 'yes' : 'no'],
            ['Created' => $account->createdAt->format(DATE_ATOM)],
            ['Updated' => $account->updatedAt->format(DATE_ATOM)],
        );

        return ExitCode::Success->value;
    }
}
