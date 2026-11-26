<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a nullable, unique Darkwood identity UUID to Sylius ShopUser.';
    }

    public function up(Schema $schema): void
    {
        $users = $schema->getTable('sylius_shop_user');
        $users->addColumn('darkwood_uid', Types::STRING, ['length' => 36, 'notnull' => false]);
        $users->addUniqueIndex(['darkwood_uid'], 'uniq_shop_user_darkwood_uid');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            false !== $this->connection->fetchOne('SELECT 1 FROM sylius_shop_user WHERE darkwood_uid IS NOT NULL LIMIT 1'),
            'The column contains identity links. Preserve them before removing the column.',
        );

        $users = $schema->getTable('sylius_shop_user');
        $users->dropIndex('uniq_shop_user_darkwood_uid');
        $users->dropColumn('darkwood_uid');
    }
}
