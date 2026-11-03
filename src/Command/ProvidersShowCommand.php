<?php

namespace App\Command;

use App\Account\AuthenticationMethod;
use App\Provider\Capability;
use App\Provider\ProviderCatalog;
use App\Provider\ProductDefinition;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'providers:show', description: 'Inspect a provider and its product capabilities')]
final class ProvidersShowCommand extends Command
{
    public function __construct(private readonly ProviderCatalog $catalog)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('provider', InputArgument::REQUIRED, 'Provider identifier')
            ->addOption('product', null, InputOption::VALUE_REQUIRED, 'Limit output to one product identifier')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json', 'table')
            ->setHelp('Shows declared authentication methods and explicit capability states for one provider.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!\in_array($format, ['table', 'json'], true)) {
            $io->error('Format must be "table" or "json".');

            return ExitCode::InvalidInput->value;
        }

        $provider = $this->catalog->find((string) $input->getArgument('provider'));
        if (null === $provider) {
            $io->error(sprintf('Provider "%s" was not found.', $input->getArgument('provider')));

            return ExitCode::NotFound->value;
        }

        $products = $provider->products;
        if (null !== $productKey = $input->getOption('product')) {
            $product = $provider->product((string) $productKey);
            if (null === $product) {
                $io->error(sprintf('Product "%s" was not found for provider "%s".', $productKey, $provider->key));

                return ExitCode::NotFound->value;
            }
            $products = [$product];
        }

        if ('json' === $format) {
            $data = $provider->toArray();
            $data['products'] = array_map(static fn (ProductDefinition $product): array => $product->toArray(), $products);
            JsonOutput::write($output, ['provider' => $data]);

            return ExitCode::Success->value;
        }

        $io->title(sprintf('%s (%s)', $provider->displayName, $provider->key));
        if (null !== $provider->documentationUrl) {
            $io->text(sprintf('Documentation: %s', $provider->documentationUrl));
        }

        foreach ($products as $product) {
            $io->section(sprintf('%s (%s)', $product->displayName, $product->key));
            $io->text(sprintf(
                'Authentication metadata: %s',
                implode(', ', array_map(static fn (AuthenticationMethod $method): string => sprintf('%s (%s)', $method->label(), $method->value), $product->authenticationMethods)),
            ));
            $io->table(
                ['Capability', 'Identifier', 'State'],
                array_map(static fn (Capability $capability): array => [
                    $capability->label(),
                    $capability->value,
                    $product->capability($capability)->label(),
                ], Capability::cases()),
            );
        }
        $io->note('Account registration is local metadata. It does not authenticate an account or enable these provider capabilities.');

        return ExitCode::Success->value;
    }
}
