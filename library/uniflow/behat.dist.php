<?php

declare(strict_types=1);

use App\Kernel;
use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;
use FriendsOfBehat\VariadicExtension\ServiceContainer\VariadicExtension;

$sylius = __DIR__.'/vendor/sylius/sylius';

return (new Config())
    ->import($sylius.'/src/Sylius/Behat/Resources/config/suites/domain/shipping/managing_shipping_methods.php')
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('domain_managing_shipping_methods'))
                    ->withPaths($sylius.'/features/domain/shipping'),
            )
            ->withExtension(new Extension(SymfonyExtension::class, [
                'kernel' => ['class' => Kernel::class, 'environment' => 'test'],
            ]))
            ->withExtension(new Extension(VariadicExtension::class)),
    );
