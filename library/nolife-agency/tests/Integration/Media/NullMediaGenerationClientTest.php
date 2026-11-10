<?php

namespace App\Tests\Integration\Media;

use App\Integration\Media\MediaGenerationRequest;
use App\Integration\Media\NullMediaGenerationClient;
use PHPUnit\Framework\TestCase;

final class NullMediaGenerationClientTest extends TestCase
{
    public function testRequestReturnsLocalNoopResult(): void
    {
        $client = new NullMediaGenerationClient();

        $result = $client->request(new MediaGenerationRequest('video', 'run-1', ['prompt' => 'demo']));

        self::assertSame('local-media-video-run-1', $result->requestId);
        self::assertSame('noop', $result->status);
        self::assertSame('video', $result->metadata['kind']);
    }
}
