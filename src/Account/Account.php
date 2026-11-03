<?php

namespace App\Account;

final readonly class Account
{
    public function __construct(
        public string $id,
        public string $providerKey,
        public string $productKey,
        public ?string $alias,
        public AuthenticationMethod $authenticationMethod,
        public ?string $authenticationReference,
        public bool $monitoringEnabled,
        public AuthenticationVerificationState $authenticationVerificationState,
        public ?\DateTimeImmutable $authenticationVerifiedAt,
        public ?string $authenticationStatusDetail,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function authenticationConfigured(): bool
    {
        return AuthenticationMethod::None !== $this->authenticationMethod;
    }

    public function redactedAuthenticationReference(): ?string
    {
        if (null === $this->authenticationReference) {
            return null;
        }

        return sprintf('[configured %d bytes]', strlen($this->authenticationReference));
    }

    /**
     * @return array<string, mixed>
     */
    public function toSafeArray(bool $includeReferenceDescription = false): array
    {
        $data = [
            'id' => $this->id,
            'provider' => $this->providerKey,
            'product' => $this->productKey,
            'alias' => $this->alias,
            'registration' => 'registered',
            'authenticationMethod' => $this->authenticationMethod->value,
            'authenticationConfigured' => $this->authenticationConfigured(),
            'authenticationVerification' => $this->authenticationVerificationState->value,
            'authenticationVerifiedAt' => $this->authenticationVerifiedAt?->format(DATE_ATOM),
            'monitoringEnabled' => $this->monitoringEnabled,
            'createdAt' => $this->createdAt->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->format(DATE_ATOM),
        ];

        if ($includeReferenceDescription) {
            $data['authenticationReference'] = $this->redactedAuthenticationReference();
        }

        return $data;
    }
}
