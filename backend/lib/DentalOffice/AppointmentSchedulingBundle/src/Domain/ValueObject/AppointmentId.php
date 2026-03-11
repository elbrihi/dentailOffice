<?php 

namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\InvalidAppointmentId;

class AppointmentId
{
     
   private function __construct(
    private int $value, 
   )
   {
       if($value <= 0)
       {
           throw InvalidAppointmentId::invalid($value);
       }

       $this->value = $value;
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