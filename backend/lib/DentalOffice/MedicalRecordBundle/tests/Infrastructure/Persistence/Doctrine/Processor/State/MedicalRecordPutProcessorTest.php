<?php


namespace DentalOffice\MedicalRecordBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Processor\State\MedicalRecordPutProcessor;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MedicalRecordPutProcessorTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    public static string $username = "testuser";
    public static int $medicalRecordId ;
    private ClockInterface $clock;
    private  MedicalRecordPutProcessor $medicalRecordPutProcessor;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->medicalRecordPutProcessor = $container->get(MedicalRecordPutProcessor::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $medicalRecords = $this->entityManager->getRepository(MedicalRecord::class)->findAll();
        foreach ($medicalRecords as $medicalRecord) {
            $this->entityManager->remove($medicalRecord);
        }
        // Step 1: Remove Appointments
        $appointments = $this->entityManager->getRepository(Appointment::class)->findAll();
        foreach ($appointments as $appointment) {
            $this->entityManager->remove($appointment);
        }

        // Step 2: Remove Patients
        $patients = $this->entityManager->getRepository(Patient::class)->findAll();
        foreach ($patients as $patient) {
            $this->entityManager->remove($patient);
        }

        // Step 3: Remove Users
        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            $this->entityManager->remove($user);
        }

        // Step 4: Apply the deletions
        $this->entityManager->flush();

    }    
    public function testProcessPersistsMedicalRecord(): void
    {
        $this->savePatient();
    
        $request = new Request([], [], [], [], [], [], json_encode([
            "visit_date" => "2025-02-12",
            "chief_complaint" => "Jane update updaate",
            "clinical_diagnosis" => "Caries profonde",
            "treatment_plan" => "Dévitalisation + composite",
            "prescriptions" => [
                [
                    "medication" => "Metronidazole",
                    "dosage" => "500mg three times a day for 5 days",
                    "notes" => "Avoid alcohol during treatment"
                ]
            ],
            "follow_up_date" => "2025-02-12",
            "notes" => "notes tests",
            "agreedAmout" => 1000,
    
        ]));
    
        $operation = new Put();
        $context = ["request" => $request];
        $uriVariables["id"] = static::$medicalRecordId;
    
        $existingRecord = $this->entityManager
            ->getRepository(MedicalRecord::class)
            ->find(static::$medicalRecordId);
    
        $this->medicalRecordPutProcessor->process($existingRecord, $operation, $uriVariables, $context);
    
        $record = $this->entityManager->getRepository(MedicalRecord::class)->find(static::$medicalRecordId);
        $this->assertEquals('Jane update updaate', $record->getChiefComplaint());
    }
    

    public function savePatient(): void 
    {
        $username = static::$username;
    
        // 1. Create and persist the user
        $user = new User();
        $user->setUsername($username);
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken('sample_token_value');
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    
        $this->entityManager->clear();
    
        $userFromDb = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);
    
        if (!$userFromDb instanceof \DentalOffice\UserBundle\Domain\Entity\User) {
            throw new \LogicException('Authenticated user must be an instance of DentalOffice\UserBundle\Domain\Entity\User.');
        }
    
        // Authenticate user
        $tokenStorage = static::getContainer()->get('security.token_storage');
        $tokenStorage->setToken(new UsernamePasswordToken(
            $userFromDb,
            'admin',
            $userFromDb->getRoles()
        ));
    
        // ❗ Check if the patient already exists (avoid duplication)
        $existingPatient = $this->entityManager
            ->getRepository(Patient::class)
            ->findOneBy(['cni' => 'CNI987654']);
    
        if ($existingPatient) {
            return; // Skip saving again
        }

    
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
        $patient->setCreatedAt($this->clock->now());
        $patient->setCreatedBy($userFromDb);
        $patient->setModifiedAt($this->clock->now());
        $patient->setModifiedBy($userFromDb);
        $patient->setStatus(true);
    
        $this->entityManager->persist($patient);
        $this->entityManager->flush();

        $patientObject = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni'=>"CNI987654"
        ]);


        $medicalRecord = new MedicalRecord();

        $visitDate = new DateTimeImmutable("2025-02-12");
        $followUpDate = new DateTimeImmutable("2025-05-12");
        $modifiedAt = $this->clock->now();

      
        $prescriptions = array(            
            "medication" => "Metronidazole",
            "dosage" => "500mg three times a day for 5 days",
            "notes" => "Avoid alcohol during treatment"
        );

        $medicalRecord->setVisitDate($visitDate);
        $medicalRecord->setChiefComplaint("Jane");
        $medicalRecord->setClinicalDiagnosis("Caries profonde");
        $medicalRecord->setTreatmentPlan( "Dévitalisation + composite");
        $medicalRecord->setPrescriptions($prescriptions);
        $medicalRecord->setNotes("notes tests");
        $medicalRecord->setFollowUpDate($followUpDate );
        $medicalRecord->setModifiedBy($userFromDb);
        $medicalRecord->setModifiedAt($modifiedAt);
        $medicalRecord->setCreatedBy($userFromDb);
        $medicalRecord->setCreatedAt($this->clock->now());
        $medicalRecord->setAgreedAmout(1000);
        $medicalRecord->setTotalPaid(700);
        $medicalRecord->setRemainingDue(300);       
        $medicalRecord->setPatient($patientObject);
       
        $this->entityManager->persist($medicalRecord);
        $this->entityManager->flush();

        static::$medicalRecordId =  $this->entityManager->getRepository(MedicalRecord::class)->findOneBy([
            'modifiedAt' =>  $modifiedAt
        ])->getId();

    }
    
}