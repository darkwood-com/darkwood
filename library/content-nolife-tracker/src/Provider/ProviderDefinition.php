<?php

namespace App\Provider;

final readonly class ProviderDefinition
{
    /**
     * @param list<ProductDefinition> $products
     */
    public function __construct(
        public string $key,
        public string $displayName,
        public array $products,
        public ?string $documentationUrl = null,
    ) {
    }

    public function product(string $key): ?ProductDefinition
    {
        foreach ($this->products as $product) {
            if ($product->key === $key) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->key,
            'name' => $this->displayName,
            'documentationUrl' => $this->documentationUrl,
            'products' => array_map(
                static fn (ProductDefinition $product): array => $product->toArray(),
                $this->products,
            ),
        ];
    }
}
