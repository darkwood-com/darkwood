<?php

declare(strict_types=1);

namespace App\TokenFlow;

use App\TokenFlow\Step\PipelineStepInterface;

final class PipelineStepRegistry
{
    /** @var array<string, PipelineStepInterface> */
    private array $prototypes = [];

    /**
     * @param iterable<PipelineStepInterface> $steps
     */
    public function __construct(iterable $steps = [])
    {
        foreach ($steps as $step) {
            $this->prototypes[$step::name()] = $step;
        }
    }

    /**
     * @param array<string, scalar> $args
     */
    public function create(string $name, array $args = []): PipelineStepInterface
    {
        if (!isset($this->prototypes[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown pipeline step "%s". Registered: %s',
                $name,
                implode(', ', array_keys($this->prototypes)),
            ));
        }

        return $this->prototypes[$name]->withArgs($args);
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->prototypes);
    }
}
