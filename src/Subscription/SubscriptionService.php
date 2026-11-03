<?php

namespace App\Subscription;

use App\Account\Account;
use App\Account\AccountOperationException;
use App\Account\AccountService;
use App\Provider\ProviderCatalog;
use App\Storage\SqliteStore;
use App\Terminal\ExitCode;

final class SubscriptionService
{
    public function __construct(
        private readonly SqliteStore $store,
        private readonly AccountService $accounts,
        private readonly ProviderCatalog $catalog,
    ) {
    }

    public function add(
        string $id,
        string $accountId,
        ?string $displayName,
        ?string $planName,
        string $billingType,
        string $status,
        ?string $billingAmount,
        ?string $currency,
        ?string $billingInterval,
        ?string $renewsAt,
        ?string $expiresAt,
    ): Subscription {
        $this->validateId($id);
        $this->accounts->get($accountId);
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $subscription = $this->build(
            $id, $accountId, $displayName, $planName, $billingType, $status,
            $billingAmount, $currency, $billingInterval, $renewsAt, $expiresAt,
            $now, $now,
        );
        $this->store->insertSubscription($subscription);

        return $subscription;
    }

    /** @return list<Subscription> */
    public function all(?string $provider = null, ?string $accountId = null): array
    {
        if (null !== $provider && null === $this->catalog->find($provider)) {
            throw new SubscriptionOperationException(sprintf('Provider "%s" was not found.', $provider), ExitCode::NotFound);
        }
        if (null !== $accountId) {
            $account = $this->account($accountId);
            if (null !== $provider && $provider !== $account->providerKey) {
                throw new SubscriptionOperationException('The account does not belong to the selected provider.', ExitCode::InvalidInput);
            }
        }

        return $this->store->allSubscriptions($provider, $accountId);
    }

    public function get(string $id): Subscription
    {
        $subscription = $this->store->findSubscription($id);
        if (null === $subscription) {
            throw new SubscriptionOperationException(sprintf('Subscription "%s" was not found.', $id), ExitCode::NotFound);
        }

        return $subscription;
    }

    public function edit(
        string $id,
        ?string $displayName,
        ?string $planName,
        string $billingType,
        string $status,
        ?string $billingAmount,
        ?string $currency,
        ?string $billingInterval,
        ?string $renewsAt,
        ?string $expiresAt,
    ): Subscription {
        $current = $this->get($id);
        $updated = $this->build(
            $current->id, $current->accountId, $displayName, $planName,
            $billingType, $status, $billingAmount, $currency, $billingInterval,
            $renewsAt, $expiresAt, $current->createdAt,
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );
        $this->store->updateSubscription($updated);

        return $updated;
    }

    public function remove(string $id): Subscription
    {
        $subscription = $this->get($id);
        $this->store->deleteSubscription($id);

        return $subscription;
    }

    /** @return list<Entitlement> */
    public function entitlements(string $subscriptionId): array
    {
        $this->get($subscriptionId);

        return $this->store->entitlements($subscriptionId);
    }

    public function addEntitlement(string $subscriptionId, string $productKey, string $accessStatus, ?string $evidence): Entitlement
    {
        $subscription = $this->get($subscriptionId);
        $account = $this->account($subscription->accountId);
        if (null === $this->catalog->product($account->providerKey, $productKey)) {
            throw new SubscriptionOperationException(sprintf('Product "%s" does not belong to provider "%s".', $productKey, $account->providerKey), ExitCode::InvalidInput);
        }
        $access = EntitlementAccess::tryFrom($accessStatus);
        if (null === $access) {
            throw new SubscriptionOperationException('Access status must be "unknown", "declared", or "verified".', ExitCode::InvalidInput);
        }
        if (EntitlementAccess::Verified === $access) {
            throw new SubscriptionOperationException('Manual commands cannot mark access as verified; provider verification is not implemented.', ExitCode::InvalidInput);
        }
        $evidence = $this->optionalText($evidence, 'Evidence', 255);
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $entitlement = new Entitlement(
            subscriptionId: $subscriptionId,
            productKey: $productKey,
            accessStatus: $access,
            verificationSource: EntitlementAccess::Declared === $access ? 'user_declared' : null,
            verificationEvidence: $evidence,
            verifiedAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
        $this->store->insertEntitlement($entitlement);

        return $entitlement;
    }

    public function removeEntitlement(string $subscriptionId, string $productKey): void
    {
        $this->get($subscriptionId);
        if (!$this->store->deleteEntitlement($subscriptionId, $productKey)) {
            throw new SubscriptionOperationException(sprintf('Product "%s" is not associated with subscription "%s".', $productKey, $subscriptionId), ExitCode::NotFound);
        }
    }

    public function accountFor(Subscription $subscription): Account
    {
        return $this->account($subscription->accountId);
    }

    private function account(string $id): Account
    {
        try {
            return $this->accounts->get($id);
        } catch (AccountOperationException $error) {
            throw new SubscriptionOperationException($error->getMessage(), $error->exitCode);
        }
    }

    private function build(
        string $id, string $accountId, ?string $displayName, ?string $planName,
        string $billingType, string $status, ?string $billingAmount, ?string $currency,
        ?string $billingInterval, ?string $renewsAt, ?string $expiresAt,
        \DateTimeImmutable $createdAt, \DateTimeImmutable $updatedAt,
    ): Subscription {
        $type = BillingType::tryFrom($billingType);
        $state = SubscriptionStatus::tryFrom($status);
        if (null === $type) {
            throw new SubscriptionOperationException('Billing type must be free, recurring, pay_as_you_go, prepaid, enterprise, or unknown.', ExitCode::InvalidInput);
        }
        if (null === $state) {
            throw new SubscriptionOperationException('Status must be active, inactive, expired, canceled, or unknown.', ExitCode::InvalidInput);
        }
        $interval = null;
        if (null !== $billingInterval && '' !== trim($billingInterval)) {
            $interval = BillingInterval::tryFrom(trim($billingInterval));
            if (null === $interval) {
                throw new SubscriptionOperationException('Billing interval must be day, week, month, year, or omitted.', ExitCode::InvalidInput);
            }
        }
        $amountMinor = $this->amount($billingAmount);
        $currency = $this->optionalText($currency, 'Currency', 3);
        if (null !== $currency) {
            $currency = strtoupper($currency);
            if (1 !== preg_match('/^[A-Z]{3}$/', $currency)) {
                throw new SubscriptionOperationException('Currency must be a three-letter ISO 4217 code.', ExitCode::InvalidInput);
            }
        }
        if ((null === $amountMinor) !== (null === $currency)) {
            throw new SubscriptionOperationException('Billing amount and currency must be provided or omitted together.', ExitCode::InvalidInput);
        }

        return new Subscription(
            id: $id, accountId: $accountId,
            displayName: $this->optionalText($displayName, 'Display name', 100),
            planName: $this->optionalText($planName, 'Plan name', 100),
            billingType: $type, status: $state,
            billingAmountMinor: $amountMinor, currency: $currency,
            billingInterval: $interval,
            renewsAt: $this->date($renewsAt, 'Renewal date'),
            expiresAt: $this->date($expiresAt, 'Expiration date'),
            createdAt: $createdAt, updatedAt: $updatedAt,
        );
    }

    private function validateId(string $id): void
    {
        if (1 !== preg_match('/^[a-z0-9][a-z0-9_-]{1,63}$/', $id)) {
            throw new SubscriptionOperationException('Subscription ID must be 2-64 lowercase letters, digits, underscores, or hyphens.', ExitCode::InvalidInput);
        }
    }

    private function amount(?string $amount): ?int
    {
        if (null === $amount || '' === trim($amount)) {
            return null;
        }
        $amount = trim($amount);
        if (1 !== preg_match('/^(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/', $amount)) {
            throw new SubscriptionOperationException('Billing amount must be a non-negative decimal with at most two fractional digits.', ExitCode::InvalidInput);
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function date(?string $value, string $label): ?\DateTimeImmutable
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }
        try {
            return new \DateTimeImmutable(trim($value));
        } catch (\Exception) {
            throw new SubscriptionOperationException(sprintf('%s must be a valid ISO 8601 date or timestamp.', $label), ExitCode::InvalidInput);
        }
    }

    private function optionalText(?string $value, string $label, int $maximumLength): ?string
    {
        if (null === $value || '' === trim($value)) {
            return null;
        }
        $value = trim($value);
        if (strlen($value) > $maximumLength || 1 === preg_match('/[\x00-\x1F\x7F]/u', $value)) {
            throw new SubscriptionOperationException(sprintf('%s is invalid or exceeds %d characters.', $label, $maximumLength), ExitCode::InvalidInput);
        }

        return $value;
    }
}
