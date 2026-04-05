<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Application\EvenSubscriber;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentProcessorTest;
use DentalOffice\MedicalRecordBundle\Application\EventSubscriber\MedicalRecordCreatedOnVisitSubscriber;
use DentalOffice\MedicalRecordBundle\Domain\Event\MedicalRecordCreated;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CreatedMedicalRecordOnVisitSubscriberTest extends  AppointmentProcessorTest
{

    private static $medicalRecordId ='';

    private MedicalRecordOrmEntity $medicalRecord;

    private User $user;

    private AppointmentOrmEntity $appointment;

    private MedicalRecordCreatedOnVisitSubscriber $createdMedicalRecord;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createdMedicalRecord = static::getContainer()
            ->get(MedicalRecordCreatedOnVisitSubscriber::class);
    }

    public function test_entring_visit_id_db()
    {
        $this->saveUser();
        $this->savePatient1();
        $this->saveAppointment1();
        $this->saveMedicalRecord();


        $payload = $this->payload();
        $event = MedicalRecordCreated::medicalRecordData
        (
         $this->medicalRecord->getId(),
         $this->patient->getId(),
         $this->user->getId(),
         $this->appointment->getId(),
         $payload 
        );

        $this->createdMedicalRecord->createVisit($event);
         
                // Assert
        $visit = $this->entityManager
            ->getRepository(VisitOrmEntity::class)
            ->findOneBy([
                'medicalRecord' => $this->medicalRecord->getId()
            ]);

        $this->assertNotNull($visit, 'Visit should be created');

        $this->assertEquals(
            $payload["request"]["visit"]["amount_paid"],
            $visit->getAmountPaid()
        );

        $this->assertEquals(
            $payload["request"]["visit"]["notes"],
            $visit->getNotes()
        );

        $this->assertEquals(
            $payload["request"]["visit"]["type"],
            $visit->getType()
        );

        $this->assertEquals(
            $this->appointment->getId(),
            $visit->getAppointment()->getId()
        );
        
    }

    private function saveUser()
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

        $this->user = $userFromDb ;
    }
    private function savePatient1():void
    {


        
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
        

        if (!$this->user instanceof \DentalOffice\UserBundle\Domain\Entity\User) {
            throw new \LogicException('Authenticated user must be an instance of DentalOffice\UserBundle\Domain\Entity\User.');
        }
        $patient->setCreatedBy($this->user);
        $patient->setModifiedAt($this->clock->now());
        $patient->setModifiedBy($this->user);
        $patient->setStatus(true);
      
        $this->entityManager->persist($patient);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->patient = $patient;
        

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


        $this->appointment = $orm;
        
    }

    private function saveMedicalRecord()
    {

        // ❗ Check if the patient already exists (avoid duplication)
        $existingPatient = $this->entityManager
            ->getRepository(Patient::class)
            ->findOneBy(['cni' => 'CNI987654']);
    
       
    
       
        $createdAt = $this->clock->now();
        // Create and persist patient
        $patient = new Patient();
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
        $patient->setCreatedAt( $createdAt);
        $patient->setCreatedBy($this->user);
        $patient->setModifiedAt( $createdAt);

        $patient->setModifiedBy($this->user);
        $patient->setStatus(true);
    
        $this->entityManager->persist($patient);
        $this->entityManager->flush();
 
        $patientByCni = $this->entityManager->getRepository(Patient::class)
        ->findOneBy([
            'cni' =>'CNI987654'
        ]);

        $patientId =  $patientByCni->getId();


        $medicalRecord = new MedicalRecordOrmEntity();

        $visitDate = new DateTimeImmutable("2025-02-12");
        $followUpDate = new DateTimeImmutable("2025-02-12");
     
        $medicalRecord->setChiefComplaint("Jane");
 

        $medicalRecord->setClinicalDiagnosis("Caries profonde");
      
        $medicalRecord->setTreatmentPlan("Dévitalisation + composite");

      
        $medicalRecord->setNotes("notes tests");

        
        $medicalRecord->setPatient($patient);
 
        $medicalRecord->setCreatedAt($createdAt);
        $medicalRecord->setCreatedBy($this->user);
        $medicalRecord->setModifiedAt($createdAt);
        $medicalRecord->setModifiedBy($this->user);

        $medicalRecord->setAgreedAmount(1000);
        $medicalRecord->setTotalPaid(0);
        $medicalRecord->setRemainingDue(1000);
        $medicalRecord->setUser($this->user);
       // $medicalRecord->setAppointment($appointment);
        $this->entityManager->persist($medicalRecord);
        $this->entityManager->flush();

        $this->medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)->findOneBy(
            [
                'createdAt' => $createdAt
            ]
            );
        static::$medicalRecordId = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)->findOneBy(
            [
                'createdAt' => $createdAt
            ]
        )->getId();

    }

    private function payload()
    {
        $medicalRecord = [
            "chief_complaint" => "Jane",
            "clinical_diagnosis" => "Caries profonde",
            "treatment_plan" => "Dévitalisation + composite",

            "notes" => "notes tests",
            "agreedAmout" => 1000,
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

        return ["request"=>["medicalRecord"=>$medicalRecord,"visit"=>$visit] ];
    }
}