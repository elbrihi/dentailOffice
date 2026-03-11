<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DateTimeImmutable;
use DomainException;

class InvalidAppointmentDate  extends DomainException
{

     public static function invalid(DateTimeImmutable $value):self
     {

        return new self(
           sprintf('the invalid date "%s" should not in past',
            $value->format("T-m-d H:i:s")
           )
        );
     }

}