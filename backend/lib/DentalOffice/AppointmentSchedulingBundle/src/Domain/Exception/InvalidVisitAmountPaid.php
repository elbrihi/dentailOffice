<?php


namespace DentalOffice\AppointmentSchedulingBundle\Domain\Exception;

use DomainException;
use Throwable;

final class InvalidVisitAmountPaid extends DomainException
{
    private function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
    {
     return parent::__construct($message, $code, $previous);
    }

    public static function negativeValue():self
    {
       return new self(
        sprintf("the amount paid value schould be positif")
       );
    }
}