<?php

declare(strict_types=1);

namespace App;

use App\Experiment\AsyncExecutionExperiment;
use App\Http\AnalysisHandler;
use App\Http\DescribeWorkflowHandler;
use App\Http\ExperimentHandler;
use App\Http\GetWorkflowRunHandler;
use App\Http\HealthHandler;
use App\Http\JsonErrorHandler;
use App\Http\NotFoundHandler;
use App\Http\RunWorkflowHandler;
use App\Workflow\ChangeReviewWorkflow;
use App\Workflow\WorkflowRunStore;
use FrameworkX\AccessLogHandler;
use FrameworkX\App;
use FrameworkX\Container;
use FrameworkX\ErrorHandler;

final class AppFactory
{
    public static function create(?string $projectRoot = null): App
    {
        $root = $projectRoot ?? dirname(__DIR__);
        $runsDir = $root . '/var/runs';
        $logFile = $root . '/var/log/app.log';

        $container = new Container([
            'RUNS_DIR' => $runsDir,
            'LOG_FILE' => $logFile,
            WorkflowRunStore::class => static fn (string $RUNS_DIR): WorkflowRunStore => new WorkflowRunStore($RUNS_DIR),
            ChangeReviewWorkflow::class => static fn (): ChangeReviewWorkflow => new ChangeReviewWorkflow(),
            AsyncExecutionExperiment::class => static fn (): AsyncExecutionExperiment => new AsyncExecutionExperiment(),
            JsonErrorHandler::class => static fn (string $LOG_FILE): JsonErrorHandler => new JsonErrorHandler($LOG_FILE),
            ErrorHandler::class => static fn (JsonErrorHandler $handler): ErrorHandler => $handler,
            HealthHandler::class => static fn (): HealthHandler => new HealthHandler(),
            DescribeWorkflowHandler::class => static fn (ChangeReviewWorkflow $workflow): DescribeWorkflowHandler => new DescribeWorkflowHandler($workflow),
            RunWorkflowHandler::class => static fn (
                ChangeReviewWorkflow $workflow,
                WorkflowRunStore $store,
                string $LOG_FILE,
            ): RunWorkflowHandler => new RunWorkflowHandler($workflow, $store, $LOG_FILE),
            GetWorkflowRunHandler::class => static fn (WorkflowRunStore $store): GetWorkflowRunHandler => new GetWorkflowRunHandler($store),
            AnalysisHandler::class => static fn (): AnalysisHandler => new AnalysisHandler(),
            ExperimentHandler::class => static fn (AsyncExecutionExperiment $experiment): ExperimentHandler => new ExperimentHandler($experiment),
            NotFoundHandler::class => static fn (): NotFoundHandler => new NotFoundHandler(),
        ]);

        $app = new App($container, AccessLogHandler::class, ErrorHandler::class);

        $app->get('/health', HealthHandler::class);
        $app->get('/workflows/change-review', DescribeWorkflowHandler::class);
        $app->post('/workflows/change-review/runs', RunWorkflowHandler::class);
        $app->get('/workflows/change-review/runs/{runId}', GetWorkflowRunHandler::class);
        $app->get('/analysis/framework-x-flow', AnalysisHandler::class);
        $app->get('/experiments/sequential-vs-concurrent', ExperimentHandler::class);
        $app->any('{path:.*}', NotFoundHandler::class);

        return $app;
    }
}
