<?php

namespace App\Storage;

final class Schema
{
    public const VERSION = 2;

    public static function initialize(\PDO $pdo): void
    {
        $version = (int) $pdo->query('PRAGMA user_version')->fetchColumn();
        if ($version > self::VERSION) {
            throw new \RuntimeException(sprintf('Database schema version %d is newer than supported version %d.', $version, self::VERSION));
        }
        if (self::VERSION === $version) {
            return;
        }
        if (1 === $version) {
            self::migrateFromVersionOne($pdo);

            return;
        }

        $pdo->beginTransaction();
        try {
            self::createVersionTwo($pdo);
            $pdo->exec('PRAGMA user_version = '.self::VERSION);
            $pdo->commit();
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    }

    private static function migrateFromVersionOne(\PDO $pdo): void
    {
        $pdo->exec('PRAGMA foreign_keys = OFF');
        $pdo->beginTransaction();
        try {
            $pdo->exec('ALTER TABLE account RENAME TO account_v1');
            $pdo->exec('ALTER TABLE account_default RENAME TO account_default_v1');
            self::createVersionTwo($pdo);
            $pdo->exec(<<<'SQL'
                INSERT INTO account (
                    id, provider_key, alias, auth_method, auth_reference,
                    monitoring_enabled, auth_verification_state, auth_verified_at,
                    auth_status_detail, created_at, updated_at
                )
                SELECT
                    id, provider_key, alias, auth_method, auth_reference,
                    monitoring_enabled, auth_verification_state, auth_verified_at,
                    auth_status_detail, created_at, updated_at
                FROM account_v1
                SQL);
            $pdo->exec(<<<'SQL'
                INSERT INTO account_default (provider_key, account_id, updated_at)
                SELECT scope_key, account_id, updated_at
                FROM account_default_v1
                WHERE scope_type = 'provider'
                SQL);
            $pdo->exec(<<<'SQL'
                INSERT INTO unresolved_legacy_product (
                    account_id, provider_key, product_key, was_product_default,
                    resolution_status, migrated_at
                )
                SELECT
                    account.id, account.provider_key, account.product_key,
                    CASE WHEN defaults.account_id IS NULL THEN 0 ELSE 1 END,
                    'unresolved', strftime('%Y-%m-%dT%H:%M:%fZ', 'now')
                FROM account_v1 account
                LEFT JOIN account_default_v1 defaults
                    ON defaults.scope_type = 'product'
                    AND defaults.scope_key = account.provider_key || ':' || account.product_key
                    AND defaults.account_id = account.id
                SQL);
            $pdo->exec('DROP TABLE account_default_v1');
            $pdo->exec('DROP TABLE account_v1');
            $pdo->exec('PRAGMA user_version = '.self::VERSION);
            $pdo->commit();
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        } finally {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        if ([] !== $pdo->query('PRAGMA foreign_key_check')->fetchAll()) {
            throw new \RuntimeException('The v0.1 database migration produced invalid foreign-key references.');
        }
    }

    private static function createVersionTwo(\PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
            CREATE TABLE account (
                id TEXT PRIMARY KEY,
                provider_key TEXT NOT NULL,
                alias TEXT NULL,
                auth_method TEXT NOT NULL CHECK (auth_method IN ('none', 'existing_cli', 'official_api', 'os_credential_store')),
                auth_reference TEXT NULL,
                monitoring_enabled INTEGER NOT NULL CHECK (monitoring_enabled IN (0, 1)),
                auth_verification_state TEXT NOT NULL CHECK (auth_verification_state IN ('not_configured', 'not_checked', 'verified', 'invalid', 'unavailable', 'error')),
                auth_verified_at TEXT NULL,
                auth_status_detail TEXT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )
            SQL);
        $pdo->exec('CREATE INDEX account_provider ON account (provider_key, id)');
        $pdo->exec(<<<'SQL'
            CREATE TABLE account_default (
                provider_key TEXT PRIMARY KEY,
                account_id TEXT NOT NULL REFERENCES account(id) ON DELETE CASCADE,
                updated_at TEXT NOT NULL
            )
            SQL);
        $pdo->exec(<<<'SQL'
            CREATE TABLE subscription (
                id TEXT PRIMARY KEY,
                account_id TEXT NOT NULL REFERENCES account(id) ON DELETE CASCADE,
                display_name TEXT NULL,
                plan_name TEXT NULL,
                billing_type TEXT NOT NULL CHECK (billing_type IN ('free', 'recurring', 'pay_as_you_go', 'prepaid', 'enterprise', 'unknown')),
                status TEXT NOT NULL CHECK (status IN ('active', 'inactive', 'expired', 'canceled', 'unknown')),
                billing_amount_minor INTEGER NULL CHECK (billing_amount_minor IS NULL OR billing_amount_minor >= 0),
                currency TEXT NULL,
                billing_interval TEXT NULL CHECK (billing_interval IS NULL OR billing_interval IN ('day', 'week', 'month', 'year')),
                renews_at TEXT NULL,
                expires_at TEXT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )
            SQL);
        $pdo->exec('CREATE INDEX subscription_account ON subscription (account_id, id)');
        $pdo->exec(<<<'SQL'
            CREATE TABLE subscription_entitlement (
                subscription_id TEXT NOT NULL REFERENCES subscription(id) ON DELETE CASCADE,
                product_key TEXT NOT NULL,
                access_status TEXT NOT NULL CHECK (access_status IN ('unknown', 'declared', 'verified')),
                verification_source TEXT NULL,
                verification_evidence TEXT NULL,
                verified_at TEXT NULL,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                CHECK (access_status != 'verified' OR (verification_source IS NOT NULL AND verification_evidence IS NOT NULL AND verified_at IS NOT NULL)),
                PRIMARY KEY (subscription_id, product_key)
            )
            SQL);
        $pdo->exec(<<<'SQL'
            CREATE TABLE unresolved_legacy_product (
                account_id TEXT PRIMARY KEY REFERENCES account(id) ON DELETE CASCADE,
                provider_key TEXT NOT NULL,
                product_key TEXT NOT NULL,
                was_product_default INTEGER NOT NULL CHECK (was_product_default IN (0, 1)),
                resolution_status TEXT NOT NULL CHECK (resolution_status = 'unresolved'),
                migrated_at TEXT NOT NULL
            )
            SQL);
    }
}
