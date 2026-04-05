<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;

class InvalidInvoiceTotalAmount  extends DomainException
{
   private function __construct(private float $totalAmount)
   {
      
      
   }

   public static function invalidNegativeTotalamount():self
   {
      return new self(sprintf("the total amount should be postive"));
   }
}