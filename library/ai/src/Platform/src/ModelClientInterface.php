<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform;

use Darkwood\AI\Platform\Result\RawResultInterface;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
interface ModelClientInterface
{
    public function supports(Model $model): bool;

    /**
     * @param array<string, mixed>|string $payload
     * @param array<string, mixed>        $options
     */
    public function request(Model $model, array|string $payload, array $options = []): RawResultInterface;
}
