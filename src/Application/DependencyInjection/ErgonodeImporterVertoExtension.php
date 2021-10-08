<?php
declare(strict_types=1);

namespace Ergonode\ImporterVerto\Application\DependencyInjection;

use Ergonode\ImporterVerto\Infrastructure\Factory\Product\ProductCommandFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ErgonodeImporterVertoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../Resources/config')
        );

        $container
            ->registerForAutoconfiguration(ProductCommandFactoryInterface::class)
            ->addTag('component.verto-importer.product_command_factory_interface');

        $loader->load('services.yml');
    }
}
