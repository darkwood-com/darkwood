<?php

declare(strict_types=1);

namespace App\Tests\Application\Preference;

use App\Application\Preference\PairwisePreferenceService;
use App\Domain\Tokenization\ProgramTokenizer;
use PHPUnit\Framework\TestCase;

final class PairwisePreferenceServiceTest extends TestCase
{
    public function testItBuildsPreferenceRecordWithMarginAndTokenDigests(): void
    {
        $preferred = [
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 1, 'y' => 1],
                ['type' => 'next'],
            ],
        ];
        $rejected = [
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'kill', 'x' => 1, 'y' => 1],
                ['type' => 'next'],
            ],
        ];

        $record = (new PairwisePreferenceService(new ProgramTokenizer()))->record(
            $preferred,
            $rejected,
            0.8,
            0.3,
            ['mode' => 'supervised', 'dataset' => 'block'],
            'pref_test',
        );

        self::assertSame(1, $record['schema_version']);
        self::assertSame('pref_test', $record['id']);
        self::assertSame(0.5, $record['margin']);
        self::assertSame('maximize(score_preferred - score_rejected)', $record['objective']);
        self::assertSame($preferred, $record['preferred_ast']);
        self::assertSame($rejected, $record['rejected_ast']);
        self::assertArrayHasKey('preferred', $record['token_digest']);
        self::assertArrayHasKey('rejected', $record['token_digest']);
    }
}
