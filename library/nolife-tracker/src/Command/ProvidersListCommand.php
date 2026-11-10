<?php

namespace App\Command;

use App\Provider\CapabilityState;
use App\Provider\ProviderCatalog;
use App\Provider\ProductDefinition;
use App\Terminal\ExitCode;
use App\Terminal\JsonOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'providers:list', description: 'List known AI providers and products', aliases: ['providers'])]
final class ProvidersListCommand extends Command
{
    public function __construct(private readonly ProviderCatalog $catalog)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table or json', 'table')
            ->setHelp('Lists the static provider catalog. Capability states describe Nolife Tracker support, not provider availability.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = $input->getOption('format');
        if (!\in_array($format, ['table', 'json'], true)) {
            (new SymfonyStyle($input, $output))->error('Format must be "table" or "json".');

            return ExitCode::InvalidInput->value;
        }

        $providers = $this->catalog->all();
        if ('json' === $format) {
            JsonOutput::write($output, [
                'providers' => array_map(static fn ($provider): array => $provider->toArray(), $providers),
                'capabilityStates' => array_combine(
                    array_map(static fn (CapabilityState $state): string => $state->value, CapabilityState::cases()),
                    array_map(static fn (CapabilityState $state): string => $state->label(), CapabilityState::cases()),
                ),
            ]);

            return ExitCode::Success->value;
        }

        $io = new SymfonyStyle($input, $output);
        $io->title('Nolife Tracker providers');
        $io->table(
            ['Provider ID', 'Provider', 'Products', 'Capability summary'],
            array_map(static fn ($provider): array => [
                $provider->key,
                $provider->displayName,
                implode(', ', array_map(static fn (ProductDefinition $product): string => sprintf('%s (%s)', $product->displayName, $product->key), $provider->products)),
                self::summary($provider->products),
            ], $providers),
        );
        $io->note('Available = implemented by Nolife Tracker. Unknown, unsupported, and not implemented are distinct states.');

        return ExitCode::Success->value;
    }

    /**
     * @param list<ProductDefinition> $products
     */
    private static function summary(array $products): string
    {
        $counts = array_fill_keys(array_map(static fn (CapabilityState $state): string => $state->value, CapabilityState::cases()), 0);
        foreach ($products as $product) {
            foreach ($product->capabilities as $state) {
                ++$counts[$state->value];
            }
        }

        return sprintf(
            '%d available, %d not implemented, %d unsupported, %d unknown',
            $counts[CapabilityState::Implemented->value],
            $counts[CapabilityState::NotImplemented->value],
            $counts[CapabilityState::Unsupported->value],
            $counts[CapabilityState::Unknown->value],
        );
    }
}
