<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AnalysisHandler
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return JsonResponse::ok([
            'title' => 'Framework X + Darkwood Flow integration analysis',
            'frameworkX' => [
                'role' => 'HTTP runtime',
                'responsibilities' => [
                    'Routing and middleware pipeline',
                    'PSR-7 request/response handling',
                    'Async handler return types (Response, Promise, Generator, Fiber await on CLI PHP 8.1+)',
                    'Dual runtime: ReactPHP CLI server or traditional SAPI',
                    'Access logging and default error handling',
                ],
                'unitOfExecution' => 'Request handler',
                'lifetime' => 'One HTTP request within a process (long-lived under CLI HttpServerRunner)',
            ],
            'darkwoodFlow' => [
                'role' => 'Workflow orchestration',
                'responsibilities' => [
                    'Explicit job chains via FlowFactory',
                    'Ip payload propagation between jobs',
                    'Driver-based async execution (Fiber, React, Amp, …)',
                    'IpStrategy concurrency across multiple Ips (MaxIpStrategy)',
                    'Per-stage errorJob hooks',
                ],
                'unitOfExecution' => 'Workflow step (job) over an Ip',
                'lifetime' => 'One workflow run until await completes',
            ],
            'integrationPoints' => [
                'HTTP handlers invoke ChangeReviewWorkflow',
                'EmbeddedReactDriver adapts Flow to Framework X shared event loop',
                'FrameworkX Container wires services; Flow jobs receive dependencies via constructors',
                'WorkflowRunStore persists traces outside the request attributes',
            ],
            'asyncCompatibility' => [
                'Compatible when jobs use ReactDriver/EmbeddedReactDriver delays and handlers return Promises',
                'Conflict: native ReactDriver::await() calls Loop::stop() — must not be used inside HttpServerRunner',
                'FiberDriver::await() busy-loops and can stall the HTTP server under delay',
            ],
            'lifecycleRisks' => [
                'Shared process memory across requests in CLI mode',
                'Mutable singletons leaking state between runs',
                'Blocking calls and nested Loop::run()/stop()',
                'Unclosed streams / leaked timers',
            ],
            'currentLimitations' => [
                'No cloud sandbox, multi-tenant isolation, or credential broker',
                'No native Flow DAG fan-out/join for parallel steps within one Ip',
                'EmbeddedReactDriver is a local adapter, not upstream Flow API',
                'Framework X is 0.x; experimental runner APIs may change',
                'Default Framework X HTML 404/405 unless catch-all route is registered',
            ],
            'comparison' => [
                ['concern' => 'Primary role', 'frameworkX' => 'HTTP runtime', 'flow' => 'Workflow orchestration'],
                ['concern' => 'Unit of execution', 'frameworkX' => 'Request handler', 'flow' => 'Workflow job / Ip'],
                ['concern' => 'Control flow', 'frameworkX' => 'Middleware + routes', 'flow' => 'Explicit job chain (fn / FlowFactory)'],
                ['concern' => 'Async support', 'frameworkX' => 'Promises, generators, Fibers', 'flow' => 'Drivers (Fiber default, React, Amp, …)'],
                ['concern' => 'Lifetime', 'frameworkX' => 'Request in process', 'flow' => 'Workflow run until await'],
                ['concern' => 'State', 'frameworkX' => 'Request attributes + services', 'flow' => 'Ip data pipeline'],
                ['concern' => 'Observability', 'frameworkX' => 'Access logs + middleware', 'flow' => 'App-level run/step traces (this demo)'],
                ['concern' => 'Deployment', 'frameworkX' => 'CLI server or SAPI', 'flow' => 'Embedded wherever PHP runs'],
                ['concern' => 'Team sharing', 'frameworkX' => 'Requires deployment', 'flow' => 'Requires shared storage + runtime'],
                ['concern' => 'Secure sandbox', 'frameworkX' => 'No', 'flow' => 'No'],
                ['concern' => 'Self-improvement', 'frameworkX' => 'No', 'flow' => 'Retrospective proposes changes only'],
            ],
        ]);
    }
}
