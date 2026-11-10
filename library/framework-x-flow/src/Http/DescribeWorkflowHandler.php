<?php

declare(strict_types=1);

namespace App\Http;

use App\Workflow\ChangeReviewWorkflow;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DescribeWorkflowHandler
{
    public function __construct(
        private readonly ChangeReviewWorkflow $workflow,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return JsonResponse::ok($this->workflow->describe());
    }
}
