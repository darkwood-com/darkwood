<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use App\Bridge\Replicate\Client;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;

$_ENV['APP_SECRET'] = 'thumbnail-mcp-test';
$_ENV['REPLICATE_API_KEY'] = 'fixture';

$original = 'https://example.com/thumbnail.webp';
$styled = 'https://example.com/styled.webp';
$http = new MockHttpClient([
    new MockResponse(json_encode(['status' => 'succeeded', 'output' => [$original]], JSON_THROW_ON_ERROR)),
    new MockResponse(json_encode(['status' => 'succeeded', 'output' => [$styled]], JSON_THROW_ON_ERROR)),
]);

$kernel = new Kernel('test', true);
$kernel->boot();
$kernel->getContainer()->get('test.service_container')->set(Client::class, new Client($http, new MockClock(), 'fixture'));

if ('--stdio' === ($argv[1] ?? null)) {
    $app = new Application($kernel);
    $app->run(new ArrayInput(['command' => 'mcp:server']));
    exit;
}

$headers = [
    'CONTENT_TYPE' => 'application/json',
    'HTTP_ACCEPT' => 'application/json, text/event-stream',
];
$send = static function (array $message) use ($kernel, &$headers): ?array {
    $request = Request::create('http://localhost/mcp', 'POST', server: $headers, content: json_encode(
        ['jsonrpc' => '2.0'] + $message,
        JSON_THROW_ON_ERROR,
    ));
    $response = $kernel->handle($request);
    if (!$response->isSuccessful()) {
        throw new RuntimeException('Unexpected MCP HTTP status '.$response->getStatusCode());
    }

    if ($response->headers->has('Mcp-Session-Id')) {
        $headers['HTTP_MCP_SESSION_ID'] = $response->headers->get('Mcp-Session-Id');
    }

    $body = $response->getContent();
    if (false === $body) {
        ob_start();
        $response->sendContent();
        $body = (string) ob_get_clean();
    }
    $kernel->terminate($request, $response);

    if ('' === $body) {
        return null;
    }
    if (preg_match('/^data: (.+)$/m', $body, $match)) {
        $body = $match[1];
    }

    return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
};

$init = $send([
    'id' => 1,
    'method' => 'initialize',
    'params' => [
        'protocolVersion' => '2025-03-26',
        'clientInfo' => ['name' => 'thumbnail-mcp-test', 'version' => '1.0.0'],
        'capabilities' => new stdClass(),
    ],
]);
if ('thumbnail-generator' !== ($init['result']['serverInfo']['name'] ?? null)) {
    throw new RuntimeException('Unexpected MCP server identity.');
}
$headers['HTTP_MCP_PROTOCOL_VERSION'] = $init['result']['protocolVersion'];
$send(['method' => 'notifications/initialized']);
$listed = $send(['id' => 2, 'method' => 'tools/list']);
$tools = array_column($listed['result']['tools'] ?? [], null, 'name');
$names = array_keys($tools);
sort($names);
if (['generate_ghibli_image', 'generate_thumbnail_image'] !== $names) {
    throw new RuntimeException('Unexpected MCP tools.');
}
foreach ($tools as $tool) {
    if ('string' !== ($tool['inputSchema']['properties']['prompt']['type'] ?? null)
        || ['prompt'] !== ($tool['inputSchema']['required'] ?? [])) {
        throw new RuntimeException('Unexpected MCP prompt schema.');
    }
}

$result = $send([
    'id' => 3,
    'method' => 'tools/call',
    'params' => ['name' => 'generate_thumbnail_image', 'arguments' => ['prompt' => 'Fixture']],
]);
$content = implode("\n", array_column($result['result']['content'] ?? [], 'text'));
if (!str_contains($content, $original) || !str_contains($content, $styled) || 2 !== $http->getRequestsCount()) {
    throw new RuntimeException('Unexpected MCP fixture result.');
}

echo "MCP HTTP handshake, discovery and fixture call passed.\n";
