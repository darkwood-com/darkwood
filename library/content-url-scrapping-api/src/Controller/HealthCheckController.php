<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class HealthCheckController
{
    #[Route('/ping', name: 'health_check')]
    public function ping(): JsonResponse
    {
        return new JsonResponse(['status' => 'OK']);
    }
}
