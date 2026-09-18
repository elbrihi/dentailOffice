<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Event;


final class TreatmentCreatedOnMedicalRecord
{
  private function __construct(
    public readonly array $steps = [],
    public readonly int $practionerId,
    public readonly int $appointmentId,
    public readonly  array $payload,
    private readonly int $medicalRecordId = 0

  )
  {
    
  }

  public static function treatmentData(
    array $steps,
    int $practionerId,
    int $appointmentId,
    array $payload,
    int $medicalRecordId = 0

  ):self
  {
        return new self(
           $steps,
           $practionerId,
           $appointmentId,
           $payload,
           $medicalRecordId
        );
  } 




  /**
   * Get the value of medicalRecordId
   */ 
  public function getMedicalRecordId():int
  {
      return $this->medicalRecordId;
  }

  /**
   * Get the value of practionerId
   */ 
  public function getPractionerId():int
  {
      return $this->practionerId;
  }

  /**
   * Get the value of payload
   */ 
  public function getPayload():array
  {
      return $this->payload;
  }

  /**
   * Get the value of appoitmentId
   */ 
  public function getAppointmentId():int
  {
      return $this->appointmentId;
  }

 
}