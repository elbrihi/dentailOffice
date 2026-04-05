<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidInvoiceItemStatus extends DomainException
{

   private const PLANNED = "planned";
   private const COMPLETED = "complted";
   private const PAID = "paid";

   public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
   {
      return parent::__construct($message, $code, $previous);
   }

   public static function invalidStatus(string $status):self
   {
      return new self(sprintf(
         " %s is invliad status just those accept %s , %s or %s"
         ,$status,static::PLANNED,static::PAID,static::COMPLETED
           
      ));
   }
}