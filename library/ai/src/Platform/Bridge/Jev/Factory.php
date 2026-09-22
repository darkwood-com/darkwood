<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Darkwood\AI\Platform\Bridge\Jev;

use Darkwood\AI\Platform\Platform;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Mathieu Ledru <matyo91@gmail.com>
 */
final class Factory
{
    public static function createPlatform(
        #[\SensitiveParameter] string $apiKey,
        ?HttpClientInterface $httpClient = null,
        string $baseUrl = 'https://api.typesafe.ai',
    ): Platform {
        $httpClient ??= HttpClient::create();

        return new Platform(
            new ModelCatalog(),
            new ModelClient($httpClient, $apiKey, $baseUrl),
            new ResultConverter(),
        );
    }
}
