<?php

namespace App\Storage;

use App\Account\Account;
use App\Account\AccountOperationException;
use App\Account\AuthenticationMethod;
use App\Account\AuthenticationVerificationState;
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
                id, provider_key, product_key, alias, auth_method, auth_reference,
                monitoring_enabled, auth_verification_state, auth_verified_at,
                auth_status_detail, created_at, updated_at
            ) VALUES (
                :id, :provider_key, :product_key, :alias, :auth_method, :auth_reference,
                :monitoring_enabled, :auth_verification_state, :auth_verified_at,
                :auth_status_detail, :created_at, :updated_at
            )
            SQL);

        try {
            $statement->execute($this->parameters($account));
        } catch (\PDOException $error) {
            if ('23000' === $error->getCode()) {
                throw new AccountOperationException(sprintf('Account "%s" already exists.', $account->id), ExitCode::Conflict);
            }

            throw $error;
        }
    }

    /**
     * @return list<Account>
     */
    public function all(?string $provider = null, ?string $product = null, ?bool $enabled = null): array
    {
        $where = [];
        $parameters = [];
        if (null !== $provider) {
            $where[] = 'provider_key = :provider';
            $parameters['provider'] = $provider;
        }
        if (null !== $product) {
            $where[] = 'product_key = :product';
            $parameters['product'] = $product;
        }
        if (null !== $enabled) {
            $where[] = 'monitoring_enabled = :enabled';
            $parameters['enabled'] = (int) $enabled;
        }

        $sql = 'SELECT * FROM account';
        if ([] !== $where) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }
        $sql .= ' ORDER BY provider_key, product_key, id';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    public function find(string $id): ?Account
    {
        $statement = $this->pdo()->prepare('SELECT * FROM account WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return false === $row ? null : $this->hydrate($row);
    }

    public function update(Account $account): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            UPDATE account SET
                alias = :alias,
                auth_method = :auth_method,
                auth_reference = :auth_reference,
                monitoring_enabled = :monitoring_enabled,
                auth_verification_state = :auth_verification_state,
                auth_verified_at = :auth_verified_at,
                auth_status_detail = :auth_status_detail,
                updated_at = :updated_at
            WHERE id = :id
            SQL);
        $parameters = $this->parameters($account);
        unset($parameters['provider_key'], $parameters['product_key'], $parameters['created_at']);
        $statement->execute($parameters);
    }

    public function delete(string $id): void
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('DELETE FROM account WHERE id = :id');
            $statement->execute(['id' => $id]);
            $pdo->commit();
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $error;
        }
    }

    public function setDefault(string $scopeType, string $scopeKey, string $accountId): void
    {
        $statement = $this->pdo()->prepare(<<<'SQL'
            INSERT INTO account_default (scope_type, scope_key, account_id, updated_at)
            VALUES (:scope_type, :scope_key, :account_id, :updated_at)
            ON CONFLICT (scope_type, scope_key) DO UPDATE SET
                account_id = excluded.account_id,
                updated_at = excluded.updated_at
            SQL);
        $statement->execute([
            'scope_type' => $scopeType,
            'scope_key' => $scopeKey,
            'account_id' => $accountId,
            'updated_at' => gmdate('c'),
        ]);
    }

    public function clearDefault(string $scopeType, string $scopeKey): void
    {
        $statement = $this->pdo()->prepare('DELETE FROM account_default WHERE scope_type = :scope_type AND scope_key = :scope_key');
        $statement->execute(['scope_type' => $scopeType, 'scope_key' => $scopeKey]);
    }

    public function defaultAccountId(string $scopeType, string $scopeKey): ?string
    {
        $statement = $this->pdo()->prepare('SELECT account_id FROM account_default WHERE scope_type = :scope_type AND scope_key = :scope_key');
        $statement->execute(['scope_type' => $scopeType, 'scope_key' => $scopeKey]);
        $id = $statement->fetchColumn();

        return false === $id ? null : (string) $id;
    }

    /**
     * @return array{provider: bool, product: bool}
     */
    public function defaultsFor(Account $account): array
    {
        return [
            'provider' => $account->id === $this->defaultAccountId('provider', $account->providerKey),
            'product' => $account->id === $this->defaultAccountId('product', $account->providerKey.':'.$account->productKey),
        ];
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

    /**
     * @return array<string, scalar|null>
     */
    private function parameters(Account $account): array
    {
        return [
            'id' => $account->id,
            'provider_key' => $account->providerKey,
            'product_key' => $account->productKey,
            'alias' => $account->alias,
            'auth_method' => $account->authenticationMethod->value,
            'auth_reference' => $account->authenticationReference,
            'monitoring_enabled' => (int) $account->monitoringEnabled,
            'auth_verification_state' => $account->authenticationVerificationState->value,
            'auth_verified_at' => $account->authenticationVerifiedAt?->format(DATE_ATOM),
            'auth_status_detail' => $account->authenticationStatusDetail,
            'created_at' => $account->createdAt->format(DATE_ATOM),
            'updated_at' => $account->updatedAt->format(DATE_ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Account
    {
        return new Account(
            id: (string) $row['id'],
            providerKey: (string) $row['provider_key'],
            productKey: (string) $row['product_key'],
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
}
