<?php

namespace App\Agent\Content;

use App\Agent\AbstractAgent;

final class ContentAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'content';
    }

    public function role(): string
    {
        return 'Content production agent for agency deliverables';
    }
}
