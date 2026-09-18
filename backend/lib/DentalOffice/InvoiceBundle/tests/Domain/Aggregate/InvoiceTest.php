<?php

namespace DentalOffice\InvoiceBundle\Tests\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\Aggregate\Invoice;
use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\Aggregate\Refoud;
use DentalOffice\InvoiceBundle\Domain\ValueObject\AgreedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemExecutionStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemPaymentStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\NetAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\NetPaidAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\PaidAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RefundedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RemainingDue;
use DentalOffice\PaymentsBundle\Domain\Aggregate\AllocatedAmount;
use DentalOffice\PaymentsBundle\Domain\Aggregate\Allocation;
use DentalOffice\PaymentsBundle\Domain\Aggregate\Payment;
use DentalOffice\PaymentsBundle\Domain\ValueObject\PaymentAmountApaid;
use DentalOffice\PaymentsBundle\Domain\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
   protected function setUp(): void
   {
      parent::setUp();
   }
 
   //  status testing
   public function test_invoice_is_planned()
   {  
      
      
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $invoiceItemExecutionStatus= InvoiceItemExecutionStatus::inProgress();
      $invoiceItemPaymentStatus= InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

                
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
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::unbilled()
      );
      $invoice->addInvoiceItem($invoiceItem1);

      
      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::unbilled()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $invoice->recalculateInvoiceItemRefund($invoice);

 
      dd((string) $invoice->getInvoiceStatus());
    
     //$this->assertSame(InvoiceItemExecutionStatus::PLANNED, (string) $invoice->getInvoiceStatus());
     $this->assertInstanceOf(Invoice::class,$invoice);
   }

   public function test_invoice_is_in_progress()
   {

      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(0.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0.0);
      $invoiceItemExecutionStatus= InvoiceItemExecutionStatus::inProgress();
      $invoiceItemPaymentStatus= InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

          
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
         InvoiceItemExecutionStatus::inProgress(InvoiceItemExecutionStatus::planned()),
         InvoiceItemPaymentStatus::unbilled()
      );
      $invoice->addInvoiceItem($invoiceItem1);


      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::planned(),
         InvoiceItemPaymentStatus::unbilled()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $invoice->recalculateInvoiceItemRefund($invoice); 
      
      $this->assertInstanceOf(Invoice::class,$invoice);

   
      $this->assertEquals("in_progress", (string) $invoice->getExecutionStatus());

     
   }

   public function test_invoice_is_completed()
   {

      // invoice [invoiceItem1[in_progress],invoiceItem2[planned],invoiceItem3[planned]]

      // invoiceItem [executionStatus] => in_progress


      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(0.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0.0);
      $invoiceItemExecutionStatus= InvoiceItemExecutionStatus::inProgress();
      $invoiceItemPaymentStatus= InvoiceItemPaymentStatus::billed(InvoiceItemPaymentStatus::UNBILLED);

          
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

      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(700);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);

      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(200);

      $invoice = $this->generateInvoice(
                           $totalAmount,
                           $remainingDue,
                           $agreedAmount,
                           $refundedAmount
            ); 
    
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::inProgress()),
         InvoiceItemPaymentStatus::unbilled()
      );
      $invoice->addInvoiceItem($invoiceItem1);


      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::inProgress()),
         InvoiceItemPaymentStatus::unbilled()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $invoice->recalculateInvoiceItemRefund($invoice);  

   }

   // 🔵  CANCELLED TEST 🔵 but is not completed yet ===> to correct
   public function test_invoice_is_cancelled()
   {
      // invoice [invoiceItem1[in_progress],invoiceItem2[planned],invoiceItem3[planned]]

      // invoiceItem [executionStatus] => in_progress

      $invoice = Invoice::generateInvoice(
         InvoiceTotalAmount::fromPositifTotlaAmount(1000),
         RemainingDue::fromPositifRemainingDue(1000),
         AgreedAmount::fromPositifAgreedAmount(1000),
         InvoiceStatus::handleInvoiceStatus(
            InvoiceTotalAmount::fromPositifTotlaAmount(1000)->getTotalAmount(),
            RemainingDue::fromPositifRemainingDue(1000)->getRemainingDue(),
            AgreedAmount::fromPositifAgreedAmount(1000)->agreedAmount()
         )
      );
      $invoiceItem1 = InvoiceItem::invoice(
         1,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::cancelled(InvoiceItemExecutionStatus::IN_PROGRESS)
      );

      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItem2 = InvoiceItem::invoice(
         1,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::planned()

      );

      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItem3 = InvoiceItem::invoice(
         1,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::planned()

      );

      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      // $this->assertSame(InvoiceItemExecutionStatus::CANCELLED, $invoice->getExecutionStatus()->value());;

   }

   // 🔵 unbilled invoice 🔵  but is not completed yet ===> to correct
   public function  test_invoice_is_unbilled()
   {
      $totalAmount   = InvoiceTotalAmount::fromPositifTotlaAmount(1000);
      $remainingDue  = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount  = AgreedAmount::fromPositifAgreedAmount(1000);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);


      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$netPaidAmount, $paidAmount,$refundedAmount,[]); 

      $invoiceItem1 = InvoiceItem::invoice(
         1,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::unbilled()
      );

      $invoice->addInvoiceItem($invoiceItem1);
      $invoice->recalculateInvoiceItemPaymentStatus();

      $invoiceItem2 = InvoiceItem::invoice(
         1,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::unbilled()

      );

      $invoice->addInvoiceItem($invoiceItem2);
      $invoice->recalculateInvoiceItemPaymentStatus();

      $invoiceItem3 = InvoiceItem::invoice(
         1,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::unbilled()
      );

      $invoice->addInvoiceItem($invoiceItem3);
      

      $this->assertSame($invoice->getPaymentStatus()->value(), InvoiceItemPaymentStatus::unbilled);
   }

   // 🔵 billed invoice 🔵  but is not completed yet ===> to correct
   public function test_invoice_is_billed()
   { 


      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0.0);
      $executionItemStatus = InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);
      $paymentStatus=InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);
           
      $invoice = $this->generateInvoice(
                              $totalAmount,
                              $remainingDue,
                              $agreedAmount,
                              $netPaidAmount,
                              $paidAmount,
                              $refundedAmount,
                              [],
                              $executionItemStatus,
                              $paymentStatus
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

      // first payment for visit 1
      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1,
            RefundedAmount::fromPositiveRefundedAmount(20),
            NetPaidAmount::fromPositiveNetPaidAmount(0),
            PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);


     
      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);


      $invoice->recalculateInvoiceItemRefund($invoice);

      dd($invoice);
  
   }

   public function test_done_invoice_item_partially_paid_and_others_paid()
   {
 
      
     
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0.0);
      $executionItemStatus = InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);
      $paymentStatus=InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);
           
      $invoice = $this->generateInvoice(
                              $totalAmount,
                              $remainingDue,
                              $agreedAmount,
                              $netPaidAmount,
                              $paidAmount,
                              $refundedAmount,
                              [],
                              $executionItemStatus,
                              $paymentStatus
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

      // first payment for visit 1
      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1, 
            $refundedAmount,
            $netPaidAmount,
            PaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
      $netPaidAmount = $paymentSaved->getNetAmount();

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

     
      // second payment 
           
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1,
            $refundedAmount,
            $netPaidAmount,
            PaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );
  
      // second payment for visit 3
      $amountSaved2 = 50;

      $netPaidAmount = $paymentSaved->getNetAmount();

      $allocatedAmount2 = AllocatedAmount::fromAmount($amountSaved2);

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount2);

      $paymentSaved->addAllocation($alocation);


      $invoice->addPayment($paymentSaved);

      $amountPayedAtVisit1ForItem1 = 50;

      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit1ForItem1);
      $paymentSavedForSecondVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem1,
         $refundedAmount,
         $netPaidAmount,
         PaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED)
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      
      $alocation = Allocation::allocatePaymentToItem(
                                    $invoiceItem1, 
                                    $allocatedAmount
                     );

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);


      $amountPayedAtVisit2ForItem1 = 50;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit2ForItem1);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      
      
      $paymentSavedForSecondVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem1,
         $refundedAmount,
         $netPaidAmount,
         PaymentStatus::paid(PaymentStatus::BILLED)
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtVisit2ForItem1);
      
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);
 
      // payment for visit 2 item 2 

      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0);
      $amountPayedAtVisit2ForItem2 = 400;

      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit2ForItem2);
      $paymentSavedForSecondVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem2,
         $refundedAmount,
         $netPaidAmount,
         PaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtVisit2ForItem2);

      $alocation = Allocation::allocatePaymentToItem($invoiceItem2, $allocatedAmount);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);



      $amountPayedAtLastVisit2ForItem3 = 300;

      
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtLastVisit2ForItem3);
      $paymentSavedForLastVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem3,
         $refundedAmount,
         $netPaidAmount,
         PaymentStatus::paid(InvoiceItemPaymentStatus::BILLED)
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtLastVisit2ForItem3);
      
      $alocation = Allocation::allocatePaymentToItem($invoiceItem3, $allocatedAmount);

      $paymentSavedForLastVisit->addAllocation($alocation);
      $invoice->addPayment($paymentSavedForLastVisit);

       $invoice->recalculateInvoiceItemRefund($invoice);

      dd($invoice);

      
   }

   public function test_invoice_item_complted_paid_directly_on_invoice()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount); 

    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();


      $amountPayedAtVisit = 300;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit);
      $paymentSavedForSecondVisit = Payment::payVisit(
            $paymentId = 0,
            $amountPaidSavedAtVisit,
            $invoiceItem1
         );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountPayedAtVisit);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);


      $invoice->recalculateInvoiceItemPaymentStatus($paymentSavedForSecondVisit);


      $payments = $invoice->getPayments();


      $this->assertSame("paid", $payments[0]->getPaymentStatus()->getPaymentStatus());

      $this->assertSame("paid", $invoice->getInvoiceItem()[0]->getPaymentStatus()->value());
   }

   public function test_invoice_item_complted_all_partially_paid_to_paid()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);
      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount); 
     
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      
    
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1
         );


      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved1);

      $paymentSaved->addAllocation($alocation);


      $invoice->addPayment($paymentSaved);


      // second payment 

      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1
         );

      $amountSaved2 = 50;

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved2);

      $paymentSaved->addAllocation($alocation);


      $invoice->addPayment($paymentSaved);

      $amountPayedAtVisit1 = 50;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit1);
      $paymentSavedForSecondVisit = Payment::payVisit(
            $paymentId,
            $amountPaidSavedAtVisit,
            $invoiceItem1
      );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountPayedAtVisit1);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);


      $amountPayedAtVisit2 = 50;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit2);
      $paymentSavedForSecondVisit = Payment::payVisit(
            $paymentId,
            $amountPaidSavedAtVisit,
            $invoiceItem1
         );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountPayedAtVisit2);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);


      $invoice->recalculateInvoiceItemPaymentStatus($paymentSavedForSecondVisit);


      $payments = $invoice->getPayments();


     // dd($invoice);
      
      $this->assertSame("partially_paid", $payments[0]->getPaymentStatus()->getPaymentStatus());
      $this->assertSame("partially_paid", $payments[1]->getPaymentStatus()->getPaymentStatus());
      $this->assertSame("partially_paid", $payments[2]->getPaymentStatus()->getPaymentStatus());
      $this->assertSame("paid", $payments[3]->getPaymentStatus()->getPaymentStatus());

      $this->assertSame("paid", $invoice->getInvoiceItem()[0]->getPaymentStatus()->value());
   }


   public function test_invoice_item_partially_paid()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);

       $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount); 
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1
         );


      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved1);

      $paymentSaved->addAllocation($alocation);


      $invoice->addPayment($paymentSaved);


    

      $amountPayedAtVisit = 50;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit);
      $paymentSavedForSecondVisit = Payment::payVisit(
            $paymentId,
            $amountPaidSavedAtVisit,
            $invoiceItem1
         );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountPayedAtVisit);

      $paymentSavedForSecondVisit->addAllocation($alocation);

     $invoice= $invoice->recalculateInvoiceItemPaymentStatus($paymentSavedForSecondVisit);

      

      // dump($payments[0]->getPaymentStatus()->getPaymentStatus());
    
      $payments = $invoice->getPayments();

      
      foreach($payments  as $payment)
      {
         $this->assertSame("partially_paid",$payment->getPaymentStatus()-> getPaymentStatus());
       
      }
      $this->assertEquals(1000 , $invoice->getAgreedAmount()->agreedAmount());
      $this->assertEquals(850 , $invoice->getRemainingDue()->getRemainingDue());
      $this->assertEquals(150 , $invoice-> getTotalAmount()->getTotalAmount());



   }


   public function test_invoice_is_done()
   {

      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(700);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);

       $refundedAmount = RefundedAmount::fromPositiveRefundAmount(200);

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$refundedAmount); 
    
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      // first payment for visit 1
      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1,
            RefundedAmount::fromZeroRefundAmount(40),
            NetAmount::fromZeroNetAmount(0),
            PaymentStatus::partiallyPaid()
         );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved1,$allocatedAmount);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);


      // second payment for visit 2 

      $amountSaved1 = 50;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::partiallyPaid()
         );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1,  $allocatedAmount);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

   
      // second payment for visit 1

      $amountSaved1 = 50;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::partiallyPaid()
         );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);
     
      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

      // third payment for visit 1 

      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::paid()
         );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);
    



      $amountPayedAtVisit2ForItem2 = 400;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit2ForItem2);
      $paymentSavedForSecondVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem2,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::paid()
      );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem2, $amountPayedAtVisit2ForItem2);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);



      $amountPayedAtLastVisit2ForItem3 = 300;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtLastVisit2ForItem3);
      $paymentSavedForLastVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem3
      );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem3, $amountPayedAtLastVisit2ForItem3);

      $paymentSavedForLastVisit->addAllocation($alocation);


      $invoice = $invoice->recalculateInvoiceItemPaymentStatus($paymentSavedForLastVisit);

   
      $this->assertEquals("done",$invoice->getPaymentStatus()-> getInvoiceItemPaymentStatus());
      $this->assertEquals(1000 , $invoice->getAgreedAmount()->agreedAmount());
      $this->assertEquals(0 , $invoice->getRemainingDue()->getRemainingDue());
      $this->assertEquals(1000 , $invoice-> getTotalAmount()->getTotalAmount());
      

      
   }
  
   /**
    * 
    */
   public function test_first_partial_payment()
   {
   }

   public function test_invoice_is_first_paritial_payment()
   {
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(700);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);
      $refundedAmount = RefundedAmount::fromPositiveRefundAmount(0);

      $refundedAmount = RefundedAmount::fromPositiveRefundAmount(200);

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$refundedAmount); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

     


      $paymentId=0;
      $amountPayedAtLastVisit2ForItem3 = 300;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtLastVisit2ForItem3);
      $paymentSavedForLastVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem1,
         RefundedAmount::fromZeroRefundAmount(40),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::partiallyPaid()
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtLastVisit2ForItem3 );
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);

      $paymentSavedForLastVisit->addAllocation($alocation);

      $invoice->addPayment($paymentSavedForLastVisit);

      $invoice->recalculateInvoiceItemRefund($invoice);
      
      

      // $invoice = $invoice->recalculateInvoiceItemPaymentStatus($paymentSavedForLastVisit);

      
      // $this->assertEquals("billed",$invoice->getPaymentStatus()-> getInvoiceItemPaymentStatus());
      // $this->assertEquals(1000 , $invoice->getAgreedAmount()->agreedAmount());
      // $this->assertEquals(700 , $invoice->getRemainingDue()->getRemainingDue());
      // $this->assertEquals(300 , $invoice-> getTotalAmount()->getTotalAmount());
      


   }


   /**
    *  done 
    */
   public function test_partial_refund()
   {
           
      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(1000.0);
      $remainingDue = RemainingDue::fromPositifRemainingDue(1000.0); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000.0);
      $refundedAmount = RefundedAmount::fromPositiveRefundedAmount(0.0);
      $netPaidAmount = NetPaidAmount::fromPositiveNetPaidAmount(0.0);
      $paidAmount = PaidAmount::fromPositivePaidAmount(0.0);
      $executionItemStatus = InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);
      $paymentStatus=InvoiceItemPaymentStatus::partiallyPaid(InvoiceItemPaymentStatus::BILLED);
           
      $invoice = $this->generateInvoice(
                              $totalAmount,
                              $remainingDue,
                              $agreedAmount,
                              $netPaidAmount,
                              $paidAmount,
                              $refundedAmount,
                              [],
                              $executionItemStatus,
                              $paymentStatus
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

      // first payment for visit 1  // 100 
      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1,
            RefundedAmount::fromPositiveRefundedAmount(20),
            NetPaidAmount::fromPositiveNetPaidAmount(0),
            PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);


     
      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);


      // second payment for visit 2 

      $amountSaved1 = 50;   // 100 + 50
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromPositiveRefundedAmount(0),
         NetPaidAmount::fromPositiveNetPaidAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);
      
      // new refund for the same invoice item  // 100 + 50 - (25)
      $refundId1_1 = 0;
      $refundId1_1 =  $refundId1_1 + 1;
      $refundedAmount1 = RefundedAmount::fromPositiveRefundedAmount(25);
      $refund = Refoud::createRefound(
         $refundId1_1 ,
         $refundedAmount1,
         $invoiceItem1,
         "Refund for the same invoice item"
      );

      $alocation->addRefoud($refund);

      //  new refound for the same invoice item 
      //  100 + 50 - (25+25)      
      $refundId1_1 =  $refundId1_1 + 1;
      $refundedAmount1 = RefundedAmount::fromPositiveRefundedAmount(25);
      $refund = Refoud::createRefound(
         $refundId1_1 ,
         $refundedAmount1,
         $invoiceItem1,
         "Refund for the same invoice item"
      );

      $alocation->addRefoud($refund);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

   
      // second payment for visit 1

      // $amountSaved1 = 0;
      // $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      // $paymentSaved = Payment::payVisit(
      //    $paymentId + 1,
      //    $amountPaidSaved,
      //    $invoiceItem1,
      //    RefundedAmount::fromPositiveRefundedAmount(0),
      //    NetPaidAmount::fromPositiveNetPaidAmount(0),
      //    PaymentStatus::completed(PaymentStatus::pending())
      //    );

      // $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      // $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);

      // $paymentSaved->addAllocation($alocation);

      // $invoice->addPayment($paymentSaved);

      // third payment for visit 1 

      $amountSaved1 = 150;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      //  100 + 50 - (25+25) + 150 = 250

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromPositiveRefundedAmount(0),
         NetPaidAmount::fromPositiveNetPaidAmount(0),
        PaymentStatus::completed(PaymentStatus::pending())
      );

    
      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

      $amountPayedAtVisit2ForItem2 = 400;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit2ForItem2);
      $paymentSavedForSecondVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem2,
         RefundedAmount::fromPositiveRefundedAmount(0),
         NetPaidAmount::fromPositiveNetPaidAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtVisit2ForItem2);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem2, $allocatedAmount);

      $paymentSavedForSecondVisit->addAllocation($alocation);

      $invoice->addPayment($paymentSavedForSecondVisit);

      $amountPayedAtLastVisit2ForItem3 = 200;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtLastVisit2ForItem3);
      $paymentSavedForLastVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem3,
         RefundedAmount::fromPositiveRefundedAmount(0),
         NetPaidAmount::fromPositiveNetPaidAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtLastVisit2ForItem3);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem3, $allocatedAmount);

      $paymentSavedForLastVisit->addAllocation($alocation);

      $invoice->addPayment($paymentSavedForLastVisit);


      $amountPayedAtLastVisit2ForItem3 = 100;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtLastVisit2ForItem3);
      $paymentSavedForLastVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem3,
         RefundedAmount::fromPositiveRefundedAmount(0),
         NetPaidAmount::fromPositiveNetPaidAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtLastVisit2ForItem3);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem3, $allocatedAmount);

      $paymentSavedForLastVisit->addAllocation($alocation);

      $invoice->addPayment($paymentSavedForLastVisit);
      

      $invoice->recalculateInvoiceItemRefund($invoice);


     
     // $invoice = $invoice->recalculateInvoiceItemRefund($paymentSavedForLastVisit);

   
      dd($invoice);


   }
   

   /**
    *  done 
    */
   public function test_invoice_is_refund_done()
   {

      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(700);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);

      $refundedAmount = RefundedAmount::fromPositiveRefundAmount(200);

      $invoice = $this->generateInvoice($totalAmount,$remainingDue,$agreedAmount,$refundedAmount); 
    
    
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

      // first payment for visit 1
      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1,
            RefundedAmount::fromZeroRefundAmount(40),
            NetAmount::fromZeroNetAmount(0),
            PaymentStatus::completed(PaymentStatus::pending())
         );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);


      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);


      // second payment for visit 2 

      $amountSaved1 = 50;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
         );

      
      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);
     
      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

   
      // second payment for visit 1

      $amountSaved1 = 50;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
         );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);
     
      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

      // third payment for visit 1 

      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
         );

      $allocatedAmount = AllocatedAmount::fromAmount($amountSaved1);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $allocatedAmount);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);
    



      $amountPayedAtVisit2ForItem2 = 400;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit2ForItem2);
      $paymentSavedForSecondVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem2,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
         PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtVisit2ForItem2);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem2, $allocatedAmount);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);



      $amountPayedAtLastVisit2ForItem3 = 300;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtLastVisit2ForItem3);
      $paymentSavedForLastVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem3,
         RefundedAmount::fromZeroRefundAmount(0),
         NetAmount::fromZeroNetAmount(0),
        PaymentStatus::completed(PaymentStatus::pending())
      );

      $allocatedAmount = AllocatedAmount::fromAmount($amountPayedAtLastVisit2ForItem3);
      $alocation = Allocation::allocatePaymentToItem($invoiceItem3, $allocatedAmount);



      $paymentSavedForLastVisit->addAllocation($alocation);

      // new refund for the same invoice item
      $refundId1_1 = 0;
      $refundId1_1 =  $refundId1_1 + 1;
      $refundedAmount1 = RefundedAmount::fromPositiveRefundAmount(150);
      $refund = Refoud::createRefound(
         $refundId1_1 ,
         $refundedAmount1,
         $invoiceItem3,
         "Refund for the same invoice item"
      );
      $alocation->addRefoud($refund);


      $refundId1_1 =  $refundId1_1 + 1;
      $refundedAmount1 = RefundedAmount::fromPositiveRefundAmount(150);
      $refund = Refoud::createRefound(
         $refundId1_1 ,
         $refundedAmount1,
         $invoiceItem3,
         "Refund for the same invoice item"
      );


      $alocation->addRefoud($refund);

      $invoice->addPayment($paymentSavedForLastVisit);

      $invoice->recalculateInvoiceItemRefund($invoice);
     

      
   }

   /**
    * 
    */
   public function test_refund_from_multiple_payments()
   {

      $totalAmount = InvoiceTotalAmount::fromPositifTotlaAmount(700);
      $remainingDue = RemainingDue::fromPositifRemainingDue(300); // still owes 300
      $agreedAmount = AgreedAmount::fromPositifAgreedAmount(1000);

      $invoice = $this->generateInvoice(
         $totalAmount,
         $remainingDue,
         $agreedAmount
      ); 
    
      $invoiceItemId = 1;
      $invoiceItem1 = InvoiceItem::invoice(
         $invoiceItemId,
         "Consultation initiale",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem1);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem2 = InvoiceItem::invoice(
         $invoiceItemId,
         "Dévitalisation",
         400,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()

      );
      $invoice->addInvoiceItem($invoiceItem2);

      $invoiceItemId = $invoiceItemId + 1;
      $invoiceItem3 = InvoiceItem::invoice(
         $invoiceItemId,
         "Composite (reste à faire)",
         300,
         0,
         InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS),
         InvoiceItemPaymentStatus::billed()
      );
      $invoice->addInvoiceItem($invoiceItem3);

      $invoice->recalculateInvoiceItemExecutionStatus();

      // first payment for visit 1
      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentId = 0;
      $paymentSaved = Payment::payVisit(
            $paymentId + 1,
            $amountPaidSaved,
            $invoiceItem1,
            PaymentStatus::partiallyPaid()
         );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved1);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);


      // second payment for visit 2 

      $amountSaved1 = 50;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         PaymentStatus::partiallyPaid()
         );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved1);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

   
      // second payment for visit 1

      $amountSaved1 = 50;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         PaymentStatus::partiallyPaid()
         );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved1);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);

      // third payment for visit 1 

      $amountSaved1 = 100;
      $amountPaidSaved = PaymentAmountApaid::fromFloat($amountSaved1);

      $paymentSaved = Payment::payVisit(
         $paymentId + 1,
         $amountPaidSaved,
         $invoiceItem1,
         PaymentStatus::paid()
         );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem1, $amountSaved1);

      $paymentSaved->addAllocation($alocation);

      $invoice->addPayment($paymentSaved);
    



      $amountPayedAtVisit2ForItem2 = 400;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtVisit2ForItem2);
      $paymentSavedForSecondVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem2,
         PaymentStatus::paid()
      );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem2, $amountPayedAtVisit2ForItem2);

      $paymentSavedForSecondVisit->addAllocation($alocation);


      $invoice->addPayment($paymentSavedForSecondVisit);



      $amountPayedAtLastVisit2ForItem3 = 300;
      $amountPaidSavedAtVisit = PaymentAmountApaid::fromFloat($amountPayedAtLastVisit2ForItem3);
      $paymentSavedForLastVisit = Payment::payVisit(
         $paymentId,
         $amountPaidSavedAtVisit,
         $invoiceItem3
      );

      $alocation = Allocation::allocatePaymentToItem($invoiceItem3, $amountPayedAtLastVisit2ForItem3);

      $paymentSavedForLastVisit->addAllocation($alocation);


      $invoice = $invoice->recalculateInvoiceItemPaymentStatus($paymentSavedForLastVisit);

   
    
      $this->assertEquals("done",$invoice->getPaymentStatus()-> getInvoiceItemPaymentStatus());
      $this->assertEquals(1000 , $invoice->getAgreedAmount()->agreedAmount());
      $this->assertEquals(0 , $invoice->getRemainingDue()->getRemainingDue());
      $this->assertEquals(1000 , $invoice-> getTotalAmount()->getTotalAmount());
      


      
      
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
         ),
         $invoiceItemExecutionStatus,
         $invoiceItemPaymentStatus

         
      );


      return $invoice;
   }
 
}
