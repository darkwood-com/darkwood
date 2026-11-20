<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use App\Bridge\Replicate\Client;
use App\Tool\ThumbnailTool;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$original = 'https://example.com/thumbnail.webp';
$styled = 'https://example.com/styled.webp';
$http = new MockHttpClient([
    new MockResponse(json_encode(['status' => 'succeeded', 'output' => [$original]], JSON_THROW_ON_ERROR)),
    new MockResponse(json_encode(['status' => 'succeeded', 'output' => [$styled]], JSON_THROW_ON_ERROR)),
]);

$tool = new ThumbnailTool(new Client($http, new MockClock(), 'fixture'));
$result = $tool->generateThumbnailImage('John Doe');

if ($http->getRequestsCount() !== 2 || !str_contains($result, $original) || !str_contains($result, $styled)) {
    throw new RuntimeException("Unexpected thumbnail fixture result: ".$result);
}

echo $result.PHP_EOL;
