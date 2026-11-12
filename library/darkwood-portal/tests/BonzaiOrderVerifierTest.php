<?php

declare(strict_types=1);

namespace App\Tests;

use App\Enum\CommercialState;
use App\Service\Bonzai\BonzaiApiClient;
use App\Service\Bonzai\BonzaiOrderVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(BonzaiOrderVerifier::class)]
final class BonzaiOrderVerifierTest extends TestCase
{
    public function testActiveAccessRequiresAnAuthoritativeMatchingOrderAndProduct(): void
    {
        $http = new MockHttpClient(new JsonMockResponse([
            'id' => 12345,
            'intent_status' => 'completed',
            'subscription_status' => 'active',
            'subscription_ends_at' => '2026-12-01T00:00:00+00:00',
            'has_access' => true,
            'product' => ['id' => 199],
            'metadata' => ['correlation' => 'opaque'],
        ]));
        $verifier = new BonzaiOrderVerifier(new BonzaiApiClient($http, 'test-token'), '199');

        $order = $verifier->verify('12345');

        self::assertSame(CommercialState::Active, $order->state);
        self::assertSame('199', $order->productId);
        self::assertTrue($order->auditData['has_access']);
    }

    public function testMismatchedProductCannotGrantAccess(): void
    {
        $http = new MockHttpClient(new JsonMockResponse([
            'id' => 12345,
            'intent_status' => 'completed',
            'has_access' => true,
            'product' => ['id' => 999],
        ]));
        $verifier = new BonzaiOrderVerifier(new BonzaiApiClient($http, 'test-token'), '199');

        $this->expectException(\RuntimeException::class);
        $verifier->verify('12345');
    }
}
