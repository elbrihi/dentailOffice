<?php

namespace DentalOffice\PaymentsBundle\Tests;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Visit;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\VisitAmountPaid;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\InvoiceBundle\Domain\Aggregate\Invoice;
use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\ValueObject\AgreedAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceStatus;
use DentalOffice\InvoiceBundle\Domain\ValueObject\InvoiceTotalAmount;
use DentalOffice\InvoiceBundle\Domain\ValueObject\RemainingDue;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceItemOrmEntity;
use DentalOffice\MedicalRecordBundle\Domain\Aggregate\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordAgreedAmount;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordChiefComplaint;
use DentalOffice\MedicalRecordBundle\Domain\ValueObject\MedicalRecordId;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\PaymentsBundle\Infrastructure\Persistence\Doctrine\Processor\State\PaymentPostProcessor;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use Proxies\__CG__\DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PaymentProcessorTest extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ClockInterface $clock;
    protected const CNI = 'CNI987654';
    protected $container;
    protected User $user;
    protected Patient $patient;
    protected Appointment $appointment;
    protected Invoice $invoice;
    protected InvoiceItem $invoiceItem;
    protected MedicalRecordOrmEntity $medicalRecordEntity;
    protected PaymentPostProcessor $paymentProcessor;
    protected int $medicalRecordId;
    protected int $appointmentId;
    protected int $invoiceId;
    public static string $username = "testuser";
    

    protected function setUp():void
    {


        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here

        $this->paymentProcessor = $container->get(PaymentPostProcessor::class);

        foreach ($this->entityManager->getRepository(VisitOrmEntity::class)->findAll() as $visit) {

            
            $this->entityManager->remove($visit);
        }

        foreach ($this->entityManager->getRepository(InvoiceItemOrmEntity::class)->findAll() as $invoice) {

            
            $this->entityManager->remove($invoice);
        }

       
        foreach ($this->entityManager->getRepository(InvoiceOrmEntity::class)->findAll() as $invoice) {
            $this->entityManager->remove($invoice);
        }
       
        // 1. Prescreption (deepest child)
        foreach ($this->entityManager->getRepository(PrescriptionOrmEntity::class)->findAll() as $presciption) {
            $this->entityManager->remove( $presciption);
        }

        // 1. Visits (deepest child)
        foreach ($this->entityManager->getRepository(VisitOrmEntity::class)->findAll() as $visit) {
            $this->entityManager->remove($visit);
        }

        // 2. Medical Records
        foreach ($this->entityManager->getRepository(MedicalRecordOrmEntity::class)->findAll() as $mr) {
            $this->entityManager->remove($mr);
        }

        // 3. Appointments
        foreach ($this->entityManager->getRepository(AppointmentOrmEntity::class)->findAll() as $appointment) {
            $this->entityManager->remove($appointment);
        }

        // 4. Patients
        foreach ($this->entityManager->getRepository(Patient::class)->findAll() as $patient) {
            $this->entityManager->remove($patient);
        }

        // 5. Users (root)
        foreach ($this->entityManager->getRepository(User::class)->findAll() as $user) {
            $this->entityManager->remove($user);
        }

        $this->entityManager->flush();

    }
    
    protected function saveUser():void{
        
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

        $this->user = $user;

    }
       
    protected function savePatient():void
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
        

    }

    protected function saveAppointment()
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
        
        $appointmentConfirmed = Appointment::book(
               PatientId::fromInt((int) $patientId),
               $timeSlot,
               PractitionerId::fromInt($practitionerId),
               PurposeId::fromString("Jane"),
               AppointmentStatus::confirmed($appointmentSchoudled->getStatus()->getStatus())
        );

        $appointment = Appointment::book(
               PatientId::fromInt((int) $patientId),
               $timeSlot,
               PractitionerId::fromInt($practitionerId),
               PurposeId::fromString("Jane"),
               AppointmentStatus::completed($appointmentConfirmed->getStatus()->getStatus())
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
        
        $this->appointmentId =  $orm->getId();
    }

    protected function saveInvoice()
    {
        $invoiceEntity =  new InvoiceOrmEntity();
        $patientId = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni' => self::CNI
        ])->getId();


        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)->findOneBy([
            'id' => $this->medicalRecordId
        ]);


        $totalAmount   = InvoiceTotalAmount::fromPositifTotlaAmount(0);
        $remainingDue  = RemainingDue::fromPositifRemainingDue($this->payload()['medicalRecordInput']['agreedAmount']);
        $agreedAmount  = AgreedAmount::fromPositifAgreedAmount($this->payload()['medicalRecordInput']['agreedAmount']);

        $invoiceStatus = InvoiceStatus::handleInvoiceStatus(
            $remainingDue->getRemainingDue(),
            $totalAmount->getTotalAmount(),
            $agreedAmount->agreedAmount()
        );

        $invoice = Invoice::generateInvoice(
            $totalAmount,
            $remainingDue,
            $agreedAmount,
            $invoiceStatus,

        );
       
        $invoiceEntity->setTotalAmount($invoice->getTotalAmount()->getTotalAmount());
        $invoiceEntity->setRemainingDue($invoice->getRemainingDue()->getRemainingDue());
        $invoiceEntity->setAgreedAmount($invoice->getAgreedAmount()->agreedAmount());
        $invoiceEntity->setMedicalRecord($medicalRecord);
        $invoiceEntity->setTotalAmount($invoice->getTotalAmount()->getTotalAmount());
        $invoiceEntity->setStatus($invoice->getInvoiceStatus()->getInvoiceStatus());
        $invoiceEntity->setInvoiceNumber($invoiceEntity-> generateInvoiceNumber());
        $invoiceEntity->setTotalAmount($invoice->getTotalAmount()->getTotalAmount());
        $invoiceEntity->setTotalPaid(0);

        $this->entityManager->persist($invoiceEntity);
        $this->entityManager->flush();

        $this->invoiceId = $invoiceEntity->getId();
        $this->entityManager->clear();
          
    }

    protected function saveMedicalRecord()
    {

        $patientId = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni' => self::CNI
        ])->getId();

        $practitionerId = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => 'testuser'])->getId();


        $medicalRecord = MedicalRecord::medicalRecord(
            MedicalRecordId::toInt(0),
            MedicalRecordChiefComplaint::chiefComplaint( $this->payload()['medicalRecordInput']['chief_complaint']),
            MedicalRecordAgreedAmount::fromNumeric($this->payload()['medicalRecordInput']['agreedAmount'])
        );

        $patient = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'id' => $patientId 
        ]);

        $user = $practitionerId = $this->entityManager
                            ->getRepository(User::class)
                            ->findOneBy([              
                        'id' => $patientId 
        ]);

        $orm = new MedicalRecordOrmEntity();
        $orm->setModifiedAt($this->clock->now());
        $orm->setCreatedAt( $this->clock->now());
        $orm->setChiefComplaint($medicalRecord->getMedicalRecodChiefComplaint()->getValue());        
        $orm->setAgreedAmount($medicalRecord->getAgreedAmout()->getAgreedAmountValue());
        $orm->setClinicalDiagnosis($this->payload()['medicalRecordInput']['clinical_diagnosis']);
        $orm->setTreatmentPlan($this->payload()['medicalRecordInput']['treatment_plan']);
        $orm->setNotes($this->payload()['medicalRecordInput']['notes']);
        $orm->setTotalPaid(0);
        $orm->setRemainingDue(1000);
        $orm->setPatient($patient);
        $orm->setUser($user);
        $orm->setCreatedBy($user);
        $orm->setModifiedBy($user);
        $this->medicalRecordEntity = $orm;  
        $this->entityManager->persist($orm);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->medicalRecordId = $orm->getId();
       
    }

    protected function savePrescption()
    {
        $medicalRecord = $this->entityManager->getRepository(MedicalRecordOrmEntity::class)
                              ->findOneBy(
                                [
                                    'id' => $this->medicalRecordId
                                ]
        );

        $appointment = $this->entityManager->getRepository(AppointmentOrmEntity::class)
                             ->findOneBy([
                                'id' =>  $this->appointmentId
        ]);

        $createdAt = new DateTimeImmutable();
        $presciption = new PrescriptionOrmEntity();

            $user = $medicalRecord->getCreatedBy();
      
     
     
        
        $visit = Visit::createVisit(
            VisitAmountPaid::fromFloatPositive($this->payload()['visit']['amount_paid']),
        );

        $visitOrmEntity = new VisitOrmEntity();

        $visitOrmEntity->setCreatedAt( $createdAt);
        $visitOrmEntity->setModifiedAt( $createdAt);
        $visitOrmEntity->setAmountPaid($visit->getVisitAmountPaid()->getAmountPaid());
        $visitOrmEntity->setNotes($this->payload()['visit']['notes']);
        $visitOrmEntity->setCreatedAt($createdAt);
        $visitOrmEntity->setCreatedBy($user);
        $visitOrmEntity->setNotes($this->payload()['visit']['notes']);
        $visitOrmEntity->setModifiedBy($user);
        $visitOrmEntity->setMedicalRecord($medicalRecord );
        $visitOrmEntity->setStart($appointment->getStart());
        $visitOrmEntity->setEnd($appointment->getEnd());
        $visitOrmEntity->setModifiedBy($user);
        $visitOrmEntity->setStatus($appointment->getStatus());
        $visitOrmEntity->setType($this->payload()['visit']['type']);
        $visitOrmEntity->setAppointment($appointment);
        
        $this->entityManager->persist($visitOrmEntity);

        $this->entityManager->flush();

        $prescriptient = new PrescriptionOrmEntity();
        
        $prescriptients = $this->payload()['visit']['prescriptions'];
 
        foreach ($prescriptients as $prescriptient) {

            $prescriptientOrmEntity = new PrescriptionOrmEntity();

            $prescriptientOrmEntity ->setMedication($prescriptient["medication"]);

            $prescriptientOrmEntity->setDosage($prescriptient["dosage"]);

            $prescriptientOrmEntity->setNotes($prescriptient["notes"]);

            $prescriptientOrmEntity->setVisitOrmEntity($visitOrmEntity);

            $this->entityManager->persist($prescriptientOrmEntity);

        }
 

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    protected function saveInvoiceItems()
    {
        $invoice = $this->entityManager->getRepository(InvoiceOrmEntity::class)->findOneBy([
            'id' => $this->invoiceId
        ]);
        $invoiceItems = $this->payload()['visit']['items'];   


        foreach ($invoiceItems as $invoiceItem) {
            $itemStatus = InvoiceItem::invoice
            (
                0,
                $invoiceItem['description'],
                $invoiceItem['amount'],
                0,

            );

            $invoiceItemOrmEntity = new InvoiceItemOrmEntity();
            $invoiceItemOrmEntity->setDescription($invoiceItem['description']);
            $invoiceItemOrmEntity->setAmount($invoiceItem['amount']);
            $invoiceItemOrmEntity->setInvoiceOrmEntity($invoice);
            
            $invoiceItemOrmEntity->setStatus($itemStatus->getStatus()::planned()->getStatus());

            $this->entityManager->persist($invoiceItemOrmEntity);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function payload():array
    {

        $medicalRecordInput = [
            "chief_complaint" => "Jane",
            "clinical_diagnosis" => "Caries profonde",
            "treatment_plan" => "Dévitalisation + composite",

            "notes" => "notes tests",
            "agreedAmount" => 1000,
        ];

        $visit = [
            "notes" => "Consultation initiale + radio",
            "amount_paid" => 300,
            "remaining_due_after_visit" => 0,
            "type" => "consultation",

            "items"=> [
                [
                    "description" => "Consultation + radio",
                    "amount"=>  200,
                
                ],
                [
                    "description"=> "Extraction",
                    "amount" => 200,
                ],
                [
                    "description" => "Implant",
                    "amount"=> 600,
                ]
        
            ],
            "prescriptions" => [
                [
                    "medication" => "Metronidazole",
                    "dosage" => "500mg three times a day for 5 days",
                    "notes" => "Avoid alcohol during treatment"
                ],
                [
                    "medication" => "Amoxicillin",
                    "dosage" => "500mg three times a day for 5 days",
                    "notes" => "Avoid alcohol during treatment"
                ]
            ],
        ];

        return [
            "medicalRecordInput" => $medicalRecordInput,
            "visit" => $visit,
        ];
    }

}