<?php

declare(strict_types=1);

use App\Mario\Infrastructure\AI\MarioAgentOrchestrator;
use App\Mario\Infrastructure\AI\MarioMultiAgentFactory;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;
use Mcp\Server\Server;
use Mcp\Server\Transport\StdioTransport;

require dirname(__DIR__).'/vendor/autoload.php';

$fixture = in_array('--fixture', $argv, true);
$orchestrator = new MarioAgentOrchestrator(new MarioMultiAgentFactory());
$server = Server::builder()
    ->setServerInfo('Darkwood original level generator', '1.0.0')
    ->addTool(
        static function (string $prompt) use ($orchestrator, $fixture): array|CallToolResult {
            try {
                $result = $orchestrator->orchestrate($prompt, $fixture);
            } catch (InvalidArgumentException) {
                return new CallToolResult([new TextContent('Invalid prompt: provide 1–2000 non-whitespace characters.')], true, ['error' => 'invalid_prompt']);
            } catch (RuntimeException $error) {
                $code = str_starts_with($error->getMessage(), 'AI output') ? 'invalid_provider_output' : 'provider_failure';
                return new CallToolResult([new TextContent('Generation failed: '.$code.'. No level exported; retry explicitly.')], true, ['error' => $code]);
            }
            if (!$result['validation']->isValid()) {
                return new CallToolResult(
                    [new TextContent('Invalid generated geometry: '.implode(' ', $result['validation']->errors()))],
                    true,
                    ['error' => 'invalid_geometry', 'validation_errors' => $result['validation']->errors()],
                );
            }

            return [
                'mode' => $fixture ? 'fixture' : 'real_ai',
                'agent' => $result['selected_agent'],
                'level' => $result['payload'],
                'warnings' => $result['validation']->warnings(),
            ];
        },
        name: 'generate_original_level',
        description: 'Generate and validate one original platformer level through Symfony AI. Calls OpenAI, returns JSON and does not write files.',
        inputSchema: [
            'type' => 'object',
            'properties' => ['prompt' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 2000]],
            'required' => ['prompt'],
            'additionalProperties' => false,
        ],
    )
    ->build();

exit($server->run(new StdioTransport()));
