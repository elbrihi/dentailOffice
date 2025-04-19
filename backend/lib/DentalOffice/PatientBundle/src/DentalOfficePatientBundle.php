<?php
namespace DentalOffice\PatientBundle;

use DentalOffice\PatientBundle\Infrastructure\Symfony\DependencyInjection\DentalOfficePatientExtension as DependencyInjectionDentalOfficePatientExtension;
use DentalOffice\PatientBundle\Infrastucture\Symfony\DependencyInjection\DentalOfficePatientExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class DentalOfficePatientBundle extends Bundle
{
 
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DependencyInjectionDentalOfficePatientExtension();
    }
}