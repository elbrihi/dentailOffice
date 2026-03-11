<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DomainException;
use Throwable;

class ShowException extends DomainException
{

  private function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
  {
   return parent::__construct($message, $code, $previous);
  }

  public static function nowShp():self
  {

      return new self(
         sprintf("Only confirmed appointments can become no-show")
      );
  }
  
}