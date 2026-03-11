<?php

namespace DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate;

use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;

final class Appointment
{
   private function __construct(
    
    private PatientId $patientId, 
    private TimeSlot $timeSlot,
    private PractitionerId $practitionerId,
    private PurposeId $purposeId,
    private AppointmentStatus $status
   )
   {
   
   }

   public static function book(
      PatientId $patientId ,
      TimeSlot $timeSlot,
      PractitionerId $practitionerId,
      PurposeId $purposeId,
      AppointmentStatus $status

   ):self
   {
     
        return new self($patientId,$timeSlot,$practitionerId, $purposeId,$status);
   }

    /**
     * Get the value of patientId
     */ 
    public function getPatientId():PatientId
    {
        return $this->patientId;
    }

    /**
     * Get the value of timeSlot
     */ 
    public function getTimeSlot():TimeSlot
    {
        return $this->timeSlot;
    }

    /**
     * Get the value of practitionerId
     */ 
    public function getPractitionerId():PractitionerId
    {
        return $this->practitionerId;
    }

      /**
       * Get the value of purposeId
       */ 
      public function getPurposeId():PurposeId
      {
            return $this->purposeId;
      }

    /**
     * Get the value of status
     */ 
    public function getStatus():AppointmentStatus
    {
        return $this->status;
    }
}
