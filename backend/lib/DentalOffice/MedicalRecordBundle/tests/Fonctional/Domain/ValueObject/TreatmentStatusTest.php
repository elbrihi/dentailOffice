<?php

namespace DentalOffice\MedicalRecordBundle\Tests\Fonctional\Domain\ValueObject;

use DentalOffice\MedicalRecordBundle\Domain\ValueObject\TreatmentStatus;
use PHPUnit\Framework\TestCase;

class TreatmentStatusTest extends TestCase
{
   private const PLANNED = 'planned';
   private const SCHEDULED = 'scheduled';
   private const IN_PROGRESS = 'in_progress';
   private const COMPLETED = 'completed';
   private const CANCELLED = 'cancelled';

   public function test_treatment_status_is_planned()
   {
      $status = TreatmentStatus::planned();

      $this->assertEquals(self::PLANNED, $status->getStatus());
   }

   public function test_treatment_status_is_scheduled()
   {
      $status = TreatmentStatus::scheduled(self::PLANNED);

      $this->assertEquals(self::SCHEDULED, $status->getStatus());
   }

   public function test_treatment_status_is_in_progress()
   {
      $status = TreatmentStatus::inProgress(self::SCHEDULED);

      $this->assertEquals(self::IN_PROGRESS, $status->getStatus());
   }

   public function test_treatment_status_is_completed()
   {
      $status = TreatmentStatus::completed(self::IN_PROGRESS);

      $this->assertEquals(self::COMPLETED, $status->getStatus());
   }

   public function test_treatment_status_is_cancelled()
   {
    
         $status = TreatmentStatus::cancelled(self::IN_PROGRESS);
         $this->assertEquals(self::CANCELLED, $status->getStatus());

         $status = TreatmentStatus::cancelled(self::SCHEDULED);
         $this->assertEquals(self::CANCELLED, $status->getStatus());

    
   }

 
}