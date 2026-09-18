<?php

namespace DentalOffice\InvoiceBundle\Domain\Exception;

use DomainException;
use Throwable;

class InvalidInvoiceItemStatus extends DomainException
{

   private const PLANNED = "planned";
   private const COMPLETED = "complted";
   private const PAID = "paid"; 
   private const IN_PROGRESS = "in_progress";  

   private function __construct(string $message = "", int $code = 0, Throwable|null $previous = null)
   {
      return parent::__construct($message, $code, $previous);
   }

   public static function invalidStatus(string $status):self
   {
      return new self(sprintf(
         " %s is invliad status just those accept %s , %s , %s or %s"
         ,$status,static::PLANNED,static::PAID,static::COMPLETED,static::IN_PROGRESS
           
      ));
   }

   public static function invalidTransition(string $from, string $to): self
   {

        return new self(
            sprintf(
                'Invalid transition from "%s" to "%s". Only "planned" can become "in_progress".',
                $from,
                $to
            )
        );
   }
   // public static function invalidTransition(string $from, string $to): self
   // {
   //      return new self(
   //          sprintf(
   //              'Invalid transition from "%s" to "%s". Only "planned" can become "in_progress".',
   //              $from,
   //              $to
   //          )
   //      );
   // }
     
}