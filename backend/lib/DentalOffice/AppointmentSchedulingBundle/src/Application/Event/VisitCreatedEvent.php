<?php

namespace DentalOffice\AppointmentSchedulingBundle\Application\Event;

use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;

final class VisitCreatedEvent
{

    public function __construct(
        private readonly Visit $visit,
        private int $medicalRecordID
    )
    {}

    public function getVisit(): Visit
    {
        return $this->visit;
    }
    
    public function getMedicalRecordId(): int
    {
        return  $this->medicalRecordID;
    }
}