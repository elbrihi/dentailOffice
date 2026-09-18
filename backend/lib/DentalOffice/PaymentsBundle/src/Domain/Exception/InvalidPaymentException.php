<?php

namespace DentalOffice\PaymentsBundle\Domain\Exception;

use DomainException;
use Throwable;





final class InvalidPaymentException extends DomainException
{
   private function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
   {
      return parent::__construct($message, $code, $previous);
   }

   public static function invalidNegativeValue(float $amountPaid):self
   {
      return new self(
         printf("amount % is invilaid ",$amountPaid)
      );
   }
}