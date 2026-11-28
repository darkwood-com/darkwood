<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DarkwoodOidcSettings
{
    public function __construct(
        #[Autowire('%env(default::DARKWOOD_OIDC_ISSUER)%')]
        private ?string $issuer,
        #[Autowire('%env(default::DARKWOOD_OIDC_CLIENT_ID)%')]
        private ?string $clientId,
        #[Autowire('%env(default::DARKWOOD_OIDC_CLIENT_SECRET)%')]
        #[\SensitiveParameter]
        private ?string $clientSecret,
        #[Autowire('%env(default::UNIFLOW_OIDC_PILOT_UID)%')]
        private ?string $pilotUid,
        #[Autowire('%env(bool:default::UNIFLOW_OIDC_ALLOW_LINKED)%')]
        private bool $allowLinked,
    ) {}

    public function isEnabled(): bool
    {
        return '' !== $this->getIssuer()
            && '' !== trim((string) $this->clientId)
            && '' !== trim((string) $this->clientSecret)
            && ($this->allowLinked || '' !== trim((string) $this->pilotUid));
    }

    public function allowsSubject(string $subject): bool
    {
        return $this->isEnabled() && ($this->allowLinked || $subject === $this->pilotUid);
    }

    public function getIssuer(): string
    {
        return rtrim(trim((string) $this->issuer), '/');
    }
}
