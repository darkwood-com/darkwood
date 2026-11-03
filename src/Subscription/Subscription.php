<?php

namespace App\Subscription;

final readonly class Subscription
{
    public function __construct(
        public string $id,
        public string $accountId,
        public ?string $displayName,
        public ?string $planName,
        public BillingType $billingType,
        public SubscriptionStatus $status,
        public ?int $billingAmountMinor,
        public ?string $currency,
        public ?BillingInterval $billingInterval,
        public ?\DateTimeImmutable $renewsAt,
        public ?\DateTimeImmutable $expiresAt,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'accountId' => $this->accountId,
            'displayName' => $this->displayName,
            'planName' => $this->planName,
            'billingType' => $this->billingType->value,
            'status' => $this->status->value,
            'billingAmount' => null === $this->billingAmountMinor ? null : number_format($this->billingAmountMinor / 100, 2, '.', ''),
            'currency' => $this->currency,
            'billingInterval' => $this->billingInterval?->value,
            'renewsAt' => $this->renewsAt?->format(DATE_ATOM),
            'expiresAt' => $this->expiresAt?->format(DATE_ATOM),
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];
    }
}
