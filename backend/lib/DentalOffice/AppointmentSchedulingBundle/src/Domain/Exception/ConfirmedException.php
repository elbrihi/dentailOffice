<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DomainException;
use Throwable;

class ConfirmedException extends DomainException
{
     private function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
     {
      return parent::__construct($message, $code, $previous);
     }

     public static function invalid():self
     {
         return new self(
            
           sprintf("to confirme the appoinment schould be shoudled first") 
        
        );
     }
}