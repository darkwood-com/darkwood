<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;
use React\Http\Message\Response;

final class JsonResponse
{
    public static function ok(array $data, int $status = 200): ResponseInterface
    {
        return Response::json($data)->withStatus($status);
    }

    public static function error(int $status, string $code, string $message, array $extra = []): ResponseInterface
    {
        return Response::json([
            'error' => [
                'code' => $code,
                'message' => $message,
                ...$extra,
            ],
        ])->withStatus($status);
    }
}
