<?php

namespace App\Demo;

final readonly class RunDirectory
{
    public function __construct(
        public string $runId,
        public string $path,
    ) {
    }
}
