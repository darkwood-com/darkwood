<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CommercialState;
use App\Enum\PaymentProvider;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'external_purchase')]
#[ORM\UniqueConstraint(name: 'uniq_external_purchase_provider_id', columns: ['provider', 'provider_purchase_id'])]
class ExternalPurchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: PaymentProvider::class, length: 16)]
    private PaymentProvider $provider;

    #[ORM\Column(length: 128)]
    private string $providerPurchaseId;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $providerProductId = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $providerCustomerId = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(enumType: CommercialState::class, length: 16)]
    private CommercialState $state = CommercialState::Pending;

    #[ORM\Column(type: Types::JSON)]
    private array $providerData = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $validUntil = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct(PaymentProvider $provider, string $providerPurchaseId)
    {
        $this->provider = $provider;
        $this->providerPurchaseId = $providerPurchaseId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getProvider(): PaymentProvider { return $this->provider; }
    public function getProviderPurchaseId(): string { return $this->providerPurchaseId; }
    public function getUser(): ?User { return $this->user; }
    public function getState(): CommercialState { return $this->state; }
    public function getValidUntil(): ?DateTimeImmutable { return $this->validUntil; }
    public function isVerified(): bool { return $this->verifiedAt instanceof DateTimeImmutable; }

    public function associate(User $user): void
    {
        if ($this->user instanceof User && $this->user !== $user) {
            throw new \LogicException('External purchase is already associated with another Darkwood account.');
        }
        $this->user = $user;
        $this->updatedAt = new DateTimeImmutable();
    }

    /** @param array<string, mixed> $providerData */
    public function recordCheckout(array $providerData): void
    {
        $this->providerData = $providerData;
        $this->updatedAt = new DateTimeImmutable();
    }

    /** @param array<string, mixed> $providerData */
    public function recordVerification(
        CommercialState $state,
        ?string $providerProductId,
        ?string $providerCustomerId,
        ?DateTimeImmutable $validUntil,
        array $providerData,
    ): void {
        $this->state = $state;
        $this->providerProductId = $providerProductId;
        $this->providerCustomerId = $providerCustomerId;
        $this->validUntil = $validUntil;
        $this->providerData = $providerData;
        $this->verifiedAt = new DateTimeImmutable();
        $this->updatedAt = $this->verifiedAt;
    }
}
