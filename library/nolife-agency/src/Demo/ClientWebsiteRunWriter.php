<?php

namespace App\Demo;

final class ClientWebsiteRunWriter
{
    /**
     * @param array<string, mixed> $brief
     * @param array<string, mixed> $workflowSummary
     */
    public function write(RunDirectory $runDirectory, array $brief, array $workflowSummary): void
    {
        file_put_contents($runDirectory->path.'/brief.json', json_encode($brief, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
        file_put_contents($runDirectory->path.'/landing-page.md', "# Landing Page\n\nGenerated draft for {$brief['client']}.\n");
        file_put_contents($runDirectory->path.'/seo-geo.md', "# SEO/GEO\n\n- Keyword cluster recommendations\n- Structured data suggestions\n");
        file_put_contents($runDirectory->path.'/article-draft.md', "# Article Draft\n\nLong-form article draft placeholder.\n");
        file_put_contents($runDirectory->path.'/media-brief.yaml', "kind: video\nstyle: product-demo\n");
        if (!is_file($runDirectory->path.'/trace.json')) {
            file_put_contents($runDirectory->path.'/trace.json', json_encode(['trace_id' => 'local-trace-'.$runDirectory->runId, 'run_id' => $runDirectory->runId], \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
        }
        file_put_contents($runDirectory->path.'/workflow-result.json', json_encode($workflowSummary, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
    }
}
