<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

// ======================================
// InvalidTimeSlot Exception
// ======================================

use DomainException;

final class InvalidTimeSlot extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            'The provided time slot is invalid. End date must be greater than start date.'
        );
    }
}