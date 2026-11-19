<?php

declare(strict_types=1);

namespace App\Mario\Infrastructure\AI;

use Mcp\Client\Client;
use Mcp\Client\Transport\StdioTransport;
use Symfony\AI\Agent\Toolbox\ToolFactoryInterface;
use Symfony\AI\Platform\Tool\ExecutionReference;
use Symfony\AI\Platform\Tool\Tool;
use Symfony\AI\Platform\Tool\ToolEffectHints;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class McpLevelTool implements ToolFactoryInterface
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

    /** Preserve the actual SDK schema, including its input length bounds. */
    public function getTool(object|string $reference): iterable
    {
        $this->discover();
        foreach ($this->client->listTools()->tools as $remote) {
            if ('generate_original_level' === $remote->name) {
                yield new Tool(
                    new ExecutionReference(self::class),
                    $remote->name,
                    $remote->description ?? 'Generate one original level.',
                    $remote->inputSchema,
                    effectHints: new ToolEffectHints(readOnlyHint: true, destructiveHint: false, idempotentHint: false, openWorldHint: true),
                );
                return;
            }
        }
        throw new \RuntimeException('The expected generation tool is not available.');
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
