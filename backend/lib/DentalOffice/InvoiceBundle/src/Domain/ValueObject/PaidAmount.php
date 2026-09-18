<?php


namespace DentalOffice\InvoiceBundle\Domain\ValueObject;


final class PaidAmount
{

   private function __construct(private float $paidAmount)
   {

   }

   public static function fromPositivePaidAmount(float $paidAmount): self
   {
      return new self($paidAmount);
   }

   public static function fromZeroPaidAmount(float $paidAmount = 0): self
   {
      return new self($paidAmount);
   }

   public function paidAmount(): float
   {
      return $this->paidAmount;
   }

   // private function validate(float $netAmount): void
   // {
   //    if ($netAmount < 0) {
   //       throw new InvalidNetAmountException('Net amount must be a positive number.');
   //    }
   // }
}