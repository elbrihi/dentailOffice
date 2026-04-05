<?php


namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceItemStatus;

class InvoiceItemStatus
{
   private const PLANNED = "planned";
   private const COMPLETED = "complted";
   private const PAID = "paid";
   // planned → completed → paid
   private function __construct(private $status)
   {
     if(!in_array($status, [
            self::PLANNED,
            self::COMPLETED,
            self::PAID
        ], true) ) 
      {
         throw InvalidInvoiceItemStatus::invalidStatus($status);
      }
   }

   public static function planned(): self 
   { 
      return new self(self::PLANNED); 
   }
   
   public static function completed(): self 
   { 
      return new self(self::COMPLETED); 
   }
   
   public static function paid(): self 
   { 
      return new self(self::PAID); 
   
   }

   public function isPlanned(): bool 
   { 
      return $this->status === self::PLANNED; 
   }
   
   public function isCompleted(): bool 
   { 
      return $this->status === self::COMPLETED; 
   }
   
   public function isPaid(): bool {
       return $this->status === self::PAID; 
   }
   
   /**
    * Get the value of status
    */ 
   public function getStatus():string
   {
      return $this->status;
   }


}