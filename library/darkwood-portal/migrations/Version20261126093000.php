<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Preserve the origin and identifiers of contacts imported from Darkwaar.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE public.contact ADD legacy_reference VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_contact_legacy_reference ON public.contact (legacy_reference)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Removing the column would discard the provenance of imported contacts.');
    }
}
