<?php

/*
 * This file is part of Darkwood AI.
 *
 * (c) Mathieu Ledru <matyo91@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Darkwood\AI\Platform\Bridge\Jev\Factory;
use Darkwood\AI\Platform\Bridge\Jev\Output\EvaluationResult;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

$argv = $_SERVER['argv'] ?? [];
$useMock = in_array('--mock', $argv, true);
$apiKey = getenv('TYPESAFE_API_KEY');

if (!$useMock && (false === $apiKey || '' === $apiKey)) {
    fwrite(\STDERR, "Set TYPESAFE_API_KEY or pass --mock.\n");
    exit(1);
}

$httpClient = $useMock ? createMockHttpClient() : null;
$platform = Factory::createPlatform($useMock ? 'test-api-key' : (string) $apiKey, $httpClient);

$result = $platform->invoke('jev-latest', 'Help! My payouts have been failing for 3 days.', [
    'questions' => [
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
    ],
])->asObject();

assert($result instanceof EvaluationResult);

echo 'model: '.$result->getModel().\PHP_EOL;
echo 'urgent: '.$result->getNoul('is_urgent').\PHP_EOL;
echo 'department: '.$result->getChoice('department').\PHP_EOL;
echo 'frustration: '.$result->getScore('frustration').\PHP_EOL;

function createMockHttpClient(): HttpClientInterface
{
    $body = json_encode([
        'model' => 'jev-1.13.0',
        'answers' => [
            'is_urgent' => [
                'type' => 'noul',
                'noul' => 0.95,
            ],
            'department' => [
                'type' => 'choice',
                'choice' => 'billing',
                'probabilities' => [
                    'billing' => 0.88,
                    'technical' => 0.12,
                    'sales' => 0.0,
                ],
                'confidence' => 0.81,
            ],
            'frustration' => [
                'type' => 'score',
                'score' => 1.05,
                'legend' => [
                    '0' => 'Calm',
                    '1' => 'Frustrated',
                    '2' => 'Very angry',
                ],
                'probabilities' => [
                    '0' => 0.0,
                    '1' => 0.95,
                    '2' => 0.05,
                ],
                'confidence' => 0.92,
            ],
        ],
        'usage' => [
            'input_tokens' => 307,
            'output_tokens' => 20,
        ],
    ], \JSON_THROW_ON_ERROR);

    return new MockHttpClient([
        new MockResponse($body, [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json'],
        ]),
    ]);
}
