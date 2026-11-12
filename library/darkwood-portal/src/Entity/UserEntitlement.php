<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EntitlementStatus;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_entitlement')]
#[ORM\UniqueConstraint(name: 'uniq_user_entitlement_source', columns: ['user_id', 'entitlement_id', 'source_purchase_id'])]
class UserEntitlement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: EntitlementDefinition::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private EntitlementDefinition $entitlement;

    #[ORM\ManyToOne(targetEntity: ExternalPurchase::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ExternalPurchase $sourcePurchase;

    #[ORM\Column(enumType: EntitlementStatus::class, length: 16)]
    private EntitlementStatus $status = EntitlementStatus::Pending;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $validFrom;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $validUntil = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(User $user, EntitlementDefinition $entitlement, ?ExternalPurchase $sourcePurchase)
    {
        $this->user = $user;
        $this->entitlement = $entitlement;
        $this->sourcePurchase = $sourcePurchase;
        $this->validFrom = new DateTimeImmutable();
        $this->updatedAt = $this->validFrom;
    }

    public function getStatus(): EntitlementStatus { return $this->status; }
    public function getEntitlement(): EntitlementDefinition { return $this->entitlement; }
    public function getValidUntil(): ?DateTimeImmutable { return $this->validUntil; }

    public function update(EntitlementStatus $status, ?DateTimeImmutable $validUntil): void
    {
        $this->status = $status;
        $this->validUntil = $validUntil;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isActiveAt(DateTimeImmutable $at): bool
    {
        return EntitlementStatus::Active === $this->status
            && $this->validFrom <= $at
            && (null === $this->validUntil || $this->validUntil > $at);
    }
}
