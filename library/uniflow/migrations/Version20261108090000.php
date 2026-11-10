<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Guarantee that one OIDC provider subject maps to at most one Sylius user.';
    }

    public function up(Schema $schema): void
    {
        $schema->getTable('sylius_user_oauth')->addUniqueIndex(['provider', 'identifier'], 'oauth_provider_identifier');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('sylius_user_oauth')->dropIndex('oauth_provider_identifier');
    }
}
