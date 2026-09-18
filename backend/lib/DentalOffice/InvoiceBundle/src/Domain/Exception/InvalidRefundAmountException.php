<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;
use Throwable;



class InvalidRefundAmountException extends DomainException
{

   public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
   {
      parent::__construct($message, $code, $previous);
   }
   public static function withPositiveValue(float $value): self
   {
      return new self(sprintf('Invalid refund amount: %s. Refund amount must be a positive number.', $value));
   }
}