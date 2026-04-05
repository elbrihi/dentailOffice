<?php

namespace DentalOffice\MedicalRecordBundle\Domain\Aggregate;

use DentalOffice\MedicalRecordBundle\Domain\ValueObject\AgreedAmoutn;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordAgreedAmount;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordAgreedAmout;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordChiefComplaint;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordId;

class MedicalRecord{


  private function __construct(
    private MedicalRecordId $medicalRecodId,
    private MedicalRecordChiefComplaint $medicalRecodChiefComplaint,
    private MedicalRecordAgreedAmount $agreedAmount
  )
  {
    
  }
  public static function medicalRecord(
    MedicalRecordId $medicalRecodId,  
    MedicalRecordChiefComplaint $medicalRecodChiefComplaint,
    MedicalRecordAgreedAmount $agreedAmount

  ):self
  {
    return new self( $medicalRecodId,$medicalRecodChiefComplaint, $agreedAmount);     
  }

  /**
   * Get the value of medicalRecodId
   */ 
  public function getMedicalRecodId()
  {
    return $this->medicalRecodId;
  }

  
 

  /**
   * Get the value of medicalRecodChiefComplaint
   */ 
  public function getMedicalRecodChiefComplaint():MedicalRecordChiefComplaint
  {
      return $this->medicalRecodChiefComplaint;
  }

  /**
   * Get the value of agreedAmout
   */ 
  public function getAgreedAmout():MedicalRecordAgreedAmount
  {
      return $this->agreedAmount;
  }
}