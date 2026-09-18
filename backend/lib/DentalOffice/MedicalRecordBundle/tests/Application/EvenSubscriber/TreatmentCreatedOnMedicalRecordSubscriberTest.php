<?php

namespace DentalOffice\MedicalRecordBundle\tests\Application\EvenSubscriber;

use DentalOffice\MedicalRecordBundle\Application\EventSubscriber\MedicalRecordCreatedOnVisitSubscriber;
use DentalOffice\MedicalRecordBundle\Application\EventSubscriber\TreatmentCreatedOnMedicalRecordSubscriber;
use DentalOffice\MedicalRecordBundle\Domain\Event\TreatmentCreatedOnMedicalRecord;
use DentalOffice\MedicalRecordBundle\Tests\Application\InitialSystemTest;

class TreatmentCreatedOnMedicalRecordSubscriberTest extends InitialSystemTest
{
    private TreatmentCreatedOnMedicalRecordSubscriber $treatmentCreatedOnMedicalRecord;

    public function test_treatment_is_created_on_medical_record_created(): void
    {
       
        $this->saveUser();
        $this->savePatient();
        $this->saveAppointment();
        $this->saveMedicalRecord();
        $this->prescription();

        $this->treatmentCreatedOnMedicalRecord = static::getContainer()
            ->get(TreatmentCreatedOnMedicalRecordSubscriber::class);
     
        $treatmentCreatedOnMedicalRecord = TreatmentCreatedOnMedicalRecord::treatmentData(
            [],
            $this->user->getId(),
            $this->appointment->getId(),
            $this->payload(),
            self::$medicalRecordId
        );
        
        $this->treatmentCreatedOnMedicalRecord->onTreatmentCreatedOnMedicalRecord($treatmentCreatedOnMedicalRecord);



    }
}