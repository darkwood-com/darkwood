<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use App\Domain\Execution\Action;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\ActionResult;
use App\Domain\Execution\Context;
use App\Domain\Execution\Event;
use App\Domain\Execution\ExecutionState;
use App\Service\LocalAiService;

/**
 * DESIGN AGENT — Step 2/2
 * Calls the local LLM with the design prompts and captures the raw response.
 *
 * Context reads : design_prompt_system, design_prompt_user
 * Context writes: design_raw
 * Events emitted: design.generated
 */
final class GenerateDesignAction implements Action
{
    public function __construct(private readonly LocalAiService $aiService)
    {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('generate_design');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $rawResponse = $this->aiService->generate(
            systemPrompt: (string) $context->get('design_prompt_system'),
            userPrompt: (string) $context->get('design_prompt_user'),
        );

        return ActionResult::continueWith(
            $context->merge(['design_raw' => $rawResponse]),
            Event::fromName('design.generated'),
        );
    }
}
