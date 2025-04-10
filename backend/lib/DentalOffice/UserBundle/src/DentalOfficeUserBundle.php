<?php
namespace DentalOffice\UserBundle;

use DentalOffice\UserBundle\Infrastructure\Symfony\DependencyInjection\DentalOfficeUserExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class DentalOfficeUserBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DentalOfficeUserExtension();
    }
}