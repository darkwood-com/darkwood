<?php

namespace App\Demo;

final class ClientWebsiteDemoInputFactory
{
    /**
     * @return array<string, mixed>
     */
    public function create(string $clientSlug): array
    {
        return [
            'client' => $clientSlug,
            'goal' => [
                'create_landing_page',
                'generate_seo_geo_recommendations',
                'generate_article_draft',
                'generate_media_video_brief',
                'produce_audit_trace',
                'produce_delivery_report',
            ],
        ];
    }
}
