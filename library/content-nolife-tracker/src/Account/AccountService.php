<?php

namespace App\Account;

use App\Provider\ProviderCatalog;
use App\Storage\SqliteStore;
use App\Terminal\ExitCode;

final class AccountService
{
    public function __construct(
        private readonly ProviderCatalog $catalog,
        private readonly SqliteStore $store,
    ) {
    }

    public function add(
        string $id,
        string $providerKey,
        ?string $alias,
        string $authenticationMethod,
        ?string $authenticationReference,
        bool $monitoringEnabled,
    ): Account {
        $id = trim($id);
        if (1 !== preg_match('/^[a-z0-9][a-z0-9_-]{1,63}$/', $id)) {
            throw new AccountOperationException('Account ID must be 2-64 lowercase letters, digits, underscores, or hyphens.', ExitCode::InvalidInput);
        }

        $provider = $this->catalog->find($providerKey);
        if (null === $provider) {
            throw new AccountOperationException(sprintf('Provider "%s" was not found.', $providerKey), ExitCode::NotFound);
        }
        $method = AuthenticationMethod::tryFrom($authenticationMethod);
        if (null === $method || !\in_array($method, $this->catalog->authenticationMethods($providerKey), true)) {
            throw new AccountOperationException(sprintf('Authentication method "%s" is not declared for provider "%s".', $authenticationMethod, $providerKey), ExitCode::InvalidInput);
        }

        $alias = $this->optionalText($alias, 'Alias', 80);
        $authenticationReference = $this->authenticationReference($method, $authenticationReference);
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $account = new Account(
            id: $id,
            providerKey: $providerKey,
            alias: $alias,
            authenticationMethod: $method,
            authenticationReference: $authenticationReference,
            monitoringEnabled: $monitoringEnabled,
            authenticationVerificationState: AuthenticationMethod::None === $method
                ? AuthenticationVerificationState::NotConfigured
                : AuthenticationVerificationState::NotChecked,
            authenticationVerifiedAt: null,
            authenticationStatusDetail: null,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->store->insert($account);

        return $account;
    }

    /**
     * @return list<Account>
     */
    public function all(?string $provider = null, ?string $product = null, ?bool $enabled = null): array
    {
        if (null !== $provider && null === $this->catalog->find($provider)) {
            throw new AccountOperationException(sprintf('Provider "%s" was not found.', $provider), ExitCode::NotFound);
        }
        if (null !== $product && null === $provider) {
            throw new AccountOperationException('The --product filter requires --provider.', ExitCode::InvalidInput);
        }
        if (null !== $product && null === $this->catalog->product((string) $provider, $product)) {
            throw new AccountOperationException(sprintf('Product "%s" was not found for provider "%s".', $product, $provider), ExitCode::NotFound);
        }

        return $this->store->all($provider, $product, $enabled);
    }

    public function get(string $id): Account
    {
        $account = $this->store->find($id);
        if (null === $account) {
            throw new AccountOperationException(sprintf('Account "%s" was not found.', $id), ExitCode::NotFound);
        }

        return $account;
    }

    public function edit(
        string $id,
        ?string $alias,
        string $authenticationMethod,
        ?string $authenticationReference,
        bool $monitoringEnabled,
    ): Account {
        $current = $this->get($id);
        $method = AuthenticationMethod::tryFrom($authenticationMethod);
        if (null === $method || !\in_array($method, $this->catalog->authenticationMethods($current->providerKey), true)) {
            throw new AccountOperationException(sprintf('Authentication method "%s" is not declared for provider "%s".', $authenticationMethod, $current->providerKey), ExitCode::InvalidInput);
        }
        $alias = $this->optionalText($alias, 'Alias', 80);
        $authenticationReference = $this->authenticationReference($method, $authenticationReference);
        $authenticationChanged = $method !== $current->authenticationMethod
            || $authenticationReference !== $current->authenticationReference;

        $updated = new Account(
            id: $current->id,
            providerKey: $current->providerKey,
            alias: $alias,
            authenticationMethod: $method,
            authenticationReference: $authenticationReference,
            monitoringEnabled: $monitoringEnabled,
            authenticationVerificationState: $authenticationChanged
                ? (AuthenticationMethod::None === $method ? AuthenticationVerificationState::NotConfigured : AuthenticationVerificationState::NotChecked)
                : $current->authenticationVerificationState,
            authenticationVerifiedAt: $authenticationChanged ? null : $current->authenticationVerifiedAt,
            authenticationStatusDetail: $authenticationChanged ? null : $current->authenticationStatusDetail,
            createdAt: $current->createdAt,
            updatedAt: new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
        $this->store->update($updated);

        return $updated;
    }

    public function setMonitoring(string $id, bool $enabled): Account
    {
        $current = $this->get($id);

        return $this->edit(
            $current->id,
            $current->alias,
            $current->authenticationMethod->value,
            $current->authenticationReference,
            $enabled,
        );
    }

    public function remove(string $id): Account
    {
        $account = $this->get($id);
        $this->store->delete($id);

        return $account;
    }

    public function isDefault(Account $account): bool
    {
        return $this->store->isDefault($account);
    }

    public function setDefault(string $id, bool $clear = false): Account
    {
        $account = $this->get($id);

        if ($clear) {
            $this->store->clearDefault($account->providerKey);
        } else {
            $this->store->setDefault($account->providerKey, $account->id);
        }

        return $account;
    }

    /** @return array<string, mixed>|null */
    public function unresolvedLegacyProduct(string $id): ?array
    {
        $this->get($id);

        return $this->store->unresolvedLegacyProduct($id);
    }

    private function optionalText(?string $value, string $label, int $maximumLength): ?string
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }
        $value = trim($value);
        if (strlen($value) > $maximumLength) {
            throw new AccountOperationException(sprintf('%s must not exceed %d characters.', $label, $maximumLength), ExitCode::InvalidInput);
        }
        if (1 === preg_match('/[\x00-\x1F\x7F]/u', $value)) {
            throw new AccountOperationException(sprintf('%s must not contain control characters.', $label), ExitCode::InvalidInput);
        }

        return $value;
    }

    private function authenticationReference(AuthenticationMethod $method, ?string $reference): ?string
    {
        $reference = $this->optionalText($reference, 'Authentication reference', 255);
        if (AuthenticationMethod::None === $method) {
            if (null !== $reference) {
                throw new AccountOperationException('Authentication method "none" cannot have a reference.', ExitCode::InvalidInput);
            }

            return null;
        }
        if (AuthenticationMethod::OfficialApi === $method) {
            if (null === $reference || 1 !== preg_match('/^[A-Z][A-Z0-9_]{1,63}$/', $reference)) {
                throw new AccountOperationException('Official API authentication references must be environment variable names, never key values.', ExitCode::InvalidInput);
            }

            return $reference;
        }
        if (null === $reference) {
            throw new AccountOperationException(sprintf('Authentication method "%s" requires a non-secret reference.', $method->value), ExitCode::InvalidInput);
        }
        if (1 === preg_match('/(?:^sk-|bearer\s|cookie|token=|password=)/i', $reference)) {
            throw new AccountOperationException('Authentication references must be locators, not credentials or secret values.', ExitCode::InvalidInput);
        }

        return $reference;
    }
}
