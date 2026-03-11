<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DomainException;

class InvalidAppointmentId extends DomainException
{
   public static function invalid($value):self
   {
      return new self(
         sprintf(
                'Invalid AppointmentId %d. It must be a positive integer.',
                $value
               
         )
      );
   }
}
