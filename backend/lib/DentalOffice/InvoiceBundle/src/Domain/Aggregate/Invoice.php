<?php

namespace DentalOffice\InvoiceBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;

class Invoice
{
   private function __construct(
      private InvoiceTotalAmount $totalAmount
      
   )
   {
      
   }

   public static function generateInvoice(
       InvoiceTotalAmount $totalAmount
   ):self
   {

      
      return new self($totalAmount);
   }

      /**
       * Get the value of totalAmount
       */ 
      public function getTotalAmount():InvoiceTotalAmount
      {
            return $this->totalAmount;
      }
}