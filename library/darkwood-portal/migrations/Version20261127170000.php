<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261127170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update the canonical Slide links in the audited article translations.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE article_translation
             SET content = REPLACE(content, :old, :new)
             WHERE id IN (2578, 2579, 2580, 2680, 2681, 2682, 2755, 2756, 2757)
               AND STRPOS(content, :old) > 0',
            ['old' => 'https://slides.darkwood.com/slides/', 'new' => 'https://slide.darkwood.com/slides/'],
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore the affected translations from the verified backup if needed.');
    }
}
