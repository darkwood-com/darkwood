<?php

declare(strict_types=1);

namespace App\Study\Game;

use App\Study\JevInvoke;
use App\Study\Payload;
use Flow\JobInterface;
use Symfony\AI\Platform\PlatformInterface;

/**
 * @implements JobInterface<Payload, Payload>
 */
final class JevGameJob implements JobInterface
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly string $source,
    ) {
    }

    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $board = RuleGameJob::board($payload->normalized);
        $remaining = RuleGameJob::remaining($board['shots'], $board['size']);
        $start = hrtime(true);

        $criteria = [];
        foreach ($remaining as $cell) {
            $criteria[$cell] = 'Fire at '.$cell.' (unshot).';
        }

        try {
            $result = JevInvoke::call($this->platform, [
                'size' => $board['size'],
                'shots' => $board['shots'],
                'remaining' => $remaining,
            ], [
                'cell' => [
                    'type' => 'choice',
                    'instructions' => 'Choose the next unshot cell on this tiny Battleship-like grid. Prefer a hunt that spaces shots. Do not invent cells that were already shot.',
                    'criteria' => $criteria,
                ],
            ]);
            $payload->decision = $result->getChoice('cell');
            $payload->confidence = $result->getConfidence('cell');
            $payload->alternatives = $result->getProbabilities('cell');
            $payload->answers = $result->toArray();
            $payload->error = null;
        } catch (\Throwable $e) {
            $payload->decision = null;
            $payload->error = $e->getMessage();
        }

        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = $this->source;
        $payload->job = self::class;
        $payload->context = $board + ['remaining' => $remaining];

        return $payload;
    }
}
