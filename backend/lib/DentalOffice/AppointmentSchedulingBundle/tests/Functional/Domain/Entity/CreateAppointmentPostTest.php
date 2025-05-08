<?php 

namespace DentalOffice\AppointmentSchedulingBundle\Tests\Functional\Domail\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CreateAppointmentPostTest extends ApiTestCase
{
    private ClockInterface $clock;
    private EntityManagerInterface $entityManager;
    protected function setUp():void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->clock = $container->get(ClockInterface::class); // 👈 Fix here

          // Clear existing data
          $this->entityManager->createQuery('DELETE FROM DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Appointment')->execute();
          $this->entityManager->createQuery('DELETE FROM DentalOffice\PatientBundle\Domain\Entity\Patient')->execute();
          $this->entityManager->createQuery('DELETE FROM DentalOffice\UserBundle\Domain\Entity\User')->execute();
    }
    public function testAppointmentProcessPersists(): void
    {
        $client = static::createClient();

        // Create user and persist to DB
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword('hashed_password'); // Mock this or hash properly
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken('sample_token_value');
        
        $this->getEntityManager()->persist($user);

        // Create a patient
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
        

        if (!$user instanceof \DentalOffice\UserBundle\Domain\Entity\User) {
            throw new \LogicException('Authenticated user must be an instance of DentalOffice\UserBundle\Domain\Entity\User.');
        }
        $patient->setCreatedBy($user);
        $patient->setModifiedAt($this->clock->now());
        $patient->setModifiedBy($user);
        $patient->setStatus(true);
        
        $this->getEntityManager()->persist($patient);
        $this->getEntityManager()->flush();

        // Simulate authentication
        self::getContainer()->get('security.token_storage')->setToken(
            new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                $user,
                'main',
                $user->getRoles()
            )
        );

        // Make POST request to the custom route
        $client->request('POST', "/api/create/patient/{$patient->getId()}/appointment", [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json' => [
                'reason' => 'Dental Cleaning',
                'appointment_date' => '2025-06-20',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $appointmentRepo = $this->getEntityManager()->getRepository(Appointment::class);
        $appointments = $appointmentRepo->findAll();
       // $this->assertCount(1, $appointments);
        $this->assertEquals('Dental Cleaning', $appointments[0]->getReason());
        $this->assertEquals($user->getId(), $appointments[0]->getUser()->getId());
    }

    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine')->getManager();
    }
}
