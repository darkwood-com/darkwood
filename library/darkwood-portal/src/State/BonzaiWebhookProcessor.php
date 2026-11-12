<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\Bonzai\BonzaiNotificationHandler;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class BonzaiWebhookProcessor implements ProcessorInterface
{
    public function __construct(
        private BonzaiNotificationHandler $handler,
        private RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            throw new BadRequestHttpException('No current request.');
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            throw new BadRequestHttpException('Request body must be valid JSON.');
        }

        $orderId = trim((string) ($payload['order_id'] ?? ''));
        if ($orderId === '' || !ctype_digit($orderId)) {
            throw new BadRequestHttpException('A numeric order_id is required.');
        }

        return $this->handler->handle($orderId);
    }
}
