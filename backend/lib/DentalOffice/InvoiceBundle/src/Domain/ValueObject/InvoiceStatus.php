<?php

namespace DentalOffice\InvoiceBundle\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Exception\InvalidDoneException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidBilledException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidPartiallyPaidException;
use DentalOffice\PaymentsBundle\Domain\Exception\InvalidRefundedException;

class InvoiceStatus
{

  
   // 💰 --- FINANCIAL STATES ---
   private const PAID="paid";
   private const UNPAID="unpaid";
   private const PARTIALLY_PAID = "partially_paid";

   
   // 🟡 Invoice created
   // no treatment started
   // no payment yet
   private const PLANNED = 'planned';

   public const UNBILLED = 'unbilled';

   // 🔵 Treatment started
   // at least one item in progress
    private const IN_PROGRESS = 'in_progress';

    // 🟠 All medical work completed
    // but no payment received yet
    private const COMPLETED = 'completed';

    // 🟣 Financial phase active
    // partial payment received
    // remaining amount still exists
   private const BILLED = 'billed';

   // 🟢 Everything completed and fully paid
   private const DONE = 'done';

   // 🔁 Payments refunded
   // fully or partially reversed
   private const REFUNDED = 'refunded';

   private const PARTIALLY_REFUNDED = 'partially_refunded';

   // ❌ Entire invoice cancelled
   private const CANCELLED = 'cancelled';

   private string $invoiceStatus;
   
   private function __construct(
      
      private float $remainingDue = 0,
      private float $invoiceTotalAmount = 0,
      private float $agreedAmount = 0,
      private float $refundedAmount,
      private float $paidAmount,
      private float $netPaidAmount,
      private array $multipleExecutionStatus = [],
      private ?InvoiceItemExecutionStatus $executionStatus = null,
      private ?InvoiceItemPaymentStatus $paymentStatus = null,
      private string $value = ''

      
      )
     { 

     
  //    
      if (count($this->multipleExecutionStatus) > 0) {
 
       
         if(
            $this->netPaidAmount === 0.00 
            && $this->refundedAmount === 0.00 
            && $this->containsOnlyBilledorUnbilled($this->multipleExecutionStatus)
         ){
            

            $this->invoiceStatus = self::BILLED;
         } 
         elseif(
            
            $this->allBilled($this->multipleExecutionStatus)
            &&$this->netPaidAmount === 0.00 
            &&$this->refundedAmount === 0.00 
            
            ){

            
            $status = (string) $this->executionStatus;    
            if($status !== self::COMPLETED)
            {
               throw InvalidBilledException::invalidTransition($status,self::COMPLETED);
            }
            
            $this->invoiceStatus = self::BILLED;

         }
         
         elseif(
            $this->paidAmount>0 
            &&$this->netPaidAmount<$this->agreedAmount
            &&$this->remainingDue>0
            &&$this->refundedAmount===0.0
         )
          
         {
            
         
            $status = (string) $this->executionStatus;
           
            if($status  !== self::COMPLETED)
            {
               throw InvalidPartiallyPaidException::invalidTransition($status ,self::COMPLETED);
               
            }
            $this->invoiceStatus = self::PARTIALLY_PAID;
         }

         elseif(
            $this->netPaidAmount===$this->agreedAmount 
            &&$this->remainingDue===0.0
            &&$this->isPaid()
            && $this->allCompleted()

         
            )
         {

    
          
            $status = (string) $this->getPaymentStatus();
            if (!in_array($status,[self::BILLED,self::PARTIALLY_PAID])) {
              throw InvalidDoneException::invalidTransition($status,self::PAID);
            }
            
            $this->invoiceStatus = self::DONE;

         }elseif($this->partiallyPaidBuildPaidUnbilled($this->multipleExecutionStatus)){
            $this->invoiceStatus = self::PARTIALLY_PAID;
         }
         elseif(
            $this->refundedAmount >0 
            &&$this->netPaidAmount >0
            &&$this->netPaidAmount < $this->agreedAmount
            &&($this->isPartiallyRefunded() || $this->isAnyRefounded()) 
            &&!$this->partiallyPaid()
         
         )
         {
            
            $status = (string) $this->getPaymentStatus();

            

            if ( !in_array($status,[self::DONE,self::PAID,self::PARTIALLY_PAID])  ) {
              InvalidRefundedException::invalidTransition($status,[self::DONE,self::PAID,self::PARTIALLY_PAID]);
            }
           
            $this->invoiceStatus = self::PARTIALLY_REFUNDED;
            
            $this->executionStatus=InvoiceItemExecutionStatus::inProgress(self::PLANNED);
            //$this->

         }elseif(
            $this->isRefunded($this->multipleExecutionStatus)
            &&$this->refundedAmount > 0
            &&$this->netPaidAmount === 0
         )
         {

            $status = (string) $this->getPaymentStatus();

            
            if ( !in_array($status,[self::DONE,self::PAID,self::PARTIALLY_PAID])  ) {
              InvalidRefundedException::invalidTransition($status,[self::DONE,self::PAID,self::PARTIALLY_PAID]);
            }
            
            $this->invoiceStatus = self::REFUNDED;
         }
         elseif (
            $this->refundedAmount > 0.0
            &&$this->netPaidAmount === 0.0
         ) {
           
            $this->invoiceStatus = self::REFUNDED;
         }
         elseif($this->isAllPlannedToCreateInvoicePlanned())
         {
              
           
            $this->invoiceStatus = self::PLANNED;;

            //dd($this);
           

         }
         elseif($this->isPlanned()){
            //$this->isPlanned($this->multipleExecutionStatus) && $this->remainingDue > 0
            $this->invoiceStatus = self::PLANNED;
         }elseif($this->isInprogress($this->multipleExecutionStatus))
         
            
            $this->invoiceStatus = self::IN_PROGRESS;

         }

      
   }

   
   private function containsOnlyBilledorUnbilled(array $invoiceItems):bool
   {

      $billedExisty = false;
      $unbilledExist = false;
      $otherStatus = false;

      foreach ( $invoiceItems as $key =>  $invoiceItem) {
        $status =(string) $invoiceItem->getPaymentStatus();
        if ($status == self::BILLED){
           $billedExisty = true;
        }
        else if($status == self::UNBILLED){
           
           $unbilledExist = true;
        }else if(!in_array($status, [self::BILLED, self::UNBILLED])){

        }
         
      }

      if($otherStatus){
         return false;
      }
      if($billedExisty && $unbilledExist){
         return true;
      }
         
      return false;
   }
 
   private function allBilled(array $multipleExecutionStatus):bool
   {
         
       
         if(in_array(self::IN_PROGRESS,$multipleExecutionStatus))
         {
            return false;
         }
         foreach($multipleExecutionStatus as $multipleExecutionStatusItem){
               $status =(string) $multipleExecutionStatusItem->getInvoiceItemStatus();
               if($status !== self::BILLED){
                  return false;
               }
         }
         return true;
   }

   private function allUnbilled(array $invoiceItems):bool
   {
      foreach($invoiceItems as $item){
         if((string)$item->getPaymentStatus() !== self::UNBILLED){
            return false;
         }
      }
      return true;
   }

   private function partiallyPaid():bool
   {

      $multipleExecutionStatus = $this->multipleExecutionStatus;
         
      if(in_array(self::IN_PROGRESS,$multipleExecutionStatus))
      {
         return false;
      }


      foreach ($multipleExecutionStatus as $multipleExecutionIntemStatus) {
      
         $status =(string)$multipleExecutionIntemStatus->getInvoiceItemStatus();

         if($status === self::PARTIALLY_PAID)
         {
            return true;
         }

      }
      

      return false;

   }

   private function partiallyPaidBuildPaidUnbilled(array $multipleExecutionStatus):bool
   {

      $billed=false;
      $unbilled=false;
      $paid=false;
  

      if(in_array(self::IN_PROGRESS,$multipleExecutionStatus))
      {
         return false;
      }
      foreach($multipleExecutionStatus as $multipleExecutionIntemStatus){
         $status =(string)$multipleExecutionIntemStatus->getPaymentStatus();
         if($status === self::PAID){
            $paid=true;
         }
         else if($status === self::BILLED){
            $billed=true;
         }
         else if($status === self::UNBILLED){
            $unbilled=true;
         }
      }

      if($paid && ($unbilled  || $billed)){
         return true;
      }

      return false;

   }
   
   private function isPaid():bool
   {
      $multipleExecutionStatus= $this->multipleExecutionStatus ;
      if(in_array(self::IN_PROGRESS,$multipleExecutionStatus))
      {
         return false;
      }
      foreach( $multipleExecutionStatus as $multipleExecutionIntemStatus)
      {
         $status =(string)$multipleExecutionIntemStatus->getPaymentStatus();
         
         if ( $status !== self::PAID) {
          return false;
         }

      }

      return true ;
   }

   private function isPartiallyRefunded():bool
   {

    
      $multipleExecutionStatus = $this->multipleExecutionStatus ;

 
      foreach($multipleExecutionStatus as $multipleExecutionIntemStatus)
      {
         $status =(string)$multipleExecutionIntemStatus->getPaymentStatus();
         if ( $status === self::PARTIALLY_REFUNDED) {
          return true;
         }

      }

      return false ;
   }

   private function isAnyRefounded():bool
   {
      $otherStatus = false;
      $isRefund = false;
      $invoiceItems = $this->multipleExecutionStatus;
      foreach($invoiceItems as $invoiceItem)
      {
         $status =(string) $invoiceItem->getPaymentStatus();
        
         if($status===self::REFUNDED)
         {
            $isRefund=true;
         }elseif ($status!==self::REFUNDED) {
            $isRefund=true;
         }
      }

      if ($isRefund && $isRefund) {
         return true;
      }

      return false;
   }


   private function isRefunded(array $multipleExecutionStatus)
   {

      $refund = [];
      foreach($multipleExecutionStatus as $multipleExecutionIntemStatus)
      {
         $status =(string)$multipleExecutionIntemStatus->getPaymentStatus();
         if($status === self::REFUNDED)
         {
            $refund[]=true;
         }
         

      }
      if(sizeof($refund)!==sizeof($multipleExecutionStatus))
      {
         return false;
      }

      return true;

   }
  
   private function isPlanned():bool
   {

      
      $multipleExecutionStatus = $this->multipleExecutionStatus;
      foreach($multipleExecutionStatus as $multipleExecutionIntemStatus)
      {
         $invoicePaymentStatus =(string)$multipleExecutionIntemStatus->getPaymentStatus();
         $invoiceItemStatus = (string)$multipleExecutionIntemStatus->getExecutionStatus();

         if($invoiceItemStatus !== self::PARTIALLY_PAID || $invoiceItemStatus !== self::PAID )
         {
            return false;
         }
         if($invoiceItemStatus !== self::PLANNED && $invoicePaymentStatus !== self::UNBILLED)
         {
            return false;
         }
      }

      return true;
   }

   public function isAllPlannedToCreateInvoicePlanned()
   
   {
      $multipleExecutionStatus = $this->multipleExecutionStatus;
      foreach($multipleExecutionStatus as $multipleExecutionIntemStatus)
      {  
         $invoicePaymentStatus =(string)$multipleExecutionIntemStatus->getPaymentStatus();
         $invoiceItemStatus = (string)$multipleExecutionIntemStatus->getExecutionStatus();
         
         
         if(self::PLANNED!==$invoiceItemStatus)
         {
            return false;
         }

      }

      return true;
      
   }
   

   public function isInprogress(array $multipleExecutionStatus):bool
   {
      
      //dd($multipleExecutionStatus);
      foreach ($multipleExecutionStatus as $multipleExecutionIntemStatus) {
         
      
         $status =(string)$multipleExecutionIntemStatus->getInvoiceItemStatus();

         
         if($status===self::IN_PROGRESS)
         {
            return true;
         }
         
      }
      return false;
   }


   private function allCompleted():bool
   {
      foreach($this->multipleExecutionStatus as $multipleExecutionStatus)
      {
        if(self::COMPLETED!==(string) $multipleExecutionStatus->getExecutionStatus())
        {
               return false;
        }
      }

      return true;
   }
   
   public static function handleInvoiceStatus(
      float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      float $refundedAmount,
      float $paidAmount,
      float $netPaidAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus ,
      InvoiceItemPaymentStatus $paymentStatus,

   ):self
   {
    
     
      return new self(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount, 
         $refundedAmount,
         $paidAmount,
         $netPaidAmount, 
         $multipleExecutionStatus,   
         $executionStatus,
         $paymentStatus
      );   
   }

   public static function planned(
      float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      float $refundedAmount,
      float $paidAmount,
      float $netPaidAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus ,
      InvoiceItemPaymentStatus $paymentStatus,
      ) : self
   {
      if($agreedAmount > $invoiceTotalAmount){
         throw new \Exception('Agreed amount cannot be greater than invoice total amount');
      }
      return new self(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount, 
         $refundedAmount,
         $paidAmount,
         $netPaidAmount, 
         $multipleExecutionStatus,   
         $executionStatus,
         $paymentStatus
      );
   }

   public static function unbilled(
      float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      float $refundedAmount,
      float $paidAmount,
      float $netPaidAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus ,
      InvoiceItemPaymentStatus $paymentStatus,
      string $status = self::UNBILLED
   ):self
   {
      return new self(
          $remainingDue,
          $invoiceTotalAmount,
          $agreedAmount, 
          $refundedAmount,
          $paidAmount,
          $netPaidAmount, 
          $multipleExecutionStatus,   
          $executionStatus,
          $paymentStatus
      ); 
   }

   public static function billed(
      float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus,
      InvoiceItemPaymentStatus $paymentStatus,
      string $status = self::COMPLETED
   )
   {

     

      if ($status !== self::COMPLETED) {

         throw new InvalidBilledException($status,self::COMPLETED);
      }
      return new self($remainingDue,$invoiceTotalAmount,$agreedAmount,$multipleExecutionStatus, $executionStatus,$paymentStatus); 
   }


   public function inProgress(
      float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus,
      InvoiceItemPaymentStatus $paymentStatus,
      string $status = self::IN_PROGRESS
   )
   {
      if ($status !== self::IN_PROGRESS) {

        // throw new InvalidBilledException($status,self::COMPLETED);
      }

      return new self($remainingDue,$invoiceTotalAmount,$agreedAmount,$multipleExecutionStatus, $executionStatus,$paymentStatus); 
   }


   public function done(
       float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus,
      InvoiceItemPaymentStatus $paymentStatus,
      string $status = self::PLANNED
   )
   {
      return new self($remainingDue,$invoiceTotalAmount,$agreedAmount,$multipleExecutionStatus, $executionStatus,$paymentStatus); 
   }

   public function refunded(
       float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus,
      InvoiceItemPaymentStatus $paymentStatus,
      string $status = self::PLANNED
   )
   {
      return new self($remainingDue,$invoiceTotalAmount,$agreedAmount,$multipleExecutionStatus, $executionStatus,$paymentStatus); 
   }

   public function cancelled(
      float $remainingDue,
      float $invoiceTotalAmount,
      float $agreedAmount,
      array $multipleExecutionStatus,
      InvoiceItemExecutionStatus $executionStatus,
      InvoiceItemPaymentStatus $paymentStatus,
      string $status = self::PLANNED
   )
   {
      return new self($remainingDue,$invoiceTotalAmount,$agreedAmount,$multipleExecutionStatus, $executionStatus,$paymentStatus); 
   }

   
   /**
    * Get the value of invoiceStatus
    */
   public function getInvoiceStatus(): string
   {
         return $this->invoiceStatus;
   }


   public function isUnpaid(): bool
   {
      return $this->invoiceStatus === self::UNPAID;
   }

  

  

   public function __toString()
   {
      return $this->invoiceStatus;
   }



   /**
    * Get the value of executionStatus
    */
   public function getExecutionStatus(): ?InvoiceItemExecutionStatus
   {
         return $this->executionStatus;
   }

   /**
    * Get the value of paymentStatus
    */
   public function getPaymentStatus(): ?InvoiceItemPaymentStatus
   {
         return $this->paymentStatus;
   }
}  