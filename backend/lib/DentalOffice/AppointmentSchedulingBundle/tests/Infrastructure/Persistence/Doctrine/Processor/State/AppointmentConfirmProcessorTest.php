<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\tructure\Persistence\Doctrine\Processor\State ;

use ApiPlatform\Metadata\Put;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentConfirmProcessor;
use DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentProcessorTest;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use Symfony\Component\HttpFoundation\Request;

class AppointmentConfirmProcessorTest extends AppointmentProcessorTest
{

   private const CONFIRMED = 'confirmed';
     
   
   public function test_appointment_is_confirmed()
   {
      

        $this->savePatient();
        $this->saveAppointment();

        $this->entityManager->flush();
        $this->entityManager->clear();
        

        $patient = $this->entityManager->getRepository(Patient::class)
                   ->findOneBy(['cni' => 'CNI987654']);
        $patientId = $patient->getId();

        $latestAppointment = $this->entityManager
                            ->getRepository(AppointmentOrmEntity::class)
                            ->findOneBy(
                             ['patient' => $patient],

                        );
        $appointment = new AppointmentOrmEntity();
        
        $request = new Request([], [], [], [], [], [], json_encode([]));

        $context["request"] = $request ;
        
        $operation =  new Put();
        
        $uriVariables = ["appointmentId" => $latestAppointment->getId()];
        
        $processor = static::getContainer()->get(AppointmentConfirmProcessor::class);

        $processor->process($appointment,$operation, $uriVariables, $context);

        $status = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                      ->findOneBy([
                              "id" => $latestAppointment->getId()
                    ])->getStatus();

        $this->assertSame($status, self::CONFIRMED);
     

   }
}