<?php
namespace Stock\SuplierBundle\Infrastucture\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class StockSuplierExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        
        //$loader = new XmlFileLoader($container, new FileLocator('/var/www/app/lib/Stock/UserBundle/src/Resources/config'));

        //$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config'));


        //$loader->load('services.xml');
    }
}

