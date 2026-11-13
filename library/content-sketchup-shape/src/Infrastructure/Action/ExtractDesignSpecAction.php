<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use App\Domain\Execution\Action;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\ActionResult;
use App\Domain\Execution\Context;
use App\Domain\Execution\Event;
use App\Domain\Execution\ExecutionState;
use App\Domain\Validation\DesignSpecValidator;

/**
 * Extracts and validates the JSON design specification from the raw LLM response.
 *
 * Attempts (in order):
 *   1. Extract content from a ```json ... ``` fence
 *   2. Treat the full trimmed response as bare JSON
 *
 * Validates structure via {@see DesignSpecValidator} (architectural hierarchy or generic `parts`).
 *
 * Context reads : design_raw
 * Context writes: design_spec (JSON string, validated)
 * Events emitted: design_spec.extracted
 */
final class ExtractDesignSpecAction implements Action
{
    public function __construct(private readonly DesignSpecValidator $designSpecValidator)
    {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('extract_design_spec');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $raw  = trim((string) $context->get('design_raw', ''));
        $json = $this->extract($raw);
        $data = $this->designSpecValidator->validate($json);

        $type  = (string) ($data['type'] ?? 'unknown');
        $count = $this->countGeometryGroups($data);

        return ActionResult::continueWith(
            $context->merge([
                'design_spec'  => $json,
                'design_parts' => $count,
            ]),
            Event::fromName('design_spec.extracted', [
                'name' => $data['name'] ?? 'unknown',
                'type' => $type,
                'parts' => $count,
            ]),
        );
    }

    private function extract(string $raw): string
    {
        if (preg_match('/```(?:json)?\s*\n(.*?)```/s', $raw, $matches)) {
            return trim($matches[1]);
        }

        return $raw;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function countGeometryGroups(array $data): int
    {
        if (isset($data['parts']) && \is_array($data['parts'])) {
            return \count($data['parts']);
        }

        $n = 0;
        if (isset($data['foundation'])) {
            ++$n;
        }
        if (isset($data['structure']['floor'])) {
            ++$n;
        }
        if (isset($data['structure']['walls'])) {
            ++$n;
        }
        if (isset($data['roof'])) {
            ++$n;
        }
        if (isset($data['openings']['door']) && null !== $data['openings']['door']) {
            ++$n;
        }
        if (isset($data['openings']['windows']) && \is_array($data['openings']['windows'])) {
            $n += \count($data['openings']['windows']);
        }

        return $n;
    }
}
