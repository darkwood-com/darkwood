<?php

declare(strict_types=1);

namespace App\TokenFlow;

use App\TokenFlow\Step\PipelineStepInterface;

/**
 * Delimiter-driven pipeline expression parser with registered operations.
 *
 * Inspired by expression-parser design (Pratt parsing, Twig token registration):
 * each step owns its name and argument parsing via the registry instead of a
 * monolithic switch statement.
 */
final class PipelineExpressionParser
{
    public function __construct(
        private readonly PipelineStepRegistry $registry,
    ) {
    }

    /**
     * @return list<PipelineStepInterface>
     */
    public function parse(string $expression): array
    {
        $expression = trim($expression);
        if ($expression === '') {
            throw new \InvalidArgumentException('Pipeline expression cannot be empty.');
        }

        $segments = preg_split('/\s*\|>\s*/', $expression);
        if ($segments === false || $segments === []) {
            throw new \InvalidArgumentException('Failed to parse pipeline expression.');
        }

        $steps = [];
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '') {
                continue;
            }

            $name = $segment;
            $args = [];

            if (str_contains($segment, ':')) {
                [$name, $value] = explode(':', $segment, 2);
                $name = trim($name);
                $args = $this->parseArgs($name, trim($value));
            }

            $steps[] = $this->registry->create($name, $args);
        }

        if ($steps === []) {
            throw new \InvalidArgumentException('Pipeline expression produced no steps.');
        }

        if ($steps[0]::name() !== 'source') {
            throw new \InvalidArgumentException('Pipeline must start with "source".');
        }

        if ($steps[array_key_last($steps)]::name() !== 'sink') {
            throw new \InvalidArgumentException('Pipeline must end with "sink".');
        }

        return $steps;
    }

    /**
     * @return array<string, scalar>
     */
    private function parseArgs(string $name, string $value): array
    {
        if ($name === 'chunk') {
            return ['size' => (int) $value];
        }

        if ($name === 'budget') {
            return ['limit' => (int) $value];
        }

        return ['value' => is_numeric($value) ? (int) $value : $value];
    }
}
