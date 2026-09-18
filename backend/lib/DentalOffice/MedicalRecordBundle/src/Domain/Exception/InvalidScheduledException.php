<?php


namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;

class InvalidScheduledException extends DomainException
{
   private function __construct(string $message = "")
   {
         return parent::__construct($message);
   }

   public static function invalidStatus(string $currentStatus): self
    {
        return new self(
            sprintf(
                'Invalid status "%s" cannot be scheduled. Only "planned" can become "scheduled".',
                  $currentStatus
            )
        );
    }
    
    
}
