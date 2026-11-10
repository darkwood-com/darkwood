<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class ArtifactPathMustStayInsideRunDirectoryRule implements GovernanceRuleInterface
{
    public function name(): string
    {
        return 'artifact_path_must_stay_inside_run_directory';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        if (null === $output) {
            return [];
        }

        $runDir = $input->metadata['run_dir'] ?? null;
        if (!is_string($runDir) || $runDir === '') {
            return [];
        }

        foreach ($output->artifacts as $artifactPath) {
            if (!str_starts_with($artifactPath, $runDir.'/') && $artifactPath !== $runDir) {
                return [new PolicyViolation($this->name(), 'Artifact path escapes run directory.')];
            }
        }

        return [];
    }
}
