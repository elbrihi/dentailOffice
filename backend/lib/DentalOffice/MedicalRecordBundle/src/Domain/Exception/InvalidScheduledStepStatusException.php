<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;

class InvalidScheduledStepStatusException extends DomainException
{
    private function __construct(
       private string $status
    )
    {
        parent::__construct(sprintf('Invalid step status: %s', $status));
    }
    public static function invalidStatus(string $status): self
    {
      return new self(
         
         sprintf('Invalid step status: %s step should be planned', $status)

      );
    }

    public static function invalidTransition(string $transition): self
    {
      return new self(sprintf('Invalid transition: %s', $transition));
    }
}