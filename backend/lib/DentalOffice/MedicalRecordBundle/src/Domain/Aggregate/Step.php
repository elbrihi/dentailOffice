<?php


namespace DentalOffice\MedicalRecordBundle\Domain\Aggregate;

use DentalOffice\MedicalRecordBundle\Domain\ValueObject\StepStatus;

class Step
{

     
   private function __construct(
      
      private int $stepId,
      private string $name,
      private float $amount,
      private int $visitId,
      private StepStatus $status,
      private \DateTimeInterface $performedAt   

   )
   {
      
   }
   

   public static function createStep(
      int $stepId,
      string $name,
      float $amount,
      int $visitId,
      StepStatus $status,
      \DateTimeInterface $performedAt
   )
   {
      return new self($stepId, $name,$amount, $visitId, $status, $performedAt);
   }
   
   /**
    * Get the value of name
    */
   public function getName(): string
   {
         return $this->name;
   }

   /**
    * Get the value of stepId
    */
   public function getStepId(): int
   {
         return $this->stepId;
   }

   /**
    * Get the value of visitId
    */
   public function getVisitId(): int
   {
         return $this->visitId;
   }

   /**
    * Get the value of status
    */
   public function getStatus(): StepStatus
   {
         return $this->status;
   }

   /**
    * Get the value of performedAt
    */
   public function getPerformedAt(): \DateTimeInterface
   {
         return $this->performedAt;
   }

      /**
       * Get the value of amount
       */
      public function getAmount(): float
      {
            return $this->amount;
      }
}