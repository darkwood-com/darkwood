<?php

namespace App\Storage;

final class Schema
{
    public const VERSION = 1;

    public static function initialize(\PDO $pdo): void
    {
        $version = (int) $pdo->query('PRAGMA user_version')->fetchColumn();
        if ($version > self::VERSION) {
            throw new \RuntimeException(sprintf('Database schema version %d is newer than supported version %d.', $version, self::VERSION));
        }
        if (self::VERSION === $version) {
            return;
        }

        $pdo->beginTransaction();
        try {
            if (0 === $version) {
                $pdo->exec(<<<'SQL'
                    CREATE TABLE account (
                        id TEXT PRIMARY KEY,
                        provider_key TEXT NOT NULL,
                        product_key TEXT NOT NULL,
                        alias TEXT NULL,
                        auth_method TEXT NOT NULL,
                        auth_reference TEXT NULL,
                        monitoring_enabled INTEGER NOT NULL CHECK (monitoring_enabled IN (0, 1)),
                        auth_verification_state TEXT NOT NULL,
                        auth_verified_at TEXT NULL,
                        auth_status_detail TEXT NULL,
                        created_at TEXT NOT NULL,
                        updated_at TEXT NOT NULL
                    )
                    SQL);
                $pdo->exec('CREATE INDEX account_provider_product ON account (provider_key, product_key)');
                $pdo->exec(<<<'SQL'
                    CREATE TABLE account_default (
                        scope_type TEXT NOT NULL CHECK (scope_type IN ('provider', 'product')),
                        scope_key TEXT NOT NULL,
                        account_id TEXT NOT NULL REFERENCES account(id) ON DELETE CASCADE,
                        updated_at TEXT NOT NULL,
                        PRIMARY KEY (scope_type, scope_key)
                    )
                    SQL);
            }

            $pdo->exec('PRAGMA user_version = '.self::VERSION);
            $pdo->commit();
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $error;
        }
    }
}
