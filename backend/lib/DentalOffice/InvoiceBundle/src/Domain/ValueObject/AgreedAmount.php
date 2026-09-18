<?php

namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

final class AgreedAmount
{
   private function __construct(private float $agreedAmount)
   {


      $this->agreedAmount = $agreedAmount;
   
   }

   public static function fromPositifAgreedAmount(float  $agreedAmount): self 
   {
      return new self($agreedAmount);
   }

   public function agreedAmount(): float
   {
      return $this->agreedAmount;
   }
}