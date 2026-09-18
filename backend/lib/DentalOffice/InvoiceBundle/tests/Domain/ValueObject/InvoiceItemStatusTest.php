<?php

namespace DentalOffice\InvoiceBundle\Tests\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemStatus;
use PHPUnit\Framework\TestCase;

class InvoiceItemStatusTest extends TestCase
{

     
     public function test_medical_record_is_planned_by_default(): void
     {

          $status = InvoiceItemStatus::planned();
          $this->assertTrue($status->isPlanned());

     }

     public function test_medical_record_is_in_progress_is_created():void
     {
          $status = InvoiceItemStatus::inProgress();
          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("in_progress", $status->value());
          $this->assertTrue($status->isInProgress());
     }

     public function test_invoice_item_is_completed_is_created()
     {
          $status = InvoiceItemStatus::completed();
          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("completed", $status->value());
          $this->assertTrue($status->isCompleted());
     }

     /**
      * @test Unbilled status can only be created from completed status
      
      */
     public function test_invoice_item_is_unbilled_is_created()
     {
          $status = InvoiceItemStatus::unbilled();

          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("unbilled", $status->value());
          $this->assertTrue($status->isUnbilled());
     }

     public function test_medical_record_is_billed_is_created()
     {
          $status = InvoiceItemStatus::billed();
          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("billed", $status->value());
          $this->assertTrue($status->isBilled());
     }

     public function test_medical_record_is_partially_paid_is_created()
     {
          $status = InvoiceItemStatus::partiallyPaid();
          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("partially_paid", $status->value());
          $this->assertTrue($status->isPartiallyPaid());
     }

     public function test_medical_record_is_paid_is_created()
     {
          $status = InvoiceItemStatus::paid();
          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("paid", $status->value());
          $this->assertTrue($status->isPaid());
     }

     public function test_medical_record_is_refunded_is_created()
     {
          $status = InvoiceItemStatus::refunded();
          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("refunded", $status->value());
          $this->assertTrue($status->isRefunded());
     }

     public function test_medical_record_is_cancelled_is_created()
     {
          $status = InvoiceItemStatus::cancelled();
          $this->assertInstanceOf(InvoiceItemStatus::class, $status);
          $this->assertEquals("cancelled", $status->value());
          $this->assertTrue($status->isCancelled());
     }
     
     // 🧩 1. FULL REAL SCENARIO

     public function test_full_lifecycle_real_scenario(): void
     {
        // 🧠 Patient comes, treatment planned
        $status = InvoiceItemStatus::planned();
        $this->assertTrue($status->isPlanned());

        // 🏥 Treatment starts
        $status = $status->markInProgress();
        $this->assertTrue($status->isInProgress());

        // ✅ Treatment completed
        $status = $status->markCompleted();
        $this->assertTrue($status->isCompleted());

        // 🧾 Billed
        $status = $status->markBilled();
        $this->assertTrue($status->isBilled());

        // 💰 Partial payment
        $status = $status->markPartiallyPaid();
        $this->assertTrue($status->isPartiallyPaid());

        // 💰 Full payment
        $status = $status->markPaid();
        $this->assertTrue($status->isPaid());
    }
}