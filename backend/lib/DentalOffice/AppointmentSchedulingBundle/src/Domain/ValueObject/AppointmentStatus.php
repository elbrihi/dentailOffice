<?php


namespace DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject;

use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\CancelledException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\CompletedException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\ConfirmedException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\RescheduleException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\ReschouldException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\ShowException;


class AppointmentStatus
{
   private const SCHEDULED = 'scheduled';
   private const CONFIRMED = 'confirmed';
   private const CANCELLED = 'cancelled';
   private const NO_SHOW = 'no_show';
   private const RESCHEDULED = 'rescheduled';
   private const COMPLETED = 'completed';

   private $value ;

   private function __construct(string $value)
   {
      
        $this->value = $value;
   }

   public static function scheduled():self
   {
      return new self(self::SCHEDULED);
   }


   public static function confirmed(string $scheduled):self
   {
  
      if($scheduled !== self::SCHEDULED)
      {
         throw ConfirmedException::invalid();
      }
      return new self(self::CONFIRMED);
   }

   public static function noShow(string $confiremed):self
   {
     
      if ($confiremed !== self::CONFIRMED) {
         
         throw ShowException::nowShp();
      
      }

      return new self(self::NO_SHOW);
   }

   public static function reschould(string $currentStatus):self
   {
     
    if (!in_array($currentStatus, [self::CONFIRMED, self::SCHEDULED], true)) {
        // Pass the actual status of the appointment
        throw RescheduleException::invalidStatus($currentStatus);
    }

    return new self(self::RESCHEDULED);
   }
   public static function cancelled(string $confirmed):self
   {
      if($confirmed !== self::CONFIRMED)
      {
         throw CancelledException::invalid();
      }
      
      return new self(self::CANCELLED);
   }

   public static function completed(string  $confirmed):self
   {

   
      if($confirmed !== self::CONFIRMED)
      {
         throw CompletedException::invalidStatus($confirmed);
      }
      return new self(self::COMPLETED);
   }
    public function transitionTo(string $newStatus): self
    {
        $allowed = [
            self::SCHEDULED => [self::CONFIRMED, self::CANCELLED, self::RESCHEDULED],
            self::CONFIRMED => [self::COMPLETED, self::CANCELLED, self::RESCHEDULED],
        ];

        if (!in_array($newStatus, $allowed[$this->value] ?? [], true)) {
            throw new \DomainException(
                "Invalid transition from {$this->value} to {$newStatus}"
            );
        }

        return new self($newStatus);
    }
   public function getStatus(): string 
   {
     return $this->value;
   }
   

}