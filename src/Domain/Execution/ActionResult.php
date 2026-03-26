<?php

declare(strict_types=1);

namespace App\Domain\Execution;

final readonly class ActionResult
{
    /**
     * @param Event[] $events
     */
    private function __construct(
        private Context $context,
        private array $events
    ) {
    }

    public static function continueWith(Context $context, Event ...$events): self
    {
        return new self($context, $events);
    }

    public function context(): Context
    {
        return $this->context;
    }

    /**
     * @return Event[]
     */
    public function events(): array
    {
        return $this->events;
    }
}
