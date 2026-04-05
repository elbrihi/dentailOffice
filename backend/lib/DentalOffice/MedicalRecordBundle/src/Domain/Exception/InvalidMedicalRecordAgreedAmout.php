<?php



namespace DentalOffice\MedicalRecordBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidMedicalRecordAgreedAmout extends DomainException
{
   public function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
   {
    
    return parent::__construct($message, $code, $previous);
   }

   public static function fromNumeric($value):self
   {
       return new self(
        sprintf("the agreed amount %s must be of type float or integer",$value)
       );
   }
}