<?php

namespace DentalOffice\PatientBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\PatientBundle\Infrastructure\Persistence\Doctrine\Processor\State\PatientPostProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\State\ProcessorInterface;
use DentalOffice\UserBundle\Domain\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;

class PatientPostProcessorTest extends TestCase
{
    public function testProcessWithValidData()
    {
        // Mocks
        $persistProcessor = $this->createMock(ProcessorInterface::class);
        $removeProcessor = $this->createMock(ProcessorInterface::class);
        $security = $this->createMock(Security::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $clock = $this->createMock(ClockInterface::class);
    
        // Simulate current time
        $now = new \DateTimeImmutable('2024-01-01 10:00:00');
        $clock->method('now')->willReturn($now);
    
        // Simulate authenticated user
        $user = new User();
        $user->setUsername('admin');
        $user->setPassword('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setApiToken("JuJiTnJqy6fvU0XdXdw2U6tDYrYLgTgJgjAY7esnrK6oqptOCIssYTNDVAC+nvYt3+8=");
    
        // Manually set ID using Reflection
        $ref = new \ReflectionClass($user);
        $property = $ref->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1); // simulate persisted User
    
        // Mock repository
        $userRepository = $this->createMock(EntityRepository::class);
        $userRepository->method('findOneBy')->with(['apiToken' => $user->getApiToken()])
        ->willReturn($user);
    
        // Mock getRepository call
        $entityManager->method('getRepository')->with(User::class)
        ->willReturn($userRepository);
    
        // Security will return authenticated user
        $security->method('getUser')->willReturn($user);
    
        // Set up persistProcessor to return the patient it receives
        $persistProcessor->method('process')
            ->willReturnCallback(function ($data) {
                return $data;
            });
    
        // Instantiate the processor
        $processor = new PatientPostProcessor(
            $persistProcessor,
            $removeProcessor,
            $security,
            $entityManager,
            $clock
        );
    
        // Fake request content
        $requestData = [
            "lastName" => "Doe",
            "firstName" => "John",
            "birthDate" => "1990-05-20",
            "gender" => "Male",
            "cni" => "CNI123456",
            "phone" => "1234567890",
            "email" => "john.doe@example.com",
            "address" => "123 Main St",
            "bloodType" => "A+",
            "medicalHistory" => "None",
            "notes" => "Test notes"
        ];
    
        $request = new Request([], [], [], [], [], [], json_encode($requestData));
        $context = ['request' => $request];
        $operation = $this->createMock(Operation::class);
    
        // Empty patient entity to fill
        $patient = new Patient();
    
        // Act
        $result = $processor->process($patient, $operation, [], $context);
    
        // Assert values
        $this->assertInstanceOf(Patient::class, $result);
        $this->assertEquals('Doe', $result->getLastName());
        $this->assertEquals('John', $result->getFirstName());
        $this->assertEquals('1990-05-20', $result->getBirthDate()->format('Y-m-d'));
        $this->assertEquals('Male', $result->getGender());
        $this->assertEquals('CNI123456', $result->getCni());
        $this->assertEquals('1234567890', $result->getPhone());
        $this->assertEquals('john.doe@example.com', $result->getEmail());
        $this->assertEquals('123 Main St', $result->getAddress());
        $this->assertEquals('A+', $result->getBloodType());
        $this->assertEquals('None', $result->getMedicalHistory());
        $this->assertEquals('Test notes', $result->getNotes());
        $this->assertEquals($now, $result->getCreatedAt());
        $this->assertEquals($user, $result->getCreatedBy());
        $this->assertEquals($user, $result->getModifiedBy());
        $this->assertEquals($now, $result->getModifiedAt());
        //$this->assertTrue($result->getStatus());
    }
    
}
