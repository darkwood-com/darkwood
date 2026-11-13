<?php

namespace App\Agent\Architect;

use App\Agent\AbstractAgent;

final class ArchitectAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'architect';
    }

    public function role(): string
    {
        return 'System designer and technical decision owner';
    }
}
