<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;

class InvalidInvoiceIdException extends DomainException
{
   private function __construct()
   {
      
   }

   public static function invalidInvoiveId():Self
   {
      return new self(sprintf("the total amount should be postive"));
   }
}