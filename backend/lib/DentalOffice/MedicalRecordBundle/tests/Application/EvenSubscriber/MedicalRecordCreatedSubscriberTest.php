<?php


namespace DentalOffice\MedicalRecordBundle\Application\EvenSubscriber;

use DentalOffice\AppointmentSchedulingBundle\Domain\Event\AppointmentCompleted;
use DentalOffice\MedicalRecordBundle\Application\EventSubscriber\MedicalRecordCreatedSubscriber;
use DentalOffice\MedicalRecordBundle\Tests\Application\InitialSystemTest;


class MedicalRecordCreatedSubscriberTest extends  InitialSystemTest
{


    protected function setUp(): void
    {
        parent::setUp();


    }

    public function test_intiale_meical_record()
    {
        
        $this->saveUser();
        $this->savePatient();
        $this->saveAppointment();
 
        
        $appointmentComplted = AppointmentCompleted::fromData(
               0,
                $this->appointment->getId(),
                $this->patient->getId(),
                $this->user->getId(),
                $this->appointment->getEnd(),
                [],
                $this->payload()


        );
        $createdMedicalRecord = static::getContainer()
            ->get(MedicalRecordCreatedSubscriber::class);
       
       $createdMedicalRecord->onAppointmentCompleted($appointmentComplted );


    }


}