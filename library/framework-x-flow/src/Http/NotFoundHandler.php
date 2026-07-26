<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Catch-all JSON 404. Framework X RouteHandler uses its own HTML ErrorHandler
 * for unmatched routes; registering this last with any() avoids that HTML page.
 */
final class NotFoundHandler
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return JsonResponse::error(404, 'not_found', 'No route matched this request.');
    }
}
