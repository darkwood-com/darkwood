<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class SeoGeoSpecialistAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'seo_geo_specialist',
            name: 'SEO/GEO Specialist Agent',
            role: 'Search and generative engine optimization agent',
            description: 'Improves discoverability for search engines and AI answer engines.',
            responsibilities: [
                'Produce SEO and GEO briefs.',
                'Recommend metadata and structured data.',
                'Validate content discoverability.',
                'Prepare optimization requirements for Content and Developer agents.',
            ],
            capabilities: [
                new AgentCapability('seo'),
                new AgentCapability('geo'),
                new AgentCapability('structured_data'),
                new AgentCapability('content_optimization'),
            ],
        );
    }
}
