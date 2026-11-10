<?php

declare(strict_types=1);

namespace App\Service\Bonzai;

use App\Entity\ExternalPurchase;
use App\Entity\User;
use App\Enum\PaymentProvider;
use App\Service\CanonicalEntitlementService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

final readonly class BonzaiNotificationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BonzaiOrderVerifier $verifier,
        private CanonicalEntitlementService $entitlements,
        private LoggerInterface $logger,
    ) {}

    /** @return array{status: string, reason?: string} */
    public function handle(string $orderId): array
    {
        $purchase = $this->entityManager->getRepository(ExternalPurchase::class)->findOneBy([
            'provider' => PaymentProvider::Bonzai,
            'providerPurchaseId' => $orderId,
        ]);
        if (!$purchase instanceof ExternalPurchase) {
            $purchase = new ExternalPurchase(PaymentProvider::Bonzai, $orderId);
            $this->entityManager->persist($purchase);
            $this->entityManager->flush();
        }

        try {
            $verified = $this->verifier->verify($orderId);
        } catch (Throwable $exception) {
            $this->logger->warning('bonzai.order_verification_failed', [
                'order_hash' => hash('sha256', $orderId),
                'exception' => $exception::class,
            ]);

            return ['status' => 'pending', 'reason' => 'provider_verification_failed'];
        }

        $purchase->recordVerification(
            $verified->state,
            $verified->productId,
            $verified->customerId,
            $verified->validUntil,
            $verified->auditData,
        );
        $this->entityManager->flush();
        $this->entitlements->synchronize($purchase);

        return ['status' => null === $purchase->getUser() ? 'pending_association' : 'synchronized'];
    }

    public function associateVerifiedOrder(string $orderId, User $user): ExternalPurchase
    {
        $this->handle($orderId);
        $purchase = $this->entityManager->getRepository(ExternalPurchase::class)->findOneBy([
            'provider' => PaymentProvider::Bonzai,
            'providerPurchaseId' => $orderId,
        ]);
        if (!$purchase instanceof ExternalPurchase || !$purchase->isVerified()) {
            throw new RuntimeException('Bonzai order could not be verified; association refused.');
        }

        $purchase->associate($user);
        $this->entityManager->flush();
        $this->entitlements->synchronize($purchase);

        return $purchase;
    }
}
