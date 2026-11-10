<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Entitlement;
use App\Entity\UserEntitlement;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\EntitlementRepository;

/**
 * Minimal entitlement service: premium only if active entitlement exists for the user.
 * No environment-based behavior.
 */
class DarkwoodEntitlementService
{
    public function __construct(
        private readonly EntitlementRepository $entitlementRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function isPremium(?User $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        return in_array('darkwood.membership', $this->activeCodes($user), true);
    }

    /** @return list<string> */
    public function activeCodes(User $user): array
    {
        $now = new DateTimeImmutable();
        $rows = $this->entityManager->createQueryBuilder()
            ->select('definition.code')
            ->from(UserEntitlement::class, 'grant')
            ->join('grant.entitlement', 'definition')
            ->andWhere('grant.user = :user')
            ->andWhere('grant.status = :status')
            ->andWhere('grant.validFrom <= :now')
            ->andWhere('grant.validUntil IS NULL OR grant.validUntil > :now')
            ->setParameter('user', $user)
            ->setParameter('status', \App\Enum\EntitlementStatus::Active)
            ->setParameter('now', $now)
            ->orderBy('definition.code', 'ASC')
            ->getQuery()
            ->getScalarResult();

        $codes = array_values(array_map(static fn (array $row): string => (string) $row['code'], $rows));
        if ($user->isPremium() || $this->entitlementRepository->findActivePremiumForUser($user) instanceof Entitlement) {
            $codes[] = 'darkwood.membership';
            $codes[] = 'darkwaar.premium_levels';
        }

        return array_values(array_unique($codes));
    }
}
