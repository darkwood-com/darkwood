<?php

declare(strict_types=1);

namespace App\Infrastructure\LambdaLanguage;

use App\Domain\CellularAutomaton\Frame;
use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\CellularAutomaton\Grid;
use App\Domain\LambdaLanguage\LambdaEvaluationService;
use App\Domain\LambdaLanguage\LambdaExecutionContext;
use App\Domain\LambdaLanguage\LambdaGridState;
use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramExecutorInterface;
use App\Domain\LambdaLanguage\ProgramValidationException;

final readonly class FlowProgramExecutor implements ProgramExecutorInterface
{
    public function __construct(
        private LambdaEvaluationService $evaluation,
        private GameOfLifeStepper $stepper,
    ) {
    }

    /**
     * @return list<Frame>
     */
    public function execute(Program $program, Grid $initialGrid, int $maxFrames = 120): array
    {
        $state = new LambdaGridState($initialGrid, $this->stepper, $maxFrames);
        $context = new LambdaExecutionContext($state);

        $this->executeNode($program->toArray(), $context);

        return $state->frames();
    }

    /**
     * @param array<string,mixed> $node
     */
    private function executeNode(array $node, LambdaExecutionContext $context): void
    {
        $type = $node['type'] ?? null;
        if (!\is_string($type)) {
            throw new ProgramValidationException('Node type must be a string.');
        }

        match ($type) {
            'sequence' => $this->executeSequence($node, $context),
            'birth', 'kill', 'paint', 'next' => $this->executePrimitive($node, $context),
            'repeat' => $this->executeRepeat($node, $context),
            'compose' => $this->executeCompose($node, $context),
            default => throw new ProgramValidationException(\sprintf('Unsupported node type "%s".', $type)),
        };
    }

    /**
     * @param array<string,mixed> $node
     */
    private function executeSequence(array $node, LambdaExecutionContext $context): void
    {
        $children = $node['nodes'] ?? null;
        if (!\is_array($children)) {
            throw new ProgramValidationException('Sequence node must contain children.');
        }

        foreach ($children as $child) {
            if (!\is_array($child)) {
                throw new ProgramValidationException('Sequence child must be an object node.');
            }

            $this->executeNode($child, $context);
        }
    }

    /**
     * @param array<string,mixed> $node
     */
    private function executePrimitive(array $node, LambdaExecutionContext $context): void
    {
        $type = (string) $node['type'];

        $expression = match ($type) {
            'birth' => 'λbirth.λpayload.birth(payload)',
            'kill' => 'λkill.λpayload.kill(payload)',
            'paint' => 'λpaint.λpayload.paint(payload)',
            'next' => 'λnext.next',
            default => throw new ProgramValidationException(\sprintf('Unsupported primitive "%s".', $type)),
        };

        $arguments = match ($type) {
            'birth', 'kill' => [[
                'x' => $node['x'] ?? null,
                'y' => $node['y'] ?? null,
            ]],
            'paint' => [[
                'x' => $node['x'] ?? null,
                'y' => $node['y'] ?? null,
                'color' => $node['color'] ?? null,
            ]],
            'next' => [],
            default => [],
        };

        $this->evaluation->evaluate($expression, $context, $arguments, [$type]);
    }

    /**
     * @param array<string,mixed> $node
     */
    private function executeRepeat(array $node, LambdaExecutionContext $context): void
    {
        $times = $node['n'] ?? null;
        $child = $node['node'] ?? null;

        if (!\is_int($times) || !\is_array($child)) {
            throw new ProgramValidationException('Repeat node must define integer n and child node.');
        }

        for ($i = 0; $i < $times; ++$i) {
            $this->executeNode($child, $context);
        }
    }

    /**
     * @param array<string,mixed> $node
     */
    private function executeCompose(array $node, LambdaExecutionContext $context): void
    {
        $first = $node['first'] ?? null;
        $second = $node['second'] ?? null;

        if (!\is_array($first) || !\is_array($second)) {
            throw new ProgramValidationException('Compose node requires first and second nodes.');
        }

        $this->executeNode($first, $context);
        $this->executeNode($second, $context);
    }
}
