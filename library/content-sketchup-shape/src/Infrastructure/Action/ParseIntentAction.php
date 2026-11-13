<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use App\Domain\Execution\Action;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\ActionResult;
use App\Domain\Execution\Context;
use App\Domain\Execution\Event;
use App\Domain\Execution\ExecutionState;

/**
 * Step 1 — Validates that the Context carries a non-empty 'intention' key.
 *
 * Context reads : intention (string)
 * Context writes: intention (trimmed)
 * Events emitted: intent.parsed
 */
final class ParseIntentAction implements Action
{
    public function name(): ActionName
    {
        return ActionName::fromString('parse_intent');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $intention = trim((string) $context->get('intention', ''));

        if ('' === $intention) {
            throw new \InvalidArgumentException('Intention cannot be empty.');
        }

        return ActionResult::continueWith(
            $context->merge(['intention' => $intention]),
            Event::fromName('intent.parsed', ['intention' => $intention]),
        );
    }
}
