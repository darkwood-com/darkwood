<?php

declare(strict_types=1);

namespace App\Domain\Tokenization;

use App\Domain\LambdaLanguage\Program;

final class ProgramTokenizer
{
    /**
     * @param Program|array<string,mixed> $program
     *
     * @return list<string>
     */
    public function tokenizeProgram(Program|array $program): array
    {
        $ast = $program instanceof Program ? $program->toArray() : $program;

        return $this->tokenizeNode($ast);
    }

    /**
     * @param array<string,mixed> $node
     *
     * @return list<string>
     */
    private function tokenizeNode(array $node): array
    {
        $type = $node['type'] ?? null;
        if (!\is_string($type)) {
            throw new \InvalidArgumentException('Program node must define a string type.');
        }

        return match ($type) {
            'sequence' => $this->tokenizeSequence($node),
            'birth' => [sprintf('BIRTH_%d_%d', $this->readInt($node, 'x'), $this->readInt($node, 'y'))],
            'kill' => [sprintf('KILL_%d_%d', $this->readInt($node, 'x'), $this->readInt($node, 'y'))],
            'paint' => [sprintf(
                'PAINT_%d_%d_%s',
                $this->readInt($node, 'x'),
                $this->readInt($node, 'y'),
                $this->colorToken($this->readString($node, 'color')),
            )],
            'next' => [VisualToken::NEXT_FRAME],
            'repeat' => $this->tokenizeRepeat($node),
            'compose' => $this->tokenizeCompose($node),
            default => throw new \InvalidArgumentException(sprintf('Unsupported program node type "%s".', $type)),
        };
    }

    /**
     * @param array<string,mixed> $node
     *
     * @return list<string>
     */
    private function tokenizeSequence(array $node): array
    {
        $nodes = $node['nodes'] ?? null;
        if (!\is_array($nodes)) {
            throw new \InvalidArgumentException('Sequence node must contain nodes.');
        }

        $tokens = [];
        foreach ($nodes as $child) {
            if (!\is_array($child)) {
                throw new \InvalidArgumentException('Sequence children must be objects.');
            }

            array_push($tokens, ...$this->tokenizeNode($child));
        }

        return $tokens;
    }

    /**
     * @param array<string,mixed> $node
     *
     * @return list<string>
     */
    private function tokenizeRepeat(array $node): array
    {
        $count = $this->readInt($node, 'n');
        $child = $node['node'] ?? null;
        if (!\is_array($child)) {
            throw new \InvalidArgumentException('Repeat node must contain a child node.');
        }

        $tokens = [];
        for ($i = 0; $i < $count; ++$i) {
            array_push($tokens, ...$this->tokenizeNode($child));
        }

        return $tokens;
    }

    /**
     * @param array<string,mixed> $node
     *
     * @return list<string>
     */
    private function tokenizeCompose(array $node): array
    {
        $first = $node['first'] ?? null;
        $second = $node['second'] ?? null;
        if (!\is_array($first) || !\is_array($second)) {
            throw new \InvalidArgumentException('Compose node must contain first and second nodes.');
        }

        return [
            ...$this->tokenizeNode($first),
            ...$this->tokenizeNode($second),
        ];
    }

    /**
     * @param array<string,mixed> $node
     */
    private function readInt(array $node, string $key): int
    {
        $value = $node[$key] ?? null;
        if (!\is_int($value)) {
            throw new \InvalidArgumentException(sprintf('Program node field "%s" must be an integer.', $key));
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $node
     */
    private function readString(array $node, string $key): string
    {
        $value = $node[$key] ?? null;
        if (!\is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Program node field "%s" must be a string.', $key));
        }

        return $value;
    }

    private function colorToken(string $hex): string
    {
        return match (strtolower($hex)) {
            '#ff0000' => VisualToken::RED_COLOR,
            '#0000ff' => VisualToken::BLUE_COLOR,
            '#ffffff' => VisualToken::WHITE_COLOR,
            '#000000' => VisualToken::BLACK_COLOR,
            default => 'COLOR_' . strtoupper(ltrim($hex, '#')),
        };
    }
}
