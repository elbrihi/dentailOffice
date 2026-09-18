<?php

namespace DentalOffice\InvoiceBundle\Tests\Domain\Aggregate;

use PHPUnit\Framework\TestCase;
   use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemExecutionStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemStatus;

final class InvoiceItemTest extends TestCase
{
    public const UNBILLED            = 'unbilled';
    public const BILLED              = 'billed';

    public const PENDING             = 'pending';
    public const COMPLETED           = 'completed';

    public const PARTIALLY_PAID      = 'partially_paid';
    public const PAID                = 'paid';

    public const PARTIALLY_REFUNDED  = 'partially_refunded';
    public const REFUNDED            = 'refunded';

    public const OVERPAID            = 'overpaid';

    public const FAILED              = 'failed';
    public const CANCELLED           = 'cancelled';

   protected function setUp(): void
   {
      parent::setUp();
   }
   
   public function test_it_create_invoice_item_planned_status()
   {
    

      $executionStatus = InvoiceItemExecutionStatus::planned();
      $paymentStatus = InvoiceItemPaymentStatus::unbilled();

      $invoiceItemStatus = InvoiceItemStatus::planned();

      $invoiceItem = $this->generateInvoiceItem(
         $invoiceItemStatus,
         $executionStatus,
         $paymentStatus
      );
      InvoiceItem::handleInvoiceItemStatus($invoiceItem);

      $this->assertEquals(
         'planned',
         (string) $invoiceItem
      );

      
   }

   public function test_it_create_invoice_item_in_progress_status()
   {
    

    
      $executionStatus = InvoiceItemExecutionStatus::inProgress(InvoiceItemExecutionStatus::PLANNED);
      $paymentStatus = InvoiceItemPaymentStatus::unbilled();

      $invoiceItemStatus = InvoiceItemStatus::planned();
      $invoiceItem = $this->generateInvoiceItem($invoiceItemStatus,$executionStatus,$paymentStatus);
      InvoiceItem::handleInvoiceItemStatus($invoiceItem);
      $this->assertEquals((string) $invoiceItem, "in_progress");

      
   }

   public function test_it_create_invoice_item_completed_status()
   {
    
    
      $executionStatus=InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);
     
      $paymentStatus=InvoiceItemPaymentStatus::unbilled();
     
      $invoiceItemStatus = InvoiceItemStatus::inProgress(InvoiceItemStatus::PLANNED);
      
      
      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;
     
     InvoiceItem::handleInvoiceItemStatus($invoiceItem);
      $this->assertEquals((string) $invoiceItem, "completed");

      
   }

   public function test_it_create_invoice_item_billed_status()
   {
    
    
      $executionStatus=InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);
     
      $paymentStatus=InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);
     
      $invoiceItemStatus = InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS);
      
      
      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;
     
     InvoiceItem::handleInvoiceItemStatus($invoiceItem);
      $this->assertEquals((string) $invoiceItem, "billed");

      
   }

   public function test_it_create_invoice_item_completed()
   {
         
     
     
     
      $invoiceItemStatus = InvoiceItemStatus::inProgress(InvoiceItemStatus::PLANNED);
      
      $executionStatus=InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);

       $paymentStatus=InvoiceItemPaymentStatus::unbilled();


      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;
     InvoiceItem::handleInvoiceItemStatus($invoiceItem);
     
      $this->assertEquals((string) $invoiceItem, "completed");
   }

   public function test_it_create_invoice_item_partially_paid()
   {
         
     
     
     
     
      $executionStatus=InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);

      $paymentStatus=InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);
     
     // $invoiceItemStatus = InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED);

      $invoiceItemStatus = InvoiceItemStatus::partiallyPaid(InvoiceItemStatus::BILLED);
      
      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;
     
                           
     InvoiceItem::handleInvoiceItemStatus($invoiceItem);
      $this->assertEquals((string) $invoiceItem, "partially_paid");
   }

   public function test_it_create_from_billed_to_invoice_item_done()
   {
         
     
     
     
      $executionStatus=InvoiceItemExecutionStatus::completed(
                               InvoiceItemExecutionStatus::IN_PROGRESS
                              );
     
      $paymentStatus=InvoiceItemPaymentStatus::paid(
                             InvoiceItemPaymentStatus::BILLED
                           );
     
      $invoiceItemStatus = InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED);
     
      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;

      
      InvoiceItem::handleInvoiceItemStatus($invoiceItem);
      $this->assertEquals((string) $invoiceItem, "done");
   }

   public function test_it_create_from_billed_to_invoice_item_overpaid()
   {
         
     
     
     
      $executionStatus=InvoiceItemExecutionStatus::completed(
                               InvoiceItemExecutionStatus::IN_PROGRESS
                              );
                       
      $paymentStatus=InvoiceItemPaymentStatus::overpaid(
                             InvoiceItemPaymentStatus::PAID
                           );
        
      $invoiceItemStatus = InvoiceItemStatus::done(InvoiceItemStatus::PAID);

      
      
      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;

      InvoiceItem::handleInvoiceItemStatus($invoiceItem);
      
      $this->assertEquals((string) $invoiceItem, "overpaid");
   }

   public function test_it_create_from_billed_to_from_partially_to_invoice_item_partially_refound()
   {
         
     
     
     
      $executionStatus=InvoiceItemExecutionStatus::completed(
                               InvoiceItemExecutionStatus::IN_PROGRESS
                              );
                        
      $paymentStatus=InvoiceItemPaymentStatus::partiallyRefounded(
                             InvoiceItemPaymentStatus::PAID
                           );
        
      $invoiceItemStatus = InvoiceItemStatus::partiallyPaid(InvoiceItemStatus::BILLED);

      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;

     
      
      $this->assertEquals((string) $invoiceItem, "partially_refounded");
   }

   ////////////////

   public function test_it_create_from_partially_refound_to_invoice_item_refound()
   {
         
     
     
      
      $executionStatus=InvoiceItemExecutionStatus::completed(
                               InvoiceItemExecutionStatus::IN_PROGRESS
                              );
                        
      $paymentStatus=InvoiceItemPaymentStatus::refunded(
                             InvoiceItemPaymentStatus::PAID
                           );
    
      $invoiceItemStatus = InvoiceItemStatus::partiallyPaid(InvoiceItemStatus::BILLED);

      $invoiceItem = $this->generateInvoiceItem
                           (
                              $invoiceItemStatus,
                              $executionStatus,
                              $paymentStatus)
                           ;

     
      
      $this->assertEquals((string) $invoiceItem, "refunded");
   }


   public function generateInvoiceItem
   (
      InvoiceItemStatus $invoiceItemStatus,
      InvoiceItemExecutionStatus   $executionStatus,
      InvoiceItemPaymentStatus $paymentStatus ,
    
   ): InvoiceItem
   {
      $id=1;
      $desciption = "Consultation initiale";
      $amount = 300.0;
      $visitId= 0;

      $invoiceItem = InvoiceItem::invoice(
         $id,
         $desciption,
         $amount,
         $visitId,
         $invoiceItemStatus,
         $executionStatus,
         $paymentStatus,
      );

      return  $invoiceItem ;
   }

}
