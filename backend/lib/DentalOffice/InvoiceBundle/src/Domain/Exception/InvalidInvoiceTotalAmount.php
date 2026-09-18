<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;

class InvalidInvoiceTotalAmount  extends DomainException
{
   private function __construct(string $message)
   {   
      parent::__construct($message);
      
   }

   public static function invalidNegativeTotalamount():self
   {
      return new self(sprintf("the total amount should be postive"));
   }
}