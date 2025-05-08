<?php

namespace DentalOffice\PatientBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PatientPostProcessorIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;


    protected function setUp(): void
    {
        self::bootKernel();

        // inject EntityManagerInterface 

    
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Clear existing data
        $this->entityManager->createQuery('DELETE FROM DentalOffice\PatientBundle\Domain\Entity\Patient')->execute();
        $this->entityManager->createQuery('DELETE FROM DentalOffice\UserBundle\Domain\Entity\User')->execute();
    }

    public function testProcessPersistsPatient(): void
    {
        // 1. Create and persist a user
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken("f9g0lvoZZ4yvr3TjpWh3ZahYl7z7TS3dXiZud/zBrFJpZOU3kG8fG7d51r4KRmqQCzs=");

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 2. Simulate authenticated user
        $tokenStorage = static::getContainer()->get('security.token_storage');

        $tokenStorage->setToken(new UsernamePasswordToken(
            $user,
            'admin',
            $user->getRoles()
        ));


        // 3. Build fake request
        $request = new Request([], [], [], [], [], [], json_encode([
            "lastName" => "Doe",
            "firstName" => "Jane",
            "birthDate" => "1985-06-15",
            "gender" => "Female",
            "cni" => "CNI987654",
            "phone" => "123456789",
            "email" => "jane.doe@example.com",
            "address" => "42 Sunset Blvd",
            "bloodType" => "O+",
            "medicalHistory" => "Asthma",
            "notes" => "Test patient"
        ]));

   
        // 4. Create the context and dependencies
        $context = ['request' => $request];
        $operation = new Post();
        $patient = new Patient();

        /** @var PatientPostProcessor $processor */
        $processor = static::getContainer()->get(PatientPostProcessor::class);

        // 5. Process and assert
        $result = $processor->process($patient, $operation, [], $context);

  
        $this->assertInstanceOf(Patient::class, $result);
        $this->assertSame("Doe", $result->getLastName());
        $this->assertSame("jane.doe@example.com", $result->getEmail());

        // 6. Assert DB contains the patient
        $found = $this->entityManager
            ->getRepository(Patient::class)
            ->findOneBy(['email' => 'jane.doe@example.com']);

        $this->assertNotNull($found);
        $this->assertSame("Doe", $found->getLastName());
    }
}
