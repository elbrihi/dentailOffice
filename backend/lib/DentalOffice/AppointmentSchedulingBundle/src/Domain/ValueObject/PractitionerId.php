<?php 

namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidPractitionerId;

class PractitionerId
{

   private function __construct(private int $value)
   {
     
       if($value < 1)
      {
         // throw InvalidPractitionerId::invalid($value);

         return throw InvalidPractitionerId::invalid($value);
      }
   }

   public function PractitionerId():int
   {
      return $this->value;
   }
   public  function generate():int
   {
      return $this->value;
   }

   public static function fromInt(int $value):self
   {
      return new self($value);

      
   }
}