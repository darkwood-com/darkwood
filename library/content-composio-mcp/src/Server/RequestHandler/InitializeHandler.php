<?php

declare(strict_types=1);

namespace App\Server\RequestHandler;

use Symfony\AI\McpSdk\Message\Request;
use Symfony\AI\McpSdk\Message\Response;
use Symfony\AI\McpSdk\Server\RequestHandler\BaseRequestHandler;

final class InitializeHandler extends BaseRequestHandler
{
    public function __construct(
        private readonly string $name = 'app',
        private readonly string $version = 'dev',
    ) {
    }

    public function createResponse(Request $message): Response
    {
        return new Response($message->id, [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => ['listChanged' => true],
            ],
            'serverInfo' => ['name' => $this->name, 'version' => $this->version],
        ]);
    }

    protected function supportedMethod(): string
    {
        return 'initialize';
    }
}
