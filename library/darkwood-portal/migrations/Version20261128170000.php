<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261128170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the atomic email sign-in version to Darkwood users.';
    }

    public function up(Schema $schema): void
    {
        $table = $this->connection->quoteIdentifier('user');
        $this->addSql("ALTER TABLE {$table} ADD email_login_version INT DEFAULT 0 NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $table = $this->connection->quoteIdentifier('user');
        $this->addSql("ALTER TABLE {$table} DROP COLUMN email_login_version");
    }
}
