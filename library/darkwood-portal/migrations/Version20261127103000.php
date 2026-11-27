<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261127103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update the X profile link in the three translations of the audited Darkwood article.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE article_translation
             SET content = REPLACE(content, :old, :new)
             WHERE id IN (481, 482, 483) AND STRPOS(content, :old) > 0',
            ['old' => '](https://twitter.com/matyo91)', 'new' => '](https://x.com/matyo91)'],
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore the affected translations from the verified backup if needed.');
    }
}
