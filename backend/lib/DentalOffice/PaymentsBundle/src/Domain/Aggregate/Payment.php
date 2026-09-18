<?php

namespace DentalOffice\PaymentsBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\NetAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\NetPaidAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundedAmount;
use DentalOffice\PaymentsBundle\Domain\ValueObject\PaymentStatus;
use DentalOffice\PaymentsBundle\Domain\ValueObject\PaymentAmountApaid;

class Payment
{
   private int $paymentId;
   private PaymentStatus $paymentStatus;
   private PaymentAmountApaid $amountPaid ;

   private Allocation $allocation;
   private array $allocations;
   private ?InvoiceItem $invoiceItem;
   

   private function __construct(
      PaymentAmountApaid $amountPaid,
      private RefundedAmount $refundedAmount,
      private NetPaidAmount $netPaidAmount,
      ?InvoiceItem $invoiceItem = null,
      ?PaymentStatus $paymentStatus = null,
      array $allocations = [],
      
      )
   {
      $this->paymentStatus = $paymentStatus
        ?? InvoiceItemPaymentStatus::unpaid(InvoiceItemPaymentStatus::UNPAID);
      $this->amountPaid  = $amountPaid;
      $this->allocations = $allocations;
      $this->invoiceItem = $invoiceItem;
   }

   
   public static function payVisit(
      int $paymentId,
      PaymentAmountApaid $amountPaid,
      InvoiceItem $invoiceItem,
      RefundedAmount $refundedAmount ,
      NetPaidAmount $netPaidAmount,
      ?PaymentStatus $paymentStatus = null
      
     ): self
   {
    //  dd($amountPaid);
      return new self(
         $amountPaid, 
         $refundedAmount,
         $netPaidAmount,
         $invoiceItem,
         $paymentStatus
      );
   }

   public function getPaymentStatus(): PaymentStatus
   {
      return $this->paymentStatus;
   }

   public function getAmountPaid(): PaymentAmountApaid
   {
      return $this->amountPaid;
   }

    public function getRefundedAmount(): RefundedAmount
   {
      return $this->refundedAmount;
   }

   public function setPaymentStatus(PaymentStatus $paymentStatus): void
   {
      $this->paymentStatus = $paymentStatus;
   }

   public function setAmountPaid(PaymentAmountApaid $amountPaid): void
   {
      $this->amountPaid = $amountPaid;
   }
   
   public function addAllocation(Allocation $allocations): void
   {
      $this->allocations[] = $allocations;
   }

   public function getAllocation():Allocation
   {
      return $this->allocation;
   }

   public function getAllocations(): array
   {
      return $this->allocations;
   }

   public function unpaid()
   {
      
   }

   /**
    * Get the value of invoiceItem
    */
   public function getInvoiceItem(): ?InvoiceItem
   {
      return $this->invoiceItem;
   }


      /**
       * Set the value of refundedAmount
       */
      public function setRefundedAmount(RefundedAmount $refundedAmount): self
      {
            $this->refundedAmount = $refundedAmount;

            return $this;
      }



   /**
    * Get the value of netAmount
    */
   public function getNetAmount(): NetPaidAmount
   {
         return $this->netPaidAmount;
   }

   /**
    * Set the value of netAmount
    */
   public function setNetAmount(NetPaidAmount $netPaidAmount): self
   {
         $this->netPaidAmount = $netPaidAmount;

         return $this;
   }
}