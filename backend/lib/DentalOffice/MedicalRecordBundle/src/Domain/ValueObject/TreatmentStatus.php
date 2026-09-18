<?php

namespace DentalOffice\MedicalRecordBundle\Domain\ValueObject;

use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidCancelledException;
use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidInProgressException;
use DentalOffice\MedicalRecordBundle\Domain\Exception\InvalidScheduledException;


final class TreatmentStatus
{

   private const PLANNED = 'planned';
   private const SCHEDULED = 'scheduled';
   private const IN_PROGRESS = 'in_progress';
   private const COMPLETED = 'completed';
   private const CANCELLED = 'cancelled';

   private  function __construct(private string $status)
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
         throw InvalidScheduledException::invalidStatus($planned);
      } 
      return new self(self::SCHEDULED);
   }

   public static function inProgress(string $scheduled): self
   {
      if($scheduled !== self::SCHEDULED)
      {
         throw InvalidInProgressException::invalidStatus($scheduled);
      } 
      return new self(self::IN_PROGRESS);
   }

   public static function completed(string $in_progress): self
   {
      if($in_progress !== self::IN_PROGRESS)
      {
         throw InvalidInProgressException::invalidStatus($in_progress);
      } 
      return new self(self::COMPLETED);
   }

   public static function addStatus(string $status): self
   {
      return new self($status);
   }
   public static function cancelled(string $toCancelled): self
   {

      
      if (!in_array($toCancelled, [self::IN_PROGRESS, self::SCHEDULED], true)) {
        throw InvalidCancelledException::invalidTransition($toCancelled);
    }
      return new self(self::CANCELLED);
   }

   public static function make(string $status): self
   {
      return new self($status);
   }

   public function getStatus(): string
   {
      return $this->status;
   }

   private function validateStatus(string $status): void
   {
        if (!in_array($status, [self::PLANNED, self::SCHEDULED, self::IN_PROGRESS, self::COMPLETED, self::CANCELLED])) {
            throw new \InvalidArgumentException('Invalid treatment status');
        }
   }
}