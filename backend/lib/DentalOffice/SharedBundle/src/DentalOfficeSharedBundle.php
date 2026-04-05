<?php

namespace DentalOffice\SharedBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use DentalOffice\SharedBundle\Infrastructure\Symfony\DependencyInjection\DentalOfficeSharedExtension;
class DentalOfficeSharedBundle extends Bundle
{

     public function getContainerExtension(): ?ExtensionInterface
    {
        return new DentalOfficeSharedExtension();
    }
}