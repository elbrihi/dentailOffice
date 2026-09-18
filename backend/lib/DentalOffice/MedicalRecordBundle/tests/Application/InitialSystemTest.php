<?php

namespace DentalOffice\MedicalRecordBundle\Tests\Application;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\InvoiceBundle\Application\Subscriber\InvoiceCreatedOnMedicalRecord;
use DentalOffice\MedicalRecordBundle\Application\EventSubscriber\MedicalRecordCreatedOnVisitSubscriber;
use DentalOffice\MedicalRecordBundle\Domain\Aggregate\Step;
use DentalOffice\MedicalRecordBundle\Domain\Aggregate\Treatment;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\StepStatus;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\StepOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\TreatmentOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class InitialSystemTest extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ClockInterface $clock;
    protected const CNI = 'CNI987654';
    protected $container;
    
    public static string $username = "testuser";
    
    protected static $medicalRecordId ='';

    protected static int $visitId = 0 ;
    protected MedicalRecordOrmEntity $medicalRecord;

    protected User $user;

    protected AppointmentOrmEntity $appointment;

    protected MedicalRecordCreatedOnVisitSubscriber $createdMedicalRecord;

    protected InvoiceCreatedOnMedicalRecord $invoiceOnMedicalRecord;

    protected Patient $patient;
   

    protected function setUp(): void
    {
        parent::setUp();

        $this->createdMedicalRecord = static::getContainer()
            ->get(MedicalRecordCreatedOnVisitSubscriber::class);
                self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here

        $this->entityManager->createQuery('DELETE FROM DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceItemOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\StepOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\TreatmentOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\PatientBundle\Domain\Entity\Patient')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\UserBundle\Domain\Entity\User')->execute();

        $this->entityManager->flush();
    }


    public function test_intialing_system()
    {
        
        $this->saveUser();
        $this->savePatient();
        $this->saveAppointment();
        $this->saveMedicalRecord();
        $this->saveTreatment();
        $this->saveVisit();
        $this->begingTreatment();
        $this->savePrescription();
    
        $this->assertNotNull($this->user);
        $this->assertNotNull($this->patient);
        $this->assertNotNull($this->appointment);
        

    }



    
    protected function saveUser()
    {
        $user = new User();
        $user->setUsername(static::$username);
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken('sample_token_value');

        $this->entityManager->persist($user);

        $this->user = $user;

        $tokenStorage = static::getContainer()->get('security.token_storage');
        $tokenStorage->setToken(new UsernamePasswordToken(
            $user,
            'admin',
            $user->getRoles()
        ));
    }
    protected function savePatient():void
    {


        
        $patient =  new Patient();
        $birthDate = new DateTimeImmutable("1985-06-15");
        $patient->setLastName("Doe");
        $patient->setFirstName("Jane");
        $patient->setBirthDate($birthDate);
        $patient->setGender("Female");
        $patient->setCni(self::CNI);
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
        $this->patient = $patient;
        

    }

    protected function saveAppointment()
    {
       
        $patientId =$this->patient->getId();

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
        


        $this->appointment = $orm;
        
    }

    protected function saveMedicalRecord()
    {

        // ❗ Check if the patient already exists (avoid duplication)
        $existingPatient = $this->entityManager
            ->getRepository(Patient::class)
            ->findOneBy(['cni' => self::CNI]);
    
       
        // dd( $existingPatient);
       
         $createdAt = $this->clock->now();
        
 
        $patientByCni = $this->entityManager->getRepository(Patient::class)
        ->findOneBy([
            'cni' =>'CNI987654'
        ]);

        $patientId =  $patientByCni->getId();

       
        $prescriptionInput = $this->payload()[0]['visit']['prescriptions'];

    
       


        $medicalRecord = new MedicalRecordOrmEntity();

        $visitDate = new DateTimeImmutable("2025-02-12");
        $followUpDate = new DateTimeImmutable("2025-02-12");
     
        $medicalRecord->setChiefComplaint("Jane");
 

        $medicalRecord->setClinicalDiagnosis("Caries profonde");
      
        //$medicalRecord->setTreatmentPlan("Dévitalisation + composite");

      
        $medicalRecord->setNotes("notes tests");

        
        $medicalRecord->setPatient($existingPatient );
 
        $medicalRecord->setCreatedAt( $createdAt);
        $medicalRecord->setCreatedBy($this->user);
        $medicalRecord->setModifiedAt( $createdAt );
        $medicalRecord->setModifiedBy($this->user);

        $medicalRecord->setAgreedAmount(1000);
        $medicalRecord->setTotalPaid(0);
        $medicalRecord->setRemainingDue(1000);
        $medicalRecord->setUser($this->user);
       // $medicalRecord->setAppointment($appointment);
        $this->entityManager->persist($medicalRecord);
        $this->entityManager->flush();
        static::$medicalRecordId = $medicalRecord->getId();
        // visit 

    }

    protected function saveVisit()
    {
        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
        ->findOneBy([
            'id' => static::$medicalRecordId
        ]);
        $visitInpout = $this->payload()[0]["visit"];
        $createdAt = $this->clock->now();
                $visitOrmEntity = new VisitOrmEntity();

        //  $visit = Visit::createVisit(
        //     VisitAmountPaid::fromFloatPositive($visitInpout["amount_paid"]),
        // );

        $visitOrmEntity->setCreatedAt( $createdAt);
        $visitOrmEntity->setModifiedAt( $createdAt);
      //  $visitOrmEntity->setAmountPaid($visit->getVisitAmountPaid()->getAmountPaid());
        $visitOrmEntity->setNotes($visitInpout ["notes"]);
        $visitOrmEntity->setCreatedAt($createdAt);
        $visitOrmEntity->setCreatedBy($this->user);
        $visitOrmEntity->setNotes($visitInpout["notes"]);
        $visitOrmEntity->setModifiedBy($this->user);
        $visitOrmEntity->setMedicalRecord($medicalRecord );
        $visitOrmEntity->setStart($this->appointment->getStart());
        $visitOrmEntity->setEnd($this->appointment->getEnd());
        $visitOrmEntity->setModifiedBy($this->user);
        $visitOrmEntity->setStatus($this->appointment->getStatus());
        $visitOrmEntity->setType($visitInpout["type"]);
        $visitOrmEntity->setAppointment($this->appointment);
        
       
        $this->entityManager->persist($visitOrmEntity);
      
        $this->entityManager->flush();

        self::$visitId = $visitOrmEntity->getId();
    }
   
    protected function savePrescription(){

        $visit = $this->entityManager->getRepository(VisitOrmEntity::class)
                        ->findOneBy([
                            'id' => self::$visitId
                        ]);

        $prescription = new PrescriptionOrmEntity();

        $prescriptions = $this->payload()[0]['visit']['prescriptions'];

        $prescription->setDosage($prescriptions[0]['dosage']);
        $prescription->setMedication($prescriptions[0]['medication']);
        $prescription->setNotes($prescriptions[0]['notes']);
        $prescription->setVisitOrmEntity($visit );

        $this->entityManager->persist($prescription);
        $this->entityManager->flush();

    }

    protected function saveTreatment()
    {
        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
                        ->findOneBy([
                            'id' => self::$medicalRecordId
                        ]);

        $treatments = $this->payload()[0]['medicalRecord']['treatments'];
        
        
    
        $treatmentData=[];
        foreach($treatments as $treatment){

            $treatmentPlan = Treatment::createTreatmentPlan(0,$treatment['description'],$treatment['type']);
            
            foreach($treatment['steps'] as $step){
                $step = Step::createStep(
                    0,
                    $step['name'],
                    $step['amount'],
                    0,
                    StepStatus:: planned(),
                    $this->clock->now()
                );

                $treatmentPlan->addStep($step);
               
               
            }
            $treatmentData[]=$treatmentPlan;

                   
        }
         

     
     foreach ($treatmentData as $treatment) {

            $treatmentOrmEntity = new TreatmentOrmEntity();
            $treatmentOrmEntity->setType($treatment->getType());
            $treatmentOrmEntity->setDescription($treatment->getDescription());
            $treatmentOrmEntity->setStatus('planned');
            $treatmentOrmEntity->setMedicalRecordOrmEntity($medicalRecord);

            foreach ($treatment->getSteps() as $step) {

                $stepOrmEntity = new StepOrmEntity();
                $stepOrmEntity->setName($step->getName());
                $stepOrmEntity->setStatus($step->getStatus()->getStatus());
                $stepOrmEntity->setUser($this->user);
                $stepOrmEntity->setAmount($step->getAmount());
               // $stepOrmEntity->setPerformedAt($step->getPerformedAt());

                $treatmentOrmEntity->addStep($stepOrmEntity); // ✅ ONLY THIS
            }

            $medicalRecord->addTreatment($treatmentOrmEntity);
        }

        $this->entityManager->persist($medicalRecord);
        $this->entityManager->flush();
        
        
    }

    protected function changeTreatmentSepStatus()
    {     
        // planned to in sheduled  at the begining       
    }

    protected function begingTreatment(){
       
        // 
        $treatment = $this->entityManager->getRepository(TreatmentOrmEntity::class)
                        ->findAll()[0];
                     
       
        

        //$this->entityManager->persist($treatment);
        //$this->entityManager->flush();

    
    }

    protected function payload()
    {
        $medicalRecord = [
            "chief_complaint" => "Jane",
            "clinical_diagnosis" => "Caries profonde",
            "notes" => "notes tests",
            "agreedAmount" => 1000,
            "treatments" => [
                [
                    "type" => "ROOT_CANAL",
                    "description" => "Dévitalisation prémolaire",
                   // "status" => "planned",
                    "steps" => [
                        [
                            "name" => "Consultation + diagnostic",
                            "amount" => 300,
                            "status" => "completed",
                        ],
                        [
                            "name" => "Nettoyage + obturation canal",
                            "amount" => 400,
                            "status" => "planned",
                        ],
                    ],
                ],
                [
                    "type" => "COMPOSITE",
                    "description" => "Composite final",
                    "status" => "planned",
                    "steps" => [
                        [
                            "name" => "Pose composite",
                            "amount" => 300,
                            "status" => "planned",
                        ],
                    ],
                ],
            ],
        ];

        $visit = [
            "appointment_id" => 101,
            "type"=> "CONSULTATION",
            "visit_date" => "2025-02-12",
            "notes" => "Consultation initiale + diagnostic",

            "prescriptions" => [
                [
                    "medication" => "Metronidazole",
                    "dosage" => "500mg three times a day for 5 days",
                    "notes" => "Avoid alcohol during treatment"
                ],
                
            ],

            "items" => [
                [
                    "code" => "CONSULTATION",
                    "description" => "Consultation initiale",
                    "amount" => 300,
                    "treatment_index" => 0,
                    "step_index" => 0,
                    "status" => "completed",
                ],
                [
                    "code" => "ROOT_CANAL",
                    "description" => "Dévitalisation",
                    "amount" => 400,
                    "treatment_index" => 0,
                    "step_index" => 1,
                    "status" => "planned",
                ],
                [
                    "code" => "COMPOSITE",
                    "description" => "Composite final",
                    "amount" => 300,
                    "treatment_index" => 1,
                    "step_index" => 0,
                    "status" => "planned",
                ],
            ],
          
        ];

        return [
            [
                "medicalRecord" => $medicalRecord,
                "visit" => $visit,
            ],
            $this->user
        ];
    }
}




?>