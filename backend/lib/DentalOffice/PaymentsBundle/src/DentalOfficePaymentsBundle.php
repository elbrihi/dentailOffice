<?php

namespace DentalOffice\PaymentsBundle;

use DentalOffice\PaymentsBundle\Infrastructure\Symfony\DependencyInjection\DentalOfficePaymentsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DentalOfficePaymentsBundle extends Bundle
{
    public function boot():void
    {
       
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DentalOfficePaymentsExtension();
    }
}