<?php

declare(strict_types=1);

namespace App\Application\Video\Port;

use App\Application\Video\DTO\VideoDefinition;
use App\Application\Video\Exception\InvalidVideoDefinitionException;

interface VideoDefinitionLoaderInterface
{
    /**
     * Load and validate a video definition from the given path.
     *
     * @throws InvalidVideoDefinitionException
     */
    public function load(string $path): VideoDefinition;
}
