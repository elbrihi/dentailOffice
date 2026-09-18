<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;
use Throwable;

class  InvalidCancelledException extends DomainException{
   
   private function __construct(string $message = "", int $code = 0, ?Throwable $previous = null) {
         parent::__construct($message,$code,$previous);
   }

   public static function invalidTransition(string $currentStatus): self
   {
        return new self(
            sprintf(
                'Invalid transition from "%s" to "cancelled". Only "scheduled" or "in_progress" can be cancelled.',
                $currentStatus
            )
        );
   }
}

   