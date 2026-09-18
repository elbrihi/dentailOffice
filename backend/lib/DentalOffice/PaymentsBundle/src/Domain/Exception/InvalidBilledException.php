<?php

namespace DentalOffice\PaymentsBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidBilledException extends DomainException
{
    private const UNBILLED = "unbilled";
    private const BILLED = "billed";
    
   public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
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
                static::UNBILLED,
                static::BILLED
            )
        );
   }

  
}
