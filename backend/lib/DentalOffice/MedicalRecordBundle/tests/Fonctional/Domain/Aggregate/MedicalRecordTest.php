<?php

namespace DentalOffice\MedicalRecordBundle\Fonctional\Domain\Aggregate;

use DentalOffice\MedicalRecordBundle\Domain\Aggregate\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordAgreedAmount;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordChiefComplaint;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordId;
use PHPUnit\Framework\TestCase;

class MedicalRecordTest extends TestCase
{
    public function test_medical_recod_id_in_int()
    {


        $medicalRecordData = $this->getMedicalRecordData();
        $medicalRecordId = MedicalRecordId::toInt(1);
                
        $chiefComplaint = MedicalRecordChiefComplaint::chiefComplaint($medicalRecordData['chief_complaint']);

        $amountAgreed = MedicalRecordAgreedAmount::fromNumeric($medicalRecordData['agreedAmount']);
 
        $medicalRecord = MedicalRecord::medicalRecord($medicalRecordId, $chiefComplaint,$amountAgreed);

        $this->assertInstanceOf(MedicalRecord::class, $medicalRecord);
      
        
        $this->assertEquals(1000.00,$medicalRecord-> getAgreedAmout()->getAgreedAmountValue());
       
    }

    public function getMedicalRecordData():array
    {

      return [
         "chief_complaint" => "Jane",
         "clinical_diagnosis" => "Caries profonde",
         "treatment_plan" => "Dévitalisation + composite",
         "prescriptions" => array(
            array(
             "medication" => "Metronidazole",
             "dosage" => "500 mg three times a day for 5 days",
             "notes" => "Avoid alcohol during treatment",
            ),
           ),
         "notes" => "notes tests",
         "agreedAmount" => 1000.00,
      ];

    }
}