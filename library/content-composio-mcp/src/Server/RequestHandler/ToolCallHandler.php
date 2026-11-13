<?php

namespace App\Server\RequestHandler;

use Symfony\AI\McpSdk\Capability\Tool\ToolCall;
use Symfony\AI\McpSdk\Capability\Tool\ToolExecutorInterface;
use Symfony\AI\McpSdk\Exception\ExceptionInterface;
use Symfony\AI\McpSdk\Message\Error;
use Symfony\AI\McpSdk\Message\Request;
use Symfony\AI\McpSdk\Message\Response;
use Symfony\AI\McpSdk\Server\RequestHandler\BaseRequestHandler;

final class ToolCallHandler extends BaseRequestHandler
{
    public function __construct(
        private readonly ToolExecutorInterface $toolExecutor,
    ) {
    }

    public function createResponse(Request $message): Response|Error
    {
        $name = $message->params['name'];
        $arguments = $message->params['arguments'] ?? [];

        try {
            $result = $this->toolExecutor->call(new ToolCall(uniqid('', true), $name, $arguments));
        } catch (ExceptionInterface) {
            return Error::internalError($message->id, 'Error while executing tool');
        }

        return new Response($message->id, [
            'content' => [
                ['type' => 'text', 'text' => $result->result],
            ],
        ]);
    }

    protected function supportedMethod(): string
    {
        return 'tools/call';
    }
}
