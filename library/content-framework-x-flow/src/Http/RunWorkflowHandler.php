<?php

declare(strict_types=1);

namespace App\Http;

use App\Workflow\ChangeReviewContext;
use App\Workflow\ChangeReviewWorkflow;
use App\Workflow\WorkflowRun;
use App\Workflow\WorkflowRunStore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;

final class RunWorkflowHandler
{
    public function __construct(
        private readonly ChangeReviewWorkflow $workflow,
        private readonly WorkflowRunStore $store,
        private readonly string $logFile,
    ) {
    }

    /**
     * @return ResponseInterface|PromiseInterface<ResponseInterface>
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $raw = (string) $request->getBody();
        if ($raw === '') {
            return JsonResponse::error(400, 'invalid_json', 'Request body must be JSON.');
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return JsonResponse::error(400, 'invalid_json', 'Request body is not valid JSON.');
        }

        if (!is_array($decoded)) {
            return JsonResponse::error(400, 'invalid_json', 'JSON body must be an object.');
        }

        /** @var array<string, mixed> $decoded */
        $runId = $this->newRunId();

        return $this->workflow->runAsync($decoded, $runId)->then(
            function (ChangeReviewContext $ctx) {
                // If validation threw before finish(), ensure status is set.
                if ($ctx->finishedAt === null) {
                    $ctx->finish($ctx->status === 'failed' ? 'failed' : 'completed');
                }

                $report = $ctx->toReport();
                try {
                    $this->store->save(WorkflowRun::fromReport($report));
                } catch (\Throwable $e) {
                    $this->log('persist_failed', $e);
                    return JsonResponse::error(500, 'persist_failed', 'Workflow finished but run persistence failed.');
                }

                $status = $ctx->status === 'completed' ? 201 : 422;

                return JsonResponse::ok($report, $status);
            },
            function (\Throwable $e) {
                $this->log('workflow_rejected', $e);

                return JsonResponse::error(500, 'workflow_failed', 'Workflow execution failed.');
            }
        );
    }

    private function newRunId(): string
    {
        return bin2hex(random_bytes(8));
    }

    private function log(string $code, \Throwable $e): void
    {
        $line = sprintf("%s %s %s: %s\n", date('c'), $code, $e::class, $e->getMessage());
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
