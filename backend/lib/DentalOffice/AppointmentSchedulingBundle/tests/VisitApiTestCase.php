<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPostStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPutStateProcessor;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class VisitApiTestCase extends ApiTestCase
{
    protected EntityManagerInterface $entityManager ;
    protected ClockInterface $clock;
    protected VisitPostStateProcessor $visitPostStateProcessor ;
    protected VisitPutStateProcessor $visitPutStateProcessor ;
    protected static string $username = "testuser";
    protected static int $medicalRecordId ;
    protected static int $visitId;
    private MedicalRecord $medicalRecord;
    private User $user;
    
    protected function setUp():void 
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->visitPostStateProcessor = $container->get(VisitPostStateProcessor::class);
        $this->visitPutStateProcessor = $container->get(VisitPutStateProcessor::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $visits = $this->entityManager->getRepository(Visit::class)->findAll();
        foreach ($visits as $visit) {
            $this->entityManager->remove($visit);
        }
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

    protected function saveMedicalRecord()
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
    
        $this->user = $userFromDb = $this->entityManager
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

        $patientId =  $patientByCni->getId();

        
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


        $appointment = $this->entityManager->getRepository(Appointment::class)->findOneBy([
            'createdAt' => $createdAt
        ]);

        $prescriptions = [
            "medication" => "Metronidazole",
            "dosage" => "500mg three times a day for 5 days",
            "notes" => "Avoid alcohol during treatment"
        ];
        $medicalRecord = new MedicalRecord();

        $visitDate = new DateTimeImmutable("2025-02-12");
        $followUpDate = new DateTimeImmutable("2025-02-12");
        $medicalRecord->setVisitDate($visitDate);
     
        $medicalRecord->setChiefComplaint("Jane");
 

        $medicalRecord->setClinicalDiagnosis("Caries profonde");
      
        $medicalRecord->setTreatmentPlan("Dévitalisation + composite");

        $medicalRecord->setPrescriptions( $prescriptions);     
      
        $medicalRecord->setNotes("notes tests");

        $medicalRecord->setFollowUpDate($followUpDate);
        
        $medicalRecord->setPatient($patient);
 
        $medicalRecord->setCreatedAt($createdAt);
        $medicalRecord->setCreatedBy($userFromDb);
        $medicalRecord->setModifiedAt($createdAt);
        $medicalRecord->setModifiedBy($userFromDb);

        $medicalRecord->setAgreedAmout(1000);
        $medicalRecord->setTotalPaid(0);
        $medicalRecord->setRemainingDue(1000);
        $medicalRecord->setAppointment($appointment);

        $this->entityManager->persist($medicalRecord);
        $this->entityManager->flush();

        $this->medicalRecord = $this->entityManager->getRepository(MedicalRecord::class)->findOneBy(
            [
                'createdAt' => $createdAt
            ]
            );
        static::$medicalRecordId = $this->entityManager->getRepository(MedicalRecord::class)->findOneBy(
            [
                'createdAt' => $createdAt
            ]
        )->getId();

    }
    function saveVisits()
    {
        $visit =  new Visit();

        $visitDate =  new DateTimeImmutable("2025-02-12");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Consultation initiale + radio");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate1 =  new DateTimeImmutable("2025-02-19");
        $visit->setVisitDate($visitDate1);
        $visit->setNotes("Dévitalisation");
        $visit->setAmountPaid(400);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-03-01");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        static::$visitId = $this->entityManager->getRepository(Visit::class)->findOneBy([
            "visitDate" => $visitDate1
        ])->getId();
        

    }
}