<?php

namespace App\Agent\Developer;

use App\Agent\AbstractAgent;

final class DeveloperAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'developer';
    }

    public function role(): string
    {
        return 'Implementation agent for Symfony and integrations';
    }
}
