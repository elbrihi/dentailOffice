<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Put;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentConfirmProcessor;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentRescheduledProcessor;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AppointmentRescheduledProcessorTest extends AppointmentProcessorTest
{
   private const RESCHOULDED = 'rescheduled';
     
   
     public function test_appointment_is_reschoulded_from_schedouled()
     {
          // ----------------------------------------
          // 1️⃣ Setup: save patient and initial appointment
          // ----------------------------------------
          $this->savePatient();
          $this->saveAppointment();

          $this->entityManager->flush();
          $this->entityManager->clear();

          
          $patient = $this->entityManager->getRepository(Patient::class)
                         ->findOneBy(['cni' => 'CNI987654']);
          $patientId = $patient->getId();

          $latestAppointment = $this->entityManager
                              ->getRepository(AppointmentOrmEntity::class)
                              ->findOneBy(['patient' => $patient]);
          $appointmentId = $latestAppointment->getId();

          $appointment = new AppointmentOrmEntity();

          $request = new Request([], [], [], [], [], [], json_encode([]));
          $context["request"] = $request;

          $operation = new Put();
          $uriVariables = ["appointmentId" => $appointmentId];

          $processor = static::getContainer()->get(AppointmentRescheduledProcessor::class);

          // ----------------------------------------
          // 2️⃣ First reschedule — should succeed
          // ----------------------------------------
          $processor->process($appointment, $operation, $uriVariables, $context);

          $statusAfterFirst = $this->entityManager
                                   ->getRepository(AppointmentOrmEntity::class)
                                   ->find($appointmentId)
                                   ->getStatus();

          // Assert that status is now RESCHEDULED
          //$this->assertSame(AppointmentStatus::RESCHEDULED, $statusAfterFirst);

          // ----------------------------------------
          // 3️⃣ Second reschedule — should throw RescheduleException
          // ----------------------------------------
          $this->expectException(\DentalOffice\AppointmentSchedulingBundle\Domain\Exception\RescheduleException::class);

          try {
               $processor->process(new AppointmentOrmEntity(), $operation, $uriVariables, $context);
          } catch (\DentalOffice\AppointmentSchedulingBundle\Domain\Exception\RescheduleException $e) {
               // Decode JSON from exception message
               $json = json_decode($e->getMessage(), true, 512, JSON_THROW_ON_ERROR);

               // Assert proper HTTP problem details
               $this->assertSame(409, $json['status']);
               $this->assertSame('Conflict', $json['title']);
               $this->assertStringContainsString(
                    sprintf('Cannot reschedule appointment with status "%s"', $statusAfterFirst),
                    $json['detail']
               );

               // Re-throw so PHPUnit registers the exception
               throw $e;
          }
     }

        public function test_appointment_is_reschoulded_from_confirmed()
  {
          // ----------------------------------------
          // 1️⃣ Setup: save patient and initial appointment
          // ----------------------------------------
          $this->savePatient1();
          $this->saveAppointment1();

          $this->entityManager->flush();
          $this->entityManager->clear();

          
          $patient = $this->entityManager->getRepository(Patient::class)
                         ->findOneBy(['cni' => 'CNI987654']);
          $patientId = $patient->getId();

          $latestAppointment = $this->entityManager
                              ->getRepository(AppointmentOrmEntity::class)
                              ->findOneBy(['patient' => $patient]);
          $appointmentId = $latestAppointment->getId();

          $appointment = new AppointmentOrmEntity();

          $request = new Request([], [], [], [], [], [], json_encode([]));
          $context["request"] = $request;

          $operation = new Put();
          $uriVariables = ["appointmentId" => $appointmentId];

          $processor = static::getContainer()->get(AppointmentRescheduledProcessor::class);

          // ----------------------------------------
          // 2️⃣ First reschedule — should succeed
          // ----------------------------------------
          $processor->process($appointment, $operation, $uriVariables, $context);

          $statusAfterFirst = $this->entityManager
                                   ->getRepository(AppointmentOrmEntity::class)
                                   ->find($appointmentId)
                                   ->getStatus();

          // Assert that status is now RESCHEDULED
          //$this->assertSame(AppointmentStatus::RESCHEDULED, $statusAfterFirst);

          // ----------------------------------------
          // 3️⃣ Second reschedule — should throw RescheduleException
          // ----------------------------------------
          $this->expectException(\DentalOffice\AppointmentSchedulingBundle\Domain\Exception\RescheduleException::class);

          try {
               $processor->process(new AppointmentOrmEntity(), $operation, $uriVariables, $context);
          } catch (\DentalOffice\AppointmentSchedulingBundle\Domain\Exception\RescheduleException $e) {
               // Decode JSON from exception message
               $json = json_decode($e->getMessage(), true, 512, JSON_THROW_ON_ERROR);

               // Assert proper HTTP problem details
               $this->assertSame(409, $json['status']);
               $this->assertSame('Conflict', $json['title']);
               $this->assertStringContainsString(
                    sprintf('Cannot reschedule appointment with status "%s"', $statusAfterFirst),
                    $json['detail']
               );

               // Re-throw so PHPUnit registers the exception
               throw $e;
          }
    }
     protected function savePatient1():void
     {
          $user = new User();
          $user->setUsername(static::$username);
          $user->setPassword('password');
          $user->setRoles(['ROLE_ADMIN']);
          $user->setApiToken('sample_token_value');

          // 2. Persist User
          $this->entityManager->persist($user);
          $this->entityManager->flush();

          

          
          // 4. Fetch the user from the DB
          $userFromDb = $this->entityManager
               ->getRepository(User::class)
               ->findOneBy(['username' => 'testuser']);

          // 2. Simulate authenticated user
          $tokenStorage = static::getContainer()->get('security.token_storage');

          $tokenStorage->setToken(new UsernamePasswordToken(
               $user,
               'admin',
               $user->getRoles()
          ));

          
          $patient =  new Patient();
          $birthDate = new DateTimeImmutable("1985-06-15");
          $patient->setLastName("Doe");
          $patient->setFirstName("Jane");
          $patient->setBirthDate($birthDate);
          $patient->setGender("Female");
          $patient->setCni("CNI987654");
          $patient->setPhone("123456789");
          $patient->setEmail("jane.doe@example.com");
          $patient->setAddress("42 Sunset Blvd");
          $patient->setBloodType("O+");
          $patient->setMedicalHistory("Asthma");
          $patient->setNotes("Test patient");
          $patient->setCreatedAt($this->clock->now());
          

          if (!$user instanceof \DentalOffice\UserBundle\Domain\Entity\User) {
               throw new \LogicException('Authenticated user must be an instance of DentalOffice\UserBundle\Domain\Entity\User.');
          }
          $patient->setCreatedBy($user);
          $patient->setModifiedAt($this->clock->now());
          $patient->setModifiedBy($user);
          $patient->setStatus(true);
          
          $this->entityManager->persist($patient);
          $this->entityManager->flush();
          $this->entityManager->clear();
          

     }

    protected function saveAppointment1()
    {
        $patientId = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni' => self::CNI
        ])->getId();

        $practitionerId = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'testuser'])->getId();



        $timeSlot = new TimeSlot(
                        new DateTimeImmutable("2026-03-01 09:00:00") ,
                        new DateTimeImmutable("2026-03-01 09:30:00") 
          );

        $appointmentSchoudled = Appointment::book(
               PatientId::fromInt((int) $patientId ),
               $timeSlot,
               PractitionerId::fromInt($practitionerId),
               PurposeId::fromString("Jane"),
               AppointmentStatus::scheduled()
        );
        
        $appointment = Appointment::book(
               PatientId::fromInt((int) $patientId),
               $timeSlot,
               PractitionerId::fromInt($practitionerId),
               PurposeId::fromString("Jane"),
               AppointmentStatus::confirmed($appointmentSchoudled->getStatus()->getStatus())
        );


        $patient = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'id' => $appointment->getPatientId()->toInt()
        ]);

        $user = $practitionerId = $this->entityManager
                            ->getRepository(User::class)
                            ->findOneBy([              
                        'id' => $appointment->getPractitionerId()->PractitionerId()
          ]);
        $orm = new AppointmentOrmEntity();
        $orm->setModifiedAt($this->clock->now());
        $orm->setCreatedAt( $this->clock->now());        
        $orm->setStart($appointment->getTimeSlot()->getStart());
        $orm->setEnd($appointment->getTimeSlot()->getEnd());
        $orm->setReason($appointment->getPurposeId()->getPruposeValue());
        $orm->setPatient($patient);
        $orm->setUser($user);
        $orm->setCreatedBy($user);
        $orm->setModifiedBy($user);
        $orm->setStatus($appointment->getStatus()->getStatus());

        $this->entityManager->persist($orm);
        $this->entityManager->flush();
        $this->entityManager->clear();
        
    }
}