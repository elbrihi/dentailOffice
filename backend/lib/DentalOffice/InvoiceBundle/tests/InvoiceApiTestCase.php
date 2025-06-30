<?php

namespace DentalOffice\InvoiceBundle\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitDeleteProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPostStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\VisitPutStateProcessor;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Provider\State\VisitsGetCollectionProvider; // ✅ Correct
use DentalOffice\InvoiceBundle\Domain\Entity\Invoice;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Provider\State\InvoicesGetCollectionProvider;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\PaymentsBundle\Domain\Entity\Payment;
use DentalOffice\PaymentsBundle\Domain\Repository\PaymentRepository;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class InvoiceApiTestCase extends ApiTestCase
{
    protected EntityManagerInterface $entityManager ;
    protected ClockInterface $clock;
    protected VisitPostStateProcessor $visitPostStateProcessor ;
    protected VisitPutStateProcessor $visitPutStateProcessor ;
    protected VisitDeleteProcessor $visitDeleteStateProcessor ;
    protected VisitsGetCollectionProvider $visitsGetCollectionProvider;
    protected EventDispatcherInterface $dispatcher;
    protected PaymentRepository $paymentRepository;
    protected InvoicesGetCollectionProvider $invoicesGetCollection;
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
        $this->visitDeleteStateProcessor = $container->get(VisitDeleteProcessor::class);
        $this->visitsGetCollectionProvider = $container->get(VisitsGetCollectionProvider::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->paymentRepository = $container->get(PaymentRepository::class);
        $this->invoicesGetCollection = $container->get(InvoicesGetCollectionProvider::class);
        $this->dispatcher = $container->get(EventDispatcherInterface::class);
        $this->clock = $container->get(ClockInterface::class); 

        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        $tables = [
            'invoice',
            'payment',
            'visit',
            'medical_record',
            'appointment',
            'patient',
            'user'
        ];

        foreach ($tables as $table) {
            $connection->executeStatement($platform->getTruncateTableSQL($table, true));
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);


    }


    protected function saveMedicalRecords()
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
    
        $createdAt =$this->clock->now()->setTime(9, 0);
        
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

        $startDateTime = new \DateTimeImmutable("2025-02-12T09:00:00");

        for($i=0; $i<15; $i++)
        {

            $appointment  = new Appointment();
            $date = $startDateTime->add(new \DateInterval("P{$i}M")); // every month
            //$date->format('Y-m-d H:i:s') . PHP_EOL;
            $appointment->setReason("Reason test");
            $appointment->setAppointmentDate($date);
            $appointment->setModifiedAt( $createdAt);
            $appointment->setCreatedAt( $createdAt);
            $appointment->setCreatedBy($userFromDb );
            $appointment->setModifiedBy($userFromDb );
            $appointment->setUser($userFromDb);
            $appointment->setStatus(true);
            $appointment->setPatient($patientByCni);

            $this->entityManager->persist($appointment);
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
        
            $medicalRecord->setChiefComplaint("Jane".$i);
    

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

            $invoice = new Invoice();

            $invoice->setInvoiceDate($date);
            $invoice->setMedicalRecord($medicalRecord);
            $invoice->setTotalPaid($medicalRecord->getTotalPaid());
            $invoice->setTotalAmount($medicalRecord->getAgreedAmout());
            $invoice->setRemainingDue($medicalRecord->getRemainingDue());
            $invoiceNumber = $invoice->generateInvoiceNumber();
            $invoice->setInvoiceNumber($invoiceNumber);
            $this->entityManager->persist($invoice);
            $this->entityManager->flush();

            $invoiceEntity = $this->entityManager->getRepository(Invoice::class)->
                                findOneBy([
                                    "invoiceNumber" => $invoiceNumber
                                ]);

            $amount = 400;
            $payment = new Payment();
            $payment->setMethod("Carte");
            $payment->setAmount($amount);
            $payment->setPaymentDate(new DateTimeImmutable("2025-03-15"));
            $payment->setInvoice($invoiceEntity);
            $this->entityManager->persist($payment);
            $this->entityManager->flush();
            $this->paymentRepository->findLastPayment();
            $visit = new Visit();
            $visitDate1=  new DateTimeImmutable("2025-03-15");
            $visit->setVisitDate($visitDate1);
            $visit->setNotes("Suivi traitement");
            $visit->setAmountPaid($amount);
            $visit->setRemainingDueAfterVisit(500);
            $visit->setDurationMinutes(40);
            $visit->setType("consultation");
            $visit->setStatus(true);

            $visit->setMedicalRecord( $this->medicalRecord );
            $visit->setCreatedAt($createdAt = $this->clock->now());
            $visit->setModifiedAt($createdAt = $this->clock->now());
            $visit->setCreatedBy($this->user);
            $visit->setModifiedBy($this->user);
            $visit->addPayment($this->paymentRepository-> findLastPayment());
            $this->entityManager->persist($visit);
            $this->entityManager->flush();

            
            $medicalRecord->setAppointment($appointment);

            
        }
      //  $this->entityManager->flush();


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

        $this->entityManager->persist($appointment);
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

        $amount = 300;
        $payment = new Payment();
        $payment->setMethod("Virement");
        $payment->setAmount($amount);
        $payment->setPaymentDate(new DateTimeImmutable("2025-03-01"));
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->paymentRepository->findLastPayment();
        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2023-02-12");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Consultation initiale + radio");
        $visit->setAmountPaid($amount);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);

        $visit->setMedicalRecord( $this->medicalRecord );
        $visit->setCreatedAt($createdAt = $this->clock->now());
        $visit->setModifiedAt($createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user);
        $visit->addPayment($this->paymentRepository-> findLastPayment());
        $this->entityManager->persist($visit);
        $this->entityManager->flush();


        $amount = 400;
        $payment = new Payment();
        $payment->setMethod("Carte");
        $payment->setAmount($amount);
        $payment->setPaymentDate(new DateTimeImmutable("2025-03-15"));
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        $this->paymentRepository->findLastPayment();
        $visit = new Visit();
        $visitDate1=  new DateTimeImmutable("2025-03-15");
        $visit->setVisitDate($visitDate1);
        $visit->setNotes("Suivi traitement");
        $visit->setAmountPaid($amount);
        $visit->setRemainingDueAfterVisit(500);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);

        $visit->setMedicalRecord( $this->medicalRecord );
        $visit->setCreatedAt($createdAt = $this->clock->now());
        $visit->setModifiedAt($createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user);
        $visit->addPayment($this->paymentRepository-> findLastPayment());
        $this->entityManager->persist($visit);
        $this->entityManager->flush();






        $amount = 300;
        $payment = new Payment();
        $payment->setMethod("Virement");
        $payment->setAmount($amount);
        $payment->setPaymentDate(new DateTimeImmutable("2025-03-15"));
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        $this->paymentRepository->findLastPayment();
        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-03-15");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Suivi traitement");
        $visit->setAmountPaid($amount);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);

        $visit->setMedicalRecord( $this->medicalRecord );
        $visit->setCreatedAt($createdAt = $this->clock->now());
        $visit->setModifiedAt($createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user);
        $visit->addPayment($this->paymentRepository-> findLastPayment());
        $this->entityManager->persist($visit);
        $this->entityManager->flush();


        static::$visitId = $this->entityManager->getRepository(Visit::class)->findOneBy([
            "visitDate" => $visitDate1
        ])->getId();
        

    }

    function saveVisitsForGetCollections()
    {
        
        $visit = new Visit();
        $visitDate = new DateTimeImmutable("2023-02-12");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Consultation initiale + radio");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit = new Visit();
        $visitDate1 = new DateTimeImmutable("2023-02-19");
        $visit->setVisitDate($visitDate1);
        $visit->setNotes("Dévitalisation");
        $visit->setAmountPaid(400);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit = new Visit();
        $visitDate = new DateTimeImmutable("2023-03-01");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
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
        

        $visit = new Visit();
        $visitDate =  new DateTimeImmutable("2023-04-01");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit = new Visit();
        $visitDate =  new DateTimeImmutable("2024-04-10");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit = new Visit();
        $visitDate = new DateTimeImmutable("2024-04-15");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2024-04-28");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-05-01");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-05-10");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-05-20");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-05-27");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-06-01");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $visit =  new Visit();
        $visitDate =  new DateTimeImmutable("2025-06-10");
        $visit->setVisitDate($visitDate);
        $visit->setNotes("Finalisation traitement");
        $visit->setAmountPaid(300);
        $visit->setRemainingDueAfterVisit(700);
        $visit->setDurationMinutes(40);
        $visit->setType("consultation");
        $visit->setStatus(true);
        $visit->setMedicalRecord($this->medicalRecord );
        $visit->setCreatedAt( $createdAt = $this->clock->now());
        $visit->setModifiedAt( $createdAt = $this->clock->now());
        $visit->setCreatedBy($this->user);
        $visit->setModifiedBy($this->user );
        $this->entityManager->persist($visit);
        $this->entityManager->flush();

    }

    protected function createMedicalRecordWithVisits(array $visits, int $agreedAmount): MedicalRecord
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

        $totalPaid = 0;

        foreach ($visits as $visitData) {
            $visit = new Visit();
           
            $visit->setAmountPaid($visitData['amount_paid']);
            $visit->setNotes("Consultation initiale + radio");
            $visit->setVisitDate(new DateTimeImmutable("2025-02-12"));
            $visit->setRemainingDueAfterVisit(700);
            $visit->setDurationMinutes(40);
            $visit->setType("consultation");
            $visit->setCreatedAt(new DateTimeImmutable());
            $visit->setCreatedBy($user);
            $visit->setModifiedAt(new DateTimeImmutable("2025-02-12"));
            $visit->setModifiedBy($user);
            $visit->setStatus(true);

            $payment = new Payment();
            $payment->setAmount($visitData['amount_paid']);
            $payment->setMethod("Virement");
            $payment->setPaymentDate(new DateTimeImmutable("2025-02-12"));
            
            $totalPaid += $visitData['amount_paid'];
            $visit->setMedicalRecord($medicalRecord);
            //$visit->addPayment( $payment);
           // $medicalRecord->addVisit($visit);
        }

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
        

        return $medicalRecord;
        
        /*$medicalRecord = new MedicalRecord();
        $medicalRecord->setAgreedAmout($agreedAmount);
        $medicalRecord->setVisitDate(new DateTimeImmutable("2025-02-12")  );
        $medicalRecord->setClinicalDiagnosis("Caries profonde");
        $medicalRecord->setTreatmentPlan("Dévitalisation + composite");
        $medicalRecord->setChiefComplaint("Swollen jaw and tooth pain");
        $medicalRecord->setNotes("Emergency procedure performed with patient consent");
        $medicalRecord->setFollowUpDate(new DateTimeImmutable("2025-02-12") );
        $totalPaid = 0;

        foreach ($visits as $visitData) {
            $visit = new Visit();
            $visit->setAmountPaid($visitData['amount_paid']);
            $visit->setNotes("Consultation initiale + radio");
            $visit->setRemainingDueAfterVisit(700);
            $totalPaid += $visitData['amount_paid'];
            $visit->setMedicalRecord($medicalRecord);
            $medicalRecord->addVisit($visit);
        }

        $medicalRecord->setTotalPaid($totalPaid);
        $medicalRecord->setRemainingDue($agreedAmount - $totalPaid);

        $this->entityManager->persist($medicalRecord);

        return $medicalRecord;*/
    }

    protected function saveMedicalRecordWithVisitsForUpdatingInvoice(array $visits, int $agreedAmount): MedicalRecord
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

        $totalPaid = 0;

        foreach ($visits as $visitData) {
            $visit = new Visit();
           
            $visit->setAmountPaid($visitData['amount_paid']);
            $visit->setNotes("Consultation initiale + radio");
            $visit->setVisitDate(new DateTimeImmutable("2025-02-12"));
            $visit->setRemainingDueAfterVisit(700);
            $visit->setDurationMinutes(40);
            $visit->setType("consultation");
            $visit->setCreatedAt(new DateTimeImmutable());
            $visit->setCreatedBy($user);
            $visit->setModifiedAt(new DateTimeImmutable("2025-02-12"));
            $visit->setModifiedBy($user);
            $visit->setStatus(true);

            $payment = new Payment();
            $payment->setAmount($visitData['amount_paid']);
            $payment->setMethod("Virement");
            $payment->setPaymentDate(new DateTimeImmutable("2025-02-12"));
            
            $totalPaid += $visitData['amount_paid'];
            $visit->setMedicalRecord($medicalRecord);
            $visit->addPayment( $payment);
            $medicalRecord->addVisit($visit);
        }

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
        

        return $medicalRecord;
        
        /*$medicalRecord = new MedicalRecord();
        $medicalRecord->setAgreedAmout($agreedAmount);
        $medicalRecord->setVisitDate(new DateTimeImmutable("2025-02-12")  );
        $medicalRecord->setClinicalDiagnosis("Caries profonde");
        $medicalRecord->setTreatmentPlan("Dévitalisation + composite");
        $medicalRecord->setChiefComplaint("Swollen jaw and tooth pain");
        $medicalRecord->setNotes("Emergency procedure performed with patient consent");
        $medicalRecord->setFollowUpDate(new DateTimeImmutable("2025-02-12") );
        $totalPaid = 0;

        foreach ($visits as $visitData) {
            $visit = new Visit();
            $visit->setAmountPaid($visitData['amount_paid']);
            $visit->setNotes("Consultation initiale + radio");
            $visit->setRemainingDueAfterVisit(700);
            $totalPaid += $visitData['amount_paid'];
            $visit->setMedicalRecord($medicalRecord);
            $medicalRecord->addVisit($visit);
        }

        $medicalRecord->setTotalPaid($totalPaid);
        $medicalRecord->setRemainingDue($agreedAmount - $totalPaid);

        $this->entityManager->persist($medicalRecord);

        return $medicalRecord;*/
    }
}