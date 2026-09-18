<?php


namespace DentalOffice\InvoiceBundle\Domain\ValueObject;


final class NetPaidAmount
{

   private function __construct(private float $netPaidAmount)
   {

   }

   public static function fromPositiveNetPaidAmount(float $netPaidAmount): self
   {
      return new self($netPaidAmount);
   }

   public static function fromZeroNetPaidAmount(float $netPaidAmount = 0): self
   {
      return new self($netPaidAmount);
   }

   public function getNetPaidAmount(): float
   {
      return $this->netPaidAmount;
   }

   // private function validate(float $netAmount): void
   // {
   //    if ($netAmount < 0) {
   //       throw new InvalidNetAmountException('Net amount must be a positive number.');
   //    }
   // }
}