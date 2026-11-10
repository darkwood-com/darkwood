<?php

declare(strict_types=1);

namespace App\AI\Jev;

use Symfony\AI\Platform\Bridge\Jev\Factory as JevFactory;
use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Test\Replay\CassetteHttpClient;
use Symfony\AI\Platform\Test\Replay\HttpCassette;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Wraps the Symfony AI Jev bridge with cassette record/replay for the study command.
 */
final class Factory
{
    public const DEFAULT_RESULTS_DIR = '/Users/math/pCloud Drive/MathieuData/Darkwood/tasks/#67eaba_flow-jev/results';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(default:jev.empty_string:TYPESAFE_API_KEY)%')]
        private readonly string $apiKey = '',
        #[Autowire('%env(default:jev.default_results_dir:JEV_RESULTS_DIR)%')]
        private readonly string $resultsDir = self::DEFAULT_RESULTS_DIR,
    ) {
    }

    public function hasApiKey(): bool
    {
        return '' !== trim($this->apiKey);
    }

    public function cassettePath(string $name): string
    {
        return rtrim($this->resultsDir !== '' ? $this->resultsDir : self::DEFAULT_RESULTS_DIR, '/').'/cassettes/'.$name.'.json';
    }

    /**
     * @return PlatformHandle|null null when Jev cannot run (no key and no cassette)
     */
    public function tryCreate(string $cassetteName, bool $live): ?PlatformHandle
    {
        $path = $this->cassettePath($cassetteName);
        $cassette = new HttpCassette($path);
        $dir = \dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(\sprintf('Cannot create cassette directory "%s".', $dir));
        }

        if ($live) {
            if (!$this->hasApiKey()) {
                return null;
            }

            $http = new CassetteHttpClient($cassette, $this->httpClient, record: true);

            return new PlatformHandle($this->platform($http), 'live', $path);
        }

        if ($cassette->exists()) {
            $http = new CassetteHttpClient($cassette, null, record: false);

            return new PlatformHandle($this->platform($http), 'cassette', $path);
        }

        return null;
    }

    private function platform(HttpClientInterface $http): Platform
    {
        return JevFactory::createPlatform(
            '' !== $this->apiKey ? $this->apiKey : 'cassette',
            $http,
        );
    }
}
