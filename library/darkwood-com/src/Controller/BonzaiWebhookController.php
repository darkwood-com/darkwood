<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Bonzai\BonzaiNotificationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BonzaiWebhookController extends AbstractController
{
    public function __construct(
        private readonly BonzaiNotificationHandler $handler,
        #[Autowire('%env(BONZAI_WEBHOOK_TOKEN)%')]
        private readonly string $webhookToken,
    ) {}

    #[Route('/webhooks/bonzai/{token}', name: 'bonzai_webhook', methods: ['POST'])]
    public function __invoke(Request $request, string $token): JsonResponse
    {
        if ('' === $this->webhookToken || !hash_equals($this->webhookToken, $token)) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        }

        if (strlen($request->getContent()) > 64_000) {
            return $this->json(['error' => 'payload_too_large'], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        try {
            $payload = $request->toArray();
        } catch (\JsonException) {
            return $this->json(['error' => 'invalid_json'], Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($payload['event_type'] ?? null, ['product_access_granted', 'product_access_revoked'], true)) {
            return $this->json(['status' => 'ignored'], Response::HTTP_ACCEPTED);
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        if ('' === $orderId || !ctype_digit($orderId)) {
            return $this->json(['status' => 'ignored', 'reason' => 'missing_order'], Response::HTTP_ACCEPTED);
        }

        return $this->json($this->handler->handle($orderId), Response::HTTP_ACCEPTED, ['Cache-Control' => 'no-store']);
    }
}
