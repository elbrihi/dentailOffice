<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\Handler;

use DentalOffice\AppointmentSchedulingBundle\Domain\Service\AppointmentConflictCheckerInterface;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Repository\AppointmentRepository;

class ScheduleAppointmentHandler implements ScheduleAppointmentHandlerInterface
{
    
   public function __construct(
        private AppointmentRepository $repository,
      
    ) {}


}