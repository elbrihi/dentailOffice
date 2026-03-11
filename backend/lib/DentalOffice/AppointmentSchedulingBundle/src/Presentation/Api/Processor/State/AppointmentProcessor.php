<?php

namespace DentalOffice\AppointmentSchedulingBundle\Presentation\Api\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DentalOffice\AppointmentSchedulingBundle\Application\Handler\ScheduleAppointmentHandlerInterface;
use DentalOffice\AppointmentSchedulingBundle\Domain\Service\AppointmentConflictChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppointmentProcessor implements ProcessorInterface 
{
    public function __construct(
                #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        protected ProcessorInterface $persistProcessor,
        protected ScheduleAppointmentHandlerInterface $handler,
        protected AppointmentConflictChecker $appointmentConflictChecker,
        protected Security $security, 
        protected ClockInterface $clock,
        protected EntityManagerInterface $entityManager
    )
    {
       
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // Handle the state
    }
}
