<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class Version20261126235500 extends AbstractMigration
{
    private const string VALID_USERNAME = '^[A-Za-z0-9-]{2,32}$';

    public function getDescription(): string
    {
        return 'Normalize Darkwood usernames and constrain them to ASCII letters, digits and hyphens.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'PostgreSQL is required.');
        $rows = $this->connection->fetchAllAssociative('SELECT id, username FROM public."user" ORDER BY id');
        $renames = $this->buildRenames($rows);

        $this->addSql("SET LOCAL lock_timeout = '5s'");
        $this->addSql('LOCK TABLE public."user" IN ACCESS EXCLUSIVE MODE');
        $this->addSql('CREATE TEMP TABLE darkwood_username_normalization (
            id INTEGER PRIMARY KEY, former_username TEXT NOT NULL, new_username VARCHAR(32) NOT NULL UNIQUE
        ) ON COMMIT DROP');

        foreach (array_chunk($renames, 100) as $batch) {
            $values = implode(', ', array_fill(0, count($batch), '(?, ?, ?)'));
            $this->addSql('INSERT INTO darkwood_username_normalization VALUES ' . $values, array_merge(...$batch));
        }

        $this->addSql(<<<'SQL'
            DO $validate$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM darkwood_username_normalization p
                    LEFT JOIN public."user" u ON u.id = p.id
                    WHERE u.username IS DISTINCT FROM p.former_username
                ) OR EXISTS (
                    SELECT 1 FROM darkwood_username_normalization p
                    JOIN public."user" u ON u.id <> p.id AND lower(u.username) = lower(p.new_username)
                ) THEN
                    RAISE EXCEPTION 'Username data changed during migration; rerun against the current database.';
                END IF;
            END
            $validate$
            SQL);
        $this->addSql(<<<'SQL'
            UPDATE public."user" u
               SET username = p.new_username,
                   uniflow_activation_required = u.uniflow_activation_required OR (
                       left(p.former_username, 3) = 'uf_' AND NOT u.is_verified AND coalesce(u.password, '') = ''
                   )
              FROM darkwood_username_normalization p
             WHERE u.id = p.id
            SQL);
        $this->addSql(sprintf(
            'ALTER TABLE public."user" ADD CONSTRAINT darkwood_username_format CHECK (username COLLATE "C" ~ \'%s\')',
            self::VALID_USERNAME,
        ));
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore the original usernames from the secured pre-migration backup if required.');
    }

    /**
     * @param list<array{id: int|string, username: string}> $rows
     * @return list<array{int, string, string}>
     */
    private function buildRenames(array $rows): array
    {
        $slugger = new AsciiSlugger('en');
        $occupied = array_fill_keys(array_map(static fn (array $row): string => strtolower($row['username']), $rows), true);
        $renames = [];

        foreach ($rows as $row) {
            $former = $row['username'];
            if (1 === preg_match('/\A[A-Za-z0-9-]{2,32}\z/', $former)) {
                continue;
            }

            $id = (int) $row['id'];
            $name = str_starts_with($former, 'uf_') ? substr($former, 3) : $former;
            $base = rtrim(substr($slugger->slug($name)->toString(), 0, 32), '-');
            $base = strlen($base) >= 2 ? $base : 'user-' . $id;
            $candidate = $base;

            for ($attempt = 1; isset($occupied[strtolower($candidate)]); ++$attempt) {
                $suffix = '-' . $id . ($attempt > 1 ? '-' . $attempt : '');
                $candidate = rtrim(substr($base, 0, 32 - strlen($suffix)), '-') . $suffix;
            }

            $occupied[strtolower($candidate)] = true;
            $renames[] = [$id, $former, $candidate];
        }

        return $renames;
    }
}
