<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use App\Appointment\Domain\Exception\InvalidTimeSlot;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidTimeSlot as ExceptionInvalidTimeSlot;

// ======================================
// TimeSlot Value Object
// ======================================


final class TimeSlot 
{
    private \DateTimeImmutable $start;
    private \DateTimeImmutable $end;

    public function __construct(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {

       
        
        if ($end <= $start) {
            throw new ExceptionInvalidTimeSlot();
        }

        $this->start = $start;
        $this->end = $end;
    }

    public function overlaps(TimeSlot $other): bool
    {
        
        return $this->start < $other->end &&
               $other->start < $this->end;
    }

    public function isInPast(): bool
    {
        return $this->start < new \DateTimeImmutable();
    }

    public function getStart(): DateTimeImmutable
    {
            return $this->start;
    }
    public function getEnd(): DateTimeImmutable
    {
            return $this->end;
    }
}