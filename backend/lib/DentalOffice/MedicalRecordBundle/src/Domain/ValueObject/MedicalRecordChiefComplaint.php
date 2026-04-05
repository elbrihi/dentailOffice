<?php


namespace DentalOffice\MedicalRecordBundle\Domain\ValueObject;

use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidMedicalRecordChiefComplaint;

final class MedicalRecordChiefComplaint
{
   private string $value;
   private  const MIN_LENGTH = 1;
   private function __construct(
      string $value 
   )
   {
      $this->value = $value;
   }

   public static function chiefComplaint($value):self
   {
       
      if (strlen($value) < self::MIN_LENGTH) {
         throw InvalidMedicalRecordChiefComplaint::tooShort();
      }

      return new self($value);
   }

   /**
    * Get the value of value
    */ 
   public function getValue():string
   {
         return $this->value;
   }
}