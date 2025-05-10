<?php

namespace App\Container;

use App\ComposioMcpTools;
use App\ComposioSdk\Composio;
use App\ComposioSdk\ComposioToolSet;
use PhpMcp\Server\Defaults\BasicContainer;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;

class ComposioContainer extends BasicContainer
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function get(string $id): mixed
    {
        if ($id === ComposioMcpTools::class) {
            $httpClient = HttpClient::create();
            
            $composioToolSet = new ComposioToolSet(
                httpClient: $httpClient,
                apiKey: $this->apiKey
            );
            
            return new ComposioMcpTools($composioToolSet);
        }

        return parent::get($id);
    }

    public function has(string $id): bool
    {
        if ($id === ComposioMcpTools::class) {
            return true;
        }

        return parent::has($id);
    }
} 