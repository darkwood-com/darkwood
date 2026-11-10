<?php

namespace App\Storage;

use App\Account\Account;
use App\Account\AccountOperationException;
use App\Account\AuthenticationMethod;
use App\Account\AuthenticationVerificationState;
use App\Subscription\BillingInterval;
use App\Subscription\BillingType;
use App\Subscription\Entitlement;
use App\Subscription\EntitlementAccess;
use App\Subscription\Subscription;
use App\Subscription\SubscriptionOperationException;
use App\Subscription\SubscriptionStatus;
use App\Terminal\ExitCode;

final class SqliteStore
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly DataDirectory $dataDirectory)
    {
    }

    public function insert(Account $account): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            INSERT INTO account (
                id, provider_key, alias, auth_method, auth_reference,
                monitoring_enabled, auth_verification_state, auth_verified_at,
                auth_status_detail, created_at, updated_at
            ) VALUES (
                :id, :provider_key, :alias, :auth_method, :auth_reference,
                :monitoring_enabled, :auth_verification_state, :auth_verified_at,
                :auth_status_detail, :created_at, :updated_at
            )
            SQL);
        try {
            $statement->execute($this->accountParameters($account));
        } catch (\PDOException $error) {
            if ('23000' === $error->getCode()) {
                throw new AccountOperationException(sprintf('Account "%s" already exists.', $account->id), ExitCode::Conflict);
            }
            throw $error;
        }
    }

    /** @return list<Account> */
    public function all(?string $provider = null, ?string $product = null, ?bool $enabled = null): array
    {
        $where = [];
        $parameters = [];
        $sql = 'SELECT DISTINCT account.* FROM account';
        if (null !== $product) {
            $sql .= ' JOIN subscription ON subscription.account_id = account.id'
                .' JOIN subscription_entitlement entitlement ON entitlement.subscription_id = subscription.id';
            $where[] = 'entitlement.product_key = :product';
            $parameters['product'] = $product;
        }
        if (null !== $provider) {
            $where[] = 'account.provider_key = :provider';
            $parameters['provider'] = $provider;
        }
        if (null !== $enabled) {
            $where[] = 'account.monitoring_enabled = :enabled';
            $parameters['enabled'] = (int) $enabled;
        }
        if ([] !== $where) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }
        $sql .= ' ORDER BY account.provider_key, account.id';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return array_map($this->hydrateAccount(...), $statement->fetchAll());
    }

    public function find(string $id): ?Account
    {
        $statement = $this->pdo()->prepare('SELECT * FROM account WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return false === $row ? null : $this->hydrateAccount($row);
    }

    public function update(Account $account): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            UPDATE account SET
                alias = :alias, auth_method = :auth_method,
                auth_reference = :auth_reference, monitoring_enabled = :monitoring_enabled,
                auth_verification_state = :auth_verification_state,
                auth_verified_at = :auth_verified_at, auth_status_detail = :auth_status_detail,
                updated_at = :updated_at
            WHERE id = :id
            SQL);
        $parameters = $this->accountParameters($account);
        unset($parameters['provider_key'], $parameters['created_at']);
        $statement->execute($parameters);
    }

    public function delete(string $id): void
    {
        $statement = $this->pdo()->prepare('DELETE FROM account WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function setDefault(string $providerKey, string $accountId): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            INSERT INTO account_default (provider_key, account_id, updated_at)
            VALUES (:provider_key, :account_id, :updated_at)
            ON CONFLICT (provider_key) DO UPDATE SET
                account_id = excluded.account_id, updated_at = excluded.updated_at
            SQL);
        $statement->execute(['provider_key' => $providerKey, 'account_id' => $accountId, 'updated_at' => gmdate('c')]);
    }

    public function clearDefault(string $providerKey): void
    {
        $statement = $this->pdo()->prepare('DELETE FROM account_default WHERE provider_key = :provider_key');
        $statement->execute(['provider_key' => $providerKey]);
    }

    public function defaultAccountId(string $providerKey): ?string
    {
        $statement = $this->pdo()->prepare('SELECT account_id FROM account_default WHERE provider_key = :provider_key');
        $statement->execute(['provider_key' => $providerKey]);
        $id = $statement->fetchColumn();

        return false === $id ? null : (string) $id;
    }

    public function isDefault(Account $account): bool
    {
        return $account->id === $this->defaultAccountId($account->providerKey);
    }

    /** @return array<string, mixed>|null */
    public function unresolvedLegacyProduct(string $accountId): ?array
    {
        $statement = $this->pdo()->prepare('SELECT * FROM unresolved_legacy_product WHERE account_id = :account_id');
        $statement->execute(['account_id' => $accountId]);
        $row = $statement->fetch();

        return false === $row ? null : $row;
    }

    public function insertSubscription(Subscription $subscription): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            INSERT INTO subscription (
                id, account_id, display_name, plan_name, billing_type, status,
                billing_amount_minor, currency, billing_interval, renews_at,
                expires_at, created_at, updated_at
            ) VALUES (
                :id, :account_id, :display_name, :plan_name, :billing_type, :status,
                :billing_amount_minor, :currency, :billing_interval, :renews_at,
                :expires_at, :created_at, :updated_at
            )
            SQL);
        try {
            $statement->execute($this->subscriptionParameters($subscription));
        } catch (\PDOException $error) {
            if ('23000' === $error->getCode()) {
                throw new SubscriptionOperationException(sprintf('Subscription "%s" already exists.', $subscription->id), ExitCode::Conflict);
            }
            throw $error;
        }
    }

    /** @return list<Subscription> */
    public function allSubscriptions(?string $provider = null, ?string $accountId = null): array
    {
        $where = [];
        $parameters = [];
        if (null !== $provider) {
            $where[] = 'account.provider_key = :provider';
            $parameters['provider'] = $provider;
        }
        if (null !== $accountId) {
            $where[] = 'subscription.account_id = :account_id';
            $parameters['account_id'] = $accountId;
        }
        $sql = 'SELECT subscription.* FROM subscription JOIN account ON account.id = subscription.account_id';
        if ([] !== $where) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }
        $sql .= ' ORDER BY account.provider_key, subscription.account_id, subscription.id';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return array_map($this->hydrateSubscription(...), $statement->fetchAll());
    }

    public function findSubscription(string $id): ?Subscription
    {
        $statement = $this->pdo()->prepare('SELECT * FROM subscription WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return false === $row ? null : $this->hydrateSubscription($row);
    }

    public function updateSubscription(Subscription $subscription): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            UPDATE subscription SET
                display_name = :display_name, plan_name = :plan_name,
                billing_type = :billing_type, status = :status,
                billing_amount_minor = :billing_amount_minor, currency = :currency,
                billing_interval = :billing_interval, renews_at = :renews_at,
                expires_at = :expires_at, updated_at = :updated_at
            WHERE id = :id
            SQL);
        $parameters = $this->subscriptionParameters($subscription);
        unset($parameters['account_id'], $parameters['created_at']);
        $statement->execute($parameters);
    }

    public function deleteSubscription(string $id): void
    {
        $statement = $this->pdo()->prepare('DELETE FROM subscription WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function insertEntitlement(Entitlement $entitlement): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            INSERT INTO subscription_entitlement (
                subscription_id, product_key, access_status, verification_source,
                verification_evidence, verified_at, created_at, updated_at
            ) VALUES (
                :subscription_id, :product_key, :access_status, :verification_source,
                :verification_evidence, :verified_at, :created_at, :updated_at
            )
            SQL);
        try {
            $statement->execute($this->entitlementParameters($entitlement));
        } catch (\PDOException $error) {
            if ('23000' === $error->getCode()) {
                throw new SubscriptionOperationException(sprintf('Product "%s" is already associated with subscription "%s".', $entitlement->productKey, $entitlement->subscriptionId), ExitCode::Conflict);
            }
            throw $error;
        }
    }

    /** @return list<Entitlement> */
    public function entitlements(string $subscriptionId): array
    {
        $statement = $this->pdo()->prepare('SELECT * FROM subscription_entitlement WHERE subscription_id = :subscription_id ORDER BY product_key');
        $statement->execute(['subscription_id' => $subscriptionId]);

        return array_map($this->hydrateEntitlement(...), $statement->fetchAll());
    }

    public function deleteEntitlement(string $subscriptionId, string $productKey): bool
    {
        $statement = $this->pdo()->prepare('DELETE FROM subscription_entitlement WHERE subscription_id = :subscription_id AND product_key = :product_key');
        $statement->execute(['subscription_id' => $subscriptionId, 'product_key' => $productKey]);

        return 0 < $statement->rowCount();
    }

    public function databasePath(): string
    {
        return $this->dataDirectory->databasePath();
    }

    private function pdo(): \PDO
    {
        if (null !== $this->pdo) {
            return $this->pdo;
        }
        $databasePath = $this->dataDirectory->databasePath();
        $this->pdo = new \PDO('sqlite:'.$databasePath, null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        $this->pdo->exec('PRAGMA busy_timeout = 5000');
        Schema::initialize($this->pdo);
        if ('Windows' !== PHP_OS_FAMILY) {
            @chmod($databasePath, 0600);
        }

        return $this->pdo;
    }

    /** @return array<string, scalar|null> */
    private function accountParameters(Account $account): array
    {
        return [
            'id' => $account->id, 'provider_key' => $account->providerKey,
            'alias' => $account->alias, 'auth_method' => $account->authenticationMethod->value,
            'auth_reference' => $account->authenticationReference,
            'monitoring_enabled' => (int) $account->monitoringEnabled,
            'auth_verification_state' => $account->authenticationVerificationState->value,
            'auth_verified_at' => $account->authenticationVerifiedAt?->format(DATE_ATOM),
            'auth_status_detail' => $account->authenticationStatusDetail,
            'created_at' => $account->createdAt->format(DATE_ATOM),
            'updated_at' => $account->updatedAt->format(DATE_ATOM),
        ];
    }

    /** @param array<string, mixed> $row */
    private function hydrateAccount(array $row): Account
    {
        return new Account(
            id: (string) $row['id'], providerKey: (string) $row['provider_key'],
            alias: null === $row['alias'] ? null : (string) $row['alias'],
            authenticationMethod: AuthenticationMethod::from((string) $row['auth_method']),
            authenticationReference: null === $row['auth_reference'] ? null : (string) $row['auth_reference'],
            monitoringEnabled: (bool) $row['monitoring_enabled'],
            authenticationVerificationState: AuthenticationVerificationState::from((string) $row['auth_verification_state']),
            authenticationVerifiedAt: null === $row['auth_verified_at'] ? null : new \DateTimeImmutable((string) $row['auth_verified_at']),
            authenticationStatusDetail: null === $row['auth_status_detail'] ? null : (string) $row['auth_status_detail'],
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
            updatedAt: new \DateTimeImmutable((string) $row['updated_at']),
        );
    }

    /** @return array<string, scalar|null> */
    private function subscriptionParameters(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id, 'account_id' => $subscription->accountId,
            'display_name' => $subscription->displayName, 'plan_name' => $subscription->planName,
            'billing_type' => $subscription->billingType->value, 'status' => $subscription->status->value,
            'billing_amount_minor' => $subscription->billingAmountMinor, 'currency' => $subscription->currency,
            'billing_interval' => $subscription->billingInterval?->value,
            'renews_at' => $subscription->renewsAt?->format(DATE_ATOM),
            'expires_at' => $subscription->expiresAt?->format(DATE_ATOM),
            'created_at' => $subscription->createdAt->format(DATE_ATOM),
            'updated_at' => $subscription->updatedAt->format(DATE_ATOM),
        ];
    }

    /** @param array<string, mixed> $row */
    private function hydrateSubscription(array $row): Subscription
    {
        return new Subscription(
            id: (string) $row['id'], accountId: (string) $row['account_id'],
            displayName: null === $row['display_name'] ? null : (string) $row['display_name'],
            planName: null === $row['plan_name'] ? null : (string) $row['plan_name'],
            billingType: BillingType::from((string) $row['billing_type']),
            status: SubscriptionStatus::from((string) $row['status']),
            billingAmountMinor: null === $row['billing_amount_minor'] ? null : (int) $row['billing_amount_minor'],
            currency: null === $row['currency'] ? null : (string) $row['currency'],
            billingInterval: null === $row['billing_interval'] ? null : BillingInterval::from((string) $row['billing_interval']),
            renewsAt: null === $row['renews_at'] ? null : new \DateTimeImmutable((string) $row['renews_at']),
            expiresAt: null === $row['expires_at'] ? null : new \DateTimeImmutable((string) $row['expires_at']),
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
            updatedAt: new \DateTimeImmutable((string) $row['updated_at']),
        );
    }

    /** @return array<string, scalar|null> */
    private function entitlementParameters(Entitlement $entitlement): array
    {
        return [
            'subscription_id' => $entitlement->subscriptionId, 'product_key' => $entitlement->productKey,
            'access_status' => $entitlement->accessStatus->value,
            'verification_source' => $entitlement->verificationSource,
            'verification_evidence' => $entitlement->verificationEvidence,
            'verified_at' => $entitlement->verifiedAt?->format(DATE_ATOM),
            'created_at' => $entitlement->createdAt->format(DATE_ATOM),
            'updated_at' => $entitlement->updatedAt->format(DATE_ATOM),
        ];
    }

    /** @param array<string, mixed> $row */
    private function hydrateEntitlement(array $row): Entitlement
    {
        return new Entitlement(
            subscriptionId: (string) $row['subscription_id'], productKey: (string) $row['product_key'],
            accessStatus: EntitlementAccess::from((string) $row['access_status']),
            verificationSource: null === $row['verification_source'] ? null : (string) $row['verification_source'],
            verificationEvidence: null === $row['verification_evidence'] ? null : (string) $row['verification_evidence'],
            verifiedAt: null === $row['verified_at'] ? null : new \DateTimeImmutable((string) $row['verified_at']),
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
            updatedAt: new \DateTimeImmutable((string) $row['updated_at']),
        );
    }
}
