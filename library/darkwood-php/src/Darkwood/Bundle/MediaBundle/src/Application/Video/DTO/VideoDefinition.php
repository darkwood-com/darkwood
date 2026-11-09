<?php

declare(strict_types=1);

namespace App\Application\Video\DTO;

final class VideoDefinition
{
    /**
     * @param list<SceneDefinition> $scenes
     */
    public function __construct(
        public string $title,
        public array $scenes,
    ) {}
}
