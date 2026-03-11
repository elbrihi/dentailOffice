<?php


namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;
use DomainException;

class InvalidPractitionerId extends DomainException
{
   
  
    public static function invalid(int $value):self
    {

        return new self(
            sprintf("the invalid practitionerId %d should be more or equlas than 1", $value)
        );
    }

  
}