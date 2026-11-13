<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthHandler
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return JsonResponse::ok([
            'status' => 'ok',
            'runtime' => 'framework-x',
            'orchestrator' => 'darkwood/flow',
        ]);
    }
}
