<?php

declare(strict_types=1);

namespace App\NoLifeModels\Application\Runtime;

use App\NoLifeModels\Domain\Model\LocalModel;
use App\NoLifeModels\Domain\Runtime\LocalModelRuntimeInterface;

final class ListLocalModelsHandler
{
    public function __construct(
        private readonly LocalModelRuntimeInterface $runtime,
    ) {
    }

    /**
     * @return list<LocalModel>
     */
    public function handle(): array
    {
        return $this->runtime->listLocalModels();
    }
}
