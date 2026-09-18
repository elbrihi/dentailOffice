<?php

namespace DentalOffice\InvoiceBundle\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\ValueObject\AgreedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemExecutionStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\NetPaidAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\PaidAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RemainingDue;
use DentalOffice\PaymentsBundle\Domain\Aggregate\Payment;
use DentalOffice\PaymentsBundle\Domain\ValueObject\PaymentStatus;

final class  Invoice
{

   private const PLANNED         = 'planned';
   private const IN_PROGRESS     = 'in_progress';
   private const COMPLETED       = 'completed';
   private const CANCELLED       = 'cancelled';
   private const REFUNDED        = 'refunded';
   private const BILLED          = 'billed';
   private const PAID            = 'paid';
   private const DONE            = 'done';

 //  private  array $invoiceItem = [];
   
   private function __construct(
      private array $invoiceItems,
      private InvoiceTotalAmount $totalAmount,
      private RemainingDue $remainingDue,
      private AgreedAmount $agreedAmount,
      private RefundedAmount $refundedAmount,
      private PaidAmount $paidAmount,
      private NetPaidAmount $netPaidAmount,
      private InvoiceStatus $invoiceStatus ,
      private ?InvoiceItemExecutionStatus $executionStatus = null,
      private ?InvoiceItemPaymentStatus $paymentStatus = null,
      private array $payments = [],
   ) {

      

   }
   public  function addInvoiceItem(InvoiceItem $invoiceItem)
   {
      $this->invoiceItem[] = $invoiceItem;     
   }

   
   public static function generateInvoice(
            array $invoiceItems,
            InvoiceTotalAmount $totalAmount,
            RemainingDue $remainingDue,
            AgreedAmount $agreedAmount,
            RefundedAmount $refundedAmount,
            NetPaidAmount $netPaidAmount,
            PaidAmount $paidAmount,
            InvoiceStatus $invoiceStatus ,
            ?InvoiceItemExecutionStatus $executionStatus = null,
            ?InvoiceItemPaymentStatus $paymentStatus = null,
            array $payments = [],
      ): self {
         return new self(
               $invoiceItems,
               $totalAmount,
               $remainingDue,
               $agreedAmount,
               $refundedAmount,
               $paidAmount,
               $netPaidAmount,
               $invoiceStatus,
               $executionStatus,
               $paymentStatus,
               $payments
      );
   }

   public static function handledInvoiceStatus()
   {
      
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

   
   public function recalculateInvoiceItemPaymentStatus(Payment $payment): Invoice  {

   

     $paymentSavedInDatabase = $this->getInvoice()->getPayments();

    
    // array_push($paymentSavedInDatabase, $payment);

    
     $agreedAmount = $this->getAgreedAmount()->agreedAmount();

    
  
      $totalAmount = 0;
      $remainingDue = 0;
      $sumSavedAmountItem=0;
      $paymentStatus = InvoiceItemPaymentStatus::unpaid();

      
   //   dd(sizeof($paymentSavedInDatabase));
      foreach ($paymentSavedInDatabase as $paymentSaved) {
         
         $totalAmount = $totalAmount + $paymentSaved->getAmountPaid()->getAmountPaid();
         $allocations = $paymentSaved->getAllocations();
        
         foreach ($allocations as $allocation) {           
         
            
            if($allocation->getInvoiceItem()->getId() === $payment->getInvoiceItem()->getId())
            {

              
              // dd($allocation->getAllocatedAmount()->getAllocatedAmount());
              
               $sumSavedAmountItem += $allocation->getAllocatedAmount()->getAllocatedAmount();

               if($sumSavedAmountItem > 0 && $sumSavedAmountItem < $payment->getInvoiceItem()->getAmount())
               {
               

                  $paymentStatus =  PaymentStatus::partiallyPaid(PaymentStatus::BILLED);
                  
                  $paymentSaved->setPaymentStatus($paymentStatus);
               
               }elseif($sumSavedAmountItem === $payment->getInvoiceItem()->getAmount()){


                  $paymentStatus =  PaymentStatus::paid(PaymentStatus::BILLED);
                  
                  $paymentSaved->setPaymentStatus($paymentStatus);

                  $this->addPayment($paymentSaved);

                  
               }
            }
         
         }

          
        
     }
   
     $remainingDue = $agreedAmount -  $totalAmount;
        
     $invoiceItems = $this->getInvoiceItem();

     $payments = $this->getPayments();
     $newData = [];

     foreach( $invoiceItems  as  $invoiceItem)
     {  
      

         $newData = [];
         foreach($payments as $pay)
         {
            
         
            if($invoiceItem->getId() === $pay->getInvoiceItem()->getId())
            {
               $newData[$invoiceItem->getId()]["status"][]=  $pay->getPaymentStatus();
               $newData[$invoiceItem->getId()]["amount"][]=  $pay->getAmountPaid();
               
            }
         }

         if (empty($newData)) {
            continue;
         }
 
         $statuses = array_map(fn($status) => $status->getPaymentStatus(), $newData[$invoiceItem->getId()]["status"]);

         $status = (string) $invoiceItem->getPaymentStatus();
         
         if(count(array_unique($statuses)) === 1 && 
         $newData[$invoiceItem->getId()]["status"][0]->getPaymentStatus() === InvoiceItemPaymentStatus::PARTIALLY_PAID)
         {

           
            $invoiceItem->setPaymentStatus(InvoiceItemPaymentStatus::partiallyPaid( $status));

         }elseif(in_array(InvoiceItemPaymentStatus::paid($status)->value(),$statuses) ){
         
            $invoiceItem->setPaymentStatus(InvoiceItemPaymentStatus::paid($status));
            continue;
         }

         
      }

    

      $invoiceItems = $this->getInvoiceItem(); 
      
      if($invoiceItems[sizeof($invoiceItems)-1]->getPaymentStatus()->value() === Invoice::BILLED)
      {
         $status = (string) $invoiceItems[sizeof($invoiceItems)-1]->getPaymentStatus();
         $this->paymentStatus = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);
      }

     

      $count = 0;
      for ($i=0; $i < count($invoiceItems) ; $i++) { 
         
         if ($invoiceItems[$i]->getPaymentStatus()->value() === Invoice::PAID) {
           //dump($invoiceItems[$i]->getPaymentStatus()->value() === Invoice::PAID);
            $count++;
         }
        
        
         if($remainingDue === 0 || $count===sizeof($invoiceItems))
         {
            $this->paymentStatus = InvoiceItemPaymentStatus::done();
         }
        
      }
     

     
     
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount($totalAmount);
  
      $remainingDue = RemainingDue::fromPositifRemainingDue($remainingDue);
     
      $agreedAmount = $this->getAgreedAmount();
      
      $invoiceStatus = $this->getInvoiceStatus();
      $executionStatus = $this->executionStatus;
      $paymentStatus = $this->paymentStatus;
      $payments = $this->payments;
      dd("hello");
      dd($invoiceStatus, $executionStatus, $paymentStatus, $payments);
     
      return new self(
         $this->getInvoiceItem(),
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $this->getRefundedAmount(),
         $invoiceStatus,
         $executionStatus ,
         $paymentStatus,
         $payments,
      );
     

  
  
   }

  
   public function recalculateInvoiceItemRefund(Invoice $invoice): Invoice 
   {

     
      $payments = $invoice->getPayments();

      $newData = [];
      foreach ($payments as $payment) {
         $allocatedAmount = 0;
         $refundedAmount = 0;
         $locations = $payment->getAllocations();  
         $amountPaid = $payment->getAmountPaid()->getAmountPaid();
         $netAmountOneLocation = 0;
         foreach($locations as $location)
         {

            $refunds = $location->getRefunds();
           
            $allocatedAmount = $location->getAllocatedAmount()->getAllocatedAmount();
            
            foreach($refunds as $refund)
            {
               
               $refundedAmount  += $refund->getRefundAmount()->getRefundedAmount();   
                
            }

            // $netAmountOneLocation = $allocatedAmount - $refundAmountOfOneLocation;
           
         } 
 
         $netAmounOfEachPayment = $allocatedAmount  -  $refundedAmount; 
         $invoiceItemAmount = $payment->getInvoiceItem()->getAmount();

         $newData[$payment->getInvoiceItem()->getId()]["netAmount"][] = $netAmounOfEachPayment;
         $newData[$payment->getInvoiceItem()->getId()]["refundedAmount"][] = $refundedAmount;
         $newData[$payment->getInvoiceItem()->getId()]["allocatedAmount"][] = $allocatedAmount;
         
         $sumNetAmountOfMultPartsPayments = array_sum($newData[$payment->getInvoiceItem()->getId()]["netAmount"]);
         $sumRefundedAmountOfMultPartsPayments = array_sum($newData[$payment->getInvoiceItem()->getId()]["refundedAmount"]);
         $sumAllocatedAmountOfMultPartsPayments = array_sum($newData[$payment->getInvoiceItem()->getId()]["allocatedAmount"]);
         
         $_totalNetAmount=$newData[$payment->getInvoiceItem()->getId()]["totalNetAmount"] = $sumNetAmountOfMultPartsPayments;
         $_totalRefundedAmount=$newData[$payment->getInvoiceItem()->getId()]["totalRefundedAmount"] = $sumRefundedAmountOfMultPartsPayments;
         //$newData[$payment->getInvoiceItem()->getId()]["totalAllocatedAmount"] = $sumAllocatedAmountOfMultPartsPayments;

         $newData[$payment->getInvoiceItem()->getId()]["totalAllocatedAmount"] = $_totalNetAmount-$_totalRefundedAmount;

         
         $payment->setRefundedAmount( RefundedAmount::fromPositiveRefundedAmount($sumRefundedAmountOfMultPartsPayments));
         $payment->setNetAmount(NetPaidAmount::fromPositiveNetPaidAmount($sumNetAmountOfMultPartsPayments));
  
         if($sumNetAmountOfMultPartsPayments === $invoiceItemAmount ){
  
            $payment->setPaymentStatus(PaymentStatus::completed((string) $payment->getPaymentStatus()));

         }else if(
            $sumNetAmountOfMultPartsPayments > 0 && 
            $sumNetAmountOfMultPartsPayments < $invoiceItemAmount && 
            $sumRefundedAmountOfMultPartsPayments === 0
         )
         {
            $payment->setPaymentStatus(PaymentStatus::completed((string) $payment->getPaymentStatus()));
         }else if(
            $sumRefundedAmountOfMultPartsPayments > 0 && 
            $sumRefundedAmountOfMultPartsPayments < $invoiceItemAmount
            
            
         )  
         { 

            $payment->setPaymentStatus(PaymentStatus::partiallyRefunded((string) $payment->getPaymentStatus()));

         }else if(
            $invoiceItemAmount === $sumRefundedAmountOfMultPartsPayments
            
         )
         {  

            
            $payment->setPaymentStatus(PaymentStatus::refunded((string) $payment->getPaymentStatus()));
         }
    
      } 
      

    //  dd( $newData);
      $invoiceItems = $this->getInvoiceItem();

      (float) $totalRefundedAmount =0.0;
      (float) $totalAmount = 0.0;
      (float) $refundedAmount = 0.0;
      (float) $remainingDue = 0.0;
      (float) $paidAmount = 0.0;
      (float) $netPaidAmount = 0.0;
      (array) $multipleExecutionStatus=[];
      
     
      $this->refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      foreach ($invoiceItems as $invoiceItem ) {
     
         $multipleExecutionStatus[] = $invoiceItem;
     
         if(isset($newData[$invoiceItem->getId()])){
              $totalRefundedAmount += $newData[$invoiceItem->getId()]["totalRefundedAmount"];

               $totalAmount += $newData[$invoiceItem->getId()]["totalAllocatedAmount"];
         } 
       
            
         
       
     
      
         $this->refundedAmount =  RefundedAmount::fromPositiveRefundedAmount($totalRefundedAmount);
         $this->paidAmount = PaidAmount::fromPositivePaidAmount($totalAmount);
         $this->totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount($totalAmount);

         
          
      }

      $refundedAmount = $this->refundedAmount ->refundedAmount();

     
      $paidAmount =  $this->paidAmount->paidAmount();

      $netPaidAmount = $paidAmount - $refundedAmount ;


      $remainingDue = $this->agreedAmount->agreedAmount() - $netPaidAmount ;

      $this->remainingDue = RemainingDue::fromPositifRemainingDue($remainingDue);

      $this->netPaidAmount= NetPaidAmount::fromPositiveNetPaidAmount($netPaidAmount);

      $this->netPaidAmount->fromPositiveNetPaidAmount($netPaidAmount);

   
      //dd($this->getInvoiceStatus()->getExecutionStatus(),$this->getInvoiceStatus()->getPaymentStatus());
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
          $this->remainingDue->getRemainingDue(),
          $this->totalAmount->getTotalAmount(),
          $this->agreedAmount->agreedAmount(),
          $refundedAmount,
          $paidAmount,
          $this->netPaidAmount->getNetPaidAmount(),
          $multipleExecutionStatus,
          $this->getInvoiceStatus()->getExecutionStatus(),
          $this->getInvoiceStatus()->getPaymentStatus(),
      );

      $this->setInvoiceStatus($invoiceStatus);
      
      // $this->totalAmount =  $this->totalAmount->getTotalAmount();
      return $this;
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

   public function getPayments(): array
   {
      return $this->payments;
   }

   /**
    * Get the value of invoiceStatus
    */
   public function getInvoiceStatus(): InvoiceStatus
   {
         return $this->invoiceStatus;
   }

 

   /**
    * Get the value of refundAmount
    */
   public function getRefundedAmount(): RefundedAmount
   {
      return $this->refundedAmount;
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
    * Get the value of invoiceItems
    */
   public function getInvoiceItems(): array
   {
         return $this->invoiceItems;
   }

   public function setInvoiceStatus(InvoiceStatus $invoiceStatus): self
   {
         $this->invoiceStatus = $invoiceStatus;
         return $this;
   }

   /**
    * Get the value of paidAmount
    */
   public function getPaidAmount(): PaidAmount
   {
      return $this->paidAmount;
   }

   /**
    * Get the value of netPaidAmount
    */
   public function getNetPaidAmount(): NetPaidAmount
   {
      return $this->netPaidAmount;
   }



}  
