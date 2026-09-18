<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;


use DomainException;
use Throwable;

class InvalidDoneException extends DomainException
{
   private function __construct(string $message= "",int $code = 0, Throwable|null $previous = null)
   {
      parent::__construct($message,$code,$previous);
   }

   public static function invalidTransition(string $from,string ...$to):self
   {
      return new self(printf(
         'Invalid transition "%s" to "%s" Only "billed" or "partialy_paid"',$from,$to
      ));
   }
}