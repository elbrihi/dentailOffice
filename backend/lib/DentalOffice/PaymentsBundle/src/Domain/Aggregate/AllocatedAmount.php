<?php

namespace DentalOffice\PaymentsBundle\Domain\Aggregate;

class AllocatedAmount
{
   private function __construct(
      private float $allocatedAmount
   )
   {
     
   }

   public static function fromAmount(float $amount): self {
      if ($amount <= 0) {
         throw new \InvalidArgumentException('Amount must be positive');
      }
      return new self($amount);
   }
   public function getAllocatedAmount(): float
   {
      return $this->allocatedAmount;
   }
}