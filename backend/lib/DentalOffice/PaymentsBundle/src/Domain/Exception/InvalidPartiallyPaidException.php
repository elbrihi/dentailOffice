<?php

namespace DentalOffice\PaymentsBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidPartiallyPaidException extends DomainException
{
    private const PAID = 'paid';
    private const PARTIALLY_PAID = 'partially_paid';
    
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function invalidTransition(string $from, string $to): self
    {
        return new self(
            sprintf(
                'Invalid transition from "%s" to "%s". Only "%s" can become "%s".',
                $from,
                $to,
                static::PAID,
                static::PARTIALLY_PAID
            )
        );
    }
}