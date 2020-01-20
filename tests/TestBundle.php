<?php


namespace Swag\SaasConnect\Test;

use Shopware\Core\Framework\Bundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class TestBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../src/Resources/config'));
        $loader->load('services_test.xml');
    }
}
