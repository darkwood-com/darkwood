<?php

declare(strict_types=1);

namespace App\Study\Game;

use App\Study\Payload;
use Flow\JobInterface;

/**
 * Checkerboard hunt on a tiny grid. Conceptual Battleship hunt only — not copied from any repo.
 *
 * @implements JobInterface<Payload, Payload>
 */
final class RuleGameJob implements JobInterface
{
    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $board = self::board($payload->normalized);
        $start = hrtime(true);

        $choice = self::nextCell($board['shots'], $board['size']);
        $payload->decision = $choice;
        $payload->confidence = null;
        $payload->alternatives = [];
        $payload->answers = [];
        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = 'rule';
        $payload->job = self::class;
        $payload->error = null;
        $payload->context = $board;

        return $payload;
    }

    /**
     * Prefer remaining cells where row+col is even (parity hunt), else first remaining cell.
     *
     * @param list<string> $shots
     */
    public static function nextCell(array $shots, int $size): string
    {
        $remaining = self::remaining($shots, $size);
        foreach ($remaining as $cell) {
            [$col, $row] = self::parse($cell);
            if (0 === ($col + $row) % 2) {
                return $cell;
            }
        }

        return $remaining[0] ?? 'none';
    }

    /**
     * @param list<string> $shots
     *
     * @return list<string>
     */
    public static function remaining(array $shots, int $size): array
    {
        $shot = array_flip($shots);
        $cells = [];
        for ($row = 0; $row < $size; ++$row) {
            for ($col = 0; $col < $size; ++$col) {
                $cell = self::label($col, $row);
                if (!isset($shot[$cell])) {
                    $cells[] = $cell;
                }
            }
        }

        return $cells;
    }

    public static function label(int $col, int $row): string
    {
        return \chr(\ord('A') + $col).(string) ($row + 1);
    }

    /**
     * @return array{0: int, 1: int}
     */
    public static function parse(string $cell): array
    {
        $col = \ord($cell[0]) - \ord('A');
        $row = (int) substr($cell, 1) - 1;

        return [$col, $row];
    }

    /**
     * @return array{size: int, shots: list<string>}
     */
    public static function board(mixed $normalized): array
    {
        if (!\is_array($normalized)) {
            throw new \InvalidArgumentException('Game input must be an array.');
        }

        /** @var list<string> $shots */
        $shots = array_values(array_map(strval(...), $normalized['shots'] ?? []));

        return [
            'size' => (int) ($normalized['size'] ?? 5),
            'shots' => $shots,
        ];
    }
}
