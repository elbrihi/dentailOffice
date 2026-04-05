<?php


namespace DentalOffice\AppointmentSchedulingBundle\Tests\Application\EventSubscriber;

use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\EventSubscriber\VisitCreatedOnInvoicAndMedicalRecordSubscriber;
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
use DentalOffice\InvoiceBundle\Domain\Aggregate\InvoiceItem;
use DentalOffice\InvoiceBundle\Domain\Event\InvoiceCreated;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceItemOrmEntity;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use DentalOffice\MedicalRecordBundle\Application\EventSubscriber\MedicalRecordCreatedOnVisitSubscriber;
use DentalOffice\MedicalRecordBundle\Domain\Aggregate\MedicalRecord;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class VisitCreatedOnInvoicAndMRSubscriberTest extends VisitTest
{
      

    private static $medicalRecordId ='';

    private MedicalRecordOrmEntity $medicalRecordOrm;

    private User $user;

    private AppointmentOrmEntity $appointment;

    private VisitCreatedOnInvoicAndMedicalRecordSubscriber $visitOnMedicalRecord;

    private MedicalRecord $medicalRecord ;

    private InvoiceOrmEntity $invoiceOrm;

    private const TOTAL_PAID = 0;
    private const REMAINING_DUE = 0;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->visitOnMedicalRecord = static::getContainer()
            ->get(VisitCreatedOnInvoicAndMedicalRecordSubscriber::class);
    }



    public function test_visit()
    {

       
        $this->saveUser();
      
        $this->savePatient();
        $this->entityManager->flush(); 
        $this->saveAppointment();
        
        $this->saveMedicalRecord();
        
        $this->saveInvoice(); 
        $this->entityManager->flush();

        $invoiceCreated =  InvoiceCreated::initialInvoice(
            $this->invoiceOrm->getId(),
            $this->medicalRecordOrm->getId(),
            $this->patient->getId(),
            $this->user->getId(),
            $this->appointment->getId(),
            $this->payload()

        );

        $this->visitOnMedicalRecord->createVisit($invoiceCreated);

        
        
    }

    private function saveInvoice()
    {

        $medicalRecord = $this->medicalRecordOrm;

        $invoice = new InvoiceOrmEntity();

        $createdAt = new DateTimeImmutable();

        $invoice->setInvoiceDate( $createdAt);
        $invoice->setMedicalRecord($medicalRecord );
        $invoice->setInvoiceNumber($invoice->generateInvoiceNumber());
        $invoice->setAgreedAmount($medicalRecord->getAgreedAmount());
        $invoice->setTotalAmount($medicalRecord->getAgreedAmount());
        $invoice->setRemainingDue($medicalRecord->getAgreedAmount());
        $invoice->setTotalPaid(static::TOTAL_PAID);
        $invoice->setTotalAmount($medicalRecord->getAgreedAmount());
       
        $this->entityManager->persist( $invoice);
        $this->entityManager->flush();

        $items = $this->payload()[0]['visit']['items'] ;

        
       
        for($i =0 ; $i < sizeof( $items);$i++)
        {
            $invoiceItem = new InvoiceItemOrmEntity();


            $itemStatus = InvoiceItem::invoice
            (
                0,
                $items[$i]['description'],
                $items[$i]['amount'],
                0,
               

            )->getStatus()::planned();
            $invoiceItem->setAmount($items[$i]['amount']);

            $invoiceItem->setStatus($itemStatus->getStatus());

            $invoiceItem->setDescription($items[$i]['description']);

            $invoiceItem->setInvoiceOrmEntity($invoice);

            //dump($invoiceItem);
            $this->entityManager->persist(  $invoiceItem);

            $this->invoiceOrm = $invoice;

            
        }
        
      
        $medicalRecordId  = $medicalRecord->getId();

        $this->entityManager->flush();

    }


    private function saveUser()
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
    private function savePatient():void
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

        $this->patient = $patient;
        

    }

    private function saveAppointment()
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
     
        


        $this->appointment = $orm;
        
    }

    private function saveMedicalRecord()
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

        $visitInpout = $this->payload()[0]["visit"];
       


        $medicalRecord = new MedicalRecordOrmEntity();

        $visitDate = new DateTimeImmutable("2025-02-12");
        $followUpDate = new DateTimeImmutable("2025-02-12");
     
        $medicalRecord->setChiefComplaint("Jane");
 

        $medicalRecord->setClinicalDiagnosis("Caries profonde");
      
        $medicalRecord->setTreatmentPlan("Dévitalisation + composite");

      
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


        // $visitOrmEntity = new VisitOrmEntity();

        //  $visit = Visit::createVisit(
        //     VisitAmountPaid::fromFloatPositive($visitInpout["amount_paid"]),
        // );

        // $visitOrmEntity->setCreatedAt( $createdAt);
        // $visitOrmEntity->setModifiedAt( $createdAt);
        // $visitOrmEntity->setAmountPaid($visit->getVisitAmountPaid()->getAmountPaid());
        // $visitOrmEntity->setNotes($visitInpout ["notes"]);
        // $visitOrmEntity->setCreatedAt($createdAt);
        // $visitOrmEntity->setCreatedBy($this->user);
        // $visitOrmEntity->setNotes($visitInpout["notes"]);
        // $visitOrmEntity->setModifiedBy($this->user);
        // $visitOrmEntity->setMedicalRecord($medicalRecord );
        // $visitOrmEntity->setStart($this->appointment->getStart());
        // $visitOrmEntity->setEnd($this->appointment->getEnd());
        // $visitOrmEntity->setModifiedBy($this->user);
        // $visitOrmEntity->setStatus($this->appointment->getStatus());
        // $visitOrmEntity->setType($visitInpout["type"]);
        // $visitOrmEntity->setAppointment($this->appointment);
        
       
        // $this->entityManager->persist($visitOrmEntity);
      

        // $prescription = new PrescriptionOrmEntity();

        // $prescriptions = $this->payload()[0]['visit']['prescriptions'];



        
       
        // for ($i=0; $i < sizeof($prescriptions) ; $i++) { 
           
        //     $prescriptionOrmEntity = new PrescriptionOrmEntity();
            
               
        //     $prescriptionOrmEntity->setDosage($prescriptions[$i]['dosage']);
        //     $prescriptionOrmEntity->setMedication($prescriptions[$i]['medication']);
        //     $prescriptionOrmEntity->setNotes($prescriptions[$i]['notes']);
        //     $prescriptionOrmEntity->setVisitOrmEntity($visitOrmEntity);

        //     $this->entityManager->persist($prescriptionOrmEntity);
           
        // }

        // $this->entityManager->flush();

        $this->medicalRecordOrm = $medicalRecord ;
      
        
        

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
            "amount_paid" => 1000,
            "remaining_due_after_visit" => 1200,
            "type" => "consultation",

            "items"=> [
            [
                "description" => "Consultation + radio",
                "amount"=>  300,
              
            ],
            [
              "description"=> "Extraction",
              "amount" => 400,
            ],
            [
              "description" => "Implant",
              "amount"=> 300,
            ]
           
           ],
            "prescriptions" => [
                [
                    "medication" => "Metronidazole",
                    "dosage" => "500mg three times a day for 5 days",
                    "notes" => "Avoid alcohol during treatment"
                ],
                [
                    "medication" => "Metronidazole",
                    "dosage" => "500mg three times a day for 5 days",
                    "notes" => "Avoid alcohol during treatment"
                ]
            ],
        ];

        return [["medicalRecord"=>$medicalRecord,"visit"=>$visit],$this->user ];
    }
}