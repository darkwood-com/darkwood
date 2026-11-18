<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\Event;
use Navi\Domain\Execution\ExecutionState;
use App\Service\LocalAiService;

/**
 * Step 3 — Invokes the local Ollama/Mistral model with the built prompts.
 * All inference is local; no data leaves the machine (RGPD compliant).
 *
 * Context reads : system_prompt, user_prompt
 * Context writes: llm_raw_response
 * Events emitted: llm.invoked
 */
final class GenerateRubyAction implements Action
{
    public function __construct(private readonly LocalAiService $aiService)
    {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('generate_ruby');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $rawResponse = $this->aiService->generate(
            systemPrompt: (string) $context->get('code_prompt_system'),
            userPrompt: (string) $context->get('code_prompt_user'),
        );

        return ActionResult::continueWith(
            $context->merge(['llm_raw_response' => $rawResponse]),
            Event::fromName('llm.invoked'),
        );
    }
}
