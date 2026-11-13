<?php

declare(strict_types=1);

namespace App\Http;

use App\Workflow\WorkflowRunStore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetWorkflowRunHandler
{
    public function __construct(
        private readonly WorkflowRunStore $store,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $runId = (string) $request->getAttribute('runId', '');
        $run = $this->store->get($runId);
        if ($run === null) {
            return JsonResponse::error(404, 'run_not_found', 'No run found for the given runId.');
        }

        return JsonResponse::ok($run->report);
    }
}
