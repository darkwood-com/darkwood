<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Preference;

use App\Infrastructure\Preference\JsonPreferenceRepository;
use PHPUnit\Framework\TestCase;

final class JsonPreferenceRepositoryTest extends TestCase
{
    public function testItWritesAndLoadsPreferenceRecords(): void
    {
        $directory = sys_get_temp_dir() . '/llm_game_of_life_tests/preferences';
        $repository = new JsonPreferenceRepository($directory);

        $record = [
            'id' => 'pref_round_trip',
            'schema_version' => 1,
            'margin' => 0.25,
        ];

        $path = $repository->save($record);

        self::assertFileExists($path);
        self::assertSame($record, $repository->load('pref_round_trip'));
    }
}
