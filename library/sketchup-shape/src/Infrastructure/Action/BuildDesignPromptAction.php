<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use App\Domain\Execution\Action;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\ActionResult;
use App\Domain\Execution\Context;
use App\Domain\Execution\Event;
use App\Domain\Execution\ExecutionState;
use App\Domain\Port\DesignPromptBuilderInterface;

/**
 * DESIGN AGENT — Step 1/2
 * Builds the prompts that instruct the LLM to produce a JSON design specification.
 *
 * Context reads : intention
 * Context writes: design_prompt_system, design_prompt_user
 * Events emitted: design_prompt.built
 */
final class BuildDesignPromptAction implements Action
{
    public function __construct(private readonly DesignPromptBuilderInterface $promptBuilder)
    {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('build_design_prompt');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $intention = (string) $context->get('intention');

        return ActionResult::continueWith(
            $context->merge([
                'design_prompt_system' => $this->promptBuilder->buildSystemPrompt(),
                'design_prompt_user'   => $this->promptBuilder->buildUserPrompt($intention),
            ]),
            Event::fromName('design_prompt.built'),
        );
    }
}
