<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ShopIdentityVerificationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // The web shop delegates identity verification to Darkwood. Sylius still
        // enforces account suspension, and verifiedAt retains its email-specific meaning.
        $container->getDefinition('sylius.user_checker.email_verification')->clearTag('security.user_checker.shop');
    }
}
