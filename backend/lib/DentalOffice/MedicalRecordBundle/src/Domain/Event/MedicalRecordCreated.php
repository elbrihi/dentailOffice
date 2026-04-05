<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Event;


final class MedicalRecordCreated
{
  private function __construct(
    public readonly int $medicalRecordId,
    public readonly int $patientId,
    public readonly int $practionerId,
    public readonly int $appointmentId,
    public readonly  array $payload,

  )
  {
    
  }

  public static function medicalRecordData(
    int $medicalRecordId,
    int $patientId,
    int $practionerId,
    int $appointmentId,
    array $payload

  ):self
  {
        return new self(
            $medicalRecordId,
            $patientId,
           $practionerId,
           $appointmentId,
           $payload
        );
  } 

  /**
   * Get the value of patientId
   */ 
  public function getPatientId():int
  {
      return $this->patientId;
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