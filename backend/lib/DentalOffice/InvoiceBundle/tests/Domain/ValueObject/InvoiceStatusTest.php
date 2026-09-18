<?php


namespace DentalOffice\InvoiceBundle\Tests\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\Aggregate\Invoice;
use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\ValueObject\AgreedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemExecutionStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\NetPaidAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\PaidAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RemainingDue;
use PHPUnit\Framework\TestCase;

class InvoiceStatusTest extends TestCase
{
  
   // done done
   public function test_it_can_create_planned_execution_status_planned()
   {
      
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

      
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      ); 

      $invoiceItemId = 1; // planned
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         null,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::unbilled()
      );

      
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;  // planned
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         null,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::unbilled()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;    // planned
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         null,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::unbilled()
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      $invoice->setInvoiceStatus($invoiceStatus);

      
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('planned', $invoiceStatus->getInvoiceStatus());


   }

   // done done
   public function test_it_can_create_in_progress()
   {
      
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

      
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      ); 

      $invoiceItemId = 1;

      
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemStatus::inProgress(InvoiceItemStatus::PLANNED), // completed
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),   
         InvoiceItemPaymentStatus::unbilled()
      );

      $invoice->addInvoiceItem($invoiceItem1);

      
      

       $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         null, //
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::unbilled()
      );

    
      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemStatus::planned(),// in_progress
         InvoiceItemExecutionStatus::inProgress(InvoiceItemExecutionStatus::PLANNED), 
         InvoiceItemPaymentStatus::unbilled()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         null,// planned 
         InvoiceItemExecutionStatus::planned(),  
         InvoiceItemPaymentStatus::unbilled()
      );


      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      $invoice->setInvoiceStatus($invoiceStatus);
   
      
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('in_progress', $invoiceStatus->getInvoiceStatus());

   }

   // done done
   public function test_it_can_create_all_billed_to_billed_status()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

      
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      ); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS), // billed
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      
      $invoice->addInvoiceItem($invoiceItem1);

     
      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS),
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED) // billed 

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS),
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED) // billed
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      $invoice->setInvoiceStatus($invoiceStatus);

      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('billed', $invoiceStatus->getInvoiceStatus());
    
   }
   

      // done done
   public function test_it_create_partially_paid_status()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(600.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount( 1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(400);
      $paidAmount = PaidAmount::fromPositivePaidAmount(400);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

      
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      );
    
     
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0, // billed
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED),
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0, // billed
         InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS),
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0, // billed
         InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS),
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
    


      $invoice->setInvoiceStatus($invoiceStatus);

 
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);

      $this->assertEquals('partially_paid', (string) $invoiceStatus->getInvoiceStatus());
   }

         // done done
   public function test_it_create_done_status()
   {

     
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(0.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(1000);
      $paidAmount = PaidAmount::fromPositivePaidAmount(1000.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

     
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      );
    
     
      $invoiceItemId = 1;
     
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0, // completed
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED),  // billed  
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );


     
      
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,  // completed
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED), // done
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,   // completed
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED), // done
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );


   
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
    

      $invoice->setInvoiceStatus($invoiceStatus);

 
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);

   
      $this->assertEquals('done', (string) $invoiceStatus->getInvoiceStatus());
   }

  
   // done done
   public function test_it_create_partially_refund_status_from_partially_paid()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(950.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(50.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(100.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(50.0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);

     
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      );
    
     
     
      $invoiceItemId = 1;
      
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,// parially_paid
         InvoiceItemStatus::partiallyPaid(InvoiceItemStatus::BILLED), 
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyRefounded(InvoiceItemPaymentStatus::PARTIALLY_PAID)
      );

    
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS), // billed 
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);


      
      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         null,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
    

      $invoice->setInvoiceStatus($invoiceStatus);


      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);

   
      $this->assertEquals('partially_refunded', (string) $invoiceStatus->getInvoiceStatus());
   }

   // done done to review 
   public function test_it_create_partially_refunded_one_item_partially_refunded()
   {
      
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(950.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(50.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(100.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(50.0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);

     
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      );
    
     
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemStatus::paid(InvoiceItemStatus::BILLED), 
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyRefounded(InvoiceItemPaymentStatus::PARTIALLY_PAID)
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         null,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         null,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
    

      $invoice->setInvoiceStatus($invoiceStatus);


      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);

   
      $this->assertEquals('partially_refunded', (string) $invoiceStatus->getInvoiceStatus());
   }

   // done done
   public function test_it_create_partially_refunded_one_item_refunded_others_paid()
   {
      
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(700.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(100.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(300.0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);

     
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      );
    
     
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemStatus::partiallyPaid(InvoiceItemStatus::BILLED), 
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PAID)
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0, // paid
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED), 
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED), 
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
    

      $invoice->setInvoiceStatus($invoiceStatus);


      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);

      $this->assertEquals('partially_refunded', (string) $invoiceStatus->getInvoiceStatus());
   }

   // done done  8. Refunded + partially paid
   public function test_it_create_partially_refunded_refunded_partially_paid()
   {
      
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue( 500.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(500.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(800.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(300.0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);

     
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      );
    
    
      $invoiceItemId = 1;
           
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemStatus::paid(InvoiceItemStatus::BILLED), 
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PAID)
      );
      
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,  
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED), // paid
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0, // paid
         InvoiceItemStatus::billed(InvoiceItemStatus::COMPLETED),  //paid
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
    

      $invoice->setInvoiceStatus($invoiceStatus);


      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);

      $this->assertEquals('partially_refunded', (string) $invoiceStatus->getInvoiceStatus());
   }

   // done done
   public function test_it_create_partially_refunded_refunded_all_refounded()
   {
      
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(300.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(300.0);
      $invoiceItemExecutionStatus = InvoiceItemExecutionStatus::planned();
      $invoiceItemPaymentStatus = InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);

     
          
      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $netPaidAmount,
         $paidAmount,
         $refundedAmount,
         [],
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus,
         
      );
    
     
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemStatus::partiallyPaid(InvoiceItemStatus::BILLED),   // refound
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PAID)
      );
      $invoice->addInvoiceItem($invoiceItem1);
      $invoiceItemId = $invoiceItemId + 1;
      
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemStatus::completed(InvoiceItemStatus::IN_PROGRESS), // billed
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      
   
      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemStatus::completed(InvoiceItemStatus::I),
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
      
      $invoice->addInvoiceItem($invoiceItem3);
      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
    

      $invoice->setInvoiceStatus($invoiceStatus);


      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);

      $this->assertEquals('refunded', (string) $invoiceStatus->getInvoiceStatus());
   }
   // 
   // done 
  
  
  
   public function test_it_can_create_billed_unbilled_to_unbilled_status()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(900); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(900);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      

    

      $invoice = $this->generateInvoice(
                             $totalAmount,
                             $remainingDue,
                             $agreedAmount,
                             $netPaidAmount, 
                             $paidAmount,
                             $refundedAmount,
                             []
            ); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::unbilled(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      $invoice->setInvoiceStatus($invoiceStatus);

     
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('billed', $invoiceStatus->getInvoiceStatus());
    
   }

   // done 
   public function test_it_create_all_billed_to_billed_status()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(900); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(900);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      

    

      $invoice = $this->generateInvoice(
                         $totalAmount,
                         $remainingDue,
                         $agreedAmount,
                         $netPaidAmount, 
                         $paidAmount,
                         $refundedAmount,
                         []
            ); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      $invoice->setInvoiceStatus($invoiceStatus);

     
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('billed', $invoiceStatus->getInvoiceStatus());
    
   }




   // done
   public function test_it_create_paid_status()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(900);
      $remainingDue = RemainingDue::fromPositifRemainingDue(0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(900);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(900);
      $paidAmount = PaidAmount::fromPositivePaidAmount(900);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      
    

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$netPaidAmount, $paidAmount,$refundedAmount,[]); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      $invoice->setInvoiceStatus($invoiceStatus);

     
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('paid', $invoiceStatus->getInvoiceStatus());
   }

   // done
   public function test_it_create_partially_paid_mixed_billed_unbilled_status()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(900);
      $remainingDue = RemainingDue::fromPositifRemainingDue(600); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(900);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(300);
      $paidAmount = PaidAmount::fromPositivePaidAmount(300);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      
    

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$netPaidAmount, $paidAmount,$refundedAmount,[]); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale", 
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::unbilled(InvoiceItemPaymentStatus::UNBILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();


     
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED)
      );

      $invoice->setInvoiceStatus($invoiceStatus);

     
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('partially_paid', $invoiceStatus->getInvoiceStatus());
   }
    
   

   // done test_it_create_refunded_status
    public function test_it_create_refunded_status()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(900);
      $remainingDue = RemainingDue::fromPositifRemainingDue(100); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(900);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(800);
      $paidAmount = PaidAmount::fromPositivePaidAmount(900);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(100);
      
    

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$netPaidAmount, $paidAmount,$refundedAmount,[]); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PAID)
      );
      $invoice->addInvoiceItem($invoiceItem1);
    
      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
        InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PAID)

      );

     
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::refunded(InvoiceItemPaymentStatus::PAID)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();

      
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );

      
      $invoice->setInvoiceStatus($invoiceStatus);

      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('refunded', $invoiceStatus->getInvoiceStatus());
   }

  
   // done 
   public function test_it_create_partially_refunded()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(900);
      $remainingDue = RemainingDue::fromPositifRemainingDue(100); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(900);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(800);
      $paidAmount = PaidAmount::fromPositivePaidAmount(900);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(100);
      
    

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$netPaidAmount, $paidAmount,$refundedAmount,[]); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem1);
    
      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::partiallyRefounded(InvoiceItemPaymentStatus::PAID)

      );

     
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $remainingDue = $invoice->getRemainingDue()->getRemainingDue();
      $invoiceTotalAmount = $invoice->getTotalAmount()->getTotalAmount();
      $agreedAmount = $invoice->getAgreedAmount()->agreedAmount();

      
      $refundedAmount = $invoice->getRefundedAmount()->getRefundedAmount();
      $paidAmount = $invoice->getPaidAmount()->paidAmount();
      $netPaidAmount = $invoice->getNetPaidAmount()->getNetPaidAmount();
   

      $multipleExecutionStatus = [$invoiceItem1,$invoiceItem2,$invoiceItem3];
    
      
      $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
         $remainingDue,
         $invoiceTotalAmount,
         $agreedAmount,
         $refundedAmount,
         $paidAmount,
         $netPaidAmount,
         $multipleExecutionStatus,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );

      
      $invoice->setInvoiceStatus($invoiceStatus);

  
     
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('partially_refunded', $invoiceStatus->getInvoiceStatus());
   }

 


   public function test_it_throw_exception_if_billed_status_is_not_unbilled()
   {
      $executionStatus = InvoiceItemExecutionStatus::inProgress();
      $paymentStatus =  InvoiceItemPaymentStatus::unbilled();

      $invoiceStatus = InvoiceStatus::billed(100, 100, 100,$executionStatus, $paymentStatus, 'in_progress');
      
     
      $this->assertInstanceOf(InvoiceStatus::class, $invoiceStatus);
      $this->assertEquals('billed', $invoiceStatus->getInvoiceStatus());
   }


   public function generateInvoice(
            InvoiceTotalAmount $totalAmount,
            RemainingDue $remainingDue,
            AgreedAmount $agreedAmount,
            NetPaidAmount $netPaidAmount,
            PaidAmount $paidAmount,
            RefundedAmount $refundedAmount,
            array $multipleExecutionStatus,
            InvoiceItemExecutionStatus $invoiceItemExecutionStatus,
            InvoiceItemPaymentStatus $invoiceItemPaymentStatus,
          
      )
   {

   
      
      $invoice = Invoice::generateInvoice(
         [],
         $totalAmount,
         $remainingDue,
         $agreedAmount,
         $refundedAmount,
         $netPaidAmount,
         $paidAmount,
         InvoiceStatus::handleInvoiceStatus(
            $remainingDue->getRemainingDue(),
            $totalAmount->getTotalAmount(),
            $agreedAmount->agreedAmount(),
            $refundedAmount->refundedAmount(),
            $paidAmount->paidAmount(),
            $netPaidAmount->getNetPaidAmount(),
            $multipleExecutionStatus,
            $invoiceItemExecutionStatus,
            $invoiceItemPaymentStatus
         )
      );


     

      return $invoice;
   }

   
}