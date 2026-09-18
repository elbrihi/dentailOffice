<?php



namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceTotalAmount;

class  RemainingDue
{
   private  function __construct(private float $remainingDue)
   {

      $this->remainingDue = $remainingDue;
     
   }



   public static function fromPositifRemainingDue(
      float $totalAmount
      ):self
   {


      if ( $totalAmount < 0) {

  
        throw InvalidInvoiceTotalAmount::invalidNegativeTotalamount();
      }

      return new self($totalAmount);
   }

   /**
    * Get the value of remainingDue
    */
   public function getRemainingDue(): float
   {
      return $this->remainingDue;
   }
} 