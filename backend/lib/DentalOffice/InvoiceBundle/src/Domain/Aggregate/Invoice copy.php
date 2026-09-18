<?php

namespace DentalOffice\InvoiceBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\AgreedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemExecutionStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RemainingDue;
use DentalOffice\PaymentsBundle\Domain\Aggregate\Allocation;
use DentalOffice\PaymentsBundle\Domain\Aggregate\Payment;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPaymentException;
use DentalOffice\PaymentsBundle\Domain\ValueObject\PaymentStatus;

final class  Invoice
{

   private const PLANNED         = 'planned';
   private const IN_PROGRESS     = 'in_progress';
   private const COMPLETED       = 'completed';
   private const CANCELLED       = 'cancelled';
   private const REFUNDED        = 'refunded';

   private  array $invoiceItem = [];
   
   private function __construct(
      private InvoiceTotalAmount $totalAmount,
      private RemainingDue $remainingDue,
      private AgreedAmount $agreedAmount,
      private InvoiceStatus $invoiceStatus,
      private ?InvoiceItemExecutionStatus $executionStatus = null,
      private ?InvoiceItemPaymentStatus $paymentStatus = null,
      private array $payments = [],
      
   )
   {
   }
   public  function addInvoiceItem(InvoiceItem $invoiceItem)
   {
      $this->invoiceItem[] = $invoiceItem;  
      
      
            
   }


   //
   public static function generateInvoice(
         InvoiceTotalAmount $totalAmount,
         RemainingDue $remainingDue,
         AgreedAmount $agreedAmount,
         InvoiceStatus $executionStatus,
   ):self
   {      
  
      return new self($totalAmount, $remainingDue, $agreedAmount, $executionStatus);
   }

   
   /**
    * Get the value of totalAmount
    */ 
   public function getTotalAmount():InvoiceTotalAmount
   {
      return $this->totalAmount;
   }

   /**
    * Get the value of invoicdStatus
    */
   public function getExecutionStatus(): InvoiceItemExecutionStatus
   {
      return $this->executionStatus ;
   }

   /**
    * Get the value of remainingDue
    */
   public function getRemainingDue(): RemainingDue
   {
      return $this->remainingDue;
   }

   /**
    * Get the value of agreedAmount
    */
   public function getAgreedAmount(): AgreedAmount
   {
         return $this->agreedAmount;
   }

   public function recalculateInvoiceItemExecutionStatus(): void  {
      if (empty($this->invoiceItem)) {
         return;
      }
      
      $statuses = array_map(
         fn(InvoiceItem $invoiceItem) => $invoiceItem->getExecutionStatus(),
         $this->invoiceItem
      );
      
    
    
      if(count(array_unique($statuses)) === 1 && $statuses[0]->value() === self::PLANNED){
        
         $this->executionStatus = InvoiceItemExecutionStatus::planned();
         return;

      }
      // ANY item.execution_status = in_progress
      // OR (mix of planned + early financial activity)

     
      foreach ($statuses as $status) {
         if($status->value() === self::IN_PROGRESS){
            $this->executionStatus = InvoiceItemExecutionStatus::inProgress();
            return;
         }
      }

      foreach ($statuses as $status) {
         if($status->value() !== self::CANCELLED){
            
            return;
         }

         $this->executionStatus = InvoiceItemExecutionStatus::cancelled();
      }
      
   }

   
   public function recalculateInvoiceItemPaymentStatus(): void  {

      if (empty($this->invoiceItem)) {
         return;
      }


      $invoice = $this->getInvoice();
   
      
      $invoiceItems = $invoice->getInvoiceItem();
      $payments     = $invoice->payments;

    

      foreach($payments as $payment){

         foreach($invoiceItems as $invoiceItem){

            if($payment->getInvoiceItem()->getId() === $invoiceItem->getId()){

                
               // $this->paymentStatus = InvoiceItemPaymentStatus::paid();
               $allocation = Allocation::allocatePaymentToItem(
                  $invoiceItem,
                  $payment->getAmountPaid()->getAmountPaid()
               );
               $payment->addAllocation($allocation);

            }
         }
      }

      $payments = $invoice->payments;

    
      usort(
            $payments,
            static function (Payment $firstPayment, Payment $secondPayment): int {

                $firstAllocation  = $firstPayment->getAllocations()[0] ?? null;
                $secondAllocation = $secondPayment->getAllocations()[0] ?? null;

                $firstInvoiceItemId = $firstAllocation?->getInvoiceItem()->getId() ?? 0;
                $secondInvoiceItemId = $secondAllocation?->getInvoiceItem()->getId() ?? 0;

                return $firstInvoiceItemId <=> $secondInvoiceItemId;

            
            }
      );

   
      $currentInvoiceItem = 0;

      $sumPaymentAmount = 0;

      foreach ($payments as $payment) {
        
        $allocations = $payment->getAllocations();

        foreach ($allocations as $allocation) {

         if($currentInvoiceItem===0 || $currentInvoiceItem===$allocation->getInvoiceItem()->getId()){
          
           // dump($payment);
           $currentInvoiceItem =  $allocation->getInvoiceItem()->getId();
           $sumPaymentAmount += $payment->getAmountPaid()->getAmountPaid();
           if($sumPaymentAmount>=$allocation->getInvoiceItem()->getAmount())
           {
               $payment->setPaymentStatus(PaymentStatus::refunded());
               
           }else{
               $payment->setPaymentStatus(PaymentStatus::partiallyRefunded());
               
           }
        
            
         }elseif($currentInvoiceItem < $allocation->getInvoiceItem()->getId())
         {
            $sumPaymentAmount = $payment->getAmountPaid()->getAmountPaid();

            $currentInvoiceItem =  $allocation->getInvoiceItem()->getId();
        
            if($sumPaymentAmount>=$allocation->getInvoiceItem()->getAmount())
            {
               $payment->setPaymentStatus(PaymentStatus::paid());
            }     
         }
        }
      }

      $allocationByInvoiceItems = [];

      foreach($payments as $payment){
        $allocations = $payment->getAllocations();
        foreach($allocations as $allocation){

          $invoiceId = $allocation->getInvoiceItem()->getId();

          $allocationByInvoiceItems[$invoiceId][] = $payment->getPaymentStatus()->getStatus();


        }
      }

      foreach ($allocationByInvoiceItems as $invoiceItemId => $allocationByInvoiceItem) {
         
         if (in_array(self::REFUNDED, $allocationByInvoiceItem)) {
            
           
            foreach($invoiceItems as $key => $invoiceItem)
            {


                if( (int) $invoiceItemId === (int) $invoiceItem->getId() )
                {
                  
                     $invoiceItem = InvoiceItem::invoice(
                        $invoiceItem->getId(),
                        $invoiceItem->getDescription(),
                        $invoiceItem->getAmount(),
                        $invoiceItem->getVisitId(),
                        $invoiceItem->getExecutionStatus(),
                        InvoiceItemPaymentStatus::paid()
                     );

                    
                  }
                  
                  
               }
         }
      }
   


      foreach($invoiceItems as $invoiceItem){
        foreach($payments as $payment){
          if($payment->getAllocations()[0]->getInvoiceItem()->getId()===$invoiceItem->getId()){
            if ($payment->getPaymentStatus()->getStatus()===self::REFUNDED){
             
              
                $invoiceItem = $invoiceItem::invoice(
                  $invoiceItem->getId(),
                  $invoiceItem->getDescription(),
                  $invoiceItem->getAmount(),
                  $invoiceItem->getVisitId(),
                  $invoiceItem->getExecutionStatus(),
                  InvoiceItemPaymentStatus::refunded()
               );

               dump( $invoiceItem );
             
           
            }

         
          }
        }
         
      }


     
      
    

          
      // foreach ($this->payments as $payment) {
      //    if($payment->getInvoiceItem()->getId() === $this->id){
      //       $this->paymentStatus = $payment->getInvoiceItem()->getPaymentStatus();
      //    }
      // }
      
      // // [paid, billed, partially_paid, refunded]
      // $statuses = array_map(
      //    fn(InvoiceItem $invoiceItem) => $invoiceItem->getPaymentStatus(),
      //    $this->invoiceItem
      // );

      // // if all are unbilled
      // if(count(array_unique($statuses)) === 1 && $statuses[0]->value() === InvoiceItemPaymentStatus::unbilled){
        
      //    $this->paymentStatus = InvoiceItemPaymentStatus::unbilled();
      //    return;

      // }


      // 1. Tous les items sont à 0€ → billed
      // 2. Tous les items sont payés (allocation = montant total) → paid
      // 3. Sinon (mix ou partiel) → partially_paid
      // 4. S'il y a eu un remboursement → refunded

      // // if all are billed
      // if(count(array_unique($statuses)) === 1 && $statuses[0]->value() === self::billed){
         
      //    $this->paymentStatus = InvoiceItemPaymentStatus::billed();
      //    return;
      // }

      // // if all are paid
      // if(count(array_unique($statuses)) === 1 && $statuses[0]->value() === self::paid){
         
      //    $this->paymentStatus = InvoiceItemPaymentStatus::paid();
      //    return;
      // }

      // // if all are partially paid
      // if(count(array_unique($statuses)) === 1 && $statuses[0]->value() === self::partially_paid){
         
      //    $this->paymentStatus = InvoiceItemPaymentStatus::partially_paid();
      //    return;
      // }

      // // if all are refunded
      // if(count(array_unique($statuses)) === 1 && $statuses[0]->value() === self::refunded){
         
      //    $this->paymentStatus = InvoiceItemPaymentStatus::refunded();
      //    return;
      // }

      // // if mixed statuses
      // $this->paymentStatus = InvoiceItemPaymentStatus::partially_paid();
      
   }

   /**
    * Get the value of paymentStatus
    */
   public function getPaymentStatus(): ?InvoiceItemPaymentStatus
   {
         return $this->paymentStatus;
   }

   public function addPayment(Payment $payment)
   {
      $this->payments[] = $payment; 
   }

   /**
    * Get the value of invoiceItem
    */
   public function getInvoiceItem(): array
   {
      return $this->invoiceItem;
   }

   public function getInvoice():Invoice
   {
      return $this;;
   }
}
