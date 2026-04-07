<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Patch;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State\AppointmentCompletedProcessor;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AppointmentCompletedProcessorTest extends AppointmentProcessorTest
{

      private const COMPLETED = 'completed';
      public function  test_creating_medical_record_visit():void
      {
            $this->savePatient1();
            $this->saveAppointment1();

            
            $patient = $this->entityManager->getRepository(Patient::class)
                   ->findOneBy(['cni' => 'CNI987654']);
            
                   
            $patientId = $patient->getId();


            $latestAppointment = $this->entityManager
                                ->getRepository(AppointmentOrmEntity::class)
                                ->findOneBy(
                                ['patient' => $patient],
                            );
            $appointment = new AppointmentOrmEntity();

            

            $medicalRecord = [
                "chief_complaint" => "Jane",
                "clinical_diagnosis" => "Caries profonde",
                "treatment_plan" => "Dévitalisation + composite",

                "notes" => "notes tests",
                "agreedAmount" => 1000,
            ];

            $visit = [
                "notes" => "Consultation initiale + radio",
                "amount_paid" => 300,
                "remaining_due_after_visit" => 1200,
                "type" => "consultation",

                "items"=> [
                [
                    "description" => "Consultation + radio",
                    "amount"=>  500,
                
                ],
                [
                "description"=> "Extraction",
                "amount" => 1000,
                ],
                [
                "description" => "Implant",
                "amount"=> 1500,
                ]
            
                ],
                "prescriptions" => [
                    [
                        "medication" => "Metronidazole",
                        "dosage" => "500mg three times a day for 5 days",
                        "notes" => "Avoid alcohol during treatment"
                    ]
                ],
            ];
        
            $request = new Request([], [], [], [], [], [], json_encode([
                              "medicalRecord"=>$medicalRecord,
                              "visit"=>$visit
                    ])
            );


            $context["request"] = $request ;
            
            $operation =  new Patch();
            
            $uriVariables = ["appointmentId" => $latestAppointment->getId()];
            
            $processor = static::getContainer()->get(AppointmentCompletedProcessor::class);

        
            $processor->process($appointment,$operation, $uriVariables, $context);

            $status = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                        ->findOneBy([
                                "id" => $latestAppointment->getId()
                ])->getStatus();

            // 🔥 IMPORTANT: clear EM to force DB reload
            $this->entityManager->clear();

            // RE-FETCH from DB
            $updatedAppointment = $this->entityManager
                ->getRepository(AppointmentOrmEntity::class)
                ->find($latestAppointment->getId());

            // ASSERT ✅
            $this->assertEquals('completed', $updatedAppointment->getStatus());
                                    
      }
     
      public function  test_updating_medicalRecord_payment_and_invoice():void
      {

          // Given
          // patient already has medical record

          // When
          // appointment completed

          // Then
          // visit added to existing medical record

          // payment created
          // invoice created
     
      }

    private function savePatient1():void
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

    private function saveAppointment1()
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

    private function saveMedicalRecord()
    {
        
    }
}