<?php

namespace DentalOffice\PaymentsBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidPaidException extends DomainException
{

   private const PAID = 'paid';
   private const PARTIALLY_PAID = 'partially_paid';
   private const PARTIALLY_REFUNDED = 'partially_refunded';
   private const BILLED = 'billed';
   
   private function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
   {
      parent::__construct($message, $code, $previous);
   }
   public static function invalidTransition(string $from, string $to): self
   {
      return new self(
         sprintf(
            'Invalid transition from "%s" to "%s". Only "%s" can become "%s".',
            $from,
            $to,
            self::PAID,
            self::PARTIALLY_PAID
         )
      );
   }


}