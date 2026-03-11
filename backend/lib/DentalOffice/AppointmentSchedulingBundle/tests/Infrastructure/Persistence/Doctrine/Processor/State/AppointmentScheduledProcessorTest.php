<?php
namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\AppointmentConflictDetected;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\AppointmentConflictException;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentScheduledProcessor;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use Symfony\Component\HttpFoundation\Request;

class AppointmentScheduledProcessorTest extends AppointmentProcessorTest
{ 


    public function test_it_has_an_appointment_during_this_time_slot(): void
    {

   
        $this->savePatient();
        
        $this->saveAppointment();

        $appointment = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                ->findOneBy(
                    [
                        'start' => new DateTimeImmutable('2026-03-01 09:00:00'),

                    ]
        );

        $patientId = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni' => self::CNI
        ])->getId();

                    
        $request = new Request([], [], [], [], [], [], json_encode([
            "start" => "2026-03-01 09:10:00", 
            "end" => "2026-03-01 09:20:00",
            "purpose" => "Jane",
            "status" => true,
        ]));
       
        $processor = static::getContainer()->get(AppointmentScheduledProcessor::class);

        $appointment = new AppointmentOrmEntity();

        $operation = new Post();

        $uriVariables["patientId"] =  $patientId;

        $context = ["request"=> $request];
       

        try {
            $processor->process($appointment, $operation,$uriVariables, $context );
            $this->fail('Expected AppointmentConflictException was not thrown.');
        } catch (AppointmentConflictException $e) {

            $json = json_decode($e->getMessage(), true);

            $this->assertArrayHasKey('type', $json);
            $this->assertSame(409, $json['status']);
            $this->assertStringContainsString('already has an appointment', $json['detail']);
        }
    
    }
    public function test_it_has_an_not_appointment_during_this_time_slot(): void
    {

   
        $this->savePatient();
        
        $this->saveAppointment();

        $appointment = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                ->findOneBy(
                    [
                        'start' => new DateTimeImmutable('2026-03-01 09:00:00'),

                    ]
        );

        $patientId = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni' => self::CNI
        ])->getId();

                    
        $request = new Request([], [], [], [], [], [], json_encode([
            "start" => "2026-03-01 10:31:00", 
            "end" => "2026-03-01 10:40:00",
            "purpose" => "Jane",
            "status" => true,
        ]));
       
        $processor = static::getContainer()->get(AppointmentScheduledProcessor::class);

        $appointment = new AppointmentOrmEntity();

        $operation = new Post();

        $uriVariables["patientId"] =  $patientId;

        $context = ["request"=> $request];

       
        $appointment = $processor->process($appointment, $operation,$uriVariables, $context );
       
        $this->assertNotNull($appointment);

        $this->assertSame($appointment->getStatus(),"scheduled");

       
    }

    public function test_first_schdouled_appointment()
    {
        $this->savePatient();
        
        $appointment = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                ->findOneBy(
                    [
                        'start' => new DateTimeImmutable('2026-03-01 09:00:00'),

                    ]
        );

        $patientId = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni' => self::CNI
        ])->getId();

                    
        $request = new Request([], [], [], [], [], [], json_encode([
            "start" => "2026-03-01 10:31:00", 
            "end" => "2026-03-01 10:40:00",
            "purpose" => "Jane",
            "status" => true,
        ]));
       
        $processor = static::getContainer()->get(AppointmentScheduledProcessor::class);

        $appointment = new AppointmentOrmEntity();

        $operation = new Post();

        $uriVariables["patientId"] =  $patientId;

        $context = ["request"=> $request];

       
        $appointment = $processor->process($appointment, $operation,$uriVariables, $context );
      
    }
       
    private function getPatientId(): int
    {
        $patient = static::getContainer()->get('doctrine')
            ->getRepository(Patient::class)
            ->findOneBy(['cni' => 'CNI987654']);

        return $patient->getId();
    }

}