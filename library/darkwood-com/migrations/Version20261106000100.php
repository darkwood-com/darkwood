<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261106000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add provider-neutral external purchases and canonical user entitlements.';
    }

    public function up(Schema $schema): void
    {
        $definition = $schema->createTable('entitlement_definition');
        $definition->addColumn('id', 'integer', ['autoincrement' => true]);
        $definition->addColumn('code', 'string', ['length' => 120]);
        $definition->addColumn('label', 'string', ['length' => 255]);
        $definition->setPrimaryKey(['id']);
        $definition->addUniqueIndex(['code'], 'uniq_entitlement_definition_code');

        $purchase = $schema->createTable('external_purchase');
        $purchase->addColumn('id', 'integer', ['autoincrement' => true]);
        $purchase->addColumn('user_id', 'integer', ['notnull' => false]);
        $purchase->addColumn('provider', 'string', ['length' => 16]);
        $purchase->addColumn('provider_purchase_id', 'string', ['length' => 128]);
        $purchase->addColumn('provider_product_id', 'string', ['length' => 128, 'notnull' => false]);
        $purchase->addColumn('provider_customer_id', 'string', ['length' => 128, 'notnull' => false]);
        $purchase->addColumn('state', 'string', ['length' => 16]);
        $purchase->addColumn('provider_data', 'json');
        $purchase->addColumn('valid_until', 'datetime_immutable', ['notnull' => false]);
        $purchase->addColumn('verified_at', 'datetime_immutable', ['notnull' => false]);
        $purchase->addColumn('created_at', 'datetime_immutable');
        $purchase->addColumn('updated_at', 'datetime_immutable');
        $purchase->setPrimaryKey(['id']);
        $purchase->addUniqueIndex(['provider', 'provider_purchase_id'], 'uniq_external_purchase_provider_id');
        $purchase->addIndex(['user_id'], 'idx_external_purchase_user');
        $purchase->addForeignKeyConstraint('user', ['user_id'], ['id'], ['onDelete' => 'SET NULL']);

        $grant = $schema->createTable('user_entitlement');
        $grant->addColumn('id', 'integer', ['autoincrement' => true]);
        $grant->addColumn('user_id', 'integer');
        $grant->addColumn('entitlement_id', 'integer');
        $grant->addColumn('source_purchase_id', 'integer', ['notnull' => false]);
        $grant->addColumn('status', 'string', ['length' => 16]);
        $grant->addColumn('valid_from', 'datetime_immutable');
        $grant->addColumn('valid_until', 'datetime_immutable', ['notnull' => false]);
        $grant->addColumn('updated_at', 'datetime_immutable');
        $grant->setPrimaryKey(['id']);
        $grant->addUniqueIndex(['user_id', 'entitlement_id', 'source_purchase_id'], 'uniq_user_entitlement_source');
        $grant->addForeignKeyConstraint('user', ['user_id'], ['id'], ['onDelete' => 'CASCADE']);
        $grant->addForeignKeyConstraint('entitlement_definition', ['entitlement_id'], ['id'], ['onDelete' => 'CASCADE']);
        $grant->addForeignKeyConstraint('external_purchase', ['source_purchase_id'], ['id'], ['onDelete' => 'SET NULL']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user_entitlement');
        $schema->dropTable('external_purchase');
        $schema->dropTable('entitlement_definition');
    }
}
