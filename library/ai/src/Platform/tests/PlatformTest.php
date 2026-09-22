<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\Tests;

use Darkwood\AI\Platform\Capability;
use Darkwood\AI\Platform\Exception\ModelNotFoundException;
use Darkwood\AI\Platform\Model;
use Darkwood\AI\Platform\ModelCatalog\ModelCatalog;
use Darkwood\AI\Platform\ModelClientInterface;
use Darkwood\AI\Platform\Platform;
use Darkwood\AI\Platform\Result\ObjectResult;
use Darkwood\AI\Platform\Result\RawHttpResult;
use Darkwood\AI\Platform\Result\RawResultInterface;
use Darkwood\AI\Platform\Result\ResultInterface;
use Darkwood\AI\Platform\ResultConverterInterface;
use Darkwood\AI\Platform\TokenUsage\TokenUsage;
use Darkwood\AI\Platform\TokenUsage\TokenUsageExtractorInterface;
use Darkwood\AI\Platform\TokenUsage\TokenUsageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class PlatformTest extends TestCase
{
    public function testInvokeConvertsObjectResultAndTokenUsage(): void
    {
        $model = new Model('demo', [Capability::INPUT_TEXT]);
        $httpClient = new MockHttpClient([
            new MockResponse('{"ok":true}', ['http_code' => 200]),
        ]);
        $raw = new RawHttpResult($httpClient->request('GET', 'http://localhost'));

        $client = new class ($model, $raw) implements ModelClientInterface {
            public function __construct(
                private readonly Model $expected,
                private readonly RawResultInterface $raw,
            ) {
            }

            public function supports(Model $model): bool
            {
                return $model === $this->expected;
            }

            public function request(Model $model, array|string $payload, array $options = []): RawResultInterface
            {
                return $this->raw;
            }
        };

        $converter = new class ($model) implements ResultConverterInterface {
            public function __construct(private readonly Model $expected)
            {
            }

            public function supports(Model $model): bool
            {
                return $model === $this->expected;
            }

            public function convert(RawResultInterface $result, array $options = []): ResultInterface
            {
                return new ObjectResult((object) ['ok' => true]);
            }

            public function getTokenUsageExtractor(): TokenUsageExtractorInterface
            {
                return new class () implements TokenUsageExtractorInterface {
                    public function extract(RawResultInterface $rawResult, array $options = []): ?TokenUsageInterface
                    {
                        return new TokenUsage(promptTokens: 3, completionTokens: 1);
                    }
                };
            }
        };

        $platform = new Platform(new ModelCatalog(['demo' => $model]), $client, $converter);
        $deferred = $platform->invoke('demo', 'hello');

        $content = $deferred->asObject();
        $this->assertIsObject($content);
        $this->assertTrue($content->ok);

        $usage = $deferred->getResult()->getMetadata()->get('token_usage');
        $this->assertInstanceOf(TokenUsage::class, $usage);
        $this->assertSame(3, $usage->getPromptTokens());
        $this->assertSame(1, $usage->getCompletionTokens());
    }

    public function testUnknownModelThrows(): void
    {
        $model = new Model('demo');
        $client = new class () implements ModelClientInterface {
            public function supports(Model $model): bool
            {
                return true;
            }

            public function request(Model $model, array|string $payload, array $options = []): RawResultInterface
            {
                throw new \LogicException('unreachable');
            }
        };
        $converter = new class () implements ResultConverterInterface {
            public function supports(Model $model): bool
            {
                return true;
            }

            public function convert(RawResultInterface $result, array $options = []): ResultInterface
            {
                throw new \LogicException('unreachable');
            }

            public function getTokenUsageExtractor(): ?TokenUsageExtractorInterface
            {
                return null;
            }
        };

        $platform = new Platform(new ModelCatalog(['demo' => $model]), $client, $converter);

        $this->expectException(ModelNotFoundException::class);

        $platform->invoke('missing', 'hello');
    }
}
