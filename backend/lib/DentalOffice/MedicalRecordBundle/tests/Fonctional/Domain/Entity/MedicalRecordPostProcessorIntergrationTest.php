<?php

namespace DentalOffice\MedicalRecordBundle\Tests\Fonctional\Domain\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Psr\Clock\ClockInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class MedicalRecordPostProcessorIntergrationTest extends  ApiTestCase
{
    
    private EntityManagerInterface $entityManager;
    public static string $username = "testuser";
    public static int $patientId ;
    public static int $appointmentId ;
    private ClockInterface $clock;

    protected function setUp(): void{

       $container=static::getContainer();

       $this->entityManager=$container->get(EntityManagerInterface::class);
       $this->clock = $container->get(ClockInterface::class);


       
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
    public function testApiMedicalRecordMEdicalRecordPerssist()
    {
        $this->saveAppointment();

        $client = static::createClient();
            
        // 1. Create and persist the user
        $user = new User();
        $user->setUsername("admin");
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken('sample_token_value');
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    
        // Make POST request to the custom route
        $patientId = static::$patientId;
        $appointmentId = static::$appointmentId;
        $client->request('POST', "/api/create/patient/{$patientId }/sppointment/{$appointmentId}/medicalRecords", [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                "visit_date" => "2025-02-12",
                "chief_complaint" => "Jane",
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
                "totalPaid" => 700,
                "remainingDue" => 300,
            ],
        ]);
        
    }


    public function saveAppointment(): void
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
        $patient->setCreatedBy($userFromDb);
        $patient->setModifiedAt( $createdAt);
        $patient->setModifiedBy($userFromDb);
        $patient->setStatus(true);
    
        $this->entityManager->persist($patient);
        $this->entityManager->flush();

        $patientByCni = $this->entityManager->getRepository(Patient::class)
        ->findOneBy([
            'cni' =>'CNI987654'
        ]);

        static::$patientId =  $patientByCni->getId();

        
        $appointment  = new Appointment();
        $appointmentDate =  new DateTimeImmutable("2025-02-12T09:00:00");
        $appointment->setReason("Reason test");
        $appointment->setAppointmentDate($appointmentDate);
        $appointment->setModifiedAt( $createdAt);
        $appointment->setCreatedAt( $createdAt);
        $appointment->setCreatedBy($userFromDb );
        $appointment->setModifiedBy($userFromDb );
        $appointment->setUser($userFromDb);
        $appointment->setStatus(true);
        $appointment->setPatient($patientByCni);

        $this->entityManager->persist( $appointment);
        $this->entityManager->flush();


        static::$appointmentId = $this->entityManager->getRepository(Appointment::class)->findOneBy([
            'createdAt' => $createdAt
        ])->getId();

  
    }
}