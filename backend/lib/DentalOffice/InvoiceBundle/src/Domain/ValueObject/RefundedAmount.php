<?php

namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidRefundAmountException;

final class RefundedAmount
{
   public function __construct(private float $refundedAmount)
   {
      $this->validate($refundedAmount);
   }

   public static function fromPositiveRefundedAmount(float $refundedAmount): self
   {
      return new self($refundedAmount);
   }

   public static function fromZeroRefundedAmount(float $refundedAmount = 0): self
   {
      return new self($refundedAmount);
   }

   public function refundedAmount(): float
   {
      return $this->refundedAmount;
   }

   private function validate(float $refundedAmount): void
   {
      if ($refundedAmount < 0) {
         throw new InvalidRefundAmountException('Refund amount must be a positive number.');
      }
   }

   /**
    * Set the value of refundAmount
    */
   public function setRefundedAmount(float $refundedAmount): self
   {
      $this->refundedAmount = $refundedAmount;

      return $this;
   }

   /**
    * Get the value of refundAmount
    */
   public function getRefundedAmount(): float
   {
      return $this->refundedAmount;
   }
   
}