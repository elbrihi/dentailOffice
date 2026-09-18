<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidPartiallyRefoubdedException extends DomainException
{
   private const PAID = 'paid';
   private const PARTIALLY_PAID = 'partially_paid';
   private function __construct(
      string $message ="",
      int $code=0,
      Throwable|null $previous = null
   )
   {
      parent::__construct($message,$code,$previous);
   }

   public static function invalidPartiallyRefouned(string $status):self
   {
      return new self(printf(" %s is invliad status just those accept %s , %s , %s or %s",
                             $status,self::PAID,self::PARTIALLY_PAID

                  )
            );
   }
}