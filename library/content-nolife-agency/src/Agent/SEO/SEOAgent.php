<?php

namespace App\Agent\SEO;

use App\Agent\AbstractAgent;

final class SEOAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'seo';
    }

    public function role(): string
    {
        return 'Search optimization and discoverability strategist';
    }
}
