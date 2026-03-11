<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception ;

use DomainException;

class InvalidPurpose extends DomainException
{

   public static function empty():self
   {
      return new self(
       sprintf("The purpse sould not be empty")
      );
   }

   public static function tooShort(string $value):self
   {
     return new self(
      sprintf("the %s is to short should the lengh of more than 3",$value)
     );
   }
    public static function tooLength(string $value):self
   {
      return new self(
      sprintf("the %s is to long should the lengh of less than 255",$value)
     );
   }
}