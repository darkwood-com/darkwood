<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126234500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the first-password activation requirement independently of the generated Uniflow username.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL is required.');
        $this->addSql("SET LOCAL lock_timeout = '5s'");
        $this->addSql('ALTER TABLE public."user" ADD uniflow_activation_required BOOLEAN DEFAULT FALSE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore pending Uniflow usernames before removing their activation flag.');
    }
}
