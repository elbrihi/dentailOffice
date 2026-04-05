<?php


namespace DentalOffice\MedicalRecordBundle\Application\EvenSubscriber;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\Event\AppointmentCompleted;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentProcessorTest;
use DentalOffice\MedicalRecordBundle\Application\EventSubscriber\MedicalRecordCreatedSubscriber;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MedicalRecordCreatedSubscriberTest extends  AppointmentProcessorTest
{

    private static $medicalRecordId ='';

    private MedicalRecordOrmEntity $medicalRecord;

    private User $user;

    private AppointmentOrmEntity $appointment;

    private MedicalRecordCreatedSubscriber $createdMedicalRecord;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createdMedicalRecord = static::getContainer()
            ->get(MedicalRecordCreatedSubscriber::class);
    }

    public function test_intiale_meical_record()
    {
        
        $this->saveUser();
        $this->savePatient1();
        $this->saveAppointment1();
 
        
        $appointmentComplted = AppointmentCompleted::fromData(
               0,
                $this->appointment->getId(),
                $this->patient->getId(),
                $this->user->getId(),
                $this->appointment->getEnd(),
                [],
                $this->payload()


        );

       
       $this->createdMedicalRecord->onAppointmentCompleted($appointmentComplted );


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


     private function payload()
    {
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

        return [["medicalRecord"=>$medicalRecord,"visit"=>$visit], $this->user ];
    }


}