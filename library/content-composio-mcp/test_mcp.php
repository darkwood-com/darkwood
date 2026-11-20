<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use App\ComposioSdk\Composio;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Request;

$_ENV['APP_SECRET'] = 'composio-mcp-test';
$_ENV['COMPOSIO_API_KEY'] = 'fixture';
$_ENV['COMPOSIO_ENTITY_ID'] = 'fixture';
$_ENV['COMPOSIO_ACTIONS'] = 'FIXTURE_ACTION';

$http = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
    $path = parse_url($url, PHP_URL_PATH);
    $response = match ([$method, $path]) {
        ['GET', '/v3/tools/FIXTURE_ACTION'] => [
            'name' => 'FIXTURE_ACTION',
            'description' => 'Test action',
            'toolkit' => ['slug' => 'fixture'],
            'input_parameters' => ['properties' => ['prompt' => ['type' => 'string']]],
        ],
        ['GET', '/v3/connected_accounts'] => [
            'items' => [['id' => 'fixture-account', 'toolkit' => ['slug' => 'fixture']]],
        ],
        ['POST', '/v3/tools/execute/FIXTURE_ACTION'] => [
            'data' => [
                'echo' => json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR)['arguments']['prompt'],
            ],
        ],
        default => throw new RuntimeException('Unexpected Composio fixture request.'),
    };

    return new MockResponse(json_encode($response, JSON_THROW_ON_ERROR));
});

$kernel = new Kernel('test', true);
$kernel->boot();
$kernel->getContainer()->get('test.service_container')->set(
    Composio::class,
    new Composio($http, 'fixture', 'https://composio.example.invalid'),
);

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
        'clientInfo' => ['name' => 'composio-mcp-test', 'version' => '1.0.0'],
        'capabilities' => new stdClass(),
    ],
]);
if ('app' !== ($init['result']['serverInfo']['name'] ?? null)) {
    throw new RuntimeException('Unexpected MCP server identity.');
}
$headers['HTTP_MCP_PROTOCOL_VERSION'] = $init['result']['protocolVersion'];
$send(['method' => 'notifications/initialized']);
$listed = $send(['id' => 2, 'method' => 'tools/list']);
$tools = array_column($listed['result']['tools'] ?? [], null, 'name');
$names = array_keys($tools);
sort($names);
if (['fixture_action'] !== $names) {
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
    'params' => ['name' => 'fixture_action', 'arguments' => ['prompt' => 'Fixture']],
]);
$content = implode("\n", array_column($result['result']['content'] ?? [], 'text'));
if (['data' => ['echo' => 'Fixture']] !== json_decode($content, true, flags: JSON_THROW_ON_ERROR)) {
    throw new RuntimeException('Unexpected MCP fixture result.');
}

$invalid = $send([
    'id' => 4,
    'method' => 'tools/call',
    'params' => ['name' => 'fixture_action', 'arguments' => ['prompt' => 17]],
]);
if (-32602 !== ($invalid['error']['code'] ?? null)) {
    throw new RuntimeException('MCP accepted an invalid input.');
}

echo "Composio HTTP handshake, discovery, fixture call and schema validation passed.\n";
