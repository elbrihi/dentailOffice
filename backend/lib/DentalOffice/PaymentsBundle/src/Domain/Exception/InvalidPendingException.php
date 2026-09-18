<?php

namespace DentalOffice\PaymentsBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidPendingException extends DomainException
{

    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
      return parent::__construct($message, $code, $previous);
    }
    public static function invalidTransition(string $from, string $to): self
    { 
      $message = sprintf("Invalid transition from %s to %s", $from, $to);
        return new self($message);
    }
}