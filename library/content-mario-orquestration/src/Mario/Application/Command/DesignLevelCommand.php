<?php

declare(strict_types=1);

namespace App\Mario\Application\Command;

use App\Mario\Infrastructure\AI\McpLevelTool;
use App\Mario\Infrastructure\Export\LevelPreviewExporter;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\InputProcessor\SystemPromptInputProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Bridge\OpenAi\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(name: 'app:mario:design', description: 'Design one original level with a Symfony AI agent calling the existing official SDK MCP server.')]
final class DesignLevelCommand extends Command
{
    public function __construct(#[Autowire('%kernel.project_dir%')] private readonly string $projectDir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('prompt', null, InputOption::VALUE_REQUIRED, 'Original level brief, 1–2000 characters.')
            ->addOption('approve-export', null, InputOption::VALUE_NONE, 'Approve writing new JSON, HTML preview and evidence into a new var/mario-design/ run directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $prompt = trim((string) $input->getOption('prompt'));
        if ('' === $prompt || mb_strlen($prompt) > 2000) {
            $output->writeln('<error>Provide --prompt with 1–2000 characters.</error>');
            return Command::INVALID;
        }
        $key = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');
        if (!\is_string($key) || '' === trim($key)) {
            $output->writeln('<error>OPENAI_API_KEY is required. No offline fallback is used.</error>');
            return Command::FAILURE;
        }
        // The MCP child receives only the environment key; no secret file is copied.
        putenv('OPENAI_API_KEY='.$key);
        $tool = new McpLevelTool($this->projectDir, onProgress: static fn (string $message) => $output->writeln($message));
        $started = microtime(true);
        try {
            $output->writeln('Symfony AI Agent: level_designer | official PHP MCP SDK: client + server');
            $output->writeln('MCP tools/list: '.implode(', ', $tool->discover()));
            $agent = new Agent(
                Factory::createPlatform($key, HttpClient::create(['timeout' => 90, 'max_duration' => 150])),
                'gpt-5-mini',
                [new SystemPromptInputProcessor('You are a level designer. Interpret the user brief, then call generate_original_level exactly once with a concrete original level brief. Do not invent a result. After the tool returns, summarize its geometry and warnings in at most 80 words, in the language of the user. You cannot save, publish or claim playability. Never reproduce Nintendo characters or levels.')],
                name: 'level_designer',
                toolbox: new Toolbox([$tool]),
                maxToolCalls: 1,
            );
            $summary = $agent->call($prompt, ['max_output_tokens' => 4000])->asText();
            $calls = $tool->calls();
            if (1 !== \count($calls)) {
                throw new \RuntimeException('The agent did not complete exactly one real MCP generation.');
            }
            $evidence = ['mode' => 'real_ai', 'agent' => $agent->getName(), 'sdk' => 'mcp/sdk', 'tool_calls' => $calls, 'summary' => $summary, 'duration_seconds' => round(microtime(true) - $started, 3), 'generated_at' => gmdate(DATE_ATOM)];
            $output->writeln('MCP tools/call: generate_original_level | mode: real_ai | geometry: valid');
            $output->writeln((string) $summary);
            $output->writeln('Geometry checks do not establish playability.');
            if ($input->getOption('approve-export')) {
                $directory = (new LevelPreviewExporter())->write($calls[0]['result']['level'], $evidence, $this->projectDir.'/var/mario-design');
                $output->writeln('Approved export: '.$directory);
            } else {
                $output->writeln('No files written. Use --approve-export to explicitly approve local artifacts.');
            }
            return Command::SUCCESS;
        } catch (\Throwable $error) {
            // Provider and transport messages can contain request details: keep them off public recordings.
            $output->writeln('<error>Design failed ('.$error::class.'). No successful design is claimed. Check provider access and retry.</error>');
            if ($output->isVerbose()) {
                for ($cause = $error; null !== $cause; $cause = $cause->getPrevious()) {
                    $output->writeln(\Symfony\Component\Console\Formatter\OutputFormatter::escape(str_replace($key, '[REDACTED]', $cause::class.': '.$cause->getMessage())));
                }
            }
            return Command::FAILURE;
        } finally {
            $tool->disconnect();
        }
    }
}
