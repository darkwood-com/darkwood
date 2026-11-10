<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align monetization index names with the Doctrine ORM mapping.';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('entitlement_definition')->renameIndex(
            'uniq_entitlement_definition_code',
            'UNIQ_47B9DC3F77153098',
        );
        $schema->getTable('external_purchase')->renameIndex(
            'idx_external_purchase_user',
            'IDX_8E1B63C8A76ED395',
        );
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('entitlement_definition')->renameIndex(
            'UNIQ_47B9DC3F77153098',
            'uniq_entitlement_definition_code',
        );
        $schema->getTable('external_purchase')->renameIndex(
            'IDX_8E1B63C8A76ED395',
            'idx_external_purchase_user',
        );
    }
}
