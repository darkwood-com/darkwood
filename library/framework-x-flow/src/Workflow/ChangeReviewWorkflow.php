<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Flow\EmbeddedReactDriver;
use App\Flow\EmbeddedReactFlowRunner;
use App\Workflow\Step\AnalyseChangeStep;
use App\Workflow\Step\ConcurrentQualityChecksStep;
use App\Workflow\Step\FinalReviewStep;
use App\Workflow\Step\GenerateDocumentationStep;
use App\Workflow\Step\RetrospectiveStep;
use App\Workflow\Step\RunStaticAnalysisStep;
use App\Workflow\Step\RunTestsStep;
use App\Workflow\Step\SecurityReviewStep;
use App\Workflow\Step\ValidateChangeRequestStep;
use Flow\ExceptionInterface;
use Flow\FlowFactory;
use Flow\FlowInterface;
use Flow\Ip;
use React\Promise\PromiseInterface;

final class ChangeReviewWorkflow
{
    /**
     * @return array<string, mixed>
     */
    public function describe(): array
    {
        return [
            'name' => 'change-review',
            'purpose' => 'Deterministic engineering change checklist: validate, analyse, quality checks, docs, review, retrospective.',
            'executionOrder' => [
                'validate',
                'analyse',
                'tests | static-analysis | security-review (sequential Flow stages, or concurrent ReactPHP adapter when qualityMode=concurrent)',
                'documentation',
                'final-review',
                'retrospective',
            ],
            'parallelism' => [
                'nativeFlow' => 'Multiple Ips through MaxIpStrategy (see bin/experiment). Not used for intra-run quality branching.',
                'adapter' => 'ConcurrentQualityChecksStep uses ReactPHP Promise::all inside one Flow job when input.qualityMode=concurrent (default).',
            ],
            'expectedInputs' => [
                'changeId' => 'string',
                'title' => 'string',
                'files' => 'string[]',
                'risk' => 'low|medium|high',
                'qualityMode' => 'concurrent|sequential (optional)',
                'fail' => 'exception|promise|async|security (optional controlled failures)',
            ],
            'producedOutputs' => [
                'run report with step traces',
                'result.analysis / quality / documentation / finalReview',
                'retrospective with suggestedWorkflowChanges',
            ],
            'steps' => [
                ['name' => 'validate', 'class' => ValidateChangeRequestStep::class],
                ['name' => 'analyse', 'class' => AnalyseChangeStep::class],
                ['name' => 'tests', 'class' => RunTestsStep::class],
                ['name' => 'static-analysis', 'class' => RunStaticAnalysisStep::class],
                ['name' => 'security-review', 'class' => SecurityReviewStep::class],
                ['name' => 'documentation', 'class' => GenerateDocumentationStep::class],
                ['name' => 'final-review', 'class' => FinalReviewStep::class],
                ['name' => 'retrospective', 'class' => RetrospectiveStep::class],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return PromiseInterface<ChangeReviewContext>
     */
    public function runAsync(array $input, string $runId): PromiseInterface
    {
        // Fresh driver per run — EmbeddedReactDriver completion Deferred must not be shared.
        $driver = new EmbeddedReactDriver();
        $runner = new EmbeddedReactFlowRunner($driver);
        $ctx = new ChangeReviewContext($runId, $input);
        $flow = $this->buildFlow($driver, $input, $ctx);

        return $runner->run($flow, new Ip($ctx));
    }

    /**
     * @param array<string, mixed> $input
     */
    private function buildFlow(EmbeddedReactDriver $driver, array $input, ChangeReviewContext $ctx): FlowInterface
    {
        $validate = new ValidateChangeRequestStep();
        $analyse = new AnalyseChangeStep();
        $tests = new RunTestsStep($driver);
        $static = new RunStaticAnalysisStep($driver);
        $security = new SecurityReviewStep($driver);
        $concurrent = new ConcurrentQualityChecksStep();
        $docs = new GenerateDocumentationStep();
        $final = new FinalReviewStep();
        $retro = new RetrospectiveStep();

        $qualityMode = ($input['qualityMode'] ?? 'concurrent') === 'sequential' ? 'sequential' : 'concurrent';

        $errorJob = static function (ExceptionInterface $e) use ($ctx): void {
            $ctx->fail('flow_error', 'A workflow step failed.');
            $ctx->warnings[] = 'Flow errorJob invoked (details logged server-side only).';
            $ctx->steps[] = [
                'name' => 'error-handler',
                'status' => 'failed',
                'durationMs' => 0,
                'output' => ['safeMessage' => 'Step failed; see server logs if available.'],
            ];
            if ($ctx->retrospective === []) {
                $ctx->retrospective = [
                    'summary' => 'Workflow aborted after a step failure. Encode the failure mode into the shared checklist.',
                    'lessons' => ['A step threw or rejected; later steps including the full retrospective did not run.'],
                    'slowSteps' => [],
                    'suggestedWorkflowChanges' => [[
                        'target' => 'error-handling',
                        'reason' => 'Unhandled step failures stop the Ip chain in darkwood/flow.',
                        'proposal' => 'Add explicit recovery jobs or map known failures to soft status without throwing.',
                    ]],
                    'note' => 'Proposals only — this step does not rewrite application code or workflow definitions.',
                ];
            }
            $ctx->finish('failed');
        };

        return (new FlowFactory($driver))->create(static function () use (
            $validate,
            $analyse,
            $tests,
            $static,
            $security,
            $concurrent,
            $docs,
            $final,
            $retro,
            $qualityMode,
            $errorJob,
        ) {
            yield [static fn (ChangeReviewContext $ctx) => $validate($ctx), $errorJob];
            yield [static fn (ChangeReviewContext $ctx) => $analyse($ctx), $errorJob];

            if ($qualityMode === 'concurrent') {
                yield [static fn (ChangeReviewContext $ctx) => $concurrent($ctx), $errorJob];
            } else {
                yield [static fn (ChangeReviewContext $ctx) => $tests($ctx), $errorJob];
                yield [static fn (ChangeReviewContext $ctx) => $static($ctx), $errorJob];
                yield [static fn (ChangeReviewContext $ctx) => $security($ctx), $errorJob];
            }

            yield [static fn (ChangeReviewContext $ctx) => $docs($ctx), $errorJob];
            yield [static fn (ChangeReviewContext $ctx) => $final($ctx), $errorJob];
            yield [static fn (ChangeReviewContext $ctx) => $retro($ctx), $errorJob];
        }, ['driver' => $driver]);
    }
}
