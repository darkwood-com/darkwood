<?php

declare(strict_types=1);

use Darkwood\AI\Platform\Bridge\Jev\Factory;
use Darkwood\AI\Platform\Bridge\Jev\Output\EvaluationResult;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

$questions = [
    'is_urgent' => [
        'type' => 'noul',
        'instructions' => 'Does this convey urgency?',
    ],
    'department' => [
        'type' => 'choice',
        'instructions' => 'Which team should handle this?',
        'criteria' => [
            'billing' => 'Payments, invoicing, refunds',
            'technical' => 'Bugs, outages, integrations',
            'sales' => 'Pricing, upgrades, new accounts',
        ],
    ],
    'frustration' => [
        'type' => 'score',
        'instructions' => 'How frustrated is the customer?',
        'criteria' => ['Calm', 'Frustrated', 'Very angry'],
    ],
];

$argv = $_SERVER['argv'] ?? [];
$apiKey = getenv('TYPESAFE_API_KEY');
$useMock = in_array('--mock', $argv, true) || false === $apiKey || '' === $apiKey;

if ($useMock) {
    $fixture = file_get_contents(dirname(__DIR__, 2).'/src/Platform/Bridge/Jev/Tests/Fixtures/choice.json');
    if (false === $fixture) {
        throw new RuntimeException('Unable to load the mock Jev fixture.');
    }

    $httpClient = new MockHttpClient([
        new MockResponse($fixture, ['http_code' => 200, 'response_headers' => ['content-type' => 'application/json']]),
    ]);
    $platform = Factory::createPlatform('test-api-key', $httpClient);
    $state = 'Help! My payouts have been failing for 3 days.';
} else {
    $httpClient = null;
    $platform = Factory::createPlatform((string) getenv('TYPESAFE_API_KEY'));
    $state = 'Help! My payouts have been failing for 3 days.';
}

$result = $platform->invoke('jev-latest', $state, [
    'questions' => $useMock ? ['department' => $questions['department']] : $questions,
])->asObject();

if (!$result instanceof EvaluationResult) {
    throw new RuntimeException('Expected an EvaluationResult.');
}

echo ($useMock ? 'mode: mock' : 'mode: live').\PHP_EOL;
echo 'model: '.$result->getModel().\PHP_EOL;

if (null !== $result->getNoul('is_urgent')) {
    echo 'urgent: '.$result->getNoul('is_urgent').\PHP_EOL;
}

if (null !== $result->getChoice('department')) {
    echo 'department: '.$result->getChoice('department').\PHP_EOL;
}

if (null !== $result->getScore('frustration')) {
    echo 'frustration: '.$result->getScore('frustration').\PHP_EOL;
}
