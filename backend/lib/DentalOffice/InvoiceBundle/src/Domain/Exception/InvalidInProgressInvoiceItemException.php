<?php


namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidInProgressInvoiceItemException extends DomainException
{
   private const PLANNED = 'planned';
   private function __construct(string $message = '', int $code =0, ?Throwable $previous = null)
   {
      parent::__construct( $message,$code,$previous);
   }

   public static function invalidTransition(
      string $from,
      string $to):self
   {
     return new self(
            sprintf(
                'Invalid InvoiceItem transition from "%s". Allowed target status: "%s".',
                $from,
                $to
            )
        );
   }
}