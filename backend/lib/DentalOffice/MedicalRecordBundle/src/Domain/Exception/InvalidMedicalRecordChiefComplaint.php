<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidMedicalRecordChiefComplaint extends \DomainException
{
    public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
     return parent::__construct($message, $code, $previous);
    }
    public static function tooShort(): self
    {
        return new self("The chief complaint must have at least 1 character.");
    }
}