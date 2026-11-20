<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use App\Study\DecidePipeline;
use App\Study\Routing\JevRoutingJob;
use Darkwood\AI\Platform\Bridge\Jev\Factory;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$http = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
    $body = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
    if ('POST' !== $method || '/v1/systemone' !== parse_url($url, PHP_URL_PATH)
        || 'Fixture: duplicated invoice' !== $body['state']
        || JevRoutingJob::questions() !== $body['questions']) {
        throw new RuntimeException('Unexpected JEV fixture request.');
    }

    return new MockResponse(json_encode([
        'model' => 'jev-1.13.0',
        'answers' => [
            'department' => [
                'type' => 'choice',
                'choice' => 'billing',
                'probabilities' => ['billing' => 1.0],
                'confidence' => 0.98,
            ],
        ],
    ], JSON_THROW_ON_ERROR));
});

$platform = Factory::createPlatform('fixture', $http);
$result = DecidePipeline::run('Fixture: duplicated invoice', new JevRoutingJob($platform, 'fixture'));
if ('billing' !== $result->decision || 0.98 !== $result->confidence || null !== $result->error || 1 !== $http->getRequestsCount()) {
    throw new RuntimeException('Unexpected Flow or JEV result: '.($result->error ?? 'invalid response'));
}

echo "Flow and JEV routing fixture passed.\n";
