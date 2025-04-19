<?php


namespace DentalOffice\PatientBundle\Tests\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use DentalOffice\PatientBundle\Infrastructure\Persistence\Doctrine\Processor\State\PatientPostProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

final class PatientPostProcessorTest extends TestCase
{
    private MockObject|ProcessorInterface $persistProcessorMock;
    private MockObject|ProcessorInterface $removeProcessorMock;
    private MockObject|Security $securityMock;
    private MockObject|EntityManagerInterface $entityManagerMock;
    private MockObject|UserInterface $userMock;
    private MockObject|Patient $patientMock;
    private MockObject|Operation $operationMock;
    private ClockInterface $clock;
    private PatientPostProcessor $processor;

    protected function setUp(): void
    {
        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->securityMock = $this->createMock(Security::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->userMock = $this->createMock(UserInterface::class);
        $this->patientMock = $this->createMock(Patient::class);
        $this->operationMock = $this->createMock(Operation::class);
        $this->clock = new MockClock(new DateTimeImmutable('2025-01-01 12:00:00'));

        $data = new Patient(
            null, null,null,null,null,null,null,null, null,null,null,null,null,null,null, null,null
        );

        $this->persistProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->removeProcessorMock = $this->createMock(ProcessorInterface::class);
        $this->securityMock = $this->createMock(Security::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->userMock = $this->createMock(UserInterface::class);
        $this->patientMock = $this->createMock(Patient::class);
        $this->operationMock = $this->createMock(Operation::class);
        $this->clock = new MockClock(new \DateTimeImmutable('2025-01-01 12:00:00'));
    
        // Correctly instantiate the processor
        $this->processor = new PatientPostProcessor(
            $this->persistProcessorMock,
            $this->removeProcessorMock,
            $this->securityMock,
            $this->entityManagerMock,
            $this->clock
        );

    }

    public function testItProcessesValidPatientData(): void
    {


        $payload = [
            'lastName' => 'Doe',
            'firstName' => 'John',
            'birthDate' => '1985-04-12',
            'gender' => 'Male',
            'cni' => 'XYZ123456',
            'phone' => '+1234567890',
            'email' => 'john.doe@example.com',
            'address' => '123 Main St',
            'bloodType' => 'A+',
            'medicalHistory' => 'None',
            'notes' => 'No allergies',
        ];

        $request = new Request([], [], [], [], [], [], json_encode($payload));
        $context = ['request' => $request];

        $this->securityMock
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($this->userMock); // Ensure the correct user is returned

        // Expect setters to be called (mock only if needed strictly)
        $this->patientMock->expects($this->once())->method('setLastName')->with('Doe');
        $this->patientMock->expects($this->once())->method('setFirstName')->with('John');
        $this->patientMock->expects($this->once())->method('setBirthDate')->with(new DateTimeImmutable('1985-04-12'));
        $this->patientMock->expects($this->once())->method('setGender')->with('Male');
        $this->patientMock->expects($this->once())->method('setCni')->with('XYZ123456');
        $this->patientMock->expects($this->once())->method('setPhone')->with('+1234567890');
        $this->patientMock->expects($this->once())->method('setEmail')->with('john.doe@example.com');
        $this->patientMock->expects($this->once())->method('setAddress')->with('123 Main St');
        $this->patientMock->expects($this->once())->method('setBloodType')->with('A+');
        $this->patientMock->expects($this->once())->method('setMedicalHistory')->with('None');
        $this->patientMock->expects($this->once())->method('setNotes')->with('No allergies');
        $this->patientMock->expects($this->once())->method('setCreatedAt')->with($this->clock->now());
        $this->patientMock->expects($this->once())->method('setCreatedBy')->with($this->userMock); // Here, User mock is passed
        $this->patientMock->expects($this->once())->method('setModifiedAt')->with($this->clock->now());
        $this->patientMock->expects($this->once())->method('setModifiedBy')->with($this->userMock); // User mock passed
        $this->patientMock->expects($this->once())->method('setStatus')->with(true);

        $this->persistProcessorMock
            ->expects($this->once())
            ->method('process')
            ->with($this->patientMock, $this->operationMock, [], $context)
            ->willReturn($this->patientMock);

        $result = $this->processor->process($this->patientMock, $this->operationMock, [], $context);
        $this->assertSame($this->patientMock, $result);
    }
}
