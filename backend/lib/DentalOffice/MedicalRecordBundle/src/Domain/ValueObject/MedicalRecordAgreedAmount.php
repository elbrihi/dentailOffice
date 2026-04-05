<?php 

namespace DentalOffice\MedicalRecordBundle\Domain\ValueObject;

use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidMedicalRecordAgreedAmout;

class MedicalRecordAgreedAmount
{
    private float $agreedAmount;
    private function __construct( float  $agreedAmount)
    {
      $this->agreedAmount = $agreedAmount;
    }

    public static function fromNumeric( $agreedAmount):self
    {
    
        if (!is_int($agreedAmount) && !is_float($agreedAmount)) {

         
            throw InvalidMedicalRecordAgreedAmout::fromNumeric( $agreedAmount);

            //    throw new InvalidMedicalRecordAgreedAmout::fromNumeric( $agreedAmount);
        }

        if ($agreedAmount < 0) {
            throw new InvalidMedicalRecordAgreedAmout("Amount cannot be negative");
        }

        return new self((float) $agreedAmount);
    }

      /**
       * Get the value of agreedAmount
       */ 
      public function getAgreedAmountValue(): int|float
      {
            return $this->agreedAmount;
      }
}