<?php
namespace DentalOffice\MedicalRecordBundle;

use DentalOffice\MedicalRecordBundle\Infrastructure\Symfony\DependencyInjection\DentalOfficeMedicalRecordExtension;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class DentalOfficeMedicalRecordBundle extends Bundle
{
 
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DentalOfficeMedicalRecordExtension();
    }
}