<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EntitlementDefinition;
use App\Entity\ExternalPurchase;
use App\Entity\UserEntitlement;
use App\Enum\CommercialState;
use App\Enum\EntitlementStatus;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CanonicalEntitlementService
{
    private const array BONZAI_MEMBERSHIP_ENTITLEMENTS = [
        'darkwood.membership' => 'Darkwood membership',
        'darkwaar.premium_levels' => 'Darkwaar premium levels',
    ];

    public function __construct(private EntityManagerInterface $entityManager) {}

    public function synchronize(ExternalPurchase $purchase): void
    {
        if (null === $purchase->getUser()) {
            return;
        }

        $status = match ($purchase->getState()) {
            CommercialState::Active => EntitlementStatus::Active,
            CommercialState::Expired => EntitlementStatus::Expired,
            CommercialState::Refunded => EntitlementStatus::Refunded,
            CommercialState::Revoked, CommercialState::Failed => EntitlementStatus::Revoked,
            CommercialState::Pending => EntitlementStatus::Pending,
        };

        foreach (self::BONZAI_MEMBERSHIP_ENTITLEMENTS as $code => $label) {
            $definition = $this->entityManager->getRepository(EntitlementDefinition::class)->findOneBy(['code' => $code]);
            if (!$definition instanceof EntitlementDefinition) {
                $definition = new EntitlementDefinition($code, $label);
                $this->entityManager->persist($definition);
            }

            $userEntitlement = $this->entityManager->getRepository(UserEntitlement::class)->findOneBy([
                'user' => $purchase->getUser(),
                'entitlement' => $definition,
                'sourcePurchase' => $purchase,
            ]);
            if (!$userEntitlement instanceof UserEntitlement) {
                $userEntitlement = new UserEntitlement($purchase->getUser(), $definition, $purchase);
                $this->entityManager->persist($userEntitlement);
            }
            $userEntitlement->update($status, $purchase->getValidUntil());
        }

        $this->entityManager->flush();
    }
}
