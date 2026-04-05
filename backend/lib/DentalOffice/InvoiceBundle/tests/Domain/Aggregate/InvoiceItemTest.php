<?php

namespace DentalOffice\InvoiceBundle\Tests\Domain\Aggregate;

use PHPUnit\Framework\TestCase;
   use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;

final class InvoiceItemTest extends TestCase
{

      
      public function test_invoice_item_is_planned()
      {
         $invoiceItem = InvoiceItem::invoice(
               1,
               " Consultation + radio ",
               300,
               0
               
            )->getStatus()::planned();

        $this->assertSame('planned', $invoiceItem-> getStatus());
      }

      public function test_invoice_is_complated()
      {
         $invoiceItem = InvoiceItem::invoice(
               1,
               " Consultation + radio ",
               300,
               0
               
            )->getStatus()::completed();

        $this->assertSame('complted', $invoiceItem-> getStatus());

      }

      public function test_invoice_is_paid()
      {
         $invoiceItem = InvoiceItem::invoice(
               1,
               " Consultation + radio ",
               300,
               0
               
            )->getStatus()::paid();

        $this->assertSame('paid', $invoiceItem-> getStatus());

      }
}