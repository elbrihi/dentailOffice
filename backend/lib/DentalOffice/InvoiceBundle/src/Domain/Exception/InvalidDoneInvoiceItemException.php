<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;
use Throwable;

final class InvalidDoneInvoiceItemException extends DomainException
{
    private const BILLED         = 'billed';
    private const PARTIALLY_PAID = 'partially_paid';
    private const COMPLETED      = 'completed';

    private function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Invalid InvoiceItemStatus transition.
     *
     * Allowed:
     *
     * billed         → partially_paid
     * partially_paid → partially_paid
     */
    public static function invalidTransition(
        string $from,
        string $to
    ): self {
      
        return new self(
            sprintf(
                'Invalid InvoiceItemStatus transition from "%s" to "%s". '
                . 'When execution is "%s" and payment is "%s", '
                . 'the InvoiceItemStatus must be "%s" or "%s".',
                $from,
                $to,
                self::COMPLETED,
                self::PARTIALLY_PAID,
                self::BILLED,
                self::PARTIALLY_PAID
            )
        );
    }
}