<?php

declare(strict_types=1);

use PhpMcp\Server\Server;

// Ensure your project's autoloader is included
require_once __DIR__ . '/vendor/autoload.php';
// If your MCP elements are in a specific namespace, ensure that's autoloaded too (e.g., via composer.json)

// Optional: Configure logging (defaults to STDERR)
// $logger = new MyPsrLoggerImplementation(...);

$server = Server::make()
    // Optional: ->withLogger($logger)
    // Optional: ->withCache(new MyPsrCacheImplementation(...))
    // Optional: ->withContainer(new MyPsrContainerImplementation(...))
    ->withBasePath(__DIR__) // Directory to start scanning for Attributes
    ->withScanDirectories(['src']) // Specific subdirectories to scan (relative to basePath)
    ->discover(); // Find all #[Mcp*] attributes

// Run the server using the stdio transport
$exitCode = $server->run('stdio');

exit($exitCode);