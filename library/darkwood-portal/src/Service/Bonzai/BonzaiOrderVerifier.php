<?php

declare(strict_types=1);

namespace App\Service\Bonzai;

use App\Enum\CommercialState;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class BonzaiOrderVerifier
{
    public function __construct(
        private BonzaiApiClient $api,
        #[Autowire('%env(BONZAI_PRODUCT_ID)%')]
        private string $expectedProductId,
    ) {}

    public function verify(string $orderId): VerifiedBonzaiOrder
    {
        if ('' === trim($this->expectedProductId)) {
            throw new \RuntimeException('Bonzai product verification is not configured.');
        }

        $order = $this->api->getOrder($orderId);
        $verifiedId = (string) ($order['id'] ?? '');
        if ('' === $verifiedId || !hash_equals($orderId, $verifiedId)) {
            throw new \RuntimeException('Bonzai returned a different order identifier.');
        }

        $product = is_array($order['product'] ?? null) ? $order['product'] : [];
        $productId = (string) ($product['uuid'] ?? $product['id'] ?? '');
        if ('' === $productId || !hash_equals($this->expectedProductId, $productId)) {
            throw new \RuntimeException('Bonzai order belongs to an unexpected product.');
        }

        $intentStatus = strtolower((string) ($order['intent_status'] ?? ''));
        $subscriptionStatus = strtolower((string) ($order['subscription_status'] ?? ''));
        $hasAccess = true === ($order['has_access'] ?? null);

        $state = CommercialState::Pending;
        if ($hasAccess && 'completed' === $intentStatus) {
            $state = CommercialState::Active;
        } elseif (str_contains($intentStatus, 'refund') || str_contains($subscriptionStatus, 'refund')) {
            $state = CommercialState::Refunded;
        } elseif (!$hasAccess && in_array($subscriptionStatus, ['expired', 'ended'], true)) {
            $state = CommercialState::Expired;
        } elseif (!$hasAccess && in_array($intentStatus, ['failed', 'canceled', 'cancelled'], true)) {
            $state = CommercialState::Failed;
        } elseif (!$hasAccess) {
            $state = CommercialState::Revoked;
        }

        $validUntil = null;
        if (is_string($order['subscription_ends_at'] ?? null) && '' !== $order['subscription_ends_at']) {
            $validUntil = new DateTimeImmutable($order['subscription_ends_at']);
        }

        $customer = is_array($order['user'] ?? null) ? $order['user'] : [];
        $auditData = [
            'mode' => $order['mode'] ?? null,
            'intent_status' => $order['intent_status'] ?? null,
            'subscription_status' => $order['subscription_status'] ?? null,
            'has_access' => $hasAccess,
            'metadata' => is_array($order['metadata'] ?? null) ? $order['metadata'] : [],
        ];

        return new VerifiedBonzaiOrder(
            $verifiedId,
            $productId,
            $state,
            isset($customer['id']) ? (string) $customer['id'] : null,
            $validUntil,
            $auditData,
        );
    }
}
