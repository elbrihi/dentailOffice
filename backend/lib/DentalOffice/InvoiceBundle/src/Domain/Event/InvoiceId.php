<?php

namespace DentalOffice\InvoiceBundle\Domain\Event;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceIdException;

class InvoiceId
{

   private function __construct(
      private int $invoiceId
   )
   {
      
   }

   public static function fromIntNotNull(
       int $invoiceId
   ):self
   {
   

      if( isset($invoiceId) || $invoiceId < 0)
      {
         throw InvalidInvoiceIdException::invalidInvoiveId();
      }
     return new self($invoiceId);
   }

}