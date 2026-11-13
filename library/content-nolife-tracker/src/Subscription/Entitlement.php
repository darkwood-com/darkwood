<?php

namespace App\Subscription;

final readonly class Entitlement
{
    public function __construct(
        public string $subscriptionId,
        public string $productKey,
        public EntitlementAccess $accessStatus,
        public ?string $verificationSource,
        public ?string $verificationEvidence,
        public ?\DateTimeImmutable $verifiedAt,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'product' => $this->productKey,
            'accessStatus' => $this->accessStatus->value,
            'verificationSource' => $this->verificationSource,
            'verificationEvidence' => $this->verificationEvidence,
            'verifiedAt' => $this->verifiedAt?->format(DATE_ATOM),
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];
    }
}
