<?php

namespace DentalOffice\InvoiceBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundedAmount;

final readonly class Refoud
{
   public function __construct(
      private int $id,
      private RefundedAmount $refundedAmount,
      private InvoiceItem $invoiceItem,
      private string $reason,
   ) {}
      public static function createRefound(
         int $id,
         RefundedAmount $refundAmount,
         InvoiceItem $invoiceItem,
         string $reason,
      ): self {
         return new self($id, $refundAmount, $invoiceItem, $reason);
      }

      /**
       * Get the value of id
       */
      public function getId(): int
      {
         return $this->id;
      }

      /**
       * Get the value of refundAmount
       */
      public function getRefundAmount(): RefundedAmount
      {
         return $this->refundedAmount;
      }

      /**
       * Get the value of invoiceItem
       */
      public function getInvoiceItem(): InvoiceItem
      {
         return $this->invoiceItem;
      }

      /**
       * Get the value of reason
       */
      public function getReason(): string
      {
         return $this->reason;
      }
}