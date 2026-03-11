<?php


namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DomainException;

final class InvalidPatientId extends DomainException
{
    public static function fromValue(string $value): self
    {
        return new self(
            sprintf('Invalid Patient ID provided: "%s".', $value)
        );
    }

    public static function empty(): self
    {
        return new self('Patient ID cannot be empty.');
    }

    public static function invalidFormat(): self
    {
        return new self('Patient ID format is invalid.');
    }


    public static function invalid(int $value): self
    {
        return new self(
            sprintf('Invalid PatientId "%d". Must be a positive integer.', $value)
        );
    }
}