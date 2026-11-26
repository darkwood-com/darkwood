<?php

declare(strict_types=1);

namespace Darkwood\Bundle\DarkwoodBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class DarkwoodBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $catalogue = $this->getPath().'/data/identity.json';
        $container->fileExists($catalogue);
        $container->prependExtensionConfig('twig', [
            'globals' => ['darkwood_identity' => json_decode(file_get_contents($catalogue), true, flags: JSON_THROW_ON_ERROR)],
        ]);

        $container->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [$this->getPath().'/assets' => 'darkwood'],
            ],
        ]);
    }
}
