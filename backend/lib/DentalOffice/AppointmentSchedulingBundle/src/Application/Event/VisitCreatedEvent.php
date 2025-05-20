<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\Event;

use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;

final class VisitCreatedEvent
{

    public function __construct(
        private readonly Visit $visit
    )

    {
        
    }

    public function getVisit(): Visit
    {
        return $this->visit;
    }
}