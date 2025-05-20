<?php
namespace DentalOffice\AppointmentSchedulingBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Post;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State\AppointmentStateProcessor;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AppointmentStateProcessorIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ClockInterface $clock;
    public static string $username = "testuser";
    protected function setUp():void
    {

        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here



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

    public function testAppointmentProcessPersists(): void
    {



        $this->savePatient();
        $patientId = $this->entityManager->getRepository(Patient::class)->findOneBy([
            'cni' => 'CNI987654'
        ])->getId();


        $operation = new Post();

        $uriVariables = ["patientId" => $patientId  ];


        $request = new Request([], [], [], [], [], [], json_encode([
            "appointment_date" => "2025-02-12",
            "reason" => "Jane",
            "status" => true,
            
        ]));

        $context = ["request"=> $request];
        $processor = static::getContainer()->get(AppointmentStateProcessor::class);

        $appointment = new Appointment();

        $result = $processor->process($appointment, $operation,$uriVariables, $context );

        $appontmentDate =   new DateTimeImmutable("2025-02-12") ;
        $appointment =$this->entityManager->getRepository(Appointment::class)->findOneBy([
            "appointmentDate" => $appontmentDate   
        ]);

       
        $this->assertEquals($appointment->getAppointmentDate(), $appontmentDate);


    }

    public function savePatient():void
    {
        $user = new User();
        $user->setUsername(static::$username);
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken('sample_token_value');

        // 2. Persist User
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 3. Clear the EM to force reload from DB
        $this->entityManager->clear();

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

        

    }
}