<?php
namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class DentalOfficeAppointmentSchedulingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        
        $loader = new XmlFileLoader($container, new FileLocator('/var/www/app/lib/DentalOffice/PatientBundle/src/Resources/config'));

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config'));

       
        $loader->load('services.xml');
    }
}