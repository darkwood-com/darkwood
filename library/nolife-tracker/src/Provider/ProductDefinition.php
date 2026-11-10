<?php

namespace App\Provider;

use App\Account\AuthenticationMethod;

final readonly class ProductDefinition
{
    /**
     * @param list<AuthenticationMethod>          $authenticationMethods
     * @param array<string, CapabilityState>      $capabilities
     */
    public function __construct(
        public string $key,
        public string $displayName,
        public array $authenticationMethods,
        public array $capabilities,
    ) {
    }

    public function capability(Capability $capability): CapabilityState
    {
        return $this->capabilities[$capability->value];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $capabilities = [];
        foreach (Capability::cases() as $capability) {
            $capabilities[$capability->value] = $this->capability($capability)->value;
        }

        return [
            'id' => $this->key,
            'name' => $this->displayName,
            'authenticationMethods' => array_map(
                static fn (AuthenticationMethod $method): string => $method->value,
                $this->authenticationMethods,
            ),
            'capabilities' => $capabilities,
        ];
    }
}
