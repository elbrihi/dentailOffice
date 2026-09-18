<?php 

namespace DentalOffice\PaymentsBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\Aggregate\Refoud;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundedAmount;

final class Allocation{


    private int $allocationId;
    private InvoiceItem  $invoiceItem;
    private AllocatedAmount $allocatedAmount;
    private \DateTimeInterface $createdAt;
    private \DateTimeInterface $updatedAt;
    private string $createdBy;
    private string $updatedBy;


    private function __construct(
        InvoiceItem $invoiceItem,
        AllocatedAmount $allocatedAmount,
        private array $refunds = [],
        // \DateTimeInterface $createdAt = null,
        // \DateTimeInterface $updatedAt = null,
        string $createdBy = '',
        string $updatedBy = ''
    ) {
        $this->invoiceItem = $invoiceItem; 
        $this->allocatedAmount = $allocatedAmount;
        // $this->createdAt = $createdAt ?? new DateTimeImmutable();
        // $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        // $this->createdBy = $createdBy;
        // $this->updatedBy = $updatedBy;
    }
    
    public static function allocatePaymentToItem(
        InvoiceItem $invoiceItem,
        AllocatedAmount $allocatedAmount,

    ): self {
        return new self(
            $invoiceItem, 
            $allocatedAmount, 

        );
    }

    public function addRefoud(Refoud $refound)
    {
        $this->refunds[] = $refound;
    }
    
    public function getInvoiceItem(): InvoiceItem
    {
        return $this->invoiceItem;
    }

    public function getAllocatedAmount(): AllocatedAmount
    {
        return $this->allocatedAmount;
    }

    public function getRefunds(): array
    {
        return $this->refunds;
    }
}