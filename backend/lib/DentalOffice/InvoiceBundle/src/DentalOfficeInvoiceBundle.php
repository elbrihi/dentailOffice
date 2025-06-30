<?php

namespace DentalOffice\InvoiceBundle;

use DentalOffice\InvoiceBundle\Infrastructure\Symfony\DependencyInjection\DentalOfficeInvoiceExtension;;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DentalOfficeInvoiceBundle extends Bundle
{

    public function boot(): void
    {
    
    }

     public function getContainerExtension(): ?ExtensionInterface
    {
        return new DentalOfficeInvoiceExtension();
    }
}