<?php

namespace DentalOffice\InvoiceBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemStatus;

final class InvoiceItem{


    private int $id;
    private string $description;
    private float $amount;
    private ?int $visitId;
    private InvoiceItemStatus $status;
   private function __construct(
       int $id,
        string $description,
        float $amount,
        ?int $visitId = null
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        $this->id = $id;
        $this->description = trim($description);
        $this->amount = $amount;
        $this->visitId = $visitId;
        $this->status = InvoiceItemStatus::planned();
      
   }

   public static function invoice(
        int $id,
        string $description,
        float $amount,
        ?int $visitId = null
   ):self
   {
         return new self($id,$description,$amount,$visitId);
   }

   public function  getStatus(): InvoiceItemStatus
   {
         return $this->status;
   }

  
}