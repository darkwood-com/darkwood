<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

final class ProgramValidator
{
    public function __construct(
        private readonly int $gridSize,
        private readonly int $maxDepth = 8,
        private readonly int $maxInstructions = 256,
        private readonly int $maxRepeat = 128,
    ) {
    }

    /**
     * @param array<string,mixed> $ast
     */
    public function validate(array $ast): void
    {
        if (($ast['type'] ?? null) !== 'sequence') {
            throw new ProgramValidationException('Program root must be a sequence node.');
        }

        if (!isset($ast['nodes']) || !\is_array($ast['nodes'])) {
            throw new ProgramValidationException('Program root sequence must contain nodes.');
        }

        $instructionCount = 0;
        $this->validateNode($ast, 1, $instructionCount);
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validateNode(array $node, int $depth, int &$instructionCount): void
    {
        if ($depth > $this->maxDepth) {
            throw new ProgramValidationException('Program depth limit exceeded.');
        }

        ++$instructionCount;
        if ($instructionCount > $this->maxInstructions) {
            throw new ProgramValidationException('Program instruction limit exceeded.');
        }

        $type = $node['type'] ?? null;
        if (!\is_string($type)) {
            throw new ProgramValidationException('Each node must have a string type.');
        }

        match ($type) {
            'sequence' => $this->validateSequence($node, $depth, $instructionCount),
            'birth', 'kill' => $this->validateCoordinateNode($node),
            'paint' => $this->validatePaintNode($node),
            'next' => $this->validateNextNode($node),
            'mirror' => $this->validateMirrorNode($node),
            'repeat' => $this->validateRepeatNode($node, $depth, $instructionCount),
            'compose' => $this->validateComposeNode($node, $depth, $instructionCount),
            default => throw new ProgramValidationException(\sprintf('Unsupported node type "%s".', $type)),
        };
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validateSequence(array $node, int $depth, int &$instructionCount): void
    {
        if (!isset($node['nodes']) || !\is_array($node['nodes'])) {
            throw new ProgramValidationException('Sequence node must contain an array of nodes.');
        }

        foreach ($node['nodes'] as $childNode) {
            if (!\is_array($childNode)) {
                throw new ProgramValidationException('Sequence children must be objects.');
            }

            $this->validateNode($childNode, $depth + 1, $instructionCount);
        }
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validateCoordinateNode(array $node): void
    {
        $x = $node['x'] ?? null;
        $y = $node['y'] ?? null;

        if (!\is_int($x) || !\is_int($y)) {
            throw new ProgramValidationException('Coordinate nodes must define integer x and y.');
        }

        if ($x < 0 || $x >= $this->gridSize || $y < 0 || $y >= $this->gridSize) {
            throw new ProgramValidationException('Coordinates are out of grid bounds.');
        }
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validatePaintNode(array $node): void
    {
        $this->validateCoordinateNode($node);

        $color = $node['color'] ?? null;
        if (!\is_string($color) || !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            throw new ProgramValidationException('Paint node color must be a hex code like #RRGGBB.');
        }
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validateNextNode(array $node): void
    {
        if (\count($node) !== 1) {
            throw new ProgramValidationException('Next node does not accept arguments.');
        }
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validateMirrorNode(array $node): void
    {
        if (\count($node) !== 2) {
            throw new ProgramValidationException('Mirror node requires only an axis argument.');
        }

        $axis = $node['axis'] ?? null;
        if (!\is_string($axis) || !\in_array($axis, ['horizontal', 'vertical'], true)) {
            throw new ProgramValidationException('Mirror node axis must be horizontal or vertical.');
        }
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validateRepeatNode(array $node, int $depth, int &$instructionCount): void
    {
        $count = $node['n'] ?? null;
        $childNode = $node['node'] ?? null;

        if (!\is_int($count) || $count <= 0 || $count > $this->maxRepeat) {
            throw new ProgramValidationException('Repeat node must define n within allowed bounds.');
        }

        if (!\is_array($childNode)) {
            throw new ProgramValidationException('Repeat node must contain a valid child node.');
        }

        $this->validateNode($childNode, $depth + 1, $instructionCount);
    }

    /**
     * @param array<string,mixed> $node
     */
    private function validateComposeNode(array $node, int $depth, int &$instructionCount): void
    {
        $first = $node['first'] ?? null;
        $second = $node['second'] ?? null;

        if (!\is_array($first) || !\is_array($second)) {
            throw new ProgramValidationException('Compose node requires first and second nodes.');
        }

        $this->validateNode($first, $depth + 1, $instructionCount);
        $this->validateNode($second, $depth + 1, $instructionCount);
    }
}
