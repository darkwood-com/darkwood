<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Reports the active KMS backend without exposing key material.
 * KMS_BACKEND is a label only — never put a DSN or key here.
 */
final class KeyBackendInfo
{
    public function __construct(
        #[Autowire('%env(KMS_BACKEND)%')]
        private string $backend,
    ) {
    }

    public function name(): string
    {
        return $this->backend;
    }
}
