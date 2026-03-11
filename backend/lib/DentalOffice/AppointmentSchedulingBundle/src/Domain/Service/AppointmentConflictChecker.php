<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Service;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;

// Port

interface AppointmentConflictChecker 
{
        /**
     * Checks if a practitioner already has
     * an overlapping appointment.
     */
    public function hasConflict(
        PractitionerId $practitionerId,
        TimeSlot $timeSlot
    ): bool;
}