<?php
namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidInProgressException extends DomainException
{
      private function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
      {
         return parent::__construct($message,$code,$previous);
      }

      public static function invalidStatus(string $currentStatus):self
      {
            return new self(sprintf(
                  'Invalid status "%s" cannot be in progress. Only "scheduled" can become "in_progress".'
                  ,$currentStatus
            ));
      }
}