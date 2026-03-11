<?php

namespace DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DateTimeImmutable;
use DentalOffice\AppointmentSchedulingBundle\Application\Handler\ScheduleAppointmentHandlerInterface;
use DentalOffice\AppointmentSchedulingBundle\Domain\Aggregate\Appointment;
use DentalOffice\AppointmentSchedulingBundle\Domain\Service\AppointmentConflictChecker;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\AppointmentStatus;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PatientId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PractitionerId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\PurposeId;
use DentalOffice\AppointmentSchedulingBundle\Domain\ValueObject\TimeSlot;
use DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Entity\AppointmentOrmEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppointmentConfirmProcessor implements ProcessorInterface
{
    public function __construct(
                #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private ScheduleAppointmentHandlerInterface $handler,
        private AppointmentConflictChecker $appointmentConflictChecker,
        private Security $security, 
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager
    )
    {
       
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AppointmentOrmEntity
    {
        $request = $context["request"];

        $appointmentId = $uriVariables["appointmentId"];

        $orm =  $this->entityManager->getRepository(AppointmentOrmEntity::class)
                        ->findOneBy(
                        ["id" => $appointmentId]
                    );
       
        $timeSlot = new TimeSlot( 
                    $orm->getStart() ,
                    $orm->getEnd()
        );
    

       
        PatientId::fromInt($orm->getPatient()->getId());
        
        PractitionerId::fromInt($orm->getUser()->getId());

        PurposeId::fromString($orm->getReason());

        $appointment = Appointment::book(

           PatientId::fromInt($orm->getPatient()->getId()),
           $timeSlot,
           PractitionerId::fromInt($orm->getUser()->getId()),
           PurposeId::fromString($orm->getReason()),
           AppointmentStatus::confirmed($orm->getStatus())

        );
        
        $orm->setStatus($appointment->getStatus()->getStatus());

        $this->persistProcessor->process($orm, $operation, $uriVariables, $context);

        return  $orm;
        
        

    }
}
