<?php

declare(strict_types=1);

namespace App\Infrastructure\Navi;

use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\Context;

final readonly class LogGenerationAction extends AbstractNdjsonLogAction
{
    public function name(): ActionName
    {
        return ActionName::fromString('life_llm.log_generation');
    }

    protected function buildRecord(Context $context): array
    {
        $payload = $context->get('payload', []);
        if (!\is_array($payload)) {
            $payload = [];
        }

        return $this->record($context, 'generation_completed', $payload);
    }
}
