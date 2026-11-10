<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Runtime;

use App\NoLifeModels\Domain\Model\LocalModel;
use App\NoLifeModels\Domain\Model\ModelInferenceResult;

interface LocalModelRuntimeInterface
{
    /**
     * @return list<LocalModel>
     */
    public function listLocalModels(): array;

    public function generate(GeneratePromptCommand $command): ModelInferenceResult;
}
