<?php

namespace DentalOffice\InvoiceBundle\Tests\Domain\ValueObject;

use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceItemExecutionStatus;
use PHPUnit\Framework\TestCase;

class InvoiceItemExecutionStatusTest extends TestCase
{

    
     public function test_medical_record_is_planned_by_default(): void
     {
          $status = InvoiceItemExecutionStatus::planned();
          $this->assertTrue($status->isPlanned());

     }


     public function test_medical_record_is_in_progress_is_created():void
     {
          $status = InvoiceItemExecutionStatus::inProgress(InvoiceItemExecutionStatus::PLANNED);
          $this->assertInstanceOf(InvoiceItemExecutionStatus::class, $status);
          $this->assertEquals("in_progress", $status->value());
          $this->assertTrue($status->isInProgress());
     }
 
     
     public function test_invoice_item_is_completed_is_created()
     {
          $status = InvoiceItemExecutionStatus::completed(InvoiceItemExecutionStatus::IN_PROGRESS);
          $this->assertInstanceOf(InvoiceItemExecutionStatus::class, $status);
          $this->assertEquals("completed", $status->value());
          $this->assertTrue($status->isCompleted());
     }


     public function test_invoice_item_is_cancelled_is_created()
     {
          $status = InvoiceItemExecutionStatus::cancelled(InvoiceItemExecutionStatus::PLANNED);
          $this->assertInstanceOf(InvoiceItemExecutionStatus::class, $status);
          $this->assertEquals("cancelled", $status->value());
          $this->assertTrue($status->isCancelled());

          $status = InvoiceItemExecutionStatus::cancelled(InvoiceItemExecutionStatus::IN_PROGRESS);
          $this->assertInstanceOf(InvoiceItemExecutionStatus::class, $status);
          $this->assertEquals("cancelled", $status->value());
          $this->assertTrue($status->isCancelled());

          $status = InvoiceItemExecutionStatus::cancelled(InvoiceItemExecutionStatus::COMPLETED);
          $this->assertInstanceOf(InvoiceItemExecutionStatus::class, $status);
          $this->assertEquals("cancelled", $status->value());
          $this->assertTrue($status->isCancelled());
     }
     
     // 🧩 1. FULL REAL SCENARIO

     public function test_full_lifecycle_real_scenario(): void
     {
        // 🧠 Patient comes, treatment planned
        $status = InvoiceItemExecutionStatus::planned();
        $this->assertTrue($status->isPlanned());

        // 🏥 Treatment starts
        $status = $status->markInProgress();
        $this->assertTrue($status->isInProgress());

        // ✅ Treatment completed
        $status = $status->markCompleted();
        $this->assertTrue($status->isCompleted());
    }
}