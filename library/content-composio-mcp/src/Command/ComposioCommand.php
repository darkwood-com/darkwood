<?php

declare(strict_types=1);

namespace App\Command;

use App\Capability\ComposioToolChain;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Platform\Bridge\Anthropic\Claude;
use Symfony\AI\Platform\Bridge\Anthropic\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:composio', 'Fetch the latest Gmail email through Composio')]
final class ComposioCommand
{
    public function __construct(
        private readonly ComposioToolChain $composioToolChain,
        #[\SensitiveParameter] private readonly string $anthropicApiKey,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $agent = new Agent(
            Factory::createPlatform($this->anthropicApiKey),
            Claude::SONNET_37,
            toolbox: $this->composioToolChain,
        );
        $content = $agent->call('Fetch my last Gmail email')->getContent();
        if (!is_string($content)) {
            $io->error('The assistant returned a non-text response.');

            return Command::FAILURE;
        }

        $io->success($content);

        return Command::SUCCESS;
    }
}
