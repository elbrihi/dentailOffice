<?php

namespace DentalOffice\MedicalRecordBundle\Domain\ValueObject;

final class DentistId
{
   private function __construct(
    private int $value
   )
   {
     $this->value = $value;
   }

   public static function toInt()
   {
      
   }
}