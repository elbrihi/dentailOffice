<?php

namespace DentalOffice\PaymentsBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidPartiallyRefundedException extends DomainException{

   private function __construct(string $message ="", int $code = 0, ?Throwable $previous = null)
   {
      parent::__construct($message, $code, $previous);
   }
   
   public static function invalidTransition(string $from, string $to): self
   {
       return new self(sprintf('Invalid transition from %s to %s', $from, $to));
   }
   
}