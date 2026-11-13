<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\AI\McpSdk\Command\McpCommand;
use Symfony\AI\McpSdk\Controller\McpController;
use Symfony\AI\McpSdk\Routing\RouteLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.php');
    }
}
