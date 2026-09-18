<?php


namespace DentalOffice\PaymentsBundle\Domain\ValueObject;

class PaymentAmountApaid
{
   private float $amountPaid;

   private function __construct(float $amountPaid)
   {
      $this->amountPaid = $amountPaid;
   }

   public static function fromFloat(float $amountPaid): self
   {
      if ($amountPaid < 0) {
         throw new \InvalidArgumentException('Amount paid cannot be negative');
      }
      return new self($amountPaid);
   }

   public function getAmountPaid(): float
   {
      return $this->amountPaid;
   }

   public function setAmountPaid(float $amountPaid): void
   {
      $this->amountPaid = $amountPaid;
   }
   
}