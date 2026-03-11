<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DomainException;

// The practitioner already has an appointment during the selected time slot.
class AppointmentConflictDetected  extends DomainException
{
    
    public static function checkTimeSlot():self
    { 
       return new self(
           'The practitioner already has an appointment during the selected time slot.'
       );
    }
}