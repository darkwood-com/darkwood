<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'AI connections with encrypted credential blob';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_connection (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, label VARCHAR(120) NOT NULL, provider VARCHAR(32) NOT NULL, model VARCHAR(80) NOT NULL, endpoint VARCHAR(255) DEFAULT NULL, ciphertext_blob CLOB NOT NULL, kms_key_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_connection');
    }
}
