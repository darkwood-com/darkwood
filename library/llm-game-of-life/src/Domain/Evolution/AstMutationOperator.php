<?php

declare(strict_types=1);

namespace App\Domain\Evolution;

final readonly class AstMutationOperator
{
    public function __construct(
        private RandomGeneratorInterface $random,
    ) {
    }

    /**
     * @param array{type:string,nodes:list<array<string,mixed>>} $programAst
     *
     * @return array{type:string,nodes:list<array<string,mixed>>}
     */
    public function mutate(array $programAst, int $size): array
    {
        $nodes = $programAst['nodes'];
        $mutation = $this->random->int(0, 4);

        if ($mutation === 0 && count($nodes) > 0) {
            $index = $this->random->int(0, count($nodes) - 1);
            $type = $nodes[$index]['type'] ?? 'next';
            if (in_array($type, ['birth', 'kill', 'paint'], true)) {
                $nodes[$index]['x'] = $this->random->int(0, $size - 1);
                $nodes[$index]['y'] = $this->random->int(0, $size - 1);
            }
        } elseif ($mutation === 1 && count($nodes) > 0) {
            $index = $this->random->int(0, count($nodes) - 1);
            if (($nodes[$index]['type'] ?? null) === 'birth') {
                $nodes[$index]['type'] = 'kill';
            } elseif (($nodes[$index]['type'] ?? null) === 'kill') {
                $nodes[$index]['type'] = 'birth';
            }
        } elseif ($mutation === 2 && count($nodes) < 24) {
            $nodes[] = $this->randomNode($size);
        } elseif ($mutation === 3 && count($nodes) > 2) {
            array_splice($nodes, $this->random->int(0, count($nodes) - 1), 1);
        } elseif ($mutation === 4) {
            $nodes[] = [
                'type' => 'repeat',
                'n' => $this->random->int(1, 3),
                'node' => ['type' => 'next'],
            ];
        }

        if (!in_array('next', array_column($nodes, 'type'), true)) {
            $nodes[] = ['type' => 'next'];
        }

        return ['type' => 'sequence', 'nodes' => array_values($nodes)];
    }

    /**
     * @return array<string,mixed>
     */
    private function randomNode(int $size): array
    {
        $kind = $this->random->int(0, 4);
        $x = $this->random->int(0, $size - 1);
        $y = $this->random->int(0, $size - 1);

        return match ($kind) {
            0 => ['type' => 'birth', 'x' => $x, 'y' => $y],
            1 => ['type' => 'kill', 'x' => $x, 'y' => $y],
            2 => ['type' => 'paint', 'x' => $x, 'y' => $y, 'color' => $this->random->int(0, 1) ? '#ffffff' : '#000000'],
            3 => ['type' => 'mirror', 'axis' => $this->random->int(0, 1) === 0 ? 'horizontal' : 'vertical'],
            default => ['type' => 'next'],
        };
    }
}
