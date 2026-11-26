<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Require email verification for new Darkwood accounts and track administrative suspension separately.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL is required.');
        $this->addSql("SET LOCAL lock_timeout = '5s'");
        $this->addSql('ALTER TABLE public."user" ADD email_verification_required BOOLEAN DEFAULT FALSE NOT NULL, ADD email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ADD email_verification_version INT DEFAULT 0 NOT NULL, ADD enabled BOOLEAN DEFAULT TRUE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Dropping these columns would lose verification and suspension state. Restore from backup if required.');
    }
}
