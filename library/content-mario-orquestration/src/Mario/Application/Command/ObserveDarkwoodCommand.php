<?php

declare(strict_types=1);

namespace App\Mario\Application\Command;

use App\Mario\Infrastructure\AI\DarkwoodStateTool;
use Mcp\Client\Client;
use Mcp\Client\Transport\HttpTransport;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\InputProcessor\SystemPromptInputProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Bridge\OpenAi\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:mario:observe-darkwood', description: 'Read the existing Darkwood API through Symfony AI and the official MCP SDK; no action tool is exposed.')]
final class ObserveDarkwoodCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('endpoint', null, InputOption::VALUE_REQUIRED, 'Existing MCP HTTPS endpoint, or HTTP loopback for an isolated local demonstration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $endpoint = (string) $input->getOption('endpoint');
        $url = parse_url($endpoint);
        $loopback = in_array($url['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true);
        if (!$url || !isset($url['host']) || isset($url['pass']) || isset($url['user']) || isset($url['query']) || isset($url['fragment']) || ('https' !== ($url['scheme'] ?? '') && !($loopback && 'http' === ($url['scheme'] ?? '')))) {
            $output->writeln('<error>Provide a credential-free HTTPS endpoint or HTTP loopback URL.</error>');

            return Command::INVALID;
        }
        $env = static fn (string $name): string => (string) ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: '');
        $key = $env('OPENAI_API_KEY');
        $apiKey = $env('DARKWOOD_GAME_API_KEY');
        $jwt = $env('DARKWOOD_GAME_JWT');
        if ('' === $key || '' === $apiKey || '' === $jwt) {
            $output->writeln('<error>OPENAI_API_KEY, DARKWOOD_GAME_API_KEY and DARKWOOD_GAME_JWT must be provided through the environment.</error>');

            return Command::FAILURE;
        }
        $headers = ['X-API-Key' => $apiKey, 'Authorization' => 'Bearer '.$jwt];
        $host = $env('DARKWOOD_GAME_LOCAL_HOST');
        if ($loopback && '' !== $host) {
            if (!preg_match('/^[a-zA-Z0-9.-]+(?::[0-9]+)?$/D', $host)) {
                return Command::INVALID;
            }
            $headers['Host'] = $host;
        }
        $client = Client::builder()->setClientInfo('darkwood-read-only-observer', '1.0')->setMaxRetries(0)->build();
        try {
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            $http = new \Symfony\Component\HttpClient\Psr18Client(\Symfony\Component\HttpClient\HttpClient::create(['timeout' => 15, 'max_duration' => 30]), $factory, $factory);
            $client->connect(new HttpTransport($endpoint, $headers, $http, $factory, $factory));
            $tool = new DarkwoodStateTool($client);
            $agent = new Agent(Factory::createPlatform($key), 'gpt-5-mini', [new SystemPromptInputProcessor('Read the actual Darkwood state with get_darkwood_state exactly once. Explain only returned facts. Propose at most one next step, without executing it. Do not infer life, inventory or resources when absent. This client has no action tool and cannot approve an action.')], name: 'darkwood_observer', toolbox: new Toolbox([$tool], toolFactory: $tool), maxToolCalls: 1);
            $summary = $agent->call('Observe the current player and explain what the API returned. No game action is authorized.', ['max_output_tokens' => 2000])->asText();
            if (1 !== count($tool->observations())) {
                throw new \RuntimeException('The agent did not perform exactly one observation.');
            }
            $output->writeln(json_encode(['agent' => 'darkwood_observer', 'mode' => 'real_ai', 'transport' => 'official SDK HTTP client and existing Portal MCP server', 'observations' => $tool->observations(), 'summary' => $summary, 'action_tool_exposed' => false], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

            return Command::SUCCESS;
        } catch (\Throwable $error) {
            $output->writeln('<error>Darkwood observation failed ('.$error::class.'). No game action was requested or authorized.</error>');

            return Command::FAILURE;
        } finally {
            $client->disconnect();
        }
    }
}
