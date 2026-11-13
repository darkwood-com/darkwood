<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Application\Preference\PairwisePreferenceService;
use App\Command\PreferenceRecordCommand;
use App\Domain\Tokenization\ProgramTokenizer;
use App\Infrastructure\Preference\JsonPreferenceRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class PreferenceRecordCommandTest extends TestCase
{
    public function testItRecordsPairwisePreferenceJson(): void
    {
        $baseDir = sys_get_temp_dir() . '/llm_game_of_life_tests/preference-command';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $preferredPath = $baseDir . '/preferred.json';
        $rejectedPath = $baseDir . '/rejected.json';
        file_put_contents($preferredPath, json_encode([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 1, 'y' => 1],
                ['type' => 'next'],
            ],
        ], JSON_THROW_ON_ERROR));
        file_put_contents($rejectedPath, json_encode([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'kill', 'x' => 1, 'y' => 1],
                ['type' => 'next'],
            ],
        ], JSON_THROW_ON_ERROR));

        $repository = new JsonPreferenceRepository($baseDir . '/preferences');
        $tester = new CommandTester(new PreferenceRecordCommand(
            new PairwisePreferenceService(new ProgramTokenizer()),
            $repository,
        ));

        $status = $tester->execute([
            '--preferred-ast-file' => $preferredPath,
            '--rejected-ast-file' => $rejectedPath,
            '--preferred-score' => '0.8',
            '--rejected-score' => '0.3',
            '--mode' => 'supervised',
            '--dataset' => 'block',
            '--seed' => '42',
            '--size' => '4',
            '--id' => 'pref_cli_test',
        ]);

        self::assertSame(Command::SUCCESS, $status);
        $decoded = json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('pref_cli_test', $decoded['id']);
        self::assertSame(0.5, $decoded['margin']);
        self::assertSame('block', $decoded['context']['dataset']);
        self::assertFileExists($decoded['path']);
        self::assertSame('pref_cli_test', $repository->load('pref_cli_test')['id']);
    }
}
