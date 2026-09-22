<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darkwood\AI\Platform\Bridge\Jev\Tests;

use Darkwood\AI\Platform\Bridge\Jev\TokenUsageExtractor;
use Darkwood\AI\Platform\Result\RawHttpResult;
use Darkwood\AI\Platform\TokenUsage\TokenUsage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class TokenUsageExtractorTest extends TestCase
{
    public function testItExtractsInputAndOutputTokens(): void
    {
        $body = file_get_contents(__DIR__.'/Fixtures/noul.json');
        $this->assertNotFalse($body);
        $httpClient = new MockHttpClient([
            new MockResponse($body, ['http_code' => 200]),
        ]);

        $usage = (new TokenUsageExtractor())->extract(new RawHttpResult($httpClient->request('GET', 'http://localhost')));

        $this->assertInstanceOf(TokenUsage::class, $usage);
        $this->assertSame(307, $usage->getPromptTokens());
        $this->assertSame(20, $usage->getCompletionTokens());
        $this->assertSame('jev-1.13.0', $usage->getModel());
    }

    public function testItReturnsNullWhenUsageIsMissing(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"model":"jev-1.13.0","answers":{}}', ['http_code' => 200]),
        ]);

        $this->assertNull((new TokenUsageExtractor())->extract(new RawHttpResult($httpClient->request('GET', 'http://localhost'))));
    }
}
