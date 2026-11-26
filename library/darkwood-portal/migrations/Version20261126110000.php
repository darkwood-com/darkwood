<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the temporary legacy reference from Darkwood contacts.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL is required.');

        $this->addSql("SET LOCAL lock_timeout = '5s'");
        $this->addSql('ALTER TABLE public.contact DROP COLUMN legacy_reference RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore the legacy references from the secured pre-migration archive if needed.');
    }
}
