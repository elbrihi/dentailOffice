<?php

namespace DentalOffice\MedicalRecordBundle\Domain\ValueObject;

use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidCompletedStepStatusException;
use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidInProgressStepStatusException;
use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidScheduledStepStatusException;
use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidCancelledStepStatusException;

final class StepStatus
{
     private const PLANNED = 'planned';
     private const SCHEDULED = 'scheduled';
     private const IN_PROGRESS = 'in_progress';
     private const COMPLETED = 'completed';
     private const CANCELLED = 'cancelled';
    
   public function __construct(private string $status)
   {
        $this->status = $status;
   }

   public static function planned(): self
   {
      return new self(self::PLANNED);
   }

   public static function scheduled(string $planned): self
   {

      if($planned !== self::PLANNED)
      {
         throw InvalidScheduledStepStatusException::invalidStatus($planned);
      } 
      return new self(self::SCHEDULED);
   }

   public static function inProgress(string $scheduled): self
   {
      if($scheduled !== self::SCHEDULED)
      {
         throw InvalidInProgressStepStatusException::invalidTransition($scheduled);
      } 
      return new self(self::IN_PROGRESS);
   }

   public static function completed(string $inProgress): self
   {
      if($inProgress !== self::IN_PROGRESS)
      {
         throw InvalidCompletedStepStatusException::invalidTransition($inProgress);
      } 
      return new self(self::COMPLETED);
   }


   public static function cancelled(string $toCancelled): self
   {
      if (!in_array($toCancelled, [self::IN_PROGRESS, self::SCHEDULED], true)) {

       throw InvalidCancelledStepStatusException::invalidTransition($toCancelled);
    }
      return new self(self::CANCELLED);
   }

   public function getStatus(): string
   {
      return $this->status;
   }

   // private function validateStatus(string $status): void
   // {
   //      if (!in_array($status, [self::SCHEDULED, self::IN_PROGRESS, self::COMPLETED, self::CANCELLED], true)) {
   //          throw new \InvalidArgumentException('Invalid step status');
   //      }
   // }
}