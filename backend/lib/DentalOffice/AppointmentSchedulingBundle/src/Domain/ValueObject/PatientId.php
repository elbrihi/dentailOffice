<?php 

namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidPatientId;

final class PatientId 
{ 
    private int $value; 

    private function __construct(int $value) 
    { 
        
        if ($value <= 0) 
        {
            throw InvalidPatientId::invalid($value);
        } 
         
        $this->value = $value; 
    } 

    public static function fromInt(int $value): self 
    { 
        
       return new self($value);
    }

    public function toInt(): int 
    { 
        return $this->value; 
    }

    public function equals(self $other): bool 
    { 
        return $this->value === $other->value; 
    } 
}