<?php

namespace DentalOffice\InvoiceBundle\Tests\Domain\Aggregate;

use DentalOffice\InvoiceBundle\Domain\Aggregate\Invoice;
use DentalOffice\InvoiceBundle\Domain\Exception\InvalidInvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
   protected function setUp(): void
   {
      parent::setUp();
   }

   public function test_total_amount_is_positif()
   {

         $invoice=Invoice::generateInvoice(
            InvoiceTotalAmount::fromPositifTotlaAmount(456)
         );

         $this->assertEquals($invoice->getTotalAmount()->getTotalAmount(),456);


   }

   public function test_total_amount_is_not_positif()
   {
 
         try {
            $invoice = Invoice::generateInvoice(
               InvoiceTotalAmount::fromPositifTotlaAmount(-5)
            );
         } catch (InvalidInvoiceTotalAmount $e) {
           dd($e->getMessage());
         }

         

        

   }
}