<?php

namespace DentalOffice\AppointmentSchedulingBundle\Infrastructure\Persistence\Doctrine\Processor\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DentalOffice\AppointmentSchedulingBundle\Application\Event\VisitCreatedEvent;
use DentalOffice\AppointmentSchedulingBundle\Domain\Entity\Visit;
use DentalOffice\InvoiceBundle\Application\Event\InvoiceUpdatedEvent;
use DentalOffice\MedicalRecordBundle\Domain\Entity\MedicalRecord;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VisitDeleteProcessor implements ProcessorInterface
{
       // providers
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private Security $security, 
        private EntityManagerInterface $entityManager ,
        private ClockInterface $clock,
        private EventDispatcherInterface $dispatcher

        
    )
    {
        
    }
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $visitId = $uriVariables['visitId'] ?? null;

        if (!$visitId) {
            throw new BadRequestHttpException('Missing visitId.');
        }

        $visit = $this->entityManager->getRepository(Visit::class)->find($visitId);
        if (!$visit) {
            throw new NotFoundHttpException('Visit not found.');
        }

        $medicalRecord = $visit->getMedicalRecord();

        $medicalRecordId = $medicalRecord->getId();
        


        $this->removeProcessor->process($visit, $operation, $uriVariables, $context);

               
        // Dispatch the event
        $event = new VisitCreatedEvent($visit,  $medicalRecordId);
        $this->dispatcher->dispatch($event, VisitCreatedEvent::class);


        $invoiceEvent = new InvoiceUpdatedEvent($medicalRecordId);
        $this->dispatcher->dispatch($invoiceEvent, InvoiceUpdatedEvent::class);
    }
}
