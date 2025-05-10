<?php

declare(strict_types=1);

use App\Container\ComposioContainer;
use PhpMcp\Server\Defaults\ArrayConfigurationRepository;
use PhpMcp\Server\Defaults\FileCache;
use PhpMcp\Server\Server;
use Symfony\Component\Dotenv\Dotenv;
use PhpMcp\Server\Contracts\ConfigurationRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env files
$dotenv = new Dotenv();
$env = $dotenv->parse(file_get_contents(__DIR__ . '/.env'));
$envLocal = $dotenv->parse(file_get_contents(__DIR__ . '/.env.local'));
$env = array_merge($env, $envLocal);

// Optional: Configure logging (defaults to STDERR)
// $logger = new MyPsrLoggerImplementation(...);

// Get API key from environment
$apiKey = $env['COMPOSIO_API_KEY'] ?? null;
if (!$apiKey) {
    throw new RuntimeException('COMPOSIO_API_KEY environment variable is not set');
}

$container = new ComposioContainer($apiKey);
$config = new ArrayConfigurationRepository([
    'mcp' => [
        'server' => ['name' => 'PHP MCP Server', 'version' => '1.0.0'],
        'protocol_versions' => ['2024-11-05'],
        'pagination_limit' => 50,
        'capabilities' => [
            'tools' => ['enabled' => true, 'listChanged' => true],
            'resources' => ['enabled' => true, 'subscribe' => true, 'listChanged' => true],
            'prompts' => ['enabled' => true, 'listChanged' => true],
            'logging' => ['enabled' => false],
        ],
        'cache' => ['key' => 'mcp.elements.cache', 'ttl' => 3600, 'prefix' => 'mcp_state_'],
        'runtime' => ['log_level' => 'info'],
    ],
]);
$logger = new NullLogger;
$cache = new FileCache(__DIR__.'/var/cache/mcp_cache');

$container->set(ConfigurationRepositoryInterface::class, $config);
$container->set(LoggerInterface::class, $logger);
$container->set(CacheInterface::class, $cache);

$server = Server::make()
    // Optional: ->withLogger($logger)
    // Optional: ->withCache(new MyPsrCacheImplementation(...))
    ->withContainer($container)
    ->withBasePath(__DIR__) // Directory to start scanning for Attributes
    ->withScanDirectories(['src']) // Specific subdirectories to scan (relative to basePath)
    ->discover(); // Find all #[Mcp*] attributes

// Run the server using the stdio transport
$exitCode = $server->run('stdio');

exit($exitCode);