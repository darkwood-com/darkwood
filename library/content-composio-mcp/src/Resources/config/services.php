<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Server\RequestHandler\InitializeHandler;
use App\Server\RequestHandler\ToolCallHandler;
use Symfony\AI\McpSdk\Server\RequestHandler\InitializeHandler as SymfonyInitializeHandler;
use Symfony\AI\McpSdk\Server\RequestHandler\ToolCallHandler as SymfonyToolCallHandler;
use Symfony\AI\McpSdk\Server\RequestHandlerInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
        ->instanceof(RequestHandlerInterface::class)
            ->tag('mcp.server.request_handler')
        ->set(SymfonyInitializeHandler::class, InitializeHandler::class)
        ->args([
            '$name' => param('mcp.app'),
            '$version' => param('mcp.version'),
        ])
        ->set(SymfonyToolCallHandler::class, ToolCallHandler::class)
    ;
};
