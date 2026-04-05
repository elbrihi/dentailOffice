<?php

namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceTotalAmount;

class InvoiceTotalAmount
{
   private function __construct(private float $totalAmount)
   {
     
   }
    public static function fromPositifTotlaAmount(
      $totalAmount
      ):self
   {


      if ( $totalAmount < 0) {

  
        throw InvalidInvoiceTotalAmount::invalidNegativeTotalamount();
      }

      return new self($totalAmount);
   }

   /**
    * Get the value of totalAmount
    */ 
   public function getTotalAmount():float
   {
      return $this->totalAmount;
   }
}