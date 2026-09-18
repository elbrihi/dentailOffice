<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;

final class InvalidCancelledStepStatusException extends DomainException
{
    private function __construct(
       private string $status
    )
    {
        parent::__construct(sprintf('Invalid step status: %s', $status));
    }
    public static function invalidTransition(string $status): self
    {
      return new self(
         
         sprintf('Invalid step status: %s step should be scheduled, confirmed,  inprogress', $status)

      );
    }
}