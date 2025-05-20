<?php

namespace DentalOffice\AppointmentSchedulingBundle;

use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Symfony\DependencyInjection\DentalOfficeAppointmentSchedulingExtension;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DentalOfficeAppointmentSchedulingBundle extends Bundle
{
   public function boot():void
   {
      
   }

   public function getContainerExtension(): ?ExtensionInterface
   {
       return new DentalOfficeAppointmentSchedulingExtension();
   }
}

