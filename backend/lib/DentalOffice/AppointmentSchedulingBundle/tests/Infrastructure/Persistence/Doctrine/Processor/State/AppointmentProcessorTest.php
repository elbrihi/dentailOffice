<?php

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\VisitOrmEntity;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceItemOrmEntity;
use DentalOffice\InvoiceBundle\Infrastructure\Persistence\Doctrine\Entity\InvoiceOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\MedicalRecordOrmEntity;
use DentalOffice\MedicalRecordBundle\Infrastructure\Persistence\Doctrine\Entity\PrescriptionOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AppointmentProcessorTest  extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;
    protected ClockInterface $clock;
    protected const CNI = 'CNI987654';
    protected $container;

    public static string $username = "testuser";
    protected function setUp():void
    {


        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here

        foreach ($this->entityManager->getRepository(InvoiceItemOrmEntity::class)->findAll() as $invoice) {
            $this->entityManager->remove($invoice);
        }

        foreach ($this->entityManager->getRepository(InvoiceOrmEntity::class)->findAll() as $invoice) {
            $this->entityManager->remove($invoice);
        }
        // Step 2: Remove Patients

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



    protected function savePatient():void
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

        $appointment=Appointment::book(
               PatientId::fromInt((int) $patientId ),
               $timeSlot,
               PractitionerId::fromInt($practitionerId),
               PurposeId::fromString("Jane"),
               AppointmentStatus::scheduled()
        );

        $patient = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'id' => $appointment->getPatientId()->toInt()
        ]);

        $user = $practitionerId = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $appointment->getPractitionerId()->PractitionerId()]);

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

}