<?php

declare(strict_types=1);

namespace App\Infrastructure\Navi;

use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\Context;

final readonly class LogRunEventAction extends AbstractNdjsonLogAction
{
    public function name(): ActionName
    {
        return ActionName::fromString('life_llm.log_run_event');
    }

    protected function buildRecord(Context $context): array
    {
        $payload = $context->get('payload', []);
        if (!\is_array($payload)) {
            $payload = [];
        }

        return $this->record($context, (string) $context->get('event', 'unknown'), $payload);
    }
}
