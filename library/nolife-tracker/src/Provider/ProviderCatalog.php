<?php

namespace App\Provider;

use App\Account\AuthenticationMethod;

final class ProviderCatalog
{
    /**
     * @var list<ProviderDefinition>
     */
    private array $providers;

    public function __construct()
    {
        $this->providers = [
            new ProviderDefinition('openai', 'OpenAI', [
                $this->createProduct('chatgpt', 'ChatGPT', [AuthenticationMethod::None]),
                $this->createProduct('codex', 'Codex', [AuthenticationMethod::None, AuthenticationMethod::ExistingCli]),
                $this->createProduct('api', 'API', [AuthenticationMethod::None, AuthenticationMethod::OfficialApi, AuthenticationMethod::OsCredentialStore]),
            ], 'https://platform.openai.com/docs/'),
            new ProviderDefinition('anthropic', 'Anthropic', [
                $this->createProduct('claude', 'Claude', [AuthenticationMethod::None, AuthenticationMethod::ExistingCli]),
                $this->createProduct('api', 'API', [AuthenticationMethod::None, AuthenticationMethod::OfficialApi, AuthenticationMethod::OsCredentialStore]),
            ], 'https://docs.anthropic.com/'),
            new ProviderDefinition('google', 'Google', [
                $this->createProduct('gemini', 'Gemini', [AuthenticationMethod::None, AuthenticationMethod::ExistingCli]),
                $this->createProduct('api', 'API', [AuthenticationMethod::None, AuthenticationMethod::OfficialApi, AuthenticationMethod::OsCredentialStore]),
            ], 'https://ai.google.dev/gemini-api/docs'),
            new ProviderDefinition('xai', 'xAI', [
                $this->createProduct('grok', 'Grok', [AuthenticationMethod::None]),
                $this->createProduct('api', 'API', [AuthenticationMethod::None, AuthenticationMethod::OfficialApi, AuthenticationMethod::OsCredentialStore]),
            ], 'https://docs.x.ai/'),
            new ProviderDefinition('cursor', 'Cursor', [
                $this->createProduct('ide', 'IDE', [AuthenticationMethod::None]),
            ], 'https://docs.cursor.com/'),
        ];
    }

    /**
     * @return list<ProviderDefinition>
     */
    public function all(): array
    {
        return $this->providers;
    }

    public function find(string $key): ?ProviderDefinition
    {
        foreach ($this->providers as $provider) {
            if ($provider->key === $key) {
                return $provider;
            }
        }

        return null;
    }

    public function product(string $providerKey, string $productKey): ?ProductDefinition
    {
        return $this->find($providerKey)?->product($productKey);
    }

    /** @return list<AuthenticationMethod> */
    public function authenticationMethods(string $providerKey): array
    {
        $provider = $this->find($providerKey);
        if (null === $provider) {
            return [];
        }
        $methods = [];
        foreach ($provider->products as $product) {
            foreach ($product->authenticationMethods as $method) {
                $methods[$method->value] = $method;
            }
        }

        return array_values($methods);
    }

    /**
     * @param list<AuthenticationMethod> $authenticationMethods
     */
    private function createProduct(string $key, string $name, array $authenticationMethods): ProductDefinition
    {
        $capabilities = [];
        foreach (Capability::cases() as $capability) {
            $capabilities[$capability->value] = CapabilityState::NotImplemented;
        }

        return new ProductDefinition($key, $name, $authenticationMethods, $capabilities);
    }
}
