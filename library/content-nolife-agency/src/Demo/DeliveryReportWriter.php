<?php

namespace App\Demo;

final class DeliveryReportWriter
{
    /**
     * @param array<string, mixed> $workflowSummary
     */
    public function write(RunDirectory $runDirectory, array $workflowSummary): void
    {
        $lines = [
            '# Delivery Report',
            '',
            sprintf('Run: `%s`', $runDirectory->runId),
            sprintf('Workflow status: `%s`', (string) ($workflowSummary['status'] ?? 'unknown')),
            sprintf('Final state: `%s`', (string) ($workflowSummary['final_state'] ?? 'human_approval_required')),
            '',
            'Decision: human approval required',
            '',
        ];
        file_put_contents($runDirectory->path.'/delivery-report.md', implode("\n", $lines));
    }
}
