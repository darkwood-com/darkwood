<?php

declare(strict_types=1);

namespace App\Mario\Infrastructure\AI;

use Mcp\Client\Client;
use Mcp\Client\Transport\StdioTransport;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Tool\ToolEffectHints;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsTool(
    name: 'generate_original_level',
    description: 'Ask the existing MCP server to generate an original platformer level using Symfony AI, validate its geometry and return its JSON. Calls the configured AI provider; does not write files.',
    effectHints: new ToolEffectHints(readOnlyHint: true, destructiveHint: false, idempotentHint: false, openWorldHint: true),
)]
final class McpLevelTool
{
    private ?Client $client = null;
    private array $calls = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        private readonly bool $fixture = false,
        private readonly ?\Closure $onProgress = null,
    ) {
    }

    /** @return list<string> */
    public function discover(): array
    {
        if (null === $this->client) {
            $client = Client::builder()->setClientInfo('darkwood-level-designer', '1.1.0')->setMaxRetries(0)->build();
            $client->connect(new StdioTransport(PHP_BINARY, [$this->projectDir.'/bin/mcp.php', ...($this->fixture ? ['--fixture'] : [])], $this->projectDir));
            $this->client = $client;
        }

        return array_map(static fn ($tool): string => $tool->name, $this->client->listTools()->tools);
    }

    /** @return array<string, mixed> */
    public function __invoke(string $prompt): array
    {
        if ([] !== $this->calls) {
            throw new \RuntimeException('One generation per design request is allowed. Start a new request to generate again.');
        }
        $this->discover();
        ($this->onProgress ?? static function (string $message): void {})('MCP tools/call: generate_original_level requested; waiting for the real server response.');
        $result = $this->client->callTool('generate_original_level', ['prompt' => $prompt]);
        $content = $result->structuredContent;
        if ($result->isError) {
            $details = implode(' ', array_map(static fn ($item): string => $item->text ?? 'Non-text MCP error', $result->content));
            throw new \RuntimeException('MCP tool error: '.substr($details, 0, 1000));
        }
        if (!\is_array($content) || ($content['mode'] ?? null) !== ($this->fixture ? 'fixture' : 'real_ai')) {
            throw new \RuntimeException('MCP generation failed or returned an unexpected mode. No artifact was exported.');
        }
        $level = $content['level'] ?? null;
        if (!\is_array($level) || !(new MarioAgentOrchestrator(new MarioMultiAgentFactory()))->validatePayload($level)->isValid()) {
            throw new \RuntimeException('MCP returned invalid geometry. No artifact was exported.');
        }
        $this->calls[] = ['tool' => 'generate_original_level', 'arguments' => ['prompt' => $prompt], 'result' => $content];

        return $content;
    }

    /** @return list<array<string, mixed>> */
    public function calls(): array
    {
        return $this->calls;
    }

    public function disconnect(): void
    {
        $this->client?->disconnect();
        $this->client = null;
    }
}
