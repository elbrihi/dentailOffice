<?php

namespace DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Handler\ScheduleAppointmentHandler;
use DentalOffice\AppointmentSchedulingBundle\Application\Handler\ScheduleAppointmentHandlerInterface;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\AppointmentConflictDetected;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\AppointmentConflictException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Exception\PatientNotFoundException;
use DentalOffice\AppointmentSchedulingBundle\Domain\Service\AppointmentConflictChecker;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentDate;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\Testooo;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\DoctrineAppointmentConflictChecker;

class AppointmentScheduledProcessor implements ProcessorInterface
{
        // providers
    public function __construct(

        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private ScheduleAppointmentHandlerInterface $handler,
        private AppointmentConflictChecker $appointmentConflictChecker,
        private Security $security, 
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager,
        private DoctrineAppointmentConflictChecker $conflictChecker
    )
    {
        
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {

        
        $request = $context["request"];

        $json = json_decode($request->getContent(), true);

        $timeSlot = new TimeSlot(
            new \DateTimeImmutable($json['start']),
            new \DateTimeImmutable($json['end'])
        );

        $user = $this->security->getUser();

        $patientId =  $uriVariables["patientId"];
       
        $patient = $this->entityManager
            ->getRepository(Patient::class)
            ->find($patientId);

        if (!$patient) {
            throw new PatientNotFoundException($patientId);
        }
        $practitionerId = PractitionerId::fromInt((int) $user->getId());

        $this->conflictChecker->assertNoConflict($practitionerId, $timeSlot);
      
        $patient = $this->entityManager->getRepository(Patient::class)
                   ->findOneBy(['id'=>$patientId] );
       
        $purposeId =  PurposeId::fromString($json['purpose']);

        $createdAt = AppointmentDate::fromDateFrom(new \DateTimeImmutable());
        
        
        $appointment = Appointment::book(
            PatientId::fromInt((int) $patientId),
            $timeSlot,
            PractitionerId::fromInt( (int) $user->getId()),
            $purposeId,
            AppointmentStatus::scheduled()
        );

        
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
        
       
        $this->persistProcessor->process($orm, $operation, $uriVariables, $context);

        return $orm;
        
    }
}


