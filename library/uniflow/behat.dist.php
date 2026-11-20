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

$suites = [
    'order/managing_orders',
    'product/managing_products',
    'product/managing_product_variants',
    'promotion/managing_promotion_coupons',
    'promotion/managing_promotions',
    'shipping/managing_shipping_methods',
];

$config = new Config();
$profile = (new Profile('default'))
    ->withExtension(new Extension(SymfonyExtension::class, [
        'kernel' => ['class' => Kernel::class, 'environment' => 'test'],
    ]))
    ->withExtension(new Extension(VariadicExtension::class));

foreach ($suites as $suite) {
    $config = $config->import($sylius.'/src/Sylius/Behat/Resources/config/suites/domain/'.$suite.'.php');
    $profile = $profile->withSuite(
        (new Suite('domain_'.basename($suite)))
            ->withPaths($sylius.'/features/domain/'.dirname($suite)),
    );
}

return $config->withProfile($profile);
