<?php

declare(strict_types=1);

use App\Mario\Infrastructure\AI\MarioAgentOrchestrator;
use App\Mario\Infrastructure\AI\MarioMultiAgentFactory;
use Mcp\Server\Server;
use Mcp\Server\Transport\StdioTransport;

require dirname(__DIR__).'/vendor/autoload.php';

$orchestrator = new MarioAgentOrchestrator(new MarioMultiAgentFactory());
$server = Server::builder()
    ->setServerInfo('Darkwood original level generator', '1.0.0')
    ->addTool(
        static function (string $prompt) use ($orchestrator): array {
            $result = $orchestrator->orchestrate($prompt, false);
            if (!$result['validation']->isValid()) {
                throw new RuntimeException('Generated level failed geometry validation.');
            }

            return [
                'mode' => 'real_ai',
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
