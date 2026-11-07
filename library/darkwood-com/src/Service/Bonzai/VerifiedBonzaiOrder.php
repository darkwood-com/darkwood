<?php

declare(strict_types=1);

namespace App\Service\Bonzai;

use App\Enum\CommercialState;
use DateTimeImmutable;

final readonly class VerifiedBonzaiOrder
{
    /** @param array<string, mixed> $auditData */
    public function __construct(
        public string $orderId,
        public string $productId,
        public CommercialState $state,
        public ?string $customerId,
        public ?DateTimeImmutable $validUntil,
        public array $auditData,
    ) {}
}
