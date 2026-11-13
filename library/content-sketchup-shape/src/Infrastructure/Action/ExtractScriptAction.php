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
 * Step 4 — Extracts the Ruby code block from the raw LLM response.
 *
 * Attempts to pull the content of a ```ruby ... ``` fence.
 * Falls back to the full trimmed response if no fence is found.
 *
 * Context reads : llm_raw_response
 * Context writes: ruby_script
 * Events emitted: script.extracted
 */
final class ExtractScriptAction implements Action
{
    public function name(): ActionName
    {
        return ActionName::fromString('extract_script');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $raw = trim((string) $context->get('llm_raw_response', ''));
        $script = $this->extract($raw);

        if ('' === $script) {
            throw new \RuntimeException('LLM returned an empty script. Try rephrasing your intention.');
        }

        return ActionResult::continueWith(
            $context->merge(['ruby_script' => $script]),
            Event::fromName('script.extracted', ['length' => strlen($script)]),
        );
    }

    private function extract(string $raw): string
    {
        if (preg_match('/```(?:ruby)?\s*\n(.*?)```/s', $raw, $matches)) {
            return trim($matches[1]);
        }

        return $raw;
    }
}
