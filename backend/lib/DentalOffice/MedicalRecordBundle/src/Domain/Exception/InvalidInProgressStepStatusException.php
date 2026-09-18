<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;

class InvalidInProgressStepStatusException extends DomainException
{
    private function __construct(
       private string $status
    )
    {
        parent::__construct(sprintf('Invalid step status: %s step should be scheduled', $status));
    }
    public static function invalidTransition(string $status): self
    {
        return new self(
         sprintf('Invalid step status: %s step should be in progress', $status)
        );
    }
}