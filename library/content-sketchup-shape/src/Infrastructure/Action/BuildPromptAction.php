<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\Event;
use Navi\Domain\Execution\ExecutionState;
use App\Domain\Port\PromptBuilderInterface;

/**
 * Step 2 — Constructs the system and user prompts from the validated intention.
 *
 * Context reads : intention (string)
 * Context writes: system_prompt, user_prompt
 * Events emitted: prompt.built
 */
final class BuildPromptAction implements Action
{
    public function __construct(private readonly PromptBuilderInterface $promptBuilder)
    {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('build_prompt');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $intention = (string) $context->get('intention');

        return ActionResult::continueWith(
            $context->merge([
                'system_prompt' => $this->promptBuilder->buildSystemPrompt(),
                'user_prompt'   => $this->promptBuilder->buildUserPrompt($intention),
            ]),
            Event::fromName('prompt.built'),
        );
    }
}
