<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Aggregate;

use DentalOffice\MedicalRecordBundle\Domain\ValueObject\StepStatus;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\TreatmentStatus;

class Treatment
{
  
   private const PLANNED = 'planned';
   private const SCHEDULED = 'scheduled';
   private const IN_PROGRESS = 'in_progress';
   private const COMPLETED = 'completed';
   private const CANCELLED = 'cancelled';
   
   private function __construct(
      
  
      private int $treatmentId,
      private string $description,
      private string $type,
      private array $steps = [],
      private TreatmentStatus $status ,



   )
   {   
   
   }
   public static function createTreatmentPlan(
      int $treatmentId,
      string $name,
      string $type
   )
   {
      return new self($treatmentId, $name,  $type, [], TreatmentStatus::planned());
   }

   public  function addStep(Step $step)
   {

      $this->steps[] = $step;

      $this->recalculateStatus();

   }
   
   public function getStatus(): TreatmentStatus
   {
         return $this->status;
   }

 

   /**
    * Get the value of treatmentId
    */
   public function getTreatmentId(): int
   {
         return $this->treatmentId;
   }

   /**
    * Get the value of type
    */
   public function getType(): string
   {
      return $this->type;
   }

   /**
    * Get the value of steps
    */
    public function getSteps(): array
    {
      return $this->steps; 
    }

      /**
       * Get the value of description
       */
      public function getDescription(): string
      {
            return $this->description;
      }

      private function recalculateStatus(): void  {


            if (empty($this->steps)) {
                  return;
            }

            $statuses = array_map(
                  fn(Step $step) => $step->getStatus()->getStatus(),
                  $this->steps
            );

            // 🎯 RULES ENGINE

            if (count(array_unique($statuses)) === 1 && $statuses[0] === self::PLANNED) {
                  $this->status = TreatmentStatus::addStatus(self::PLANNED);
                  return;
            }

            if (in_array(self::IN_PROGRESS, $statuses, true)) {
                  $this->status = TreatmentStatus::addStatus(self::IN_PROGRESS);
                  return;
            }

            if (count(array_unique($statuses)) === 1 && $statuses[0] === self::COMPLETED) {
                  $this->status = TreatmentStatus::addStatus(self::COMPLETED);
                  return;
            }

            if (count(array_unique($statuses)) === 1 && $statuses[0] === self::CANCELLED) {
                  $this->status = TreatmentStatus::addStatus(self::CANCELLED);
                  return;
            }

            if (in_array(self::SCHEDULED, $statuses, true)) {
                  $this->status = TreatmentStatus::addStatus(self::SCHEDULED);
                  return;
            }

            // default fallback
            $this->status = TreatmentStatus::addStatus(self::IN_PROGRESS);
      }

      
    
 
}