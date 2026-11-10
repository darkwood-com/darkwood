<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use App\Domain\Execution\Action;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\ActionResult;
use App\Domain\Execution\Context;
use App\Domain\Execution\Event;
use App\Domain\Execution\ExecutionState;
use App\Domain\Port\PromptBuilderInterface;

/**
 * CODE AGENT — Step 1/2
 * Builds the prompts that instruct the LLM to convert the design spec into Ruby code.
 *
 * Context reads : design_spec
 * Context writes: code_prompt_system, code_prompt_user
 * Events emitted: code_prompt.built
 */
final class BuildCodePromptAction implements Action
{
    public function __construct(private readonly PromptBuilderInterface $promptBuilder)
    {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('build_code_prompt');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $designSpec = (string) $context->get('design_spec');

        return ActionResult::continueWith(
            $context->merge([
                'code_prompt_system' => $this->promptBuilder->buildSystemPrompt(),
                'code_prompt_user'   => $this->promptBuilder->buildUserPrompt($designSpec),
            ]),
            Event::fromName('code_prompt.built'),
        );
    }
}
