<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class MediaMotionAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'media_motion',
            name: 'Media/Motion Agent',
            role: 'Media and motion brief agent',
            description: 'Produces media and motion requirements for asset generation.',
            responsibilities: [
                'Create media briefs.',
                'Define motion constraints.',
                'Align with content and SEO context.',
            ],
            capabilities: [
                new AgentCapability('media_briefing'),
                new AgentCapability('motion_design'),
            ],
        );
    }
}
