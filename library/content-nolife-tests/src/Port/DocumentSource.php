<?php

declare(strict_types=1);

namespace App\Port;

interface DocumentSource
{
    public function fetch(string $uri): string;
}
