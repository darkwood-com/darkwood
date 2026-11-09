<?php

declare(strict_types=1);

namespace App\Domain\Evolution;

final readonly class AstCrossoverOperator
{
    public function __construct(
        private RandomGeneratorInterface $random,
    ) {
    }

    /**
     * @param array{type:string,nodes:list<array<string,mixed>>} $left
     * @param array{type:string,nodes:list<array<string,mixed>>} $right
     *
     * @return array{0:array{type:string,nodes:list<array<string,mixed>>},1:array{type:string,nodes:list<array<string,mixed>>}}
     */
    public function crossover(array $left, array $right): array
    {
        $leftNodes = $left['nodes'];
        $rightNodes = $right['nodes'];

        if ($leftNodes === [] || $rightNodes === []) {
            return [$left, $right];
        }

        $leftPoint = $this->random->int(0, count($leftNodes) - 1);
        $rightPoint = $this->random->int(0, count($rightNodes) - 1);

        $childA = [
            'type' => 'sequence',
            'nodes' => array_values(array_merge(
                array_slice($leftNodes, 0, $leftPoint),
                array_slice($rightNodes, $rightPoint),
            )),
        ];
        $childB = [
            'type' => 'sequence',
            'nodes' => array_values(array_merge(
                array_slice($rightNodes, 0, $rightPoint),
                array_slice($leftNodes, $leftPoint),
            )),
        ];

        return [$this->ensureNextNode($childA), $this->ensureNextNode($childB)];
    }

    /**
     * @param array{type:string,nodes:list<array<string,mixed>>} $programAst
     *
     * @return array{type:string,nodes:list<array<string,mixed>>}
     */
    private function ensureNextNode(array $programAst): array
    {
        $nodes = $programAst['nodes'];
        if (!in_array('next', array_column($nodes, 'type'), true)) {
            $nodes[] = ['type' => 'next'];
        }

        return ['type' => 'sequence', 'nodes' => array_values($nodes)];
    }
}
