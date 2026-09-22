<?php

declare(strict_types=1);

namespace Darkwood\AI\Platform\Result;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class RawHttpResult implements RawResultInterface
{
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
    }

    public function getData(): array
    {
        return $this->response->toArray(false);
    }

    public function getObject(): ResponseInterface
    {
        return $this->response;
    }
}
